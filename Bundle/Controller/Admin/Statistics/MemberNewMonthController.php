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
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Statistics;

use Component\Mall\Mall;
use Component\MemberStatistics\MemberStatistics;
use DateTime;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Request;

/**
 * Class 신규회원 통계
 * @package Bundle\Controller\Admin\Statistics
 * @author  su
 */
class MemberNewMonthController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        try {
            $this->callMenu('statistics', 'member', 'newDay');

            $mall = new Mall();
            $searchMallList = $mall->getStatisticsMallList();
            $this->setData('searchMallList', $searchMallList);

            $searchMall = Request::get()->get('mallFl');
            if (!$searchMall) {
                $searchMall = 'all';
            }
            $searchPeriod = Request::get()->get('searchPeriod');
            $searchDate = Request::get()->get('searchDate');

            $sDate = new DateTime();
            $eDate = new DateTime();
            $startDate = new DateTime($searchDate[0]);  // 기간검색 앞 날짜
            $endDate = new DateTime($searchDate[1]);

            if (!$searchDate[0]) {
                $date = $sDate->format('d');
                $searchDate[0] = $sDate->modify('-' . ($date - 1) . ' days')->format('Ymd');
            } else {
                if ($sDate->format('Ymd') <= $startDate->format('Ymd')) {
                    $date = $sDate->format('d');
                    $searchDate[0] = $sDate->modify('-' . ($date - 1) . ' days')->format('Ymd');
                } else {
                    $date = $startDate->format('d');
                    $searchDate[0] = $startDate->modify('-' . ($date - 1) . ' days')->format('Ymd');
                }
            }
            if (!$searchDate[1]) {
                $searchDate[1] = $eDate->format('Ymd');
            } else {
                if ($eDate->format('Ymd') <= $endDate->format('Ymd') || $endDate->format('Ym') == $eDate->format('Ym')) {
                    $searchDate[1] = $eDate->format('Ymd');
                } else {
                    $date = $endDate->format('d');
                    $searchDate[1] = $endDate->add(new \DateInterval('P1M'))->modify('-' . $date . ' days')->format('Ymd');
                }
            }

            $sDate = new DateTime($searchDate[0]);
            $eDate = new DateTime($searchDate[1]);
            $dateDiff = date_diff($sDate, $eDate);
            if ($dateDiff->days > 360) {
                $date = $eDate->format('d');
                $searchDate[0] = $eDate->modify('-' . ($date - 1) . ' days')->format('Ymd');
                $searchPeriod = 0;
            }

            $checked['searchMall'][$searchMall] = 'checked="checked"';
            $checked['searchPeriod'][$searchPeriod] = 'checked="checked"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('checked', $checked);
            $this->setData('active', $active);

            $memberStatistics = new MemberStatistics();

            $getDataArr = $memberStatistics->getMemberNewMonth($searchDate, $searchMall);

            // 통계 데이터 일자별 처리 - 빈 일자 생성
            $startDate = new DateTime($searchDate[0]);
            $endDate = new DateTime($searchDate[1]);
            for ($i = 0; $i <= 12; $i++) {
                $searchDt = $startDate->getTimestamp();
                $searchDt = date('Ym', $searchDt + (30 * 24 * 60 * 60 * $i));
                if ($searchDt <= $endDate->format('Ym')) {
                    $resetData[$searchDt]['pc'] = 0;
                    $resetData[$searchDt]['mobile'] = 0;
                    $resetData[$searchDt]['etc'] = 0;
                }
            }
            $getDataArr = $getDataArr + $resetData;
            ksort($getDataArr);

            // 통계 합계
            $memberTotal = [];

            // 통계 그래프
            $memberChart = [];
            $memberDate = array_keys($getDataArr);
            $memberPC = array_column($getDataArr, 'pc');
            $memberMobile = array_column($getDataArr, 'mobile');
            $memberEtc = array_column($getDataArr, 'etc');

