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

use Component\VisitStatistics\VisitStatistics;
use DateTime;
use Framework\Debug\Exception\AlertBackException;
use Request;

/**
 * 방문자 유입검색어분석
 * @author Seung-gak Kim <surlira@godo.co.kr>
 */
class VisitSearchWordController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('statistics', 'visit', 'inflow');

        // 모듈호출
        $visitStatistics = new VisitStatistics();

        try {
            // 2/23 디바이스별 방문자 통계 안내 문구 활성 여부
            $this->setData('noticeFl', $visitStatistics->noticeCommentFl);

            // 상점별 고유번호 - 해외상점
            $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

            $searchDevice = Request::get()->get('searchDevice');
            if (!$searchDevice) {
                $searchDevice = 'all';
            }
            $searchInflow = Request::get()->get('searchInflow');
            if (!$searchInflow) {
                $searchInflow = 'all';
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
                if ($eDate->format('Ymd') <= $endDate->format('Ymd')) {
                    $searchDate[1] = $eDate->format('Ymd');
                } else {
                    $searchDate[1] = $endDate->format('Ymd');
                }
            }

            $sDate = new DateTime($searchDate[0]);
            $eDate = new DateTime($searchDate[1]);
            $dateDiff = date_diff($sDate, $eDate);
            if ($dateDiff->days > 90) {
                $sDate = $eDate->modify('-6 day');
                $searchDate[0] = $sDate->format('Ymd');
            }

            $checked['searchPeriod'][$searchPeriod] = 'checked="checked"';
            $checked['searchDevice'][$searchDevice] = 'selected="selected"';
            $checked['searchInflow'][$searchInflow] = 'selected="selected"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('checked', $checked);
            $this->setData('active', $active);

            $searchData['date'] = $searchDate;
            $searchData['device'] = $searchDevice;
            $searchData['inflow'] = $searchInflow;
            $searchData['mallSno'] = $mallSno;
            $getDataArr = $visitStatistics->getVisitSearchWord($searchData);

            $i = 0;
            $totalSearchWord = array_sum($getDataArr);
            foreach ($getDataArr as $key => $val) {
                $returnVisitStatistics[$i]['searchWord'] = $key;
                $returnVisitStatistics[$i]['searchCount'] = $val;
                $returnVisitStatistics[$i]['searchPercent'] = round($val / $totalSearchWord * 100, 2);
                $i++;
            }

            if ($totalSearchWord > 20) {
                $rowDisplay = 20;
            } else if ($totalSearchWord == 0) {
                $rowDisplay = 5;
            } else {
                $rowDisplay = $totalSearchWord;
            }
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        $this->setData('rowList', json_encode($returnVisitStatistics));
        $this->setData('visitCount', $totalSearchWord);
        $this->setData('rowDisplay', $rowDisplay);

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
    }
}
