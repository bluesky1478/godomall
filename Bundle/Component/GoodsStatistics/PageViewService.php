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

use Component\Design\SkinDesign;
use Component\MemberStatistics\JoinStatisticsUtil;
use DateTime;
use Exception;
use Framework\Object\SimpleStorage;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;

/**
 * Class 상품분석의 페이지뷰 분석 클래스
 * @package Bundle\Component\GoodsStatistics
 * @author  yjwee
 */
class PageViewService
{
    private $dao;

    public function __construct(PageViewDAO $dao = null)
    {
        if ($dao === null) {
            $dao = new PageViewDAO();
        }
        $this->dao = $dao;
    }

    public function savePageView(PageViewParser $parser)
    {
        foreach ($parser->getPageViews() as $index => $pageView) {
            $storage = new SimpleStorage();
            $storage->set('pageUrl', $index);
            $storage->set('pageViewCount', $pageView['viewCount']);
            $storage->set('pageViewSec', $pageView['viewSeconds']);
            $storage->set('viewDate', $pageView['viewDate']);
            $storage->set('mallSno', $pageView['mallSno']);
            if ($this->hasPageUrlByViewDate($index, $pageView['viewDate']) == false) {
                $this->dao->insertPageView($storage->all());
            }
        }

        foreach ($parser->getStartPageViews() as $mallSno => $startPageVal) {
            $startPageView = [
                'mallSno'    => $mallSno,
                'pageUrl'    => key($startPageVal),
                'viewDate'   => $parser->getLogDate(),
                'startCount' => current($startPageVal),
            ];
            $this->dao->updateStartPageView($startPageView);
        }

        foreach ($parser->getEndPageViews() as $mallSno => $endPageVal) {
            $endPageView = [
                'mallSno'    => $mallSno,
                'pageUrl'    => key($endPageVal),
                'viewDate' => $parser->getLogDate(),
                'endCount' => current($endPageVal),
            ];
            $this->dao->updateEndPageView($endPageView);
        }
    }

    /**
     * url, viewDate 기준으로 데이터를 조회하는 함수
     *
     * @param      $url
     *
     * @param      $viewDate
     *
     * @return array|object
     */
    public function getPageUrl($url, $viewDate)
    {
        return $this->dao->selectPageUrl($url, $viewDate);
    }

    public function lists(array $requestParams, $offset = null, $limit = null)
    {
        $util = new JoinStatisticsUtil();
        $util->checkSearchDateTime($requestParams);
        // 검색기간이 설정되지 않은 경우 7일을 기준으로 조회
        if (StringUtils::strIsSet($requestParams['searchDt'], '') === '') {

            $requestParams['searchDt'] = DateTimeUtils::getBetweenDateTime('-7 days');
        } else {
            $requestParams['searchDt'][0] = new DateTime($requestParams['searchDt'][0], DateTimeUtils::getTimeZone());
            $requestParams['searchDt'][1] = new DateTime($requestParams['searchDt'][1], DateTimeUtils::getTimeZone());
        }

        /**
         * 검색기간의 년월 값을 설정
         * @var DateTime[] $arrSearchDt
         */
        $arrSearchDt = $requestParams['searchDt'];
        $requestParams['viewDate'] = [
            $arrSearchDt[0]->format('Ymd'),
            $arrSearchDt[1]->format('Ymd'),
        ];

        return $this->dao->lists($requestParams, $offset, $limit);
    }

    public function getTotal($column, $requestParams)
    {
        return $this->dao->getTotal($column, $requestParams);
    }

    /**
     * listsByStart
     *
     * @param array $params
     * @param null  $offset
     * @param null  $limit
     *
     * @return array|object
     * @throws Exception
     */
    public function listsByStart(array $params, $offset = null, $limit = null)
    {
        $util = new JoinStatisticsUtil();
        $util->checkSearchDateTime($params);
        $util->initSearchDateTimeByPeriod($params);

        /**
         * 검색기간의 년월 값을 설정
         * @var DateTime[] $arrSearchDt
         */
        $arrSearchDt = $params['searchDt'];
        $params['viewDate'] = [
            $arrSearchDt[0]->format('Ymd'),
            $arrSearchDt[1]->format('Ymd'),
        ];

        return $this->dao->listsByStart($params, $offset, $limit);
    }

    public function listsByEnd(array $requestParams, $offset = null, $limit = null)
    {
        $util = new JoinStatisticsUtil();
        $util->checkSearchDateTime($requestParams);
        // 검색기간이 설정되지 않은 경우 7일을 기준으로 조회
        if (StringUtils::strIsSet($requestParams['searchDt'], '') === '') {
            $requestParams['searchDt'] = DateTimeUtils::getBetweenDateTime('-7 days');
        } else {
            $requestParams['searchDt'][0] = new DateTime($requestParams['searchDt'][0], DateTimeUtils::getTimeZone());
            $requestParams['searchDt'][1] = new DateTime($requestParams['searchDt'][1], DateTimeUtils::getTimeZone());
        }

        /**
         * 검색기간의 년월 값을 설정
         * @var DateTime[] $arrSearchDt
         */
        $arrSearchDt = $requestParams['searchDt'];
        $requestParams['viewDate'] = [
            $arrSearchDt[0]->format('Ymd'),
            $arrSearchDt[1]->format('Ymd'),
        ];

        return $this->dao->listsByEnd($requestParams, $offset, $limit);
    }

    public function hasPageUrlByViewDate($pageUrl, $viewDate)
    {
        return empty($this->dao->selectPageUrl($pageUrl, $viewDate)) == false;
    }

    /**
     * getPageNameLists
     * 통계 - 페이지 뷰에서 페이지 명을 추가 - 파일 안에 등록된 글로 처리함
     *
     * @param $lists
     *
     * @return mixed
     *
     * @author su
     */
    public function getPageNameLists($lists)
    {
        $skinDesign = new SkinDesign();
        foreach ($lists as $designKey => $designVal) {
            $pageUrlArr = explode(DS, $designVal['pageUrl']);
            foreach ($pageUrlArr as $pageArrKey => $pageArrVal) {
                if (!$pageArrVal) {
                    unset($pageUrlArr[$pageArrKey]);
                }
                $pageUrlArr[$pageArrKey] = str_replace('.php', '.html', $pageArrVal);
            }
            $designVal['pageUrl'] = implode(DS, $pageUrlArr);
            try {
                $pageFileData = $skinDesign->getDesignFileTextName($designVal['pageUrl']);
                $lists[$designKey]['text'] = $pageFileData['text'];
            } catch (Exception $e) {
                $lists[$designKey]['text'] = '';
            }
        }

        return $lists;
    }
}