            // 통계 엑셀 데이터
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                $returnMemberStatistics[$i]['memberDate'] = substr($key, 0, 4) . '-' . substr($key, 4, 2);
                $total = $val['pc'] + $val['mobile'] + $val['etc'];
                $returnMemberStatistics[$i]['newTotal'] = $total;
                $returnMemberStatistics[$i]['newPc'] = $val['pc'];
                $returnMemberStatistics[$i]['newMobile'] = $val['mobile'];
                $returnMemberStatistics[$i]['newEtc'] = $val['etc'];
                if ($total > 0) {
                    if ($val['pc'] > 0) {
                        $pcPercent = round(($val['pc'] / $total) * 100);
                    } else {
                        $pcPercent = 0;
                    }
                    $mobilePercent = 100 - $pcPercent;
                } else {
                    $pcPercent = 0;
                    $mobilePercent = 0;
                }
                $returnMemberStatistics[$i]['newPercent'] = "<div class='progress'><div class='progress-bar progress-bar-info' style='width:" . $pcPercent . "%'><strong class='text-black'>" . $pcPercent . "%</strong></div><div class='progress-bar progress-bar-success' style='width:" . $mobilePercent . "%'><strong class='text-black'>" . $mobilePercent . "%</strong></div></div>";

                // 통계 그래프
                $memberChart['MemberTotal'][$i] = $total;

                if ($memberTotal['min'] >= $total) {
                    $memberTotal['min'] = $total;
                    $memberTotal['minDate'] = substr($key, 0, 4) . '-' . substr($key, 4, 2);
                }

                if ($memberTotal['max'] <= $total) {
                    $memberTotal['max'] = $total;
                    $memberTotal['maxDate'] = substr($key, 0, 4) . '-' . substr($key, 4, 2);
                }
                $i++;
            }

            // 통계 합계
            $memberTotal['pc'] = array_sum($memberPC);
            $memberTotal['mobile'] = array_sum($memberMobile);
            $memberTotal['etc'] = array_sum($memberEtc);
            $memberTotal['total'] = array_sum($memberChart['MemberTotal']);

            // 통계 그래프
            foreach ($memberDate as $key => $val) {
                $memberDate[$key] = "'" . $val . "'";
            }
            $memberChart['Date'] = $memberDate;
            $memberChart['MemberPC'] = $memberPC;
            $memberChart['MemberMobile'] = $memberMobile;
            $memberChart['MemberEtc'] = $memberEtc;
            if (count($memberChart['Date']) < 2) {
                $emptyDateArr = ['0' => "'0'"];
                $emptyArr = ['0' => 0];
                $memberChart['Date'] = array_merge($emptyDateArr, $memberChart['Date']);
                $memberChart['MemberPC'] = array_merge($emptyArr, $memberChart['MemberPC']);
                $memberChart['MemberMobile'] = array_merge($emptyArr, $memberChart['MemberMobile']);
                $memberChart['MemberEtc'] = array_merge($emptyArr, $memberChart['MemberEtc']);
                $memberChart['MemberTotal'] = array_merge($emptyArr, $memberChart['MemberTotal']);
            }

            $memberCount = count($returnMemberStatistics);
            if ($memberCount > 20) {
                $rowDisplay = 20;
            } else if ($memberCount == 0) {
                $rowDisplay = 5;
            } else {
                $rowDisplay = $memberCount;
            }

            $this->setData('rowList', json_encode($returnMemberStatistics));
            $this->setData('memberCount', $memberCount);
            $this->setData('rowDisplay', $rowDisplay);
            $this->setData('memberChart', $memberChart);
            $this->setData('memberTotal', $memberTotal);

            $this->addScript(
                [
                    'backbone/backbone-min.js',
                    'tui/code-snippet.min.js',
                    'raphael/effects.min.js',
                    'raphael/raphael-min.js',
                    'tui.chart-master/chart.min.js',
                    'tui.grid/grid.min.js',
                ]
            );

            $this->addCss(
                [
                    'chart.css',
                    'tui.grid/grid.css',
                ]
            );
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }

    }
}
