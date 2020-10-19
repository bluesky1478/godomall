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
 * 방문자 IP분석
 * @author Seung-gak Kim <surlira@godo.co.kr>
 */
class VisitIpController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('statistics', 'visit', 'ip');

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
            $searchPeriod = Request::get()->get('searchPeriod');
            $searchDate = $visitStatistics->getVisitIpSearchDate(Request::get()->get('searchDate'));
            $searchIP = Request::get()->get('searchIP');

            $checked['searchPeriod'][$searchPeriod] = 'checked="checked"';
            $checked['searchDevice'][$searchDevice] = 'selected="selected"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('checked', $checked);
            $this->setData('active', $active);
            $this->setData('searchIP', $searchIP);
            $this->setData('$searchDevice', $searchDevice);

            $searchData = [
                'searchIP' => $searchIP,
                'page' => Request::get()->get('page'),
                'pageNum' => Request::get()->get('pageNum'),
            ];
            $getDataArr = $visitStatistics->getVisitStatisticsPage($searchDate, $searchDevice, $mallSno, true, $searchData);

            foreach($getDataArr as $key => $val) {
                $getDataArr[$key]['visitDetailLink'] = '<button class="btn btn-gray btn-sm detail-link" searchIP="' . $val['visitIP'] . '" searchOS="' . $val['visitOS'] . '" searchBrowser="' . $val['visitBrowser'] . '">보기</button>';
            }

            $visitCount = count($getDataArr);

            if ($visitCount > 20) {
                $rowDisplay = 20;
            } else if ($visitCount == 0) {
                $rowDisplay = 5;
            } else {
                $rowDisplay = $visitCount;
            }
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
            $this->setData('page', $page);

        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        $this->setData('rowList', json_encode($getDataArr));
        $this->setData('visitCount', $visitCount);
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
