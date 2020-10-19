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

/**
 * [관리자 모드] 주문분석 > 주문통계 페이지
 *
 * @package Bundle\Controller\Admin\Statistics
 * @author    su
 */
class OrderAgeWeekController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            // 메뉴 설정
            $this->callMenu('statistics', 'order', 'ageDay');

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

            $checked['searchPeriod'][$searchPeriod] = 'checked="checked"';
            $checked['searchDevice'][$searchDevice] = 'selected="selected"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('checked', $checked);
            $this->setData('active', $active);

            // 모듈 호출
            $searchMall = DEFAULT_MALL_NUMBER;
            $orderSalesStatistics = new OrderSalesStatistics();
            $order['orderYMD'] = $searchDate;
            $order['mallSno'] = $searchMall;
            $order['searchDevice'] = $searchDevice;

            $getDataArr = $orderSalesStatistics->getOrderAgeWeek($order);

            // 일별 매출 통계 데이터 테이블 생성
            $returnOrderStatistics = [];
            $deviceSales = [];
            $daySales = []; // 연령별 매출
            $weekKorArr = [__('일요일'), __('월요일'), __('화요일'), __('수요일'), __('목요일'), __('금요일'), __('토요일')];
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                $ageTotal = [];
                $orderDayGoodsPrice = 0;
                $orderDayGoodsCnt = 0;
                $orderDayOrderCnt = 0;
                $orderDayMemberCnt = 0;
                $returnOrderStatistics[$i]['_extraData']['className']['row'] = ['day-border'];
                $returnOrderStatistics[$i]['_extraData']['rowSpan']['paymentDate'] = 4;
                $returnOrderStatistics[$i]['paymentDate'] = $weekKorArr[$key];
                foreach ($val as $deviceKey => $deviceVal) {
                    if ($deviceKey == 'write') {
                        $device = '수기주문';
                    } else {
                        $device = $deviceKey;
                    }
                    $returnOrderStatistics[$i]['orderDevice'] = $device;
                    for ($j = 10; $j <= 70; $j += 10) {
                        $returnOrderStatistics[$i]['_extraData']['className']['column']['goodsPrice' . $j] = ['order-price'];
                        $returnOrderStatistics[$i]['goodsPrice' . $j] = NumberUtils::moneyFormat($deviceVal[$j]['goodsPrice']);
                        $returnOrderStatistics[$i]['goodsCnt' . $j] = number_format($deviceVal[$j]['goodsCnt']);
                        $returnOrderStatistics[$i]['orderCnt' . $j] = number_format(count(array_unique($deviceVal[$j]['orderNo'])));
                        $returnOrderStatistics[$i]['memberCnt' . $j] = number_format(count(array_unique($deviceVal[$j]['memNo'])));

                        $orderDayGoodsPrice += $deviceVal[$j]['goodsPrice'];
                        $orderDayGoodsCnt += $deviceVal[$j]['goodsCnt'];
                        $orderDayOrderCnt += count(array_unique($deviceVal[$j]['orderNo']));
                        $orderDayMemberCnt += count(array_unique($deviceVal[$j]['memNo']));

                        // 총 합계
                        $deviceSales['goodsPriceTotal'][$deviceKey] += $deviceVal[$j]['goodsPrice']; // 판매금액
                        $deviceSales['orderCntTotal'][$deviceKey] += count(array_unique($deviceVal[$j]['orderNo'])); // 구매건수
                        $deviceSales['memberCntTotal'][$deviceKey] += count(array_unique($deviceVal[$j]['memNo'])); // 구매자수
                        $deviceSales['goodsCntTotal'][$deviceKey] += $deviceVal[$j]['goodsCnt']; // 구매개수

                        $ageTotal['goodsPrice' . $j][$deviceKey] = $deviceVal[$j]['goodsPrice'];
                        $ageTotal['goodsCnt' . $j][$deviceKey] = $deviceVal[$j]['goodsCnt'];
                        $ageTotal['orderCnt' . $j][$deviceKey] = count(array_unique($deviceVal[$j]['orderNo']));
                        $ageTotal['memberCnt' . $j][$deviceKey] = count(array_unique($deviceVal[$j]['memNo']));
                    }
                    $returnOrderStatistics[$i]['_extraData']['className']['column']['goodsPriceEtc'] = ['order-price'];
                    $returnOrderStatistics[$i]['goodsPriceEtc'] = NumberUtils::moneyFormat($deviceVal['etc']['goodsPrice']);
                    $returnOrderStatistics[$i]['goodsCntEtc'] = number_format($deviceVal['etc']['goodsCnt']);
                    $returnOrderStatistics[$i]['orderCntEtc'] = number_format(count(array_unique($deviceVal['etc']['orderNo'])));
                    $returnOrderStatistics[$i]['memberCntEtc'] = number_format(count(array_unique($deviceVal['etc']['memNo'])));

                    $orderDayGoodsPrice += $deviceVal['etc']['goodsPrice'];
                    $orderDayGoodsCnt += $deviceVal['etc']['goodsCnt'];
                    $orderDayOrderCnt += count(array_unique($deviceVal['etc']['orderNo']));
                    $orderDayMemberCnt += count(array_unique($deviceVal['etc']['memNo']));

                    // 총 합계
                    $deviceSales['goodsPriceTotal'][$deviceKey] += $deviceVal['etc']['goodsPrice']; // 판매금액
                    $deviceSales['orderCntTotal'][$deviceKey] += count(array_unique($deviceVal['etc']['orderNo'])); // 구매건수
                    $deviceSales['memberCntTotal'][$deviceKey] += count(array_unique($deviceVal['etc']['memNo'])); // 구매자수
                    $deviceSales['goodsCntTotal'][$deviceKey] += $deviceVal['etc']['goodsCnt']; // 구매개수

                    $ageTotal['goodsPriceEtc'][$deviceKey] = $deviceVal['etc']['goodsPrice'];
                    $ageTotal['goodsCntEtc'][$deviceKey] = $deviceVal['etc']['goodsCnt'];
                    $ageTotal['orderCntEtc'][$deviceKey] = count(array_unique($deviceVal['etc']['orderNo']));
                    $ageTotal['memberCntEtc'][$deviceKey] = count(array_unique($deviceVal['etc']['memNo']));

                    $daySales[$key]['price'] = $orderDayGoodsPrice;
                    $daySales[$key]['orderCnt'] = $orderDayOrderCnt;
                    $i++;
                }
                $returnOrderStatistics[$i]['_extraData']['className']['row'] = ['bold', 'day-price'];
                $returnOrderStatistics[$i]['orderDevice'] = '총액';
                for ($j = 10; $j <= 70; $j += 10) {
                    $returnOrderStatistics[$i]['goodsPrice' . $j] = NumberUtils::moneyFormat(array_sum($ageTotal['goodsPrice' . $j]));
                    $returnOrderStatistics[$i]['goodsCnt' . $j] = number_format(array_sum($ageTotal['goodsCnt' . $j]));
                    $returnOrderStatistics[$i]['orderCnt' . $j] = number_format(array_sum($ageTotal['orderCnt' . $j]));
                    $returnOrderStatistics[$i]['memberCnt' . $j] = number_format(array_sum($ageTotal['memberCnt' . $j]));
                }
                $returnOrderStatistics[$i]['goodsPriceEtc'] = NumberUtils::moneyFormat(array_sum($ageTotal['goodsPriceEtc']));
                $returnOrderStatistics[$i]['goodsCntEtc'] = number_format(array_sum($ageTotal['goodsCntEtc']));
                $returnOrderStatistics[$i]['orderCntEtc'] = number_format(array_sum($ageTotal['orderCntEtc']));
                $returnOrderStatistics[$i]['memberCntEtc'] = number_format(array_sum($ageTotal['memberCntEtc']));
                $i++;
            }

            $daySalesTotal = [];
            foreach ($daySales as $key => $val) {
                if ($daySalesTotal['min']['price']) {
                    if ($daySalesTotal['min']['price'] >= $val['price']) {
                        $daySalesTotal['min']['price'] = $val['price'];
                    }
                } else {
                    $daySalesTotal['min']['price'] = $val['price'];
                }
                if ($daySalesTotal['max']['price'] <= $val['price']) {
                    $daySalesTotal['max']['price'] = $val['price'];
                }
                if ($daySalesTotal['min']['orderCnt']) {
                    if ($daySalesTotal['min']['orderCnt'] >= $val['orderCnt']) {
                        $daySalesTotal['min']['orderCnt'] = $val['orderCnt'];
                    }
                } else {
                    $daySalesTotal['min']['orderCnt'] = $val['orderCnt'];
                }
                if ($daySalesTotal['max']['orderCnt'] <= $val['orderCnt']) {
                    $daySalesTotal['max']['orderCnt'] = $val['orderCnt'];
                }
            }
            // 총 합계
            $this->setData('deviceSales', gd_isset($deviceSales));

            // 총 최대/최소
            $this->setData('daySalesTotal', gd_isset($daySalesTotal));

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
            $this->setData('tabName', 'week');

            $this->getView()->setPageName('statistics/order_age.php');

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
