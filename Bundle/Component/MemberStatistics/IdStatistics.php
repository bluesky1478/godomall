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

namespace Bundle\Component\MemberStatistics;

use DateTime;
use Exception;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use Logger;
use Request;

/**
 * Class 회원 분석 클래스
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class IdStatistics extends \Component\MemberStatistics\AbstractStatistics
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->member->setTableFunctionName('tableMemberStatisticsDay');
        $this->member->setTableName(DB_MEMBER_STATISTICS_ID);
    }

    /**
     * scheduleStatistics
     *
     */
    public function scheduleStatistics()
    {
        $list = $this->listsByOrder();
        $list2 = $this->listsByVisit();
        $list = array_merge($list, $list2);
        if (count($list) > 0) {
            $list = $this->statisticsData($list);
            $this->member->save($list);
        } else {
            Logger::info(__METHOD__ . ' join member zero');
        }
    }


    /**
     * 통계 데이터를 위해 주문관련 테이블의 정보를 조회하는 함수
     *
     * @return mixed
     */
    public function listsByOrder()
    {
        $date = new DateTime();
        $yesterday = $date->modify('-1 day')->format('Y-m-d');

        $this->db->strField = 'o.memNo, m.memId, o.settlePrice, o.orderTypeFl, o.paymentDt as regDt';
        $this->db->strWhere = 'o.paymentDt LIKE \'' . $yesterday . '%\'';
        $this->db->strWhere .= ' AND o.memNo > 0';
        $this->db->strJoin = 'JOIN es_member AS m ON o.memNo= m.memNo';
        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_ORDER . ' as o' . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query);

        unset($arrBind, $arrWhere, $arrQuery);

        return $resultSet;
    }

    /**
     * 통계 데이터를 위해 방문자관련 테이블의 정보를 조회하는 함수
     *
     * @return mixed
     */
    public function listsByVisit()
    {
        $date = new DateTime();
        $yesterday = $date->modify('-1 day')->format('Y-m-d');

        $this->db->strField = 'vs.memNo, m.memId, vs.visitPageView, vs.visitOS, vs.regDt';
        $this->db->strWhere = 'vs.regDt LIKE \'' . $yesterday . '%\'';
        $this->db->strWhere .= ' AND vs.memNo > 0';
        $this->db->strJoin = 'JOIN es_member AS m ON vs.memNo= m.memNo';
        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_VISIT_STATISTICS . ' as vs ' . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query);

        unset($arrBind, $arrWhere, $arrQuery);

        return $resultSet;
    }

    /**
     * @inheritDoc
     */
    public function statisticsData(array $list)
    {
        $result = [];
        foreach ($list as $index => $item) {
            $regDt = new DateTime($item['regDt']);
            $regYm = $regDt->format('Ym');
            $regD = $regDt->format('j');
            $hasSettlePrice = key_exists('settlePrice', $item);
            $hasVisitPageView = key_exists('visitPageView', $item);
            $visitOS = gd_isset($item['visitOS'], '');
            $orderTypeFl = gd_isset($item['orderTypeFl'], 'pc');
            $settlePrice = gd_isset($item['settlePrice'], 0);
            $visitPageView = gd_isset($item['visitPageView'], 0);
            $member = $result[$regYm][$regD][$item['memId']];
            if ($orderTypeFl === 'mobile' && $hasSettlePrice) {
                $member['settlePriceMobile'] += $settlePrice;
                $member['orderCountMobile']++;
            } else if ($hasSettlePrice) {
                $member['settlePricePc'] += $settlePrice;
                $member['orderCountPc']++;
            }
            if ($hasVisitPageView && ($visitOS === 'Android' || $visitOS === 'iPhone')) {
                $member['visitPageViewMobile'] += $visitPageView;
                $member['visitMobile']++;
            } else if ($hasVisitPageView) {
                $member['visitPageViewPc'] += $visitPageView;
                $member['visitPc']++;
            }
            $result[$regYm][$regD][$item['memId']] = $member;
        }

        /*
       * Daily Array Data to Json
       */
        foreach ($result as $joinYm => &$arrDay) {
            foreach ($arrDay as $day => &$arrData) {
                $arrData = json_encode($arrData);
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getStatisticsList(array $params, $offset = 0, $limit = 20)
    {
        $util = new JoinStatisticsUtil();
        $util->checkSearchDateTime($params);
        $util->initSearchDateTimeByPeriod($params);

        $lists = $this->member->lists($params, $offset, $limit);
        $vo = new IdVO();
        foreach ($lists as $index => $item) {
            $vo->setEntryDt($item['joinYM']);
            $vo->setSearchDt($params['searchDt']);
            unset($item['joinYM'], $item['regDt'], $item['modDt']);
            $vo->setArrData($item);
        }

        return $vo;
    }

    /**
     * @inheritDoc
     */
    public function getChartJsonData($vo)
    {
        // no chart
    }

    /**
     * @inheritDoc
     *
     * @param \Bundle\Component\MemberStatistics\IdVO $vo
     */
    public function makeTable($vo)
    {
        $htmlList = [];

        $arrData = $vo->getArrData();
        if ($arrData > 0) {
            $rank = 1;
            /**
             * @var \Bundle\Component\MemberStatistics\IdVO $item
             */
            foreach ($arrData as $index => $item) {
                $htmlList[] = '<tr class="nowrap text-center">';
                $htmlList[] = '<td class="font-num">' . $rank . '</td>';
                $htmlList[] = '<td class="font-num">' . $index . '</td>';
                $htmlList[] = '<td class="font-num ">' . gd_isset(number_format($item->getOrderCount()), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . gd_isset(number_format($item->getOderCountPc()), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . gd_isset(number_format($item->getOrderCountMobile()), '-') . '</td>';
                $htmlList[] = '<td class="font-num ">' . gd_isset(number_format($item->getSettlePrice()), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . gd_isset(number_format($item->getSettlePricePc()), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . gd_isset(number_format($item->getSettlePriceMobile()), '-') . '</td>';
                $htmlList[] = '<td class="font-num ">' . gd_isset(number_format($item->getVisitPageView()), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . gd_isset(number_format($item->getVisitPageViewPc()), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . gd_isset(number_format($item->getVisitPageViewMobile()), '-') . '</td>';
                $htmlList[] = '<td class="font-num ">' . gd_isset(number_format($item->getVisit()), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . gd_isset(number_format($item->getVisitPc()), '-') . '</td>';
                $htmlList[] = '<td class="font-num">' . gd_isset(number_format($item->getVisitMobile()), '-') . '</td>';
                $htmlList[] = '</tr>';
                $rank++;
            }
        } else {
            return '<tr><td class="no-data" colspan="14">'.__('통계 정보가 없습니다.').'</td></tr>';
        }

        return join('', $htmlList);
    }


}
