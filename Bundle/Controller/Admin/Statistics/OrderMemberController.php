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
class OrderMemberController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            // 메뉴 설정
            $this->callMenu('statistics', 'order', 'day');

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
            gd_isset($searchPeriod, $dateDiff->m);
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

            $getDataArr = $orderSalesStatistics->getOrderMember($order);

            // 일별 주문 통계 데이터 테이블 생성
            $returnOrderStatistics = [];
            $daySales = []; // 일별 최대/최소 매출
            $deviceSales = []; // 디바이스별 매출
            $memberArr = ['y' => '회원', 'n' => '비회원'];
            $deviceArr = ['pc', 'mobile', 'write'];
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                unset($dateTotal);
                $returnOrderStatistics[$i]['_extraData']['rowSpan']['paymentDate'] = 3;
                $returnOrderStatistics[$i]['paymentDate'] = substr($key,0,4) . '-' . substr($key,4,2) . '-' . substr($key,-2);
                foreach ($val as $memberKey => $memberVal) {
                    $orderDayGoodsPrice = 0;
                    $orderDayGoodsCnt = 0;
                    $orderDayOrderCnt = 0;
                    $orderDayMemberCnt = 0;
                    $memberName = $memberArr[$memberKey];
                    $returnOrderStatistics[$i]['orderMember'] = $memberName;
                    foreach ($memberVal as $deviceKey => $deviceVal) {
                        $returnOrderStatistics[$i]['_extraData']['className']['column']['goodsPrice' . ucfirst($deviceKey)] = ['order-price'];
                        $returnOrderStatistics[$i]['goodsPrice' . ucfirst(ucfirst($deviceKey))] = NumberUtils::moneyFormat($deviceVal['goodsPrice']);
                        $returnOrderStatistics[$i]['goodsCnt' . ucfirst($deviceKey)] = NumberUtils::moneyFormat($deviceVal['goodsCnt']);
                        $returnOrderStatistics[$i]['orderCnt' . ucfirst($deviceKey)] = NumberUtils::moneyFormat(count(array_unique($deviceVal['orderNo'])));
                        $returnOrderStatistics[$i]['memberCnt' . ucfirst($deviceKey)] = NumberUtils::moneyFormat(count(array_unique($deviceVal['memNo'])));

                        $orderDayGoodsPrice += $deviceVal['goodsPrice'];
                        $orderDayGoodsCnt += $deviceVal['goodsCnt'];
                        $orderDayOrderCnt += count(array_unique($deviceVal['orderNo']));
                        $orderDayMemberCnt += count(array_unique($deviceVal['memNo']));

                        // 총 합계
                        $deviceSales['goodsPriceTotal'][$deviceKey] += $deviceVal['goodsPrice']; // 판매금액
                        $deviceSales['orderCntTotal'][$deviceKey] += count(array_unique($deviceVal['orderNo'])); // 구매건수
                        $deviceSales['memberCntTotal'][$deviceKey] += count(array_unique($deviceVal['memNo'])); // 구매자수
                        $deviceSales['goodsCntTotal'][$deviceKey] += $deviceVal['goodsCnt']; // 구매개수

                        $dateTotal['goodsPrice' . ucfirst(ucfirst($deviceKey))] += $deviceVal['goodsPrice'];
                        $dateTotal['goodsCnt' . ucfirst(ucfirst($deviceKey))] +=$deviceVal['goodsCnt'];
                        $dateTotal['orderCnt' . ucfirst(ucfirst($deviceKey))] += count(array_unique($deviceVal['orderNo']));
                        $dateTotal['memberCnt' . ucfirst(ucfirst($deviceKey))] += count(array_unique($deviceVal['memNo']));
                    }
                    $returnOrderStatistics[$i]['_extraData']['className']['column']['goodsPriceTotal'] = ['order-price'];
                    $returnOrderStatistics[$i]['goodsPriceTotal'] = NumberUtils::moneyFormat($orderDayGoodsPrice);
                    $returnOrderStatistics[$i]['goodsCntTotal'] = NumberUtils::moneyFormat($orderDayGoodsCnt);
                    $returnOrderStatistics[$i]['orderCntTotal'] = NumberUtils::moneyFormat($orderDayOrderCnt);
                    $returnOrderStatistics[$i]['memberCntTotal'] = NumberUtils::moneyFormat($orderDayMemberCnt);

                    $daySales[$key]['price'] += $orderDayGoodsPrice;
                    $daySales[$key]['orderCnt'] += $orderDayOrderCnt;

                    $dateTotal['goodsPriceTotal'] += $orderDayGoodsPrice;
                    $dateTotal['goodsCntTotal'] += $orderDayGoodsCnt;
                    $dateTotal['orderCntTotal'] += $orderDayOrderCnt;
                    $dateTotal['memberCntTotal'] += $orderDayMemberCnt;

                    $i++;
                }
                $returnOrderStatistics[$i]['_extraData']['className']['row'] = ['bold', 'day-price'];
                $returnOrderStatistics[$i]['orderMember'] = '총액';
                $returnOrderStatistics[$i]['goodsPriceTotal'] = NumberUtils::moneyFormat($dateTotal['goodsPriceTotal']);
                $returnOrderStatistics[$i]['goodsCntTotal'] = NumberUtils::moneyFormat($dateTotal['goodsCntTotal']);
                $returnOrderStatistics[$i]['orderCntTotal'] = NumberUtils::moneyFormat($dateTotal['orderCntTotal']);
                $returnOrderStatistics[$i]['memberCntTotal'] = NumberUtils::moneyFormat($dateTotal['memberCntTotal']);
                foreach ($deviceArr as $dKey => $dVal) {
                    $returnOrderStatistics[$i]['goodsPrice' . ucfirst(ucfirst($dVal))] = NumberUtils::moneyFormat($dateTotal['goodsPrice' . ucfirst(ucfirst($dVal))]);
                    $returnOrderStatistics[$i]['goodsCnt' . ucfirst($dVal)] = NumberUtils::moneyFormat($dateTotal['goodsCnt' . ucfirst(ucfirst($dVal))]);
                    $returnOrderStatistics[$i]['orderCnt' . ucfirst($dVal)] = NumberUtils::moneyFormat($dateTotal['orderCnt' . ucfirst(ucfirst($dVal))]);
                    $returnOrderStatistics[$i]['memberCnt' . ucfirst($dVal)] = NumberUtils::moneyFormat($dateTotal['memberCnt' . ucfirst(ucfirst($dVal))]);
                }

                $i++;
            }

            $daySalesTotal['min']['price'] = 0;
            $daySalesTotal['max']['price'] = 0;
            $daySalesTotal['min']['orderCnt'] = 0;
            $daySalesTotal['max']['orderCnt'] = 0;
            foreach ($daySales as $key => $val) {
                if ($val['price'] > 0) {
                    if ($daySalesTotal['min']['price'] > 0) {
                        if ($daySalesTotal['min']['price'] >= $val['price']) {
                            $daySalesTotal['min']['price'] = $val['price'];
                        }
                    } else {
                        $daySalesTotal['min']['price'] = $val['price'];
                    }
                    if ($daySalesTotal['max']['price'] <= $val['price']) {
                        $daySalesTotal['max']['price'] = $val['price'];
                    }
                }
                if ($val['orderCnt'] > 0) {
                    if ($daySalesTotal['min']['orderCnt'] > 0) {
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
            $this->setData('tabName', 'member');

            $this->getView()->setPageName('statistics/order_member.php');

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
