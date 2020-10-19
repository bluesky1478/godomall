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
use Request;

/**
 * Class MileageStatistics
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class MileageStatistics extends \Component\AbstractComponent
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
        $this->tableFunctionName = 'tableMemberMileage';
        $this->tableName = DB_MEMBER_MILEAGE;
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
    public function getStatisticsList(array $params, $offset = 0, $limit = 20)
    {
        $util = new JoinStatisticsUtil();
        $util->checkSearchDateTime($params);
        $util->initSearchDateTimeByPeriod($params);

        $lists = $this->lists($params, $offset, $limit);
        $vo = new MileageVO();
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
        $this->db->bindParameterByDateTimeRange('regDt', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, 'mm');

        // 시간 선택 조건 설정
        if (!empty($requestParams['searchTime'])) {
            $arrWhere[] = ' HOUR(mm.regDt) = ?';
            $this->db->bind_param_push($arrBind, 's', $requestParams['searchTime']);
        }
        // 요일 선택 조건 설정
        if (!empty($requestParams['searchWeek'])) {
            $arrWhere[] = ' DAYOFWEEK(mm.regDt) = ?';
            $this->db->bind_param_push($arrBind, 's', $requestParams['searchWeek']);
        }
        // 회원등급 선택 조건 설정
        if (!empty($requestParams['groupSno'])) {
            $arrWhere[] = ' m.groupSno = ?';
            $this->db->bind_param_push($arrBind, 'i', $requestParams['groupSno']);
        }

        $this->db->strField = 'm.memNo, m.memId, m.memNm, m.mileage,';
        $this->db->strField .= ' COUNT(IF(mm.mileage > 0, 1, null)) AS addCount, SUM(IF(mm.mileage > 0, mm.mileage, null)) AS addMileage,';
        $this->db->strField .= ' COUNT(IF(mm.mileage < 0, 1, null)) AS removeCount, SUM(IF(mm.mileage < 0, mm.mileage, null)) AS removeMileage,';
        $this->db->strField .= ' SUM(IF(mm.deleteScheduleDt != \'9999-12-31\' AND mm.deleteScheduleDt > \'0000-00-00\' AND mm.mileage > 0 AND (mm.deleteFl = \'n\' OR mm.deleteFl=\'use\'), mm.mileage, null)) AS deleteScheduleMileage';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strJoin = ' JOIN ' . DB_MEMBER . ' as m ON mm.memNo = m.memNo';
        $this->db->strGroup = ' mm.memNo';
        $this->db->strOrder = ' m.mileage DESC, m.memId, m.memNm ASC';
        if (is_null($offset) === false && is_null($limit) === false) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER_MILEAGE . ' as mm ' . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        unset($arrBind, $arrWhere, $arrQuery);

        return $resultSet;
    }

    /**
     * 화면에 보여질 테이블 html 생성
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
             * @var \Bundle\Component\MemberStatistics\MileageVO $item
             */
            foreach ($arrData as $index => $item) {
                $htmlList[] = '<tr class="nowrap text-right">';
                $htmlList[] = '<td class="font-num">' . $rank . '</td>';
                $htmlList[] = '<td class="text-center">' . $item->getMemId() . '/' . $item->getMemNm() . '</td>';
                $htmlList[] = '<td class="font-num">' . gd_isset(NumberUtils::moneyFormat($item->getMileage()), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . gd_isset($item->getAddCount(), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . gd_isset(NumberUtils::moneyFormat($item->getAddMileage()), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . gd_isset($item->getRemoveCount(), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . gd_isset(NumberUtils::moneyFormat(($item->getRemoveMileage() * -1)), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . gd_isset(NumberUtils::moneyFormat($item->getDeleteScheduleMileage()), '-') . '</td>';
                $htmlList[] = '</tr>';
                $rank++;
            }
        } else {
            return '<tr><td class="no-data" colspan="8">'.__('통계 정보가 없습니다.').'</td></tr>';
        }

        return join('', $htmlList);
    }

    /**
     * 통계 데이터를 위해 마일리지관련 테이블의 정보를 조회하는 함수
     *
     * @return array|object
     */
    public function listsByMileage()
    {
        $date = new DateTime();
        $yesterday = $date->modify('-1 day')->format('Y-m-d');

        $this->db->strField = 'mm.memNo, m.memId, m.memNm, m.mileage, mm.mileage as giveMileage, mm.deleteFl, mm.deleteScheduleDt, mm.deleteDt, mm.regDt';
        $this->db->strWhere = 'mm.regDt LIKE \'' . $yesterday . '%\'';
        if (Request::getRemoteAddress() === '127.0.0.1') {
            $this->db->strWhere = 'mm.regDt LIKE \'2016-03-%\'';
        }
        $this->db->strWhere .= ' AND mm.memNo > 0';
        $this->db->strJoin = 'JOIN ' . DB_MEMBER . ' AS m ON mm.memNo = m.memNo';
        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER_MILEAGE . ' as mm' . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query);

        unset($arrBind, $arrWhere, $arrQuery);

        return $resultSet;
    }
}
