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
use Request;
use Session;

/**
 * [관리자 모드] 매출분석 > 매출통계 페이지
 *
 * @package Bundle\Controller\Admin\Statistics
 * @author  su
 */
class SalesDayController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            // 메뉴 설정
            $this->callMenu('statistics', 'sales', 'day');

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
            $checked['searchDevice'][$searchDevice] = 'selected="selected"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('checked', $checked);
            $this->setData('active', $active);

            // 모듈 호출
            $orderSalesStatistics = new OrderSalesStatistics();
            $order['orderYMD'] = $searchDate;
            $order['mallSno'] = $searchMall;
            if (Manager::isProvider()) {
                $order['scmNo'] = Session::get('manager.scmNo');
            }
            $order['searchDevice'] = $searchDevice;

            $getDataArr = $orderSalesStatistics->getOrderSalesDay($order);

            // 일별 매출 통계 데이터 테이블 생성
            $returnOrderStatistics = [];
            $daySales = []; // 일별 최대/최소 매출
            $deviceSales = []; // 디바이스별 매출
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                $orderDayOrderSalesPrice = 0;
                $orderDayGoodsPrice = 0;
                $orderDayGoodsDcPrice = 0;
                $orderDayGoodsTotal = 0;
                $orderDayDeliveryPrice = 0;
                $orderDayDeliveryDcPrice = 0;
                $orderDayDeliveryTotal = 0;
                $orderDayTotalPrice = 0;
                $orderDayRefundGoodsPrice = 0;
                $orderDayRefundDeliveryPrice = 0;
                $orderDayRefundFeePrice = 0;
                $orderDayRefundTotal = 0;
                $returnOrderStatistics[$i]['_extraData']['className']['row'] = ['day-border'];
                $returnOrderStatistics[$i]['_extraData']['rowSpan']['paymentDate'] = 4;
                $returnOrderStatistics[$i]['paymentDate'] = substr($key,0,4) . '-' . substr($key,4,2) . '-' . substr($key,-2);
                foreach ($val as $deviceKey => $deviceVal) {
                    $returnOrderStatistics[$i]['_extraData']['className']['column']['orderSalesPrice'] = ['sales-price', 'bold'];
                    $returnOrderStatistics[$i]['_extraData']['className']['column']['goodsTotal'] = ['goods-price'];
                    $returnOrderStatistics[$i]['_extraData']['className']['column']['totalPrice'] = ['total-price'];
                    $returnOrderStatistics[$i]['_extraData']['className']['column']['refundTotal'] = ['refund-price'];
                    if ($deviceKey == 'write') {
                        $device = '수기주문';
                    } else {
                        $device = $deviceKey;
                    }
                    $returnOrderStatistics[$i]['orderDevice'] = $device;
                    $returnOrderStatistics[$i]['goodsPrice'] = NumberUtils::moneyFormat($deviceVal['goodsPrice']);
                    $returnOrderStatistics[$i]['goodsDcPrice'] = NumberUtils::moneyFormat($deviceVal['goodsDcPrice']);
                    $returnOrderStatistics[$i]['goodsTotal'] = NumberUtils::moneyFormat($deviceVal['goodsTotal']);
                    $returnOrderStatistics[$i]['deliveryPrice'] = NumberUtils::moneyFormat($deviceVal['deliveryPrice']);
                    $returnOrderStatistics[$i]['deliveryDcPrice'] = NumberUtils::moneyFormat($deviceVal['deliveryDcPrice']);
                    $returnOrderStatistics[$i]['deliveryTotal'] = NumberUtils::moneyFormat($deviceVal['deliveryTotal']);
                    $returnOrderStatistics[$i]['totalPrice'] = NumberUtils::moneyFormat($deviceVal['goodsTotal'] + $deviceVal['deliveryTotal']);
                    $returnOrderStatistics[$i]['refundGoodsPrice'] = NumberUtils::moneyFormat($deviceVal['refundGoodsPrice']);
                    $returnOrderStatistics[$i]['refundDeliveryPrice'] = NumberUtils::moneyFormat($deviceVal['refundDeliveryPrice']);
                    $returnOrderStatistics[$i]['refundFeePrice'] = NumberUtils::moneyFormat($deviceVal['refundFeePrice']);
                    $returnOrderStatistics[$i]['refundTotal'] = NumberUtils::moneyFormat($deviceVal['refundTotal']);
                    $returnOrderStatistics[$i]['orderSalesPrice'] = NumberUtils::moneyFormat($deviceVal['goodsTotal'] + $deviceVal['deliveryTotal'] - $deviceVal['refundTotal']);

                    $orderDayOrderSalesPrice += $deviceVal['goodsTotal'] + $deviceVal['deliveryTotal'] - $deviceVal['refundTotal'];
                    $orderDayGoodsPrice += $deviceVal['goodsPrice'];
                    $orderDayGoodsDcPrice += $deviceVal['goodsDcPrice'];
                    $orderDayGoodsTotal += $deviceVal['goodsTotal'];
                    $orderDayDeliveryPrice += $deviceVal['deliveryPrice'];
                    $orderDayDeliveryDcPrice += $deviceVal['deliveryDcPrice'];
                    $orderDayDeliveryTotal += $deviceVal['deliveryTotal'];
                    $orderDayTotalPrice += $deviceVal['goodsTotal'] + $deviceVal['deliveryTotal'];
                    $orderDayRefundGoodsPrice += $deviceVal['refundGoodsPrice'];
                    $orderDayRefundDeliveryPrice += $deviceVal['refundDeliveryPrice'];
                    $orderDayRefundFeePrice += $deviceVal['refundFeePrice'];
                    $orderDayRefundTotal += $deviceVal['refundTotal'];
                    $i++;

                    // 디바이스별 매출 합계
                    $deviceSales[$deviceKey]['sales'] += $deviceVal['goodsTotal'] + $deviceVal['deliveryTotal']; // 판매
                    $deviceSales[$deviceKey]['refund'] += $deviceVal['refundTotal']; // 환불

                    // 일별 최대/최소 매출을 위한 합계
                    $daySales[$key]['goods'] += $deviceVal['goodsTotal']; // 상품 판매
                    $daySales[$key]['delivery'] += $deviceVal['deliveryTotal']; // 배송비 판매
                    $daySales[$key]['refund'] += $deviceVal['refundTotal']; // 환불
                }
                $returnOrderStatistics[$i]['_extraData']['className']['row'] = ['bold', 'day-price'];
                $returnOrderStatistics[$i]['_extraData']['className']['column']['orderSalesPrice'] = ['sales-price'];
                $returnOrderStatistics[$i]['_extraData']['className']['column']['goodsTotal'] = ['goods-price'];
                $returnOrderStatistics[$i]['_extraData']['className']['column']['totalPrice'] = ['total-price'];
                $returnOrderStatistics[$i]['_extraData']['className']['column']['refundTotal'] = ['refund-price'];
                $returnOrderStatistics[$i]['orderDevice'] = '총액';
                $returnOrderStatistics[$i]['goodsPrice'] = NumberUtils::moneyFormat($orderDayGoodsPrice);
                $returnOrderStatistics[$i]['goodsDcPrice'] = NumberUtils::moneyFormat($orderDayGoodsDcPrice);
                $returnOrderStatistics[$i]['goodsTotal'] = NumberUtils::moneyFormat($orderDayGoodsTotal);
                $returnOrderStatistics[$i]['deliveryPrice'] = NumberUtils::moneyFormat($orderDayDeliveryPrice);
                $returnOrderStatistics[$i]['deliveryDcPrice'] = NumberUtils::moneyFormat($orderDayDeliveryDcPrice);
                $returnOrderStatistics[$i]['deliveryTotal'] = NumberUtils::moneyFormat($orderDayDeliveryTotal);
                $returnOrderStatistics[$i]['totalPrice'] = NumberUtils::moneyFormat($orderDayTotalPrice);
                $returnOrderStatistics[$i]['refundGoodsPrice'] = NumberUtils::moneyFormat($orderDayRefundGoodsPrice);
                $returnOrderStatistics[$i]['refundDeliveryPrice'] = NumberUtils::moneyFormat($orderDayRefundDeliveryPrice);
                $returnOrderStatistics[$i]['refundFeePrice'] = NumberUtils::moneyFormat($orderDayRefundFeePrice);
                $returnOrderStatistics[$i]['refundTotal'] = NumberUtils::moneyFormat($orderDayRefundTotal);
                $returnOrderStatistics[$i]['orderSalesPrice'] = NumberUtils::moneyFormat($orderDayOrderSalesPrice);
                $i++;
            }

            $daySalesTotal['min']['price'] = 0;
            $daySalesTotal['max']['price'] = 0;
            foreach ($daySales as $key => $val) {
                $sales = $val['goods'] + $val['delivery'];
                $refund = $val['refund'];
                $salesTotal = $val['goods'] + $val['delivery'] - $val['refund'];
                if ($daySalesTotal['min']['price'] >= $salesTotal) {
                    $daySalesTotal['min']['price'] = $salesTotal;
                    $daySalesTotal['min']['date'] = substr($key,0,4) . '-' . substr($key,4,2) . '-' . substr($key,-2);
                }
                if ($daySalesTotal['max']['price'] <= $salesTotal) {
                    $daySalesTotal['max']['price'] = $salesTotal;
                    $daySalesTotal['max']['date'] = substr($key,0,4) . '-' . substr($key,4,2) . '-' . substr($key,-2);
                }
                $daySalesTotal['all']['sales'] += $sales;
                $daySalesTotal['all']['refund'] += $refund;
            }

            // 일별 매출 총/최대/최소
            $this->setData('daySalesTotal', gd_isset($daySalesTotal));
            // 디바이스 매출
            $this->setData('deviceSales', gd_isset($deviceSales));

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
            $this->setData('tabName', 'day');

            $this->getView()->setPageName('statistics/sales.php');

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
            throw $e;
        }
    }
}
