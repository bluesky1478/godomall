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
use Framework\Debug\Exception\AlertBackException;
use Request;

/**
 * Class MemberAreaController
 * @package Bundle\Controller\Admin\Statistics
 * @author  su
 */
class MemberAreaController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws AlertBackException
     */
    public function index()
    {
        try {
            $this->callMenu('statistics', 'member', 'allGender');

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

            $getDataArr = $memberStatistics->getMemberAllArea($searchDate, $mallSno);

            $cityArrName = [
                '강원' => 'KW',
                '경기' => 'KG',
                '경남' => 'KN',
                '경북' => 'KB',
                '광주' => 'KJ',
                '대구' => 'DG',
                '대전' => 'DJ',
                '부산' => 'BS',
                '서울' => 'SW',
                '세종' => 'SJ',
                '울산' => 'WS',
                '인천' => 'IC',
                '전남' => 'JN',
                '전북' => 'JB',
                '제주' => 'JJ',
                '충남' => 'CN',
                '충북' => 'CB',
            ];

            // 통계 데이터 일자별 처리 - 빈 일자 생성
            $startDate = new DateTime($searchDate[0]);
            $endDate = new DateTime($searchDate[1]);
            $diffDay = $endDate->diff($startDate)->days;
            for ($i = 0; $i <= $diffDay; $i++) {
                $searchDt = new DateTime($searchDate[0]);
                $searchDt = $searchDt->modify('+' . $i . ' day');
                foreach ($cityArrName as $key => $val) {
                    $resetData[$searchDt->format('Ymd')][$key] = 0;
                }
                $resetData[$searchDt->format('Ymd')]['etc'] = 0;
            }
            $getDataArr = $getDataArr + $resetData;
            ksort($getDataArr);

            // 통계 합계
            $memberTotal = [];

            // 통계 그래프
            $memberChart = [];
            $memberDate = array_keys($getDataArr);
            foreach ($cityArrName as $key => $val) {
                ${'member'.$val} = array_column($getDataArr, $key);
            }
            $memberEtc = array_column($getDataArr, 'etc');

            // 통계 엑셀 데이터
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                $returnMemberStatistics[$i]['memberDate'] = substr($key, 0, 4) . '-' . substr($key, 4, 2) . '-' . substr($key, -2);
                $total = array_sum($val);
                $returnMemberStatistics[$i]['_extraData']['className']['column']['nowTotal'] = ['order-price'];
                $returnMemberStatistics[$i]['nowTotal'] = $total;
                foreach ($cityArrName as $cityKey => $cityVal) {
                    $returnMemberStatistics[$i]['now'.$cityVal] = $val[$cityKey];
                }
                $returnMemberStatistics[$i]['nowEtc'] = $val['etc'];

                // 통계 그래프
                $memberChart['MemberTotal'][$i] = $total;
                $i++;
            }

            // 통계 합계
            $memberTotal['total'] = array_pop($memberChart['MemberTotal']);
            foreach ($cityArrName as $cityKey => $cityVal) {
                $memberTotal[$cityKey] = array_pop(${'member'.$cityVal});
                if ($memberTotal['total'] > 0) {
                    $memberTotal['percent' . $cityVal] = round(($memberTotal[$cityKey] / $memberTotal['total']) * 100, 1) . '%';
                } else {
                    $memberTotal['percent' . $cityVal] = 0 . '%';
                }
            }
            $memberTotal['etc'] = array_pop($memberEtc);
            $memberTotalCityPercent = 0;
            foreach ($cityArrName as $cityKey => $cityVal) {
                $memberTotalCityPercent += $memberTotal['percent'.$cityVal];
            }
            if ($memberTotal['total'] > 0 && $memberTotalCityPercent <= 100) {
                $memberTotal['percentEtc'] = (100 - $memberTotalCityPercent) . '%';
            } else {
                $memberTotal['percentEtc'] = 0 . '%';
            }

            // 통계 그래프
            foreach ($memberDate as $key => $val) {
                $memberDate[$key] = "'" . $val . "'";
            }
            $memberChart['Date'] = $memberDate;
            foreach ($cityArrName as $cityKey => $cityVal) {
                $memberChart['Member'. $cityVal] = ${'member'.$cityVal};
            }
            $memberChart['MemberEtc'] = $memberEtc;
            if (count($memberChart['Date']) < 2) {
                $emptyDateArr = ['0' => "'0'"];
                $emptyArr = ['0' => 0];
                $memberChart['Date'] = array_merge($emptyDateArr, $memberChart['Date']);
                foreach ($cityArrName as $cityKey => $cityVal) {
                    $memberChart['Member'. $cityVal] = array_merge($emptyArr, $memberChart['Member'. $cityVal]);
                }
                $memberChart['MemberEtc'] = array_merge($emptyArr, $memberChart['MemberEtc']);
                $memberChart['MemberTotal'] = array_merge($emptyArr, $memberChart['MemberTotal']);
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
            $this->setData('cityArrName', $cityArrName);

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
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
