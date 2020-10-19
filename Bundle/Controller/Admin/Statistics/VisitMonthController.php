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

use Component\VisitStatistics\VisitStatistics;
use Component\Mall\Mall;
use DateTime;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ArrayUtils;
use Request;

/**
 * 방문통계 월별
 * @author Seung-gak Kim <surlira@godo.co.kr>
 */
class VisitMonthController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('statistics', 'visit', 'visitDay');

        // 모듈호출
        $visitStatistics = new VisitStatistics();

        try {
            // 2/23 디바이스별 방문자 통계 안내 문구 활성 여부
            $this->setData('noticeFl', $visitStatistics->noticeCommentFl);

            $mall = new Mall();
            $searchMallList = $mall->getStatisticsMallList();
            $this->setData('searchMallList', $searchMallList);

            $searchMall = Request::get()->get('mallFl');
            if (!$searchMall) {
                $searchMall = 'all';
            }
            $searchDevice = Request::get()->get('searchDevice');
            if (!$searchDevice) {
                $searchDevice = 'all';
            }
            $searchPeriod = Request::get()->get('searchPeriod');
            $searchDate = Request::get()->get('searchDate');

            $sDate = new DateTime();
            $eDate = new DateTime();

            $startDate = new DateTime($searchDate[0]);  // 기간검색 앞 날짜
            $endDate = new DateTime($searchDate[1]);

            if (!$searchDate[0]) {
                $date = $sDate->format('d');
                $searchDate[0] = ($date === '01') ? $sDate->sub(new \DateInterval('P1M'))->format('Ymd') : $sDate->modify('-' . ($date - 1) . ' days')->format('Ymd');
            } else {
                if ($sDate->format('Ymd') <= $startDate->format('Ymd')) {   // 기간검색 앞 날짜가 오늘날짜보다 뒤일 때
                    $date = $sDate->format('d');
                    $searchDate[0] = $sDate->modify('-' . ($date - 1) . ' days')->format('Ymd');
                } else {
                    $date = $startDate->format('d');
                    $searchDate[0] = $startDate->modify('-' . ($date - 1) . ' days')->format('Ymd');
                }
            }
            if (!$searchDate[1]) {
                $searchDate[1] = $eDate->modify('-1 days')->format('Ymd');
            } else {
                if ($eDate->format('Ymd') <= $endDate->format('Ymd') || $endDate->format('Ym') == $eDate->format('Ym')) {
                    $searchDate[1] = $eDate->modify('-1 days')->format('Ymd');
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
            $checked['searchDevice'][$searchDevice] = 'selected="selected"';
            $checked['searchPeriod'][$searchPeriod] = 'checked="checked"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('checked', $checked);
            $this->setData('active', $active);

            if ($searchMall == 'all') {
                $getDataArr = $visitStatistics->getVisitMonthAllMall($searchDate);
            } else {
                $getDataArr = $visitStatistics->getVisitMonth($searchDate, $searchMall);
            }
            ksort($getDataArr);

            $i = 0;
            foreach ($getDataArr as $key => $val) {
                if ($i == 0) {
                    $totalVisit['MaxCount'] = $val['visitCount'];
                    $totalVisit['pc']['MaxCount'] = $val['pc']['visitCount'];
                    $totalVisit['mobile']['MaxCount'] = $val['mobile']['visitCount'];
                    $totalVisit['hour']['MaxCount'] = substr($key,0,4) . '-' . substr($key,4,2);
                    $totalVisit['MinCount'] = $val['visitCount'];
                    $totalVisit['pc']['MinCount'] = $val['pc']['visitCount'];
                    $totalVisit['mobile']['MinCount'] = $val['mobile']['visitCount'];
                    $totalVisit['hour']['MinCount'] = substr($key,0,4) . '-' . substr($key,4,2);
                    $totalVisit['MaxPV'] = $val['pv'];
                    $totalVisit['pc']['MaxPV'] = $val['pc']['pv'];
                    $totalVisit['mobile']['MaxPV'] = $val['mobile']['pv'];
                    $totalVisit['hour']['MaxPV'] = substr($key,0,4) . '-' . substr($key,4,2);
                    $totalVisit['MinPV'] = $val['pv'];
                    $totalVisit['pc']['MinPV'] = $val['pc']['pv'];
                    $totalVisit['mobile']['MinPV'] = $val['mobile']['pv'];
                    $totalVisit['hour']['MinPV'] = substr($key,0,4) . '-' . substr($key,4,2);
                }
                if ($totalVisit['MaxCount'] <= $val['visitCount']) {
                    $totalVisit['MaxCount'] = $val['visitCount'];
                    $totalVisit['pc']['MaxCount'] = $val['pc']['visitCount'];
                    $totalVisit['mobile']['MaxCount'] = $val['mobile']['visitCount'];
                    $totalVisit['hour']['MaxCount'] = substr($key,0,4) . '-' . substr($key,4,2);
                }
                if ($totalVisit['MinCount'] >= $val['visitCount']) {
                    $totalVisit['MinCount'] = $val['visitCount'];
                    $totalVisit['pc']['MinCount'] = $val['pc']['visitCount'];
                    $totalVisit['mobile']['MinCount'] = $val['mobile']['visitCount'];
                    $totalVisit['hour']['MinCount'] = substr($key,0,4) . '-' . substr($key,4,2);
                }
                if ($totalVisit['MaxPV'] <= $val['pv']) {
                    $totalVisit['MaxPV'] = $val['pv'];
                    $totalVisit['pc']['MaxPV'] = $val['pc']['pv'];
                    $totalVisit['mobile']['MaxPV'] = $val['mobile']['pv'];
                    $totalVisit['hour']['MaxPV'] = substr($key,0,4) . '-' . substr($key,4,2);
                }
                if ($totalVisit['MinPV'] >= $val['pv']) {
                    $totalVisit['MinPV'] = $val['pv'];
                    $totalVisit['pc']['MinPV'] = $val['pc']['pv'];
                    $totalVisit['mobile']['MinPV'] = $val['mobile']['pv'];
                    $totalVisit['hour']['MinPV'] = substr($key,0,4) . '-' . substr($key,4,2);
                }

                if ($searchDevice == 'all') {
                    $visitData[$key]['visitCount'] = $val['visitCount'];
                    $visitData[$key]['visitNumber'] = $val['visitNumber'];
                    $visitData[$key]['visitNewCount'] = $val['visitNewCount'];
                    $visitData[$key]['visitReCount'] = $val['visitReCount'];
                    if ($val['pv'] > 0 && $val['visitNumber'] > 0) {
                        $visitPvNumber = round($val['pv'] / $val['visitNumber'], 2);
                    } else {
                        $visitPvNumber = 0;
                    }
                    $visitData[$key]['pv'] = $visitPvNumber;
                } else if ($searchDevice == 'pc') {
                    $visitData[$key]['visitCount'] = $val['pc']['visitCount'];
                    $visitData[$key]['visitNumber'] = $val['pc']['visitNumber'];
                    $visitData[$key]['visitNewCount'] = $val['pc']['visitNewCount'];
                    $visitData[$key]['visitReCount'] = $val['pc']['visitReCount'];
                    if ($val['pc']['pv'] > 0 && $val['pc']['visitNumber'] > 0) {
                        $visitPvNumber = round($val['pc']['pv'] / $val['pc']['visitNumber'], 2);
                    } else {
                        $visitPvNumber = 0;
                    }
                    $visitData[$key]['pv'] = $visitPvNumber;
                } else if ($searchDevice == 'mobile') {
                    $visitData[$key]['visitCount'] = $val['mobile']['visitCount'];
                    $visitData[$key]['visitNumber'] = $val['mobile']['visitNumber'];
                    $visitData[$key]['visitNewCount'] = $val['mobile']['visitNewCount'];
                    $visitData[$key]['visitReCount'] = $val['mobile']['visitReCount'];
                    if ($val['mobile']['pv'] > 0 && $val['mobile']['visitNumber'] > 0) {
                        $visitPvNumber = round($val['mobile']['pv'] / $val['mobile']['visitNumber'], 2);
                    } else {
                        $visitPvNumber = 0;
                    }
                    $visitData[$key]['pv'] = $visitPvNumber;
                }
                $i++;
            }
            $totalVisit['countDiff'] = $totalVisit['MaxCount'] - $totalVisit['MinCount'];
            $totalVisit['pvDiff'] = $totalVisit['MaxPV'] - $totalVisit['MinPV'];
            $this->setData('totalVisit', $totalVisit);

            $visitDate = array_keys($visitData);
            $visitCount = array_column($visitData, 'visitCount');
            $visitNumber = array_column($visitData, 'visitNumber');
            $visitNewCount = array_column($visitData, 'visitNewCount');
            $visitReCount = array_column($visitData, 'visitReCount');
            $pv = array_column($visitData, 'pv');

            foreach ($visitDate as $key => $val) {
                $visitDate[$key] = "'" . $val . "'";
            }
            $visitChart['Date'] = $visitDate;
            $visitChart['Count'] = $visitCount;
            $visitChart['Number'] = $visitNumber;
            $visitChart['NewCount'] = $visitNewCount;
            $visitChart['ReCount'] = $visitReCount;
            $visitChart['Pv'] = $pv;
            if (count($visitChart['Date']) < 2) {
                $emptyDateArr = ['0' => "'0'"];
                $emptyArr = ['0' => 0];
                $visitChart['Date'] = array_merge($emptyDateArr, $visitChart['Date']);
                $visitChart['Count'] = array_merge($emptyArr, $visitChart['Count']);
                $visitChart['Number'] = array_merge($emptyArr, $visitChart['Number']);
                $visitChart['NewCount'] = array_merge($emptyArr, $visitChart['NewCount']);
                $visitChart['ReCount'] = array_merge($emptyArr, $visitChart['ReCount']);
                $visitChart['Pv'] = array_merge($emptyArr, $visitChart['Pv']);
            }
            $this->setData('visitChart', $visitChart);

            $i = 0;
            foreach ($visitData as $key => $val) {
                $returnVisitStatistics[$i]['visitDate'] = substr($key,0,4) . '-' . substr($key,4,2);
                $returnVisitStatistics[$i]['visitCount'] = $val['visitCount'];
                $returnVisitStatistics[$i]['visitNumber'] = $val['visitNumber'];
                $returnVisitStatistics[$i]['visitNewCount'] = $val['visitNewCount'];
                $returnVisitStatistics[$i]['visitReCount'] = $val['visitReCount'];
                $returnVisitStatistics[$i]['visitPv'] = $val['pv'];
                $i++;
            }
            $visitCount = count($returnVisitStatistics);

            if ($visitCount > 20) {
                $rowDisplay = 20;
            } else if ($visitCount == 0) {
                $rowDisplay = 5;
            } else {
                $rowDisplay = $visitCount;
            }
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        $this->setData('rowList', json_encode($returnVisitStatistics));
        $this->setData('visitCount', $visitCount);
        $this->setData('rowDisplay', $rowDisplay);
        $this->setData('visitChart', $visitChart);
        $this->setData('getDataArr', $getDataArr);

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
    }
}
