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
use Component\Mall\Mall;
use DateTime;
use Framework\Debug\Exception\AlertBackException;
use Request;

/**
 * 방문통계 당일
 * @author Seung-gak Kim <surlira@godo.co.kr>
 */
class VisitTodayController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('statistics', 'visit', 'visitDay');

        // 모듈호출
        $visitStatistics = new VisitStatistics();

        try {
            // 2/23 디바이스별 방문자 통계 안내 문구 활성 여부
            $this->setData('noticeFl', $visitStatistics->noticeCommentFl);

            $mall = new Mall();
            $searchMallList = $mall->getStatisticsMallList();
            $this->setData('searchMallList', $searchMallList);

            $searchMall = Request::get()->get('mallFl');
            if (!$searchMall) {
                $searchMall = 'all';
            }

            $sDate = new DateTime();
            $eDate = new DateTime();
            $searchDate[0] = $sDate->format('Y-m-d');
            $searchDate[1] = $eDate->format('Y-m-d');

            $checked['searchMall'][$searchMall] = 'checked="checked"';
            $this->setData('searchDate', $searchDate);
            $this->setData('checked', $checked);

            $getDataArr = $visitStatistics->getVisitToday($searchMall);
            $this->setData('totalVisit', $getDataArr['total']);

            $visitChart = [];
            for ($i = 0; $i <= 23; $i++) {
                $hour = sprintf("%02d", $i) . ':00';
                $val = $getDataArr['hour'][$i];
                $returnVisitStatistics[$i]['visitDate'] = $hour;
                $returnVisitStatistics[$i]['visitCount'] = gd_isset($val['visitCount'], 0);
                $returnVisitStatistics[$i]['visitCountPc'] = gd_isset($val['pc']['visitCount'], 0);
                $returnVisitStatistics[$i]['visitCountMobile'] = gd_isset($val['mobile']['visitCount'], 0);
                $returnVisitStatistics[$i]['visitPv'] = gd_isset($val['pv'], 0);
                $returnVisitStatistics[$i]['visitPvPc'] = gd_isset($val['pc']['pv'], 0);
                $returnVisitStatistics[$i]['visitPvMobile'] = gd_isset($val['mobile']['pv'], 0);

                $visitChart['Date'][$i]  = '\'' . $i . '\'';
                $visitChart['Count'][$i]  = $returnVisitStatistics[$i]['visitCount'];
                $visitChart['Pv'][$i]  = $returnVisitStatistics[$i]['visitPv'];
                $visitChart['CountPc'][$i]  = $returnVisitStatistics[$i]['visitCountPc'];
                $visitChart['PvPc'][$i]  = $returnVisitStatistics[$i]['visitPvPc'];
                $visitChart['CountMobile'][$i]  = $returnVisitStatistics[$i]['visitCountMobile'];
                $visitChart['PvMobile'][$i]  = $returnVisitStatistics[$i]['visitPvMobile'];

            }
            $visitCount = count($returnVisitStatistics);

            if ($visitCount > 20) {
                $rowDisplay = 20;
            } else if ($visitCount == 0) {
                $rowDisplay = 5;
            } else {
                $rowDisplay = $visitCount;
            }
            $this->setData('visitChart', $visitChart);
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        $this->setData('rowList', json_encode($returnVisitStatistics));
        $this->setData('visitCount', $visitCount);
        $this->setData('rowDisplay', $rowDisplay);

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
    }
}
