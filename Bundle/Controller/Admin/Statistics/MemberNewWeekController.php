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
class MemberNewWeekController extends \Controller\Admin\Controller
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
            if (!$searchDate[0]) {
                $searchDate[0] = $sDate->modify('-6 days')->format('Ymd');
            } else {
                $startDate = new DateTime($searchDate[0]);
                if ($sDate->format('Ymd') <= $startDate->format('Ymd')) {
                    $searchDate[0] = $sDate->format('Ymd');
                } else {
                    $searchDate[0] = $startDate->format('Ymd');
                }
            }
            if (!$searchDate[1]) {
                $searchDate[1] = $eDate->format('Ymd');
            } else {
                $endDate = new DateTime($searchDate[1]);
                if ($eDate->format('Ymd') <= $endDate->format('Ymd') || (strlen($searchDate[1]) < 10) && $endDate->format('Ym') == $eDate->format('Ym')) {
                    $searchDate[1] = $eDate->format('Ymd');
                } else {
                    if (strlen($searchDate[1]) == 10) {
                        $searchDate[1] = $endDate->format('Ymd');
                    } else {
                        $date = $endDate->format('d');
                        $searchDate[1] = $endDate->add(new \DateInterval('P1M'))->modify('-' . $date . ' days')->format('Ymd');
                    }
                }
            }

            $sDate = new DateTime($searchDate[0]);
            $eDate = new DateTime($searchDate[1]);
            $dateDiff = date_diff($sDate, $eDate);
            if ($dateDiff->days > 90) {
                $sDate = $eDate->modify('-6 day');
                $searchDate[0] = $sDate->format('Ymd');
                $searchPeriod = 6;
            }

            $checked['searchMall'][$searchMall] = 'checked="checked"';
            $checked['searchPeriod'][$searchPeriod] = 'checked="checked"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('checked', $checked);
            $this->setData('active', $active);

            $memberStatistics = new MemberStatistics();
            $getDataArr = $memberStatistics->getMemberNewWeek($searchDate, $searchMall);
            ksort($getDataArr);

            // 통계 데이터 일자별 처리 - 빈 일자 생성
            $startDate = new DateTime($searchDate[0]);
            $endDate = new DateTime($searchDate[1]);
            $diffDay = $endDate->diff($startDate)->days;
            for ($i = 0; $i <= $diffDay; $i++) {
                $searchDt = new DateTime($searchDate[0]);
                $searchDt = $searchDt->modify('+' . $i . ' day');
                $week = $searchDt->format('w');
                if ($getDataArr[$searchDt->format('Ymd')][$week]['pc'] > 0) {
                    $resetData[$searchDt->format('Ymd')][$week]['pc'] = $getDataArr[$searchDt->format('Ymd')][$week]['pc'];
                } else {
                    $resetData[$searchDt->format('Ymd')][$week]['pc'] = 0;
                }
                if ($getDataArr[$searchDt->format('Ymd')][$week]['mobile'] > 0) {
                    $resetData[$searchDt->format('Ymd')][$week]['mobile'] = $getDataArr[$searchDt->format('Ymd')][$week]['mobile'];
                } else {
                    $resetData[$searchDt->format('Ymd')][$week]['mobile'] = 0;
                }
                if ($getDataArr[$searchDt->format('Ymd')][$week]['etc'] > 0) {
                    $resetData[$searchDt->format('Ymd')][$week]['etc'] = $getDataArr[$searchDt->format('Ymd')][$week]['etc'];
                } else {
                    $resetData[$searchDt->format('Ymd')][$week]['etc'] = 0;
                }
            }
            $getWeekArr = $resetData;
            ksort($getWeekArr);

            // 통계 합계 / 통계 그래프
            $memberTotal = [];
            $memberChart = [];
            $getChangeArr = [];
            $weekKorArr = [__('일요일'), __('월요일'), __('화요일'), __('수요일'), __('목요일'), __('금요일'), __('토요일')];
            foreach ($getWeekArr as $dataKey => $dataVal) {
                foreach ($dataVal as $dKey => $dVal) {
                    $total = array_sum($dVal);
                    $memberTotal['total'] += $total;
                    $memberTotal['pc'] += $dVal['pc'];
                    $memberTotal['mobile'] += $dVal['mobile'];
                    $memberTotal['etc'] += $dVal['etc'];

                    if ($memberTotal['min'] >= $total) {
                        $memberTotal['min'] = $total;
                        $memberTotal['minWeek'] = $weekKorArr[$dKey];
                        $memberTotal['minDate'] = substr($dataKey,0,4) . '-' . substr($dataKey,4,2) . '-' . substr($dataKey,-2);
                    }

                    if ($memberTotal['max'] <= $total) {
                        $memberTotal['max'] = $total;
                        $memberTotal['maxWeek'] = $weekKorArr[$dKey];
                        $memberTotal['maxDate'] = substr($dataKey,0,4) . '-' . substr($dataKey,4,2) . '-' . substr($dataKey,-2);
                    }

                    $memberChart['MemberPC'][$dKey] += $dVal['pc'];
                    $memberChart['MemberMobile'][$dKey] += $dVal['mobile'];
                    $memberChart['MemberEtc'][$dKey] += $dVal['etc'];
                    $memberChart['MemberTotal'][$dKey] += $total;

                    $getChangeArr[$dKey]['memberTotal'] += $total;
                    $getChangeArr[$dKey]['memberPc'] += $dVal['pc'];
                    $getChangeArr[$dKey]['memberMobile'] += $dVal['mobile'];
                    $getChangeArr[$dKey]['memberEtc'] += $dVal['etc'];
                }
            }
            ksort($getChangeArr);

            // 통계 그래프
            for ($i=0; $i<=6; $i++) {
                $memberChart['Date'][] = "'" . $weekKorArr[$i] . "'";
            }
            if (count($memberChart['Date']) < 2) {
                $emptyDateArr = ['0' => "'0'"];
                $emptyArr = ['0' => 0];
                $memberChart['Date'] = array_merge($emptyDateArr, $memberChart['Date']);
                $memberChart['MemberPC'] = array_merge($emptyArr, $memberChart['MemberPC']);
                $memberChart['MemberMobile'] = array_merge($emptyArr, $memberChart['MemberMobile']);
                $memberChart['MemberEtc'] = array_merge($emptyArr, $memberChart['MemberEtc']);
                $memberChart['MemberTotal'] = array_merge($emptyArr, $memberChart['MemberTotal']);
            }

            // 통계 엑셀 데이터
            $i = 0;
            foreach ($getChangeArr as $key => $val) {
                $returnMemberStatistics[$i]['memberDate'] = $weekKorArr[$key];
                $returnMemberStatistics[$i]['newTotal'] = $val['memberTotal'];
                $returnMemberStatistics[$i]['newPc'] = $val['memberPc'];
                $returnMemberStatistics[$i]['newMobile'] = $val['memberMobile'];
                $returnMemberStatistics[$i]['newEtc'] = $val['memberEtc'];
                if ($val['memberTotal'] > 0) {
                    if ($val['memberPc'] > 0) {
                        $pcPercent = round(($val['memberPc'] / $val['memberTotal']) * 100);
                    } else {
                        $pcPercent = 0;
                    }
                    $mobilePercent = 100 - $pcPercent;
                } else {
                    $pcPercent = 0;
                    $mobilePercent = 0;
                }
                $returnMemberStatistics[$i]['newPercent'] = "<div class='progress'><div class='progress-bar progress-bar-info' style='width:". $pcPercent . "%'><strong class='text-black'>". $pcPercent . "%</strong></div><div class='progress-bar progress-bar-success' style='width:". $mobilePercent . "%'><strong class='text-black'>". $mobilePercent . "%</strong></div></div>";
                $i++;
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
