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
 * Class 마일리지 통계
 * @package Bundle\Controller\Admin\Statistics
 * @author  su
 */
class MemberMileageController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws AlertBackException
     */
    public function index()
    {
        try {
            $this->callMenu('statistics', 'member', 'mileage');

            // 상점별 고유번호 - 해외상점
            $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

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

            $checked['searchPeriod'][$searchPeriod] = 'checked="checked"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('checked', $checked);
            $this->setData('active', $active);

            $memberStatistics = new MemberStatistics();

            $getDataArr = $memberStatistics->getMemberMileageDay($searchDate, $mallSno);

            // 통계 데이터 일자별 처리 - 빈 일자 생성
            $startDate = new DateTime($searchDate[0]);
            $endDate = new DateTime($searchDate[1]);
            $diffDay = $endDate->diff($startDate)->days;
            for ($i = 0; $i <= $diffDay; $i++) {
                $searchDt = new DateTime($searchDate[0]);
                $searchDt = $searchDt->modify('+' . $i . ' day');
                $resetData[$searchDt->format('Ymd')]['total'] = 0;
                $resetData[$searchDt->format('Ymd')]['useMileage'] = 0;
                $resetData[$searchDt->format('Ymd')]['useCount'] = 0;
                $resetData[$searchDt->format('Ymd')]['saveMileage'] = 0;
                $resetData[$searchDt->format('Ymd')]['saveCount'] = 0;
                $resetData[$searchDt->format('Ymd')]['deleteMileage'] = 0;
                $resetData[$searchDt->format('Ymd')]['deleteCount'] = 0;
            }
            $getDataArr = $getDataArr + $resetData;
            ksort($getDataArr);

            // 통계 그래프
            $memberChart['Date'] = array_keys($getDataArr);
            $memberChart['total'] = array_column($getDataArr, 'total');
            $memberChart['useMileage'] = array_column($getDataArr, 'useMileage');
            $memberChart['useCount'] = array_column($getDataArr, 'useCount');
            $memberChart['saveMileage'] = array_column($getDataArr, 'saveMileage');
            $memberChart['saveCount'] = array_column($getDataArr, 'saveCount');
            $memberChart['deleteMileage'] = array_column($getDataArr, 'deleteMileage');
            $memberChart['deleteCount'] = array_column($getDataArr, 'deleteCount');
            if (count($memberChart['Date']) < 2) {
                $emptyDateArr = ['0' => '0'];
                $emptyArr = ['0' => 0];
                $memberChart['Date'] = array_merge($emptyDateArr, $memberChart['Date']);
                $memberChart['total'] = array_merge($emptyArr, $memberChart['total']);
                $memberChart['useMileage'] = array_merge($emptyArr, $memberChart['useMileage']);
                $memberChart['useCount'] = array_merge($emptyArr, $memberChart['useCount']);
                $memberChart['saveMileage'] = array_merge($emptyArr, $memberChart['saveMileage']);
                $memberChart['saveCount'] = array_merge($emptyArr, $memberChart['saveCount']);
                $memberChart['deleteMileage'] = array_merge($emptyArr, $memberChart['deleteMileage']);
                $memberChart['deleteCount'] = array_merge($emptyArr, $memberChart['deleteCount']);
            }
            foreach ($memberChart['Date'] as $key => $val) {
                $memberChart['Date'][$key] = "'" . $val . "'";
            }

            // 통계 합계
            $total = $memberChart['total'];
            $memberTotal['total'] = array_pop($total);
            $memberTotal['useMileage'] = array_sum($memberChart['useMileage']);
            $memberTotal['useCount'] = array_sum($memberChart['useCount']);
            $memberTotal['saveMileage'] = array_sum($memberChart['saveMileage']);
            $memberTotal['saveCount'] = array_sum($memberChart['saveCount']);
            $memberTotal['deleteMileage'] = array_sum($memberChart['deleteMileage']);
            $memberTotal['deleteCount'] = array_sum($memberChart['deleteCount']);

            // 통계 엑셀 데이터
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                $returnMemberStatistics[$i]['memberDate'] = substr($key,0,4) . '-' . substr($key,4,2) . '-' . substr($key,-2);
                $returnMemberStatistics[$i]['_extraData']['className']['column']['total'] = ['order-price'];
                $returnMemberStatistics[$i]['total'] = $val['total'] + 0;
                $returnMemberStatistics[$i]['useMileage'] = $val['useMileage'];
                $returnMemberStatistics[$i]['useCount'] = $val['useCount'];
                $returnMemberStatistics[$i]['saveMileage'] = $val['saveMileage'];
                $returnMemberStatistics[$i]['saveCount'] = $val['saveCount'];
                $returnMemberStatistics[$i]['deleteMileage'] = $val['deleteMileage'];
                $returnMemberStatistics[$i]['deleteCount'] = $val['deleteCount'];
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

            // 쿼리스트링
            $queryString = Request::getQueryString();
            if (!empty($queryString)) {
                $queryString = '?' . $queryString;
            }
            $this->setData('queryString', $queryString);
            $this->setData('tabName', 'day');
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
