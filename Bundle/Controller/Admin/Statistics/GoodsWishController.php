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
 * Class 통계-상품분석-관심상품 분석
 * @package Bundle\Controller\Admin\Statistics
 * @author  su
 */
class GoodsWishController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */

        try {
            $this->callMenu('statistics', 'goods', 'wishStatistics');

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

            $searchField = [
                'all' => __('=통합검색='),
                'goodsNm' => __('상품명'),
                'goodsNo' => __('상품코드'),
                'goodsCd' => __('자체상품코드'),
                'goodsSearchWord' => __('검색 키워드'),
                'goodsModelNo' => __('모델번호')
            ];
            $searchKey = Request::get()->get('key');
            $searchKeyword = Request::get()->get('keyword');

            $searchSellFl = Request::get()->get('goodsSellFl');
            $searchSoldOut = Request::get()->get('soldOut');

            $checked['goodsSellFl'][$searchSellFl] = 'checked="checked"';
            $checked['soldOut'][$searchSoldOut] = 'checked="checked"';
            $checked['searchMall'][$searchMall] = 'checked="checked"';
            $checked['searchPeriod'][$searchPeriod] = 'checked="checked"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('searchField', $searchField);
            $this->setData('searchKey', $searchKey);
            $this->setData('searchKeyword', $searchKeyword);
            $this->setData('checked', $checked);
            $this->setData('active', $active);

            $goodsStatistics = new GoodsStatistics();

            $searchData['wishYMD'] = $searchDate;
            $searchData['goodsSellFl'] = $searchSellFl;
            $searchData['soldOutFl'] = $searchSoldOut;
            $searchData['key'] = $searchKey;
            $searchData['keyword'] = $searchKeyword;
            $searchData['mallSno'] = $searchMall;

            $getDataArr = $goodsStatistics->getGoodsWishStatistics($searchData);

            // 통계 엑셀 데이터
            $i = 0;
            foreach ($getDataArr as $key => $val) {
                if ($val['goodsNo']) {
                    $returnGoodsStatistics[$i]['goodsImg'] = gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank', null, true);
                    $returnGoodsStatistics[$i]['goodsNo'] = $val['goodsNo'];
                    $returnGoodsStatistics[$i]['goodsNm'] = $val['goodsNm'];
                    $returnGoodsStatistics[$i]['goodsPrice'] = $val['price'];
                    $returnGoodsStatistics[$i]['goodsRegDt'] = $val['regDt'];
                    if ($val['soldOutFl'] == 'y') {
                        $stockCode = '품절';
                    } else {
                        if ($val['stockFl'] == 'y') {
                            if ($val['totalStock'] <= 0) {
                                $stockCode = '품절';
                            } else {
                                $stockCode = $val['totalStock'];
                            }
                        } else {
                            $stockCode = '무제한';
                        }
                    }
                    $returnGoodsStatistics[$i]['goodsStock'] = $stockCode;
                    $returnGoodsStatistics[$i]['goodsMember'] = '<span class="js-wish-member hand" data-goods="' . $val['goodsNo'] . '">' . count($val['memNo']) . '</span>';
                    $i++;
                }
            }

            $goodsCount = count($returnGoodsStatistics);
            if ($goodsCount > 20) {
                $rowDisplay = 20;
            } else if ($goodsCount == 0) {
                $rowDisplay = 5;
            } else {
                $rowDisplay = $goodsCount;
            }

            $this->setData('rowList', json_encode($returnGoodsStatistics));
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
