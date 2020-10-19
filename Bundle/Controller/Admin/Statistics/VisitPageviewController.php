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
use Request;

/**
 * 방문통계 페이지뷰 현황
 * @author Seung-gak Kim <surlira@godo.co.kr>
 */
class VisitPageviewController extends \Controller\Admin\Controller
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
            if (!$searchDate[0]) {
                $searchDate[0] = $sDate->modify('-7 days')->format('Ymd');
            } else {
                $startDate = new DateTime($searchDate[0]);
                if ($sDate->format('Ymd') <= $startDate->format('Ymd')) {
                    $searchDate[0] = $sDate->modify('-1 days')->format('Ymd');
                } else {
                    $searchDate[0] = $startDate->format('Ymd');
                }
            }
            if (!$searchDate[1]) {
                $searchDate[1] = $eDate->modify('-1 days')->format('Ymd');
            } else {
                $endDate = new DateTime($searchDate[1]);
                if ($eDate->format('Ymd') <= $endDate->format('Ymd') || (strlen($searchDate[1]) < 10) && $endDate->format('Ym') == $eDate->format('Ym')) {
                    $searchDate[1] = $eDate->modify('-1 days')->format('Ymd');
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
                $sDate = $eDate->modify('-7 day');
                $searchDate[0] = $sDate->format('Ymd');
                $searchPeriod = 7;
            }

            $checked['searchMall'][$searchMall] = 'checked="checked"';
            $checked['searchDevice'][$searchDevice] = 'selected="selected"';
            $checked['searchPeriod'][$searchPeriod] = 'checked="checked"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('checked', $checked);
            $this->setData('active', $active);

            if ($searchMall == 'all') {
                $getDataArr = $visitStatistics->getVisitDayAllMall($searchDate);
            } else {
                $getDataArr = $visitStatistics->getVisitDay($searchDate, $searchMall);
            }
            ksort($getDataArr);

            $totalVisit = [];
            foreach ($getDataArr as $key => $val) {
                $totalVisit['Pv'] += $val['pv'];
                $totalVisit['NewPv'] += $val['visitNewPv'];
                $totalVisit['RePv'] += $val['visitRePv'];
                $totalVisit['Number'] += $val['visitNumber'];
                $totalVisit['pc']['Pv'] += $val['pc']['pv'];
                $totalVisit['pc']['NewPv'] += $val['pc']['visitNewPv'];
                $totalVisit['pc']['RePv'] += $val['pc']['visitRePv'];
                $totalVisit['pc']['Number'] += $val['pc']['visitNumber'];
                $totalVisit['mobile']['Pv'] += $val['mobile']['pv'];
                $totalVisit['mobile']['NewPv'] += $val['mobile']['visitNewPv'];
                $totalVisit['mobile']['RePv'] += $val['mobile']['visitRePv'];
                $totalVisit['mobile']['Number'] += $val['mobile']['visitNumber'];

                if ($searchDevice == 'all') {
                    $visitData[$key]['pv'] = $val['pv'];
                    $visitData[$key]['visitNewPv'] = $val['visitNewPv'];
                    $visitData[$key]['visitRePv'] = $val['visitRePv'];
                    $visitData[$key]['visitNumber'] = $val['visitNumber'];
                    if ($val['pv'] > 0 && $val['visitNumber'] > 0) {
                        $visitPvNumber = round($val['pv'] / $val['visitNumber'], 2);
                    } else {
                        $visitPvNumber = 0;
                    }
                    $visitData[$key]['visitPv'] = $visitPvNumber;
                } else if ($searchDevice == 'pc') {
                    $visitData[$key]['pv'] = $val['pc']['pv'];
                    $visitData[$key]['visitNewPv'] = $val['pc']['visitNewPv'];
                    $visitData[$key]['visitRePv'] = $val['pc']['visitRePv'];
                    $visitData[$key]['visitNumber'] = $val['pc']['visitNumber'];
                    if ($val['pc']['pv'] > 0 && $val['pc']['visitNumber'] > 0) {
                        $visitPvNumber = round($val['pc']['pv'] / $val['pc']['visitNumber'], 2);
                    } else {
                        $visitPvNumber = 0;
                    }
                    $visitData[$key]['visitPv'] = $visitPvNumber;
                } else if ($searchDevice == 'mobile') {
                    $visitData[$key]['pv'] = $val['mobile']['pv'];
                    $visitData[$key]['visitNewPv'] = $val['mobile']['visitNewPv'];
                    $visitData[$key]['visitRePv'] = $val['mobile']['visitRePv'];
                    $visitData[$key]['visitNumber'] = $val['mobile']['visitNumber'];
                    if ($val['mobile']['pv'] > 0 && $val['mobile']['visitNumber'] > 0) {
                        $visitPvNumber = round($val['mobile']['pv'] / $val['mobile']['visitNumber'], 2);
                    } else {
                        $visitPvNumber = 0;
                    }
                    $visitData[$key]['visitPv'] = $visitPvNumber;
                }
            }
            if ($totalVisit['Pv'] > 0 && $totalVisit['Number'] > 0) {
                $totalVisit['VisitPv'] = round($totalVisit['Pv'] / $totalVisit['Number'], 2);
            } else {
                $totalVisit['VisitPv'] = 0;
            }
            if ($totalVisit['pc']['Pv'] > 0 && $totalVisit['pc']['Number'] > 0) {
                $totalVisit['pc']['VisitPv'] = round($totalVisit['pc']['Pv'] / $totalVisit['pc']['Number'], 2);
            } else {
                $totalVisit['pc']['VisitPv'] = 0;
            }
            if ($totalVisit['mobile']['Pv'] > 0 && $totalVisit['mobile']['Number'] > 0) {
                $totalVisit['mobile']['VisitPv'] = round($totalVisit['mobile']['Pv'] / $totalVisit['mobile']['Number'], 2);
            } else {
                $totalVisit['mobile']['VisitPv'] = 0;
            }
            $this->setData('totalVisit', $totalVisit);

            $visitDate = array_keys($visitData);
            $pv = array_column($visitData, 'pv');
            $visitNewPv = array_column($visitData, 'visitNewPv');
            $visitRePv = array_column($visitData, 'visitRePv');
            $visitNumber = array_column($visitData, 'visitNumber');
            $visitPv = array_column($visitData, 'visitPv');

            foreach ($visitDate as $key => $val) {
                $visitDate[$key] = "'" . $val . "'";
            }
            $visitChart['Date'] = $visitDate;
            $visitChart['Pv'] = $pv;
            $visitChart['NewPv'] = $visitNewPv;
            $visitChart['RePv'] = $visitRePv;
            $visitChart['Number'] = $visitNumber;
            $visitChart['visitPv'] = $visitPv;
            if (count($visitChart['Date']) < 2) {
                $emptyDateArr = ['0' => "'0'"];
                $emptyArr = ['0' => 0];
                $visitChart['Date'] = array_merge($emptyDateArr, $visitChart['Date']);
                $visitChart['Pv'] = array_merge($emptyArr, $visitChart['Pv']);
                $visitChart['NewPv'] = array_merge($emptyArr, $visitChart['NewPv']);
                $visitChart['RePv'] = array_merge($emptyArr, $visitChart['RePv']);
                $visitChart['Number'] = array_merge($emptyArr, $visitChart['Number']);
                $visitChart['visitPv'] = array_merge($emptyArr, $visitChart['visitPv']);
            }
            $this->setData('visitChart', $visitChart);

            $i = 0;
            foreach ($visitData as $key => $val) {
                $returnVisitStatistics[$i]['visitDate'] = substr($key,0,4) . '-' . substr($key,4,2) . '-' . substr($key,-2);
                $returnVisitStatistics[$i]['pv'] = $val['pv'];
                $returnVisitStatistics[$i]['visitNewPv'] = $val['visitNewPv'];
                $returnVisitStatistics[$i]['visitRePv'] = $val['visitRePv'];
                $returnVisitStatistics[$i]['visitNumber'] = $val['visitNumber'];
                $returnVisitStatistics[$i]['visitPv'] = $val['visitPv'];
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

