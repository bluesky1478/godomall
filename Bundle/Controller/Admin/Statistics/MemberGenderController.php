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
 * Class MemberGenderController
 * @package Bundle\Controller\Admin\Statistics
 * @author  su
 */
class MemberGenderController extends \Controller\Admin\Controller
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

            $getDataArr = $memberStatistics->getMemberAllGender($searchDate, $mallSno);

            // 통계 데이터 일자별 처리 - 빈 일자 생성
            $startDate = new DateTime($searchDate[0]);
            $endDate = new DateTime($searchDate[1]);
            $diffDay = $endDate->diff($startDate)->days;
            for ($i = 0; $i <= $diffDay; $i++) {
                $searchDt = new DateTime($searchDate[0]);
                $searchDt = $searchDt->modify('+' . $i . ' day');
                $resetData[$searchDt->format('Ymd')]['male'] = 0;
                $resetData[$searchDt->format('Ymd')]['female'] = 0;
                $resetData[$searchDt->format('Ymd')]['etc'] = 0;
            }
            $getDataArr = $getDataArr + $resetData;
            ksort($getDataArr);

            // 통계 합계
            $memberTotal = [];

            // 통계 그래프
            $memberChart = [];
            $memberDate = array_keys($getDataArr);
            $memberMale = array_column($getDataArr, 'male');
            $memberFemale = array_column($getDataArr, 'female');
            $memberEtc = array_column($getDataArr, 'etc');

            // 통계 엑셀 데이터
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                $returnMemberStatistics[$i]['memberDate'] = substr($key,0,4) . '-' . substr($key,4,2) . '-' . substr($key,-2);
                $total = $val['male'] + $val['female'] + $val['etc'];
                $returnMemberStatistics[$i]['_extraData']['className']['column']['nowTotal'] = ['order-price'];
                $returnMemberStatistics[$i]['nowTotal'] = $total;
                $returnMemberStatistics[$i]['nowMale'] = $val['male'];
                $returnMemberStatistics[$i]['nowFemale'] = $val['female'];
                $returnMemberStatistics[$i]['nowEtc'] = $val['etc'];
                if ($total > 0) {
                    if ($val['male'] > 0) {
                        $malePercent = round(($val['male'] / $total) * 100);
                    } else {
                        $malePercent = 0;
                    }
                    if ($val['female'] > 0) {
                        $femalePercent = round(($val['female'] / $total) * 100);
                    } else {
                        $femalePercent = 0;
                    }
                    $etcPercent = 100 - $malePercent - $femalePercent;
                } else {
                    $malePercent = 0;
                    $femalePercent = 0;
                    $etcPercent = 0;
                }
                $returnMemberStatistics[$i]['nowPercent'] = "<div class='progress'><div class='progress-bar progress-bar-info' style='width:". $malePercent . "%'><strong class='text-black'>". $malePercent . "%</strong></div><div class='progress-bar progress-bar-success' style='width:". $femalePercent . "%'><strong class='text-black'>". $femalePercent . "%</strong></div><div class='progress-bar progress-bar-error' style='width:". $etcPercent . "%'><strong class='text-black'>". $etcPercent . "%</strong></div></div>";

                // 통계 그래프
                $memberChart['MemberTotal'][$i] = $total;
                $i++;
            }

            // 통계 합계
            $memberTotal['male'] = array_pop($memberMale);
            $memberTotal['female'] = array_pop($memberFemale);
            $memberTotal['etc'] = array_pop($memberEtc);
            $memberTotal['total'] = array_pop($memberChart['MemberTotal']);
            if ($memberTotal['total'] > 0) {
                $mPercent = round(($memberTotal['male'] / $memberTotal['total']) * 100, 1) . '%';
                $fPercent = round(($memberTotal['female'] / $memberTotal['total']) * 100, 1) . '%';
                $ePercent = (100 - $memberTotal['malePercent'] - $memberTotal['femalePercent']) . '%';
            } else {
                $mPercent = 0 . '%';
                $fPercent = 0 . '%';
                $ePercent = 0 . '%';
            }
            $memberTotal['malePercent'] = $mPercent;
            $memberTotal['femalePercent'] = $fPercent;
            $memberTotal['etcPercent'] = $ePercent;

            // 통계 그래프
            foreach ($memberDate as $key => $val) {
                $memberDate[$key] = "'" . $val . "'";
            }
            $memberChart['Date'] = $memberDate;
            $memberChart['MemberMale'] = $memberMale;
            $memberChart['MemberFemale'] = $memberFemale;
            $memberChart['MemberEtc'] = $memberEtc;
            if (count($memberChart['Date']) < 2) {
                $emptyDateArr = ['0' => "'0'"];
                $emptyArr = ['0' => 0];
                $memberChart['Date'] = array_merge($emptyDateArr, $memberChart['Date']);
                $memberChart['MemberMale'] = array_merge($emptyArr, $memberChart['MemberMale']);
                $memberChart['MemberFemale'] = array_merge($emptyArr, $memberChart['MemberFemale']);
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
        } catch (\Throwable $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
