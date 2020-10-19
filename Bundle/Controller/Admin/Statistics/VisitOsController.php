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
use DateTime;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ArrayUtils;
use Request;

/**
 * 방문자 환경분석 OS
 * @author Seung-gak Kim <surlira@godo.co.kr>
 */
class VisitOsController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('statistics', 'visit', 'stateOs');

        // 모듈호출
        $visitStatistics = new VisitStatistics();

        try {
            // 2/23 디바이스별 방문자 통계 안내 문구 활성 여부
            $this->setData('noticeFl', $visitStatistics->noticeCommentFl);

            // 상점별 고유번호 - 해외상점
            $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

            $searchDevice = Request::get()->get('searchDevice');
            if (!$searchDevice) {
                $searchDevice = 'all';
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
                if ($eDate->format('Ymd') <= $endDate->format('Ymd')) {
                    $searchDate[1] = $eDate->format('Ymd');
                } else {
                    $searchDate[1] = $endDate->format('Ymd');
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

            $checked['searchPeriod'][$searchPeriod] = 'checked="checked"';
            $checked['searchDevice'][$searchDevice] = 'selected="selected"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('checked', $checked);
            $this->setData('active', $active);

            $getDataArr = $visitStatistics->getVisitOsDay($searchDate, $mallSno);
            ksort($getDataArr);

            $deviceTotal = 0;
            $totalVisit = [];
            $totalVisit['top'] = 0;
            $totalVisit['topName'] = '기타';
            $totalVisit['pc']['top'] = 0;
            $totalVisit['pc']['topName'] = '기타';
            $totalVisit['mobile']['top'] = 0;
            $totalVisit['mobile']['topName'] = '기타';
            foreach ($getDataArr as $key => $val) {
                foreach ($val['visitOs'] as $inflowKey => $inflowVal) {
                    if ($inflowKey != '기타') {
                        $totalVisit['search'] += $inflowVal;
                    }
                    $totalVisit['searchAll'] += $inflowVal;
                    if ($totalVisit['top'] < $inflowVal) {
                        $totalVisit['top'] = $inflowVal;
                        $totalVisit['topName'] = $inflowKey;
                        $totalVisit['topDate'] = substr($key,0,4) . '-' . substr($key,4,2) . '-' . substr($key,-2);
                    }
                    if ($searchDevice == 'all') {
                        $deviceTotal += $inflowVal;
                        $visitData[$key][$inflowKey] = $inflowVal;
                    }
                }
                foreach ($val['pc']['visitOs'] as $inflowPcKey => $inflowPcVal) {
                    if ($inflowPcKey != '기타') {
                        $totalVisit['pc']['search'] += $inflowPcVal;
                        if ($totalVisit['pc']['top'] < $inflowPcVal) {
                            $totalVisit['pc']['top'] = $inflowPcVal;
                            $totalVisit['pc']['topName'] = $inflowPcKey;
                            $totalVisit['pc']['topDate'] = substr($key,0,4) . '-' . substr($key,4,2) . '-' . substr($key,-2);
                        }
                    }
                    $totalVisit['pc']['searchAll'] += $inflowPcVal;
                    if ($searchDevice == 'pc') {
                        $deviceTotal += $inflowPcVal;
                        $visitData[$key][$inflowPcKey] = $inflowPcVal;
                    }
                }
                foreach ($val['mobile']['visitOs'] as $inflowMobileKey => $inflowMobileVal) {
                    if ($inflowMobileKey != '기타') {
                        $totalVisit['mobile']['search'] += $inflowMobileVal;
                        if ($totalVisit['mobile']['top'] < $inflowMobileVal) {
                            $totalVisit['mobile']['top'] = $inflowMobileVal;
                            $totalVisit['mobile']['topName'] = $inflowMobileKey;
                            $totalVisit['mobile']['topDate'] = substr($key,0,4) . '-' . substr($key,4,2) . '-' . substr($key,-2);
                        }
                    }
                    $totalVisit['mobile']['searchAll'] += $inflowMobileVal;
                    if ($searchDevice == 'mobile') {
                        $deviceTotal += $inflowMobileVal;
                        $visitData[$key][$inflowMobileKey] = $inflowMobileVal;
                    }
                }
            }

            if ($totalVisit['pc']['searchAll'] > 0 && $totalVisit['searchAll'] > 0) {
                $totalVisit['pc']['percent'] = round($totalVisit['pc']['searchAll'] / $totalVisit['searchAll'] * 100, 2);
            } else {
                $totalVisit['pc']['percent'] = 0;
            }

            if ($totalVisit['mobile']['searchAll'] > 0 && $totalVisit['searchAll'] > 0) {
                $totalVisit['mobile']['percent'] = round($totalVisit['mobile']['searchAll'] / $totalVisit['searchAll'] * 100, 2);
            } else {
                $totalVisit['mobile']['percent'] = 0;
            }
            $this->setData('totalVisit', $totalVisit);

            foreach ($visitData as $key => $val) {
                $visitChart['Date'][] = "'" . $key . "'";
                foreach ($val as $inflowKey => $inflowVal) {
                    $visitChart[$inflowKey][] = $inflowVal;
                }
            }
            if (count($visitChart['Date']) < 2) {
                $emptyDateArr = ['0' => "'0'"];
                $emptyArr = ['0' => 0];
                $visitChart['Date'] = array_merge($emptyDateArr, $visitChart['Date']);
                foreach ($visitChart as $key => $val) {
                    if ($key != 'Date') {
                        $visitChart[$key] = array_merge($emptyArr, $visitChart[$key]);
                    }
                }
            }
            $this->setData('visitChart', $visitChart);

            foreach ($visitChart as $key => $val) {
                if ($key != 'Date') {
                    $sort[$key] = array_sum($val);
                }
            }
            arsort($sort);

            $i = 0;
            foreach ($sort as $key => $val) {
                $visitOsTitle[] = $key;
                $returnVisitStatistics[$i]['searchTool'] = $key;
                $returnVisitStatistics[$i]['searchCount'] = $val;
                $returnVisitStatistics[$i]['searchPercent'] = round($val / $deviceTotal * 100, 2);
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
        $this->setData('visitOsTitle', $visitOsTitle);

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

        // 쿼리스트링
        $queryString = Request::getQueryString();
        if (!empty($queryString)) {
            $queryString = '?' . $queryString;
        }
        $this->setData('queryString', $queryString);
        $this->setData('tabName', 'os');
    }
}
