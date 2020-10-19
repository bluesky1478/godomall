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

use Component\Order\OrderSalesStatistics;
use Component\Mall\Mall;
use Component\Member\Manager;
use DateTime;
use Exception;
use Framework\Utility\NumberUtils;
use Framework\Debug\Exception\AlertBackException;
use Request;

/**
 * [관리자 모드] 매출분석 > 결제수단별 매출통계 페이지
 *
 * @link     /admin/statistics/order_settle_month.php
 * @package  Bundle\Controller\Admin\Statistics
 * @author  su
 */
class SalesSettleMonthController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // 메뉴 설정
            $this->callMenu('statistics', 'sales', 'settleDay');

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
                $searchDate[0] = $sDate->modify('-' . ($date - 1) . ' days')->format('Ymd');
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
            $checked['searchDevice'][$searchDevice] = 'selected="selected"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('checked', $checked);
            $this->setData('active', $active);

            // 모듈 호출
            $orderSalesStatistics = new OrderSalesStatistics();
            $order['orderYMD'] = $searchDate;
            $order['mallSno'] = $searchMall;
            $order['searchDevice'] = $searchDevice;

            $getDataArr = $orderSalesStatistics->getOrderSalesSettleMonth($order);

            // 일별 매출 통계 데이터 테이블 생성
            $returnOrderStatistics = [];
            $daySales = []; // 결제수단 매출
            $settleKind = $orderSalesStatistics->getOrderSettleKind(); // 결제수단
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                $orderDayOrderSalesPrice = [];
                $returnOrderStatistics[$i]['_extraData']['className']['row'] = ['day-border'];
                $returnOrderStatistics[$i]['_extraData']['className']['column']['orderSalesPrice'] = ['order-price'];
                $returnOrderStatistics[$i]['_extraData']['rowSpan']['paymentDate'] = 4;
                $returnOrderStatistics[$i]['paymentDate'] = substr($key,0,4) . '-' . substr($key,4,2);
                foreach ($val as $deviceKey => $deviceVal) {
                    $deviceSales = 0;
                    if ($deviceKey == 'write') {
                        $device = '수기주문';
                    } else {
                        $device = $deviceKey;
                    }
                    $returnOrderStatistics[$i]['orderDevice'] = $device;
                    foreach ($settleKind as $settleKey => $settleVal) {
                        $returnOrderStatistics[$i][$settleKey] = NumberUtils::moneyFormat($deviceVal[$settleKey]);
                        $deviceSales += $deviceVal[$settleKey]; // 디바이스별 총 합계
                        $orderDayOrderSalesPrice[$settleKey] += $deviceVal[$settleKey]; // 일자별 총 합계
                        $daySales[$settleKey] += $deviceVal[$settleKey]; // 결제수단별 총 합계
                    }

                    // 결제수단별 일자별 디바이스별 합계
                    $returnOrderStatistics[$i]['orderSalesPrice'] = NumberUtils::moneyFormat($deviceSales);
                    $i++;

                    $returnOrderStatistics[$i]['_extraData']['className']['column']['orderSalesPrice'] = ['order-price'];
                }
                $returnOrderStatistics[$i]['_extraData']['className']['row'] = ['bold', 'day-price'];
                $returnOrderStatistics[$i]['_extraData']['className']['column']['orderSalesPrice'] = ['sales-price'];
                $returnOrderStatistics[$i]['orderDevice'] = '총액';
                $returnOrderStatistics[$i]['orderSalesPrice'] = NumberUtils::moneyFormat(array_sum($orderDayOrderSalesPrice));
                foreach ($settleKind as $settleKey => $settleVal) {
                    $returnOrderStatistics[$i][$settleKey] = NumberUtils::moneyFormat($orderDayOrderSalesPrice[$settleKey]);
                }
                $returnOrderStatistics[$i]['etc'] = NumberUtils::moneyFormat($orderDayOrderSalesPrice['etc']);

                $i++;
            }

            // 디바이스 매출
            $this->setData('daySales', gd_isset($daySales));

            // 결제수단 리스트
            $this->setData('settleKind', gd_isset($settleKind));

            $orderCount = count($returnOrderStatistics);
            if ($orderCount > 20) {
                $rowDisplay = 20;
            } else if ($orderCount == 0) {
                $rowDisplay = 5;
            } else {
                $rowDisplay = $orderCount;
            }

            $this->setData('rowList', json_encode($returnOrderStatistics));
            $this->setData('orderCount', $orderCount);
            $this->setData('rowDisplay', $rowDisplay);
            $this->setData('tabName', 'month');

            $this->getView()->setPageName('statistics/sales_settle.php');

            $this->addScript(
                [
                    'backbone/backbone-min.js',
                    'tui/code-snippet.min.js',
                    'tui.grid/grid.min.js',
                ]
            );
            $this->addCss(
                [
                    'tui.grid/grid.css',
                ]
            );

            // 쿼리스트링
            $queryString = Request::getQueryString();
            if (!empty($queryString)) {
                $queryString = '?' . $queryString;
            }
            $this->setData('queryString', $queryString);
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}
