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
class OrderAreaWeekController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            // 메뉴 설정
            $this->callMenu('statistics', 'order', 'areaDay');

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
            $orderSalesStatistics = new OrderSalesStatistics();

            $searchMall = DEFAULT_MALL_NUMBER;
            $order['orderYMD'] = $searchDate;
            $order['searchDevice'] = $searchDevice;
            $order['mallSno'] = $searchMall;

            $getDataArr = $orderSalesStatistics->getOrderAreaWeek($order);

            // 일별 매출 통계 데이터 테이블 생성
            $returnOrderStatistics = [];
            $deviceSales = [];
            $daySales = []; // 지역 매출
            $settleArea = $orderSalesStatistics->getOrderSettleArea(); // 지역
            $weekKorArr = [__('일요일'), __('월요일'), __('화요일'), __('수요일'), __('목요일'), __('금요일'), __('토요일')];
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                $areaTotal = [];
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
                    foreach ($settleArea as $settleKey => $settleVal) {
                        $returnOrderStatistics[$i]['_extraData']['className']['column']['goodsPrice' . $settleVal] = ['order-price'];
                        $returnOrderStatistics[$i]['goodsPrice' . $settleVal] = NumberUtils::moneyFormat($deviceVal[$settleVal]['goodsPrice']);
                        $returnOrderStatistics[$i]['goodsCnt' . $settleVal] = number_format($deviceVal[$settleVal]['goodsCnt']);
                        $returnOrderStatistics[$i]['orderCnt' . $settleVal] = number_format(count(array_unique($deviceVal[$settleVal]['orderNo'])));
                        $returnOrderStatistics[$i]['memberCnt' . $settleVal] = number_format(count(array_unique($deviceVal[$settleVal]['memNo'])));

                        $orderDayGoodsPrice += $deviceVal[$settleVal]['goodsPrice'];
                        $orderDayGoodsCnt += $deviceVal[$settleVal]['goodsCnt'];
                        $orderDayOrderCnt += count(array_unique($deviceVal[$settleVal]['orderNo']));
                        $orderDayMemberCnt += count(array_unique($deviceVal[$settleVal]['memNo']));

                        // 총 합계
                        $deviceSales['goodsPriceTotal'][$deviceKey] += $deviceVal[$settleVal]['goodsPrice']; // 판매금액
                        $deviceSales['orderCntTotal'][$deviceKey] += count(array_unique($deviceVal[$settleVal]['orderNo'])); // 구매건수
                        $deviceSales['memberCntTotal'][$deviceKey] += count(array_unique($deviceVal[$settleVal]['memNo'])); // 구매자수
                        $deviceSales['goodsCntTotal'][$deviceKey] += $deviceVal[$settleVal]['goodsCnt']; // 구매개수

                        $areaTotal['goodsPrice' . $settleVal][$deviceKey] = $deviceVal[$settleVal]['goodsPrice'];
                        $areaTotal['goodsCnt' . $settleVal][$deviceKey] = $deviceVal[$settleVal]['goodsCnt'];
                        $areaTotal['orderCnt' . $settleVal][$deviceKey] = count(array_unique($deviceVal[$settleVal]['orderNo']));
                        $areaTotal['memberCnt' . $settleVal][$deviceKey] = count(array_unique($deviceVal[$settleVal]['memNo']));
                    }
                    $daySales[$key]['price'] = $orderDayGoodsPrice;
                    $daySales[$key]['orderCnt'] = $orderDayOrderCnt;
                    $i++;
                }
                $returnOrderStatistics[$i]['_extraData']['className']['row'] = ['bold', 'day-price'];
                $returnOrderStatistics[$i]['orderDevice'] = '총액';
                foreach ($settleArea as $settleKey => $settleVal) {
                    $returnOrderStatistics[$i]['goodsPrice' . $settleVal] = NumberUtils::moneyFormat(array_sum($areaTotal['goodsPrice' . $settleVal]));
                    $returnOrderStatistics[$i]['goodsCnt' . $settleVal] = number_format(array_sum($areaTotal['goodsCnt' . $settleVal]));
                    $returnOrderStatistics[$i]['orderCnt' . $settleVal] = number_format(array_sum($areaTotal['orderCnt' . $settleVal]));
                    $returnOrderStatistics[$i]['memberCnt' . $settleVal] = number_format(array_sum($areaTotal['memberCnt' . $settleVal]));
                }
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

            $this->setData('settleArea', gd_isset($settleArea));
            $this->setData('rowList', json_encode($returnOrderStatistics));
            $this->setData('orderCount', $orderCount);
            $this->setData('rowDisplay', $rowDisplay);
            $this->setData('tabName', 'week');

            $this->getView()->setPageName('statistics/order_area.php');

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
