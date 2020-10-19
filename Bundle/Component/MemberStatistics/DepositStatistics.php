<?php
/**
 *  This is commercial software, only users who have purchased a valid license
 *  and accept to the terms of the License Agreement can install and use this
 *  program.
 *
 *  Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 *  versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\MemberStatistics;


use Component\AbstractComponent;
use DateTime;
use Exception;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;

/**
 * Class DepositStatistics
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class DepositStatistics extends \Component\AbstractComponent
{

    /** 통합검색 항목 */
    const COMBINE_SEARCH = [
        'all'       => '=통합검색=', //__('=통합검색=')
        'memId'     => '아이디', //__('아이디')
        'memNm'     => '이름', //__('이름')
        'nickNm'    => '닉네임', //__('닉네임')
        'email'     => '이메일', //__('이메일')
        'phone'     => '전화번호', //__('전화번호')
        'cellPhone' => '휴대폰번호', //__('휴대폰번호')
    ];

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->tableFunctionName = 'tableMemberDeposit';
        $this->tableName = DB_MEMBER_DEPOSIT;
    }

    /**
     * 통계 리스트 조회
     *
     * @param array $params
     * @param int   $offset
     * @param int   $limit
     *
     * @return object
     * @throws Exception
     */
    public function getStatisticsList(array $params, $offset = 0, $limit = 10)
    {
        $util = new JoinStatisticsUtil();
        $util->checkSearchDateTime($params);
        $util->initSearchDateTimeByPeriod($params);

        $lists = $this->lists($params, $offset, $limit);
        $vo = new DepositVO();
        foreach ($lists as $index => $item) {
            $vo->setArrData($item);
        }

        return $vo;
    }

    /**
     * 리스트 조회 함수
     *
     * @param array $requestParams
     * @param       $offset
     * @param       $limit
     *
     * @return array
     */
    public function lists(array $requestParams, $offset, $limit)
    {
        $arrBind = $arrWhere = [];

        $this->db->bindParameterByKeyword(self::COMBINE_SEARCH, $requestParams, $arrBind, $arrWhere, 'tableMember', 'm');

        /**
         * 검색기간의 년월 값을 설정
         * @var DateTime[] $arrSearchDt
         */
        $arrSearchDt = $requestParams['searchDt'];
        $requestParams['regDt'] = [
            $arrSearchDt[0]->format('Ymd'),
            $arrSearchDt[1]->format('Ymd'),
        ];
        $this->db->bindParameterByDateTimeRange('regDt', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, 'md');

        // 시간 선택 조건 설정
        if (!empty($requestParams['searchTime'])) {
            $arrWhere[] = ' HOUR(md.regDt) = ?';
            $this->db->bind_param_push($arrBind, 's', $requestParams['searchTime']);
        }
        // 요일 선택 조건 설정
        if (!empty($requestParams['searchWeek'])) {
            $arrWhere[] = ' DAYOFWEEK(md.regDt) = ?';
            $this->db->bind_param_push($arrBind, 's', $requestParams['searchWeek']);
        }
        // 회원등급 선택 조건 설정
        if (!empty($requestParams['groupSno'])) {
            $arrWhere[] = ' m.groupSno = ?';
            $this->db->bind_param_push($arrBind, 'i', $requestParams['groupSno']);
        }

        $this->db->strField = 'm.memNo, m.memId, m.memNm, m.deposit,';
        $this->db->strField .= ' COUNT(IF(md.deposit > 0, 1, null)) AS addCount, SUM(IF(md.deposit > 0, md.deposit, null)) AS addDeposit,';
        $this->db->strField .= ' COUNT(IF(md.deposit < 0, 1, null)) AS removeCount, SUM(IF(md.deposit < 0, md.deposit, null)) AS removeDeposit';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strJoin = ' JOIN ' . DB_MEMBER . ' as m ON md.memNo = m.memNo';
        $this->db->strGroup = ' md.memNo';
        $this->db->strOrder = ' m.deposit DESC, m.memId, m.memNm ASC';
        if (is_null($offset) === false && is_null($limit) === false) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER_DEPOSIT . ' as md ' . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        unset($arrBind, $arrWhere, $arrQuery);

        return $resultSet;
    }

    /**
     * 테이블의 Body html을 생성
     *
     * @param object $vo
     *
     * @return string
     */
    public function makeTable($vo)
    {
        $htmlList = [];

        $arrData = $vo->getArrData();
        if ($arrData > 0) {
            $rank = 1;
            /**
             * @var \Bundle\Component\MemberStatistics\DepositVO $item
             */
            foreach ($arrData as $index => $item) {
                $htmlList[] = '<tr class="nowrap text-right">';
                $htmlList[] = '<td class="font-num">' . $rank . '</td>';
                $htmlList[] = '<td class="text-center">' . $item->getMemId() . '/' . $item->getMemNm() . '</td>';
                $htmlList[] = '<td class="font-num">' . StringUtils::strIsSet(NumberUtils::moneyFormat($item->getDeposit()), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . StringUtils::strIsSet($item->getAddCount(), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . StringUtils::strIsSet(NumberUtils::moneyFormat($item->getAddDeposit()), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . StringUtils::strIsSet($item->getRemoveCount(), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . StringUtils::strIsSet(NumberUtils::moneyFormat(($item->getRemoveDeposit() * -1)), '-') . '</td>';
                $htmlList[] = '</tr>';
                $rank++;
            }
        } else {
            return '<tr><td class="no-data" colspan="7">'.__('통계 정보가 없습니다.').'</td></tr>';
        }

        return join('', $htmlList);
    }
}
