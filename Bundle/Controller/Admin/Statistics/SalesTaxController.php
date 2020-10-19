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
class SalesTaxController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
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

            $getDataArr = $orderSalesStatistics->getOrderSalesTax($order);

            // 일별 매출 통계 데이터 테이블 생성
            $returnOrderStatistics = [];
            $daySales = []; // 일별 최대/최소 매출
            $deviceSales = []; // 디바이스별 매출
            $taxArr = ['y', 'n']; // 과세별
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                foreach ($taxArr as $taxCode) {
                    ${$taxCode . 'orderDayOrderSalesPrice'} = 0;
                    ${$taxCode . 'orderDayGoodsPrice'} = 0;
                    ${$taxCode . 'orderDayGoodsDcPrice'} = 0;
                    ${$taxCode . 'orderDayGoodsTotal'} = 0;
                    ${$taxCode . 'orderDayDeliveryPrice'} = 0;
                    ${$taxCode . 'orderDayDeliveryDcPrice'} = 0;
                    ${$taxCode . 'orderDayDeliveryTotal'} = 0;
                    ${$taxCode . 'orderDayTotalPrice'} = 0;
                    ${$taxCode . 'orderDayRefundGoodsPrice'} = 0;
                    ${$taxCode . 'orderDayRefundDeliveryPrice'} = 0;
                    ${$taxCode . 'orderDayRefundFeePrice'} = 0;
                    ${$taxCode . 'orderDayRefundTotal'} = 0;
                }
                $returnOrderStatistics[$i]['_extraData']['className']['row'] = ['day-border'];
                $returnOrderStatistics[$i]['_extraData']['rowSpan']['paymentDate'] = 4;
                $returnOrderStatistics[$i]['paymentDate'] = substr($key,0,4) . '-' . substr($key,4,2) . '-' . substr($key,-2);
                foreach ($val as $deviceKey => $deviceVal) {
                    $orderSalesPrice = 0;
                    foreach ($deviceVal as $taxKey => $taxVal) {
                        $returnOrderStatistics[$i]['_extraData']['className']['column']['orderSalesPrice'] = ['sales-price', 'bold'];
                        $returnOrderStatistics[$i]['_extraData']['className']['column'][$taxKey . 'goodsTotal'] = ['goods-price'];
                        $returnOrderStatistics[$i]['_extraData']['className']['column'][$taxKey . 'totalPrice'] = ['total-price'];
                        $returnOrderStatistics[$i]['_extraData']['className']['column'][$taxKey . 'refundTotal'] = ['refund-price'];
                        if ($deviceKey == 'write') {
                            $device = '수기주문';
                        } else {
                            $device = $deviceKey;
                        }
                        $returnOrderStatistics[$i]['orderDevice'] = $device;
                        $returnOrderStatistics[$i][$taxKey . 'goodsPrice'] = NumberUtils::moneyFormat($taxVal['goodsPrice']);
                        $returnOrderStatistics[$i][$taxKey . 'goodsDcPrice'] = NumberUtils::moneyFormat($taxVal['goodsDcPrice']);
                        $returnOrderStatistics[$i][$taxKey . 'goodsTotal'] = NumberUtils::moneyFormat($taxVal['goodsTotal']);
                        $returnOrderStatistics[$i][$taxKey . 'deliveryPrice'] = NumberUtils::moneyFormat($taxVal['deliveryPrice']);
                        $returnOrderStatistics[$i][$taxKey . 'deliveryDcPrice'] = NumberUtils::moneyFormat($taxVal['deliveryDcPrice']);
                        $returnOrderStatistics[$i][$taxKey . 'deliveryTotal'] = NumberUtils::moneyFormat($taxVal['deliveryTotal']);
                        $returnOrderStatistics[$i][$taxKey . 'totalPrice'] = NumberUtils::moneyFormat($taxVal['goodsTotal'] + $taxVal['deliveryTotal']);
                        $returnOrderStatistics[$i][$taxKey . 'refundGoodsPrice'] = NumberUtils::moneyFormat($taxVal['refundGoodsPrice']);
                        $returnOrderStatistics[$i][$taxKey . 'refundDeliveryPrice'] = NumberUtils::moneyFormat($taxVal['refundDeliveryPrice']);
                        $returnOrderStatistics[$i][$taxKey . 'refundFeePrice'] = NumberUtils::moneyFormat($taxVal['refundFeePrice']);
                        $returnOrderStatistics[$i][$taxKey . 'refundTotal'] = NumberUtils::moneyFormat($taxVal['refundTotal']);
                        $returnOrderStatistics[$i][$taxKey . 'orderSalesPrice'] = NumberUtils::moneyFormat($taxVal['goodsTotal'] + $taxVal['deliveryTotal'] - $taxVal['refundTotal']);

                        ${$taxKey . 'orderDayOrderSalesPrice'} += $taxVal['goodsTotal'] + $taxVal['deliveryTotal'] - $taxVal['refundTotal'];
                        ${$taxKey . 'orderDayGoodsPrice'} += $taxVal['goodsPrice'];
                        ${$taxKey . 'orderDayGoodsDcPrice'} += $taxVal['goodsDcPrice'];
                        ${$taxKey . 'orderDayGoodsTotal'} += $taxVal['goodsTotal'];
                        ${$taxKey . 'orderDayDeliveryPrice'} += $taxVal['deliveryPrice'];
                        ${$taxKey . 'orderDayDeliveryDcPrice'} += $taxVal['deliveryDcPrice'];
                        ${$taxKey . 'orderDayDeliveryTotal'} += $taxVal['deliveryTotal'];
                        ${$taxKey . 'orderDayTotalPrice'} += $taxVal['goodsTotal'] + $taxVal['deliveryTotal'];
                        ${$taxKey . 'orderDayRefundGoodsPrice'} += $taxVal['refundGoodsPrice'];
                        ${$taxKey . 'orderDayRefundDeliveryPrice'} += $taxVal['refundDeliveryPrice'];
                        ${$taxKey . 'orderDayRefundFeePrice'} += $taxVal['refundFeePrice'];
                        ${$taxKey . 'orderDayRefundTotal'} += $taxVal['refundTotal'];

                        $orderSalesPrice += $taxVal['goodsTotal'] + $taxVal['deliveryTotal'] - $taxVal['refundTotal'];

                        // 디바이스별 매출 합계
                        $deviceSales[$deviceKey]['sales'] += $taxVal['goodsTotal'] + $taxVal['deliveryTotal']; // 판매
                        $deviceSales[$deviceKey]['refund'] += $taxVal['refundTotal']; // 환불

                        // 일별 최대/최소 매출을 위한 합계
                        $daySales[$key]['goods'] += $taxVal['goodsTotal']; // 상품 판매
                        $daySales[$key]['delivery'] += $taxVal['deliveryTotal']; // 배송비 판매
                        $daySales[$key]['refund'] += $taxVal['refundTotal']; // 환불
                    }
                    $returnOrderStatistics[$i]['orderSalesPrice'] = NumberUtils::moneyFormat($orderSalesPrice);
                    $i++;
                }
                $returnOrderStatistics[$i]['_extraData']['className']['row'] = ['bold', 'day-price'];
                $returnOrderStatistics[$i]['_extraData']['className']['column']['orderSalesPrice'] = ['sales-price'];
                $returnOrderStatistics[$i]['orderDevice'] = '총액';
                foreach ($taxArr as $taxCode) {
                    $returnOrderStatistics[$i]['_extraData']['className']['column'][$taxCode . 'goodsTotal'] = ['goods-price'];
                    $returnOrderStatistics[$i]['_extraData']['className']['column'][$taxCode . 'totalPrice'] = ['total-price'];
                    $returnOrderStatistics[$i]['_extraData']['className']['column'][$taxCode . 'refundTotal'] = ['refund-price'];
                    $returnOrderStatistics[$i][$taxCode . 'goodsPrice'] = NumberUtils::moneyFormat(${$taxCode . 'orderDayGoodsPrice'});
                    $returnOrderStatistics[$i][$taxCode . 'goodsDcPrice'] = NumberUtils::moneyFormat(${$taxCode . 'orderDayGoodsDcPrice'});
                    $returnOrderStatistics[$i][$taxCode . 'goodsTotal'] = NumberUtils::moneyFormat(${$taxCode . 'orderDayGoodsTotal'});
                    $returnOrderStatistics[$i][$taxCode . 'deliveryPrice'] = NumberUtils::moneyFormat(${$taxCode . 'orderDayDeliveryPrice'});
                    $returnOrderStatistics[$i][$taxCode . 'deliveryDcPrice'] = NumberUtils::moneyFormat(${$taxCode . 'orderDayDeliveryDcPrice'});
                    $returnOrderStatistics[$i][$taxCode . 'deliveryTotal'] = NumberUtils::moneyFormat(${$taxCode . 'orderDayDeliveryTotal'});
                    $returnOrderStatistics[$i][$taxCode . 'totalPrice'] = NumberUtils::moneyFormat(${$taxCode . 'orderDayTotalPrice'});
                    $returnOrderStatistics[$i][$taxCode . 'refundGoodsPrice'] = NumberUtils::moneyFormat(${$taxCode . 'orderDayRefundGoodsPrice'});
                    $returnOrderStatistics[$i][$taxCode . 'refundDeliveryPrice'] = NumberUtils::moneyFormat(${$taxCode . 'orderDayRefundDeliveryPrice'});
                    $returnOrderStatistics[$i][$taxCode . 'refundFeePrice'] = NumberUtils::moneyFormat(${$taxCode . 'orderDayRefundFeePrice'});
                    $returnOrderStatistics[$i][$taxCode . 'refundTotal'] = NumberUtils::moneyFormat(${$taxCode . 'orderDayRefundTotal'});
                    $returnOrderStatistics[$i][$taxCode . 'orderSalesPrice'] = NumberUtils::moneyFormat(${$taxCode . 'orderDayOrderSalesPrice'});
                }
                $returnOrderStatistics[$i]['orderSalesPrice'] = NumberUtils::moneyFormat($yorderDayOrderSalesPrice + $norderDayOrderSalesPrice);
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
            // 엑셀 표 상단 타이틀
            $topTitle[0] = '과세';
            $topTitle[1] = '비과세';
            $this->setData('topTitle', gd_isset($topTitle));

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
            $this->setData('tabName', 'tax');

            $this->getView()->setPageName('statistics/sales_rows.php');

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
