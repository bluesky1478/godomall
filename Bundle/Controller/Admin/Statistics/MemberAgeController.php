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
 * Class MemberAgeController
 * @package Bundle\Controller\Admin\Statistics
 * @author  su
 */
class MemberAgeController extends \Controller\Admin\Controller
{
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

            $getDataArr = $memberStatistics->getMemberAllAge($searchDate, $mallSno);

            // 통계 데이터 일자별 처리 - 빈 일자 생성
            $startDate = new DateTime($searchDate[0]);
            $endDate = new DateTime($searchDate[1]);
            $diffDay = $endDate->diff($startDate)->days;
            for ($i = 0; $i <= $diffDay; $i++) {
                $searchDt = new DateTime($searchDate[0]);
                $searchDt = $searchDt->modify('+' . $i . ' day');
                $resetData[$searchDt->format('Ymd')]['10'] = 0;
                $resetData[$searchDt->format('Ymd')]['20'] = 0;
                $resetData[$searchDt->format('Ymd')]['30'] = 0;
                $resetData[$searchDt->format('Ymd')]['40'] = 0;
                $resetData[$searchDt->format('Ymd')]['50'] = 0;
                $resetData[$searchDt->format('Ymd')]['60'] = 0;
                $resetData[$searchDt->format('Ymd')]['70'] = 0;
                $resetData[$searchDt->format('Ymd')]['etc'] = 0;
            }
            $getDataArr = $getDataArr + $resetData;
            ksort($getDataArr);

            // 통계 합계
            $memberTotal = [];

            // 통계 그래프
            $memberChart = [];
            $memberDate = array_keys($getDataArr);
            $member10 = array_column($getDataArr, 10);
            $member20 = array_column($getDataArr, 20);
            $member30 = array_column($getDataArr, 30);
            $member40 = array_column($getDataArr, 40);
            $member50 = array_column($getDataArr, 50);
            $member60 = array_column($getDataArr, 60);
            $member70 = array_column($getDataArr, 70);
            $memberEtc = array_column($getDataArr, 'etc');

            // 통계 엑셀 데이터
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                $returnMemberStatistics[$i]['memberDate'] = substr($key, 0, 4) . '-' . substr($key, 4, 2) . '-' . substr($key, -2);
                $total = $val['10'] + $val['20'] + $val['30'] + $val['40'] + $val['50'] + $val['60'] + $val['70'] + $val['etc'];
                $returnMemberStatistics[$i]['_extraData']['className']['column']['nowTotal'] = ['order-price'];
                $returnMemberStatistics[$i]['nowTotal'] = $total;
                $returnMemberStatistics[$i]['now10'] = $val['10'];
                $returnMemberStatistics[$i]['now20'] = $val['20'];
                $returnMemberStatistics[$i]['now30'] = $val['30'];
                $returnMemberStatistics[$i]['now40'] = $val['40'];
                $returnMemberStatistics[$i]['now50'] = $val['50'];
                $returnMemberStatistics[$i]['now60'] = $val['60'];
                $returnMemberStatistics[$i]['now70'] = $val['70'];
                $returnMemberStatistics[$i]['nowEtc'] = $val['etc'];

                // 통계 그래프
                $memberChart['MemberTotal'][$i] = $total;
                $i++;
            }

            // 통계 합계
            $memberTotal['10'] = array_pop($member10);
            $memberTotal['20'] = array_pop($member20);
            $memberTotal['30'] = array_pop($member30);
            $memberTotal['40'] = array_pop($member40);
            $memberTotal['50'] = array_pop($member50);
            $memberTotal['60'] = array_pop($member60);
            $memberTotal['70'] = array_pop($member70);
            $memberTotal['etc'] = array_pop($memberEtc);
            $memberTotal['total'] = array_pop($memberChart['MemberTotal']);
            if ($memberTotal['total'] > 0) {
                $memberTotal['percent10'] = round(($memberTotal['10'] / $memberTotal['total']) * 100, 1) . '%';
                $memberTotal['percent20'] = round(($memberTotal['20'] / $memberTotal['total']) * 100, 1) . '%';
                $memberTotal['percent30'] = round(($memberTotal['30'] / $memberTotal['total']) * 100, 1) . '%';
                $memberTotal['percent40'] = round(($memberTotal['40'] / $memberTotal['total']) * 100, 1) . '%';
                $memberTotal['percent50'] = round(($memberTotal['50'] / $memberTotal['total']) * 100, 1) . '%';
                $memberTotal['percent60'] = round(($memberTotal['60'] / $memberTotal['total']) * 100, 1) . '%';
                $memberTotal['percent70'] = round(($memberTotal['70'] / $memberTotal['total']) * 100, 1) . '%';
                $memberTotal['percentEtc'] = (100 - $memberTotal['percent10'] - $memberTotal['percent20'] - $memberTotal['percent30'] - $memberTotal['percent40'] - $memberTotal['percent50'] - $memberTotal['percent60'] - $memberTotal['percent70']) . '%';
            } else {
                $memberTotal['percent10'] = 0 . '%';
                $memberTotal['percent20'] = 0 . '%';
                $memberTotal['percent30'] = 0 . '%';
                $memberTotal['percent40'] = 0 . '%';
                $memberTotal['percent50'] = 0 . '%';
                $memberTotal['percent60'] = 0 . '%';
                $memberTotal['percent70'] = 0 . '%';
                $memberTotal['percentEtc'] = 0 . '%';
            }

            // 통계 그래프
            foreach ($memberDate as $key => $val) {
                $memberDate[$key] = "'" . $val . "'";
            }
            $memberChart['Date'] = $memberDate;
            $memberChart['Member10'] = $member10;
            $memberChart['Member20'] = $member20;
            $memberChart['Member30'] = $member30;
            $memberChart['Member40'] = $member40;
            $memberChart['Member50'] = $member50;
            $memberChart['Member60'] = $member60;
            $memberChart['Member70'] = $member70;
            $memberChart['MemberEtc'] = $memberEtc;
            if (count($memberChart['Date']) < 2) {
                $emptyDateArr = ['0' => "'0'"];
                $emptyArr = ['0' => 0];
                $memberChart['Date'] = array_merge($emptyDateArr, $memberChart['Date']);
                $memberChart['Member10'] = array_merge($emptyArr, $memberChart['Member10']);
                $memberChart['Member20'] = array_merge($emptyArr, $memberChart['Member20']);
                $memberChart['Member30'] = array_merge($emptyArr, $memberChart['Member30']);
                $memberChart['Member40'] = array_merge($emptyArr, $memberChart['Member40']);
                $memberChart['Member50'] = array_merge($emptyArr, $memberChart['Member50']);
                $memberChart['Member60'] = array_merge($emptyArr, $memberChart['Member60']);
                $memberChart['Member70'] = array_merge($emptyArr, $memberChart['Member70']);
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
