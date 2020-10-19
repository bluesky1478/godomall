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
class SalesMemberController extends \Controller\Admin\Controller
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

            $getDataArr = $orderSalesStatistics->getOrderSalesMember($order);

            // 일별 매출 통계 데이터 테이블 생성
            $returnOrderStatistics = [];
            $daySales = []; // 일별 최대/최소 매출
            $deviceSales = []; // 디바이스별 매출
            $memberArr = ['y', 'n']; // 회원별
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                foreach ($memberArr as $memberCode) {
                    ${$memberCode . 'orderDayOrderSalesPrice'} = 0;
                    ${$memberCode . 'orderDayGoodsPrice'} = 0;
                    ${$memberCode . 'orderDayGoodsDcPrice'} = 0;
                    ${$memberCode . 'orderDayGoodsTotal'} = 0;
                    ${$memberCode . 'orderDayDeliveryPrice'} = 0;
                    ${$memberCode . 'orderDayDeliveryDcPrice'} = 0;
                    ${$memberCode . 'orderDayDeliveryTotal'} = 0;
                    ${$memberCode . 'orderDayTotalPrice'} = 0;
                    ${$memberCode . 'orderDayRefundGoodsPrice'} = 0;
                    ${$memberCode . 'orderDayRefundDeliveryPrice'} = 0;
                    ${$memberCode . 'orderDayRefundFeePrice'} = 0;
                    ${$memberCode . 'orderDayRefundTotal'} = 0;
                }
                $returnOrderStatistics[$i]['_extraData']['className']['row'] = ['day-border'];
                $returnOrderStatistics[$i]['_extraData']['rowSpan']['paymentDate'] = 4;
                $returnOrderStatistics[$i]['paymentDate'] = substr($key,0,4) . '-' . substr($key,4,2) . '-' . substr($key,-2);
                foreach ($val as $deviceKey => $deviceVal) {
                    $orderSalesPrice = 0;
                    foreach ($deviceVal as $memberKey => $memberVal) {
                        $returnOrderStatistics[$i]['_extraData']['className']['column']['orderSalesPrice'] = ['sales-price', 'bold'];
                        $returnOrderStatistics[$i]['_extraData']['className']['column'][$memberKey . 'goodsTotal'] = ['goods-price'];
                        $returnOrderStatistics[$i]['_extraData']['className']['column'][$memberKey . 'totalPrice'] = ['total-price'];
                        $returnOrderStatistics[$i]['_extraData']['className']['column'][$memberKey . 'refundTotal'] = ['refund-price'];
                        if ($deviceKey == 'write') {
                            $device = '수기주문';
                        } else {
                            $device = $deviceKey;
                        }
                        $returnOrderStatistics[$i]['orderDevice'] = $device;
                        $returnOrderStatistics[$i][$memberKey . 'goodsPrice'] = NumberUtils::moneyFormat($memberVal['goodsPrice']);
                        $returnOrderStatistics[$i][$memberKey . 'goodsDcPrice'] = NumberUtils::moneyFormat($memberVal['goodsDcPrice']);
                        $returnOrderStatistics[$i][$memberKey . 'goodsTotal'] = NumberUtils::moneyFormat($memberVal['goodsTotal']);
                        $returnOrderStatistics[$i][$memberKey . 'deliveryPrice'] = NumberUtils::moneyFormat($memberVal['deliveryPrice']);
                        $returnOrderStatistics[$i][$memberKey . 'deliveryDcPrice'] = NumberUtils::moneyFormat($memberVal['deliveryDcPrice']);
                        $returnOrderStatistics[$i][$memberKey . 'deliveryTotal'] = NumberUtils::moneyFormat($memberVal['deliveryTotal']);
                        $returnOrderStatistics[$i][$memberKey . 'totalPrice'] = NumberUtils::moneyFormat($memberVal['goodsTotal'] + $memberVal['deliveryTotal']);
                        $returnOrderStatistics[$i][$memberKey . 'refundGoodsPrice'] = NumberUtils::moneyFormat($memberVal['refundGoodsPrice']);
                        $returnOrderStatistics[$i][$memberKey . 'refundDeliveryPrice'] = NumberUtils::moneyFormat($memberVal['refundDeliveryPrice']);
                        $returnOrderStatistics[$i][$memberKey . 'refundFeePrice'] = NumberUtils::moneyFormat($memberVal['refundFeePrice']);
                        $returnOrderStatistics[$i][$memberKey . 'refundTotal'] = NumberUtils::moneyFormat($memberVal['refundTotal']);
                        $returnOrderStatistics[$i][$memberKey . 'orderSalesPrice'] = NumberUtils::moneyFormat($memberVal['goodsTotal'] + $memberVal['deliveryTotal'] - $memberVal['refundTotal']);

                        ${$memberKey . 'orderDayOrderSalesPrice'} += $memberVal['goodsTotal'] + $memberVal['deliveryTotal'] - $memberVal['refundTotal'];
                        ${$memberKey . 'orderDayGoodsPrice'} += $memberVal['goodsPrice'];
                        ${$memberKey . 'orderDayGoodsDcPrice'} += $memberVal['goodsDcPrice'];
                        ${$memberKey . 'orderDayGoodsTotal'} += $memberVal['goodsTotal'];
                        ${$memberKey . 'orderDayDeliveryPrice'} += $memberVal['deliveryPrice'];
                        ${$memberKey . 'orderDayDeliveryDcPrice'} += $memberVal['deliveryDcPrice'];
                        ${$memberKey . 'orderDayDeliveryTotal'} += $memberVal['deliveryTotal'];
                        ${$memberKey . 'orderDayTotalPrice'} += $memberVal['goodsTotal'] + $memberVal['deliveryTotal'];
                        ${$memberKey . 'orderDayRefundGoodsPrice'} += $memberVal['refundGoodsPrice'];
                        ${$memberKey . 'orderDayRefundDeliveryPrice'} += $memberVal['refundDeliveryPrice'];
                        ${$memberKey . 'orderDayRefundFeePrice'} += $memberVal['refundFeePrice'];
                        ${$memberKey . 'orderDayRefundTotal'} += $memberVal['refundTotal'];

                        $orderSalesPrice += $memberVal['goodsTotal'] + $memberVal['deliveryTotal'] - $memberVal['refundTotal'];

                        // 디바이스별 매출 합계
                        $deviceSales[$deviceKey]['sales'] += $memberVal['goodsTotal'] + $memberVal['deliveryTotal']; // 판매
                        $deviceSales[$deviceKey]['refund'] += $memberVal['refundTotal']; // 환불

                        // 일별 최대/최소 매출을 위한 합계
                        $daySales[$key]['goods'] += $memberVal['goodsTotal']; // 상품 판매
                        $daySales[$key]['delivery'] += $memberVal['deliveryTotal']; // 배송비 판매
                        $daySales[$key]['refund'] += $memberVal['refundTotal']; // 환불
                    }
                    $returnOrderStatistics[$i]['orderSalesPrice'] = NumberUtils::moneyFormat($orderSalesPrice);
                    $i++;
                }
                $returnOrderStatistics[$i]['_extraData']['className']['row'] = ['bold', 'day-price'];
                $returnOrderStatistics[$i]['_extraData']['className']['column']['orderSalesPrice'] = ['sales-price'];
                $returnOrderStatistics[$i]['orderDevice'] = '총액';
                foreach ($memberArr as $memberCode) {
                    $returnOrderStatistics[$i]['_extraData']['className']['column'][$memberCode . 'goodsTotal'] = ['goods-price'];
                    $returnOrderStatistics[$i]['_extraData']['className']['column'][$memberCode . 'totalPrice'] = ['total-price'];
                    $returnOrderStatistics[$i]['_extraData']['className']['column'][$memberCode . 'refundTotal'] = ['refund-price'];
                    $returnOrderStatistics[$i][$memberCode . 'goodsPrice'] = NumberUtils::moneyFormat(${$memberCode . 'orderDayGoodsPrice'});
                    $returnOrderStatistics[$i][$memberCode . 'goodsDcPrice'] = NumberUtils::moneyFormat(${$memberCode . 'orderDayGoodsDcPrice'});
                    $returnOrderStatistics[$i][$memberCode . 'goodsTotal'] = NumberUtils::moneyFormat(${$memberCode . 'orderDayGoodsTotal'});
                    $returnOrderStatistics[$i][$memberCode . 'deliveryPrice'] = NumberUtils::moneyFormat(${$memberCode . 'orderDayDeliveryPrice'});
                    $returnOrderStatistics[$i][$memberCode . 'deliveryDcPrice'] = NumberUtils::moneyFormat(${$memberCode . 'orderDayDeliveryDcPrice'});
                    $returnOrderStatistics[$i][$memberCode . 'deliveryTotal'] = NumberUtils::moneyFormat(${$memberCode . 'orderDayDeliveryTotal'});
                    $returnOrderStatistics[$i][$memberCode . 'totalPrice'] = NumberUtils::moneyFormat(${$memberCode . 'orderDayTotalPrice'});
                    $returnOrderStatistics[$i][$memberCode . 'refundGoodsPrice'] = NumberUtils::moneyFormat(${$memberCode . 'orderDayRefundGoodsPrice'});
                    $returnOrderStatistics[$i][$memberCode . 'refundDeliveryPrice'] = NumberUtils::moneyFormat(${$memberCode . 'orderDayRefundDeliveryPrice'});
                    $returnOrderStatistics[$i][$memberCode . 'refundFeePrice'] = NumberUtils::moneyFormat(${$memberCode . 'orderDayRefundFeePrice'});
                    $returnOrderStatistics[$i][$memberCode . 'refundTotal'] = NumberUtils::moneyFormat(${$memberCode . 'orderDayRefundTotal'});
                    $returnOrderStatistics[$i][$memberCode . 'orderSalesPrice'] = NumberUtils::moneyFormat(${$memberCode . 'orderDayOrderSalesPrice'});
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
            $topTitle[0] = '회원';
            $topTitle[1] = '비회원';
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
            $this->setData('tabName', 'member');

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
