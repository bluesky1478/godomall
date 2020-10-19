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
use Framework\Utility\StringUtils;
use Framework\Utility\ComponentUtils;
use Request;

/**
 * Class 통계-상품분석-판매순위분석
 * @package Bundle\Controller\Admin\Statistics
 * @author  yjwee
 */
class GoodsSaleOptionRankController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */

        try {
            $this->callMenu('statistics', 'goods', 'sale');
            $this->setData('tabName', 'option');

            $mall = new Mall();
            $searchMallList = $mall->getStatisticsMallList();
            $this->setData('searchMallList', $searchMallList);

            $searchMall = Request::get()->get('mallFl');
            if (!$searchMall) {
                $searchMall = 'all';
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

            $searchCate = Request::get()->get('cateCd');
            $searchGoods = Request::get()->get('goodsNm');
            $noCategoryFl = Request::get()->get('noCategoryFl');
            $underCategoryFl = Request::get()->get('underCategoryFl');
            $superCategoryFl = Request::get()->get('superCategoryFl');

            gd_isset($noCategoryFl, 'y');
            gd_isset($underCategoryFl, 'y');
            gd_isset($superCategoryFl, 'y');
            $checked['noCategoryFl'][$noCategoryFl] = 'selected="selected"';
            $checked['underCategoryFl'][$underCategoryFl] = 'selected="selected"';
            $checked['superCategoryFl'][$superCategoryFl] = 'selected="selected"';
            $checked['searchMall'][$searchMall] = 'checked="checked"';
            $checked['searchPeriod'][$searchPeriod] = 'checked="checked"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('searchCate', $searchCate);
            $this->setData('searchGoods', $searchGoods);
            $this->setData('checked', $checked);
            $this->setData('active', $active);

            $goodsStatistics = new GoodsStatistics();

            $searchData['goodsYMD'] = $searchDate;
            $searchData['cateCd'] = $searchCate;
            $searchData['noCategoryFl'] = $noCategoryFl;
            $searchData['underCategoryFl'] = $underCategoryFl;
            $searchData['superCategoryFl'] = $superCategoryFl;
            $searchData['goodsNm'] = $searchGoods;
            $searchData['mallSno'] = $searchMall;

            $getDataArr = $goodsStatistics->getSaleGoodsOptionStatistics($searchData);

            // 통계 엑셀 데이터
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                if ($val['goodsNo']) {
                    $returnGoodsStatistics[$i]['goodsImg'] = gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank', null, true);
                    $returnGoodsStatistics[$i]['goodsNo'] = $val['goodsNo'];
                    $returnGoodsStatistics[$i]['goodsNm'] = $val['goodsNm'];
                    $option = json_decode(gd_htmlspecialchars_stripslashes($val['optionInfo']), true);
                    if (empty($option) === false) {
                        $optionInfo = [];
                        foreach ($option as $oKey => $oVal) {
                            $optionInfo[] = $oVal[1];
                        }
                        $returnGoodsStatistics[$i]['optionInfo'] = implode(' / ', $optionInfo);
                        unset($option);
                        unset($optionInfo);
                    }
                    StringUtils::strIsSet($val['pc']['price'], 0);
                    StringUtils::strIsSet($val['pc']['orderCnt'], 0);
                    StringUtils::strIsSet($val['pc']['goodsCnt'], 0);
                    $returnGoodsStatistics[$i]['pcPrice'] = $val['pc']['price'];
                    $returnGoodsStatistics[$i]['pcOrderCnt'] = $val['pc']['orderCnt'];
                    $returnGoodsStatistics[$i]['pcGoodsCnt'] = $val['pc']['goodsCnt'];

                    StringUtils::strIsSet($val['mobile']['price'], 0);
                    StringUtils::strIsSet($val['mobile']['orderCnt'], 0);
                    StringUtils::strIsSet($val['mobile']['goodsCnt'], 0);
                    $returnGoodsStatistics[$i]['mobilePrice'] = $val['mobile']['price'];
                    $returnGoodsStatistics[$i]['mobileOrderCnt'] = $val['mobile']['orderCnt'];
                    $returnGoodsStatistics[$i]['mobileGoodsCnt'] = $val['mobile']['goodsCnt'];

                    StringUtils::strIsSet($val['write']['price'], 0);
                    StringUtils::strIsSet($val['write']['orderCnt'], 0);
                    StringUtils::strIsSet($val['write']['goodsCnt'], 0);
                    $returnGoodsStatistics[$i]['writePrice'] = $val['write']['price'];
                    $returnGoodsStatistics[$i]['writeOrderCnt'] = $val['write']['orderCnt'];
                    $returnGoodsStatistics[$i]['writeGoodsCnt'] = $val['write']['goodsCnt'];

                    $returnGoodsStatistics[$i]['totalPrice'] = $returnGoodsStatistics[$i]['pcPrice'] + $returnGoodsStatistics[$i]['mobilePrice'] + $returnGoodsStatistics[$i]['writePrice'];
                    $returnGoodsStatistics[$i]['totalGoodsCnt'] = $returnGoodsStatistics[$i]['pcGoodsCnt'] + $returnGoodsStatistics[$i]['mobileGoodsCnt'] + $returnGoodsStatistics[$i]['writeGoodsCnt'];
                    $returnGoodsStatistics[$i]['totalOrderCnt'] = $returnGoodsStatistics[$i]['pcOrderCnt'] + $returnGoodsStatistics[$i]['mobileOrderCnt'] + $returnGoodsStatistics[$i]['writeOrderCnt'];

                    $returnGoodsStatistics[$i]['_extraData']['className']['column']['totalPrice'] = ['order-price'];
                    $returnGoodsStatistics[$i]['_extraData']['className']['column']['totalGoodsCnt'] = ['order-price'];
                    $returnGoodsStatistics[$i]['_extraData']['className']['column']['totalOrderCnt'] = ['order-price'];

                    $i++;
                }
            }

            // 통계 합계
            $goodsTotal['pcPrice'] = array_sum(array_column($returnGoodsStatistics, 'pcPrice'));
            $goodsTotal['mobilePrice'] = array_sum(array_column($returnGoodsStatistics, 'mobilePrice'));
            $goodsTotal['writePrice'] = array_sum(array_column($returnGoodsStatistics, 'writePrice'));
            $goodsTotal['totalPrice'] = $goodsTotal['pcPrice'] + $goodsTotal['mobilePrice'] + $goodsTotal['writePrice'];
            $goodsTotal['pcOrderCnt'] = array_sum(array_column($returnGoodsStatistics, 'pcOrderCnt'));
            $goodsTotal['mobileOrderCnt'] = array_sum(array_column($returnGoodsStatistics, 'mobileOrderCnt'));
            $goodsTotal['writeOrderCnt'] = array_sum(array_column($returnGoodsStatistics, 'writeOrderCnt'));
            $goodsTotal['totalOrderCnt'] = $goodsTotal['pcOrderCnt'] + $goodsTotal['mobileOrderCnt'] + $goodsTotal['writeOrderCnt'];
            $goodsTotal['pcGoodsCnt'] = array_sum(array_column($returnGoodsStatistics, 'pcGoodsCnt'));
            $goodsTotal['mobileGoodsCnt'] = array_sum(array_column($returnGoodsStatistics, 'mobileGoodsCnt'));
            $goodsTotal['writeGoodsCnt'] = array_sum(array_column($returnGoodsStatistics, 'writeGoodsCnt'));
            $goodsTotal['totalGoodsCnt'] = $goodsTotal['pcGoodsCnt'] + $goodsTotal['mobileGoodsCnt'] + $goodsTotal['writeGoodsCnt'];

            $goodsCount = count($returnGoodsStatistics);
            if ($goodsCount > 20) {
                $rowDisplay = 20;
            } else if ($goodsCount == 0) {
                $rowDisplay = 5;
            } else {
                $rowDisplay = $goodsCount;
            }

            $selectCate = ComponentUtils::makeCategorySelectBox('goods', 'cateCd', $searchCate, null, false);

            $this->setData('selectBox', $selectCate);
            $this->setData('rowList', json_encode($returnGoodsStatistics));
            $this->setData('goodsCount', $goodsCount);
            $this->setData('rowDisplay', $rowDisplay);
            $this->setData('goodsTotal', $goodsTotal);
            $this->getView()->setPageName('statistics/goods_sale_rank.php');

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
            // 쿼리스트링
            $queryString = Request::getQueryString();
            if (!empty($queryString)) {
                $queryString = '?' . $queryString;
            }
            $this->setData('queryString', $queryString);
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
