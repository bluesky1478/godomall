<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\GoodsStatistics;

use App;
use Component\Database\DBTableField;
use Component\MemberStatistics\JoinStatisticsUtil;
use Component\Validator\Validator;
use DateTime;
use Exception;
use Framework\Application\Bootstrap\Log;
use Framework\Utility\DateTimeUtils;
use Logger;
use Request;
use Session;

/**
 * Class 상품분석-검색어순위분석
 * @package Bundle\Component\GoodsStatistics
 * @author  yjwee
 */
class SearchWordStatistics
{
    /** @var  \Framework\Database\DBTool $_db */
    private $_db;
    /**
     * @var string 테이블이름
     */
    private $_tableFunctionName = 'tableSearchWordStatistics';

    /**
     * @var array 검색어 순위 분석 대상 uri 폴더명과 파일명으로 구성
     */
    private $whiteList = ['goods' => ['goods_search']];
    /**
     * @var array 검색어 순위 분석 대상에서 제외될 uri 폴더명과 파일명으로 구성
     */
    private $blackList = ['member' => ['logout']];
    /**
     * @var array 검색어 순위 분석 대상에서 제외될 referer
     */
    private $refererBlackList = ['member/login.php'];

    /**
     * SearchWordStatistics constructor.
     */
    public function __construct()
    {
        $this->_db = App::load('DB');
    }

    /**
     * 테이블 데이터 저장
     *
     * @param SearchWordStatisticsVO|null $vo
     *
     * @return SearchWordStatisticsVO|null
     */
    public function save(SearchWordStatisticsVO $vo = null)
    {
        $keyword = Request::get()->get('keyword', Request::post()->get('keyword'));
        $os = Request::isMobile() ? 'mobile' : 'pc';
        if (Session::has(SESSION_GLOBAL_MALL)) {
            $mallSno = Session::get(SESSION_GLOBAL_MALL . '.sno');
        }

        // 상점별 회원 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        if (is_null($vo)) {
            $vo = new SearchWordStatisticsVO();
            $vo->setKeyword($keyword);
            $vo->setOs($os);
            $vo->setMallSno($mallSno);
        }

        // 검색어 검증
        if (Validator::required($vo->getKeyword())) {
            // 일련번호에 따른 추가, 수정 검증
            if (Validator::number($vo->getSno(), 0, null, true) === false || Validator::number($vo->getResultCount(), 0, null, true) === false) {
                $sno = $this->insert($vo);
                $vo->setSno($sno);

                return $vo;
            } else {
                // 상품명이 검색되었을 시 일련번호, 검색결과 검증
                if (Validator::number($vo->getSno(), null, null, true) && Validator::number($vo->getResultCount(), 0, null, true)) {
                    return $this->update($vo);
                }
            }
        }

