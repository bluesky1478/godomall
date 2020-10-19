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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Statistics;

use Component\GoodsStatistics\GoodsStatistics;
use Component\Mall\Mall;
use DateTime;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Request;

/**
 * Class 통계-상품분석-검색어순위분석
 * @package Bundle\Controller\Admin\Statistics
 * @author  yjwee
 */
class GoodsSearchWordRankController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        try {
            $this->callMenu('statistics', 'goods', 'searchWordRank');

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

            $searchWord = Request::get()->get('searchWord');
            $searchType = Request::get()->get('searchType');

            $checked['searchMall'][$searchMall] = 'checked="checked"';
            $checked['searchPeriod'][$searchPeriod] = 'checked="checked"';
            $checked['searchType'][$searchType] = 'checked="checked"';
            $checked['searchDevice'][$searchDevice] = 'selected="selected"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('searchWord', $searchWord);
            $this->setData('checked', $checked);
            $this->setData('active', $active);

            $goodsStatistics = new GoodsStatistics();

            $searchData['regDt'] = $searchDate;
            $searchData['mallSno'] = $searchMall;
            $searchData['keyword'] = $searchWord;
            $searchData['searchType'] = $searchType;
            $searchData['searchDevice'] = $searchDevice;

            $getDataArr = $goodsStatistics->getSearchWordStatistics($searchData);

            $totalCount = array_sum(array_column($getDataArr, 'searchCount'));

            // 통계 엑셀 데이터
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                $returnSearchWordStatistics[$i]['keyword'] = $val['keyword'];
                $returnSearchWordStatistics[$i]['searchCount'] = $val['searchCount'];
                $percent = round(($val['searchCount'] / $totalCount) * 100, 2);
                $returnSearchWordStatistics[$i]['percent'] ="<div class='progress'><div class='progress-bar progress-bar-info' style='width:". $percent . "%'><strong class='text-black'>". $percent . "%</strong></div></div>";

                $i++;
            }

            $goodsCount = count($returnSearchWordStatistics);
            if ($goodsCount > 20) {
                $rowDisplay = 20;
            } else if ($goodsCount == 0) {
                $rowDisplay = 5;
            } else {
                $rowDisplay = $goodsCount;
            }

            $this->setData('rowList', json_encode($returnSearchWordStatistics));
            $this->setData('goodsCount', $goodsCount);
            $this->setData('rowDisplay', $rowDisplay);

            $this->addScript(
                [
                    'backbone/backbone-min.js',
                    'tui/code-snippet.min.js',
                    'tui.grid/grid.min.js',
                    'jquery/jquery.multi_select_box.js',
                ]
            );

            $this->addCss(
                [
                    'tui.grid/grid.css',
                ]
            );
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