        return null;
    }

    /**
     * 테이블 데이터 입력
     *
     * @param SearchWordStatisticsVO $vo
     *
     * @return int|string
     */
    public function insert(SearchWordStatisticsVO $vo)
    {
        Logger::info(__METHOD__);
        $arrBind = $this->_db->get_binding(DBTableField::tableSearchWordStatistics(), $vo->toArray(), 'insert');
        $this->_db->set_insert_db(DB_SEARCH_WORD_STATISTICS, $arrBind['param'], $arrBind['bind'], 'y');

        return $this->_db->insert_id();
    }


    /**
     * 테이블 데이터 수정
     * 검색어, os 컬럼 제외
     *
     * @param SearchWordStatisticsVO $vo
     *
     * @return SearchWordStatisticsVO
     */
    public function update(SearchWordStatisticsVO $vo)
    {
        Logger::info(__METHOD__);
        $excludeField = 'mallSno,keyword,os';
        $arrBind = $this->_db->get_binding(DBTableField::tableSearchWordStatistics(), $vo->toArray(), 'update', null, explode(',', $excludeField));
        $this->_db->bind_param_push($arrBind['bind'], 'i', $vo->getSno());
        $this->_db->set_update_db(DB_SEARCH_WORD_STATISTICS, $arrBind['param'], 'sno = ?', $arrBind['bind']);

        return $vo;
    }

    /**
     * 테이블 데이터 조회
     *
     * @param        $sno
     * @param string $column
     *
     * @return array|object
     */
    public function select($sno, $column = '*')
    {
        Logger::info(__METHOD__);
        $arrBind = [];
        $this->_db->bind_param_push($arrBind, 'i', $sno);

        return $this->_db->query_fetch('SELECT ' . $column . ' FROM ' . DB_SEARCH_WORD_STATISTICS . 'WHERE sno=?', $arrBind, false);
    }

    /**
     * 테이블 select count 결과 값 반환
     *
     * @return mixed
     */
    public function getSearchWordRankCount()
    {
        return $this->_db->getCount(DB_SEARCH_WORD_STATISTICS, 'sno');
    }

    /**
     * 테이블 리스트 조회
     *
     * @param array $requestParams
     * @param int   $offset
     * @param int   $limit
     *
     * @return array
     */
    public function lists(array $requestParams, $offset, $limit)
    {
        $arrBind = $arrWhere = [];

        $requestParams['regDt'][0] = new DateTime($requestParams['regDt'][0], DateTimeUtils::getTimeZone());
        $requestParams['regDt'][1] = new DateTime($requestParams['regDt'][1], DateTimeUtils::getTimeZone());

        /**
         * 검색기간의 년월 값을 설정
         * @var DateTime[] $arrSearchDt
         */
        $arrSearchDt = $requestParams['regDt'];
        $requestParams['regDt'] = [
            $arrSearchDt[0]->format('Ymd'),
            $arrSearchDt[1]->format('Ymd'),
        ];

        $this->_db->bindParameterByLike('keyword', $requestParams, $arrBind, $arrWhere, $this->_tableFunctionName);
        $this->_db->bindParameterByDateTimeRange('regDt', $requestParams, $arrBind, $arrWhere, $this->_tableFunctionName);
        if ($requestParams['searchCondition'] == 'goodsName') {
            $arrWhere[] = 'resultCount > 0';
        }

        $this->_db->strField = '* , count(keyword) as keywordCount';
        $this->_db->strWhere = implode(' AND ', $arrWhere);
        $this->_db->strGroup = 'keyword, os';
        $this->_db->strOrder = 'keywordCount DESC';
        if (is_null($offset) === false && is_null($limit) === false) {
            $this->_db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }

        $arrQuery = $this->_db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_SEARCH_WORD_STATISTICS . implode(' ', $arrQuery);
        $resultSet = $this->_db->query_fetch($query, $arrBind, true);
        $result = [];
        foreach ($resultSet as $key => $value) {
            $result[] = new SearchWordStatisticsVO($value);
        }

        unset($arrBind, $arrWhere, $arrQuery, $resultSet);

        return $result;
    }

    /**
     * 테이블 랭킹 리스트 조회
     *
     * @param array $requestParams
     * @param int   $offset
     * @param int   $limit
     *
     * @return array
     * @throws Exception
     */
    public function getSearchWordRankList(array $requestParams, $offset = 0, $limit = 20)
    {
        $util = new JoinStatisticsUtil();
        $util->checkSearchDateTime($requestParams);

        $list = $this->lists($requestParams, $offset, $limit);
        $pcTotal = 0;
        $mobileTotal = 0;
        /**
         * 검색된 데이터의 검색 카운트 수 총합
         * @var SearchWordStatisticsVO $vo
         */
        foreach ($list as $key => $vo) {
            if ($vo->getOs() == 'mobile') {
                $mobileTotal += $vo->getKeywordCount();
            } else {
                $pcTotal += $vo->getKeywordCount();
            }
        }
        // 비율 계산 시 0으로 나뉘어 지는 경우에 대한 방어
        if ($pcTotal < 1) {
            $pcTotal = 1;
        }
        if ($mobileTotal < 1) {
            $mobileTotal = 1;
        }

        $mobileList = $pcList = [];

        /**
         * 검색된 데이터의 검색 카운트의 비율
         * @var SearchWordStatisticsVO $vo
         */
        foreach ($list as $key => $vo) {
            if ($vo->getOs() == 'mobile') {
                $rate = ($vo->getKeywordCount() / $mobileTotal) * 100;
                $vo->setKeywordRate($rate);
                $mobileList[] = $vo;
            } else {
                $rate = ($vo->getKeywordCount() / $pcTotal) * 100;
                $vo->setKeywordRate($rate);
                $pcList[] = $vo;
            }
        }

        $list = [
            'mobile' => $mobileList,
            'pc'     => $pcList,
        ];

        return $list;
    }

    /**
     * 순위분석 데이터를 기준으로 html 테이블 생성
     *
     * @param SearchWordStatisticsVO[] $pcList
     * @param SearchWordStatisticsVO[] $mobileList
     *
     * @return string
     */
    public function makeTableByRankList(array $pcList, array $mobileList)
    {
        $pcCount = count($pcList);
        $mobileCount = count($mobileList);
        $listHtml = [];

        if ($pcCount > 0 || $mobileCount > 0) {
            $defaultCount = $pcCount > $mobileCount ? $pcCount : $mobileCount;
            for ($i = 0; $i < $defaultCount; $i++) {
                $html = '<tr class="center">';
                if (is_null($pcList[$i])) {
                    $html .= '<td colspan="4"></td>';
                } else {
                    $html .= '<td class="font-num">' . ($i + 1) . '</td><td class="">' . $pcList[$i]->getKeyword() . '</td><td class="font-num">' . $pcList[$i]->getKeywordCount() . '</td>';
                    $html .= '<td class="font-num"><div class="progress">';
                    $html .= '<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="' . $pcList[$i]->getKeywordRate() . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $pcList[$i]->getKeywordRate() . '%;">';
                    $html .= '<strong class="text-black">' . $pcList[$i]->getKeywordRate() . '%</strong>';
                    $html .= '</div></div></td>';
                }
                if (is_null($mobileList[$i])) {
                    $html .= '<td colspan="3"></td>';
                } else {
                    $html .= '<td class="">' . $mobileList[$i]->getKeyword() . '</td><td class="font-num">' . $mobileList[$i]->getKeywordCount() . '</td>';
                    $html .= '<td class="font-num"><div class="progress">';
                    $html .= '<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="' . $mobileList[$i]->getKeywordRate() . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $mobileList[$i]->getKeywordRate() . '%;">';
                    $html .= '<strong class="text-black">' . $mobileList[$i]->getKeywordRate() . '%</strong>';
                    $html .= '</div></div></td>';
                }
                $html .= '</tr>';
                $listHtml[] = $html;
            }

            return join('', $listHtml);
        } else {
            return '<tr><td class="no-data" colspan="6">' . __('통계 정보가 없습니다.') . '</td></tr>';
        }
    }

    /**
     * GoodsStatistics 검색어 순위 분석 preHandle 함수
     *
     * @param $isWhite
     *
     * @return bool|SearchWordStatisticsVO|null
     */
    public function preHandle(&$isWhite)
    {
        $infoUri = Request::getInfoUri();
        $referer = Request::getReferer();
        //        Logger::debug(__METHOD__ . ', referer=>' . $referer, $infoUri);

        $dirName = Request::getDirectoryUri();
        $fileName = $infoUri['filename'];

        // 검색어 순위 분석하지 않는 페이지에 대한 필터링
        if (key_exists($dirName, $this->blackList)) {
            if (in_array($fileName, $this->blackList[$dirName])) {
                return false;
            }
        }

        // 검색어 순위 분석하지 않는 페이지에 대한 필터링
        $isReferer = false;
        foreach ($this->refererBlackList as $key => $value) {
            if (strpos($referer, $value) > -1) {
                $isReferer = true;
                break;
            }
        }

        if ($isReferer) {
            return false;
        }

        // 검색어 순위 분석 대상 페이지에 대한 필터링
        if (key_exists($dirName, $this->whiteList)) {
            if (in_array($fileName, $this->whiteList[$dirName])) {
                $isWhite = true;

                return $this->save();
            }
        }

        return null;
    }

    /**
     * GoodsStatistics 검색어 순위 분석 postHandle 함수
     *
     * @param SearchWordStatisticsVO $vo
     * @param array                  $goodsList
     *
     * @return SearchWordStatisticsVO|null
     */
    public function postHandle(SearchWordStatisticsVO $vo, array $goodsList = null)
    {
        $goodsList = $goodsList[0];
        if (is_null($goodsList) === false && is_array($goodsList)) {
            $count = count($goodsList);
            // 검색어 검색 결과가 있을 경우 업데이트(상품명 검색인지 여부 체크)
            if ($count > 0) {
                $vo->setResultCount($count);

                return $this->save($vo);
            }
        }

        return null;
    }
}
