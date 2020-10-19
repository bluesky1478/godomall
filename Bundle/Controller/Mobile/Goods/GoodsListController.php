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
namespace Bundle\Controller\Mobile\Goods;

use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\Framework\Debug\Exception;
use Message;
use Globals;
use Request;
use Cookie;

class GoodsListController extends \Controller\Mobile\Controller
{

    /**
     * 상품목록
     *
     * @author artherot, sunny
     * @version 1.0
     * @since 1.0
     * @copyright Copyright (c), Godosoft
     * @throws Except
     */
    public function index()
    {
        $checkParameter = ['cateCd', 'brandCd'];
        $getValue = Request::get()->length($checkParameter)->toArray();

        // 모듈 설정
        $goods = \App::load('\\Component\\Goods\\Goods');
        if($getValue['brandCd']) $cate = \App::load('\\Component\\Category\\Brand');
        else $cate = \App::load('\\Component\\Category\\Category');

        try {
            // 카테고리 정보
            if($getValue['brandCd'])  {
                $cateCd =  $getValue['brandCd'];
                $cateType = "brand";
                $cateMode = "brand";
                $naviDisplay = gd_policy('display.navi_brand');
            } else {
                $cateCd = $getValue['cateCd'];
                $cateType = "cate";
                $cateMode = "category";
                $naviDisplay = gd_policy('display.navi_category');
            }

            $cateInfo = $cate->getCategoryGoodsList($cateCd,'y');
            $goodsCategoryList = $cate->getCategories($cateCd,'y');
            $goodsCategoryListNm = array_column($goodsCategoryList, 'cateNm');

            if(gd_isset($cateInfo['themeCd']) ===null) {
                throw new \Exception(__('상품의 테마 설정을 확인해주세요.'));
            }

            // 마일리지 정보
            $mileage = gd_mileage_give_info();

            $this->setData('gPageName', $goodsCategoryList[$cateCd]['cateNm']);
            $this->setData('cateInfo', gd_isset($cateInfo));

            Request::get()->set('page',$getValue['page']);
            Request::get()->set('sort',$getValue['sort']);

            if($cateInfo['recomDisplayMobileFl'] =='y' && $cateInfo['recomGoodsNo'])
            {
                $recomTheme = $cateInfo['recomTheme'];
                if ($recomTheme['detailSet']) {
                    $recomTheme['detailSet'] = unserialize($recomTheme['detailSet']);
                }

                gd_isset($recomTheme['lineCnt'],4);
                $imageType		= gd_isset($recomTheme['imageCd'],'list');						// 이미지 타입 - 기본 'main'
                $soldOutFl		= $recomTheme['soldOutFl'] == 'y' ? true : false;			// 품절상품 출력 여부 - true or false (기본 true)
                $brandFl		= in_array('brandCd',array_values($recomTheme['displayField']))  ? true : false;	// 브랜드 출력 여부 - true or false (기본 false)
                $couponPriceFl	= in_array('coupon',array_values($recomTheme['displayField']))  ? true : false;		// 쿠폰가격 출력 여부 - true or false (기본 false)
                $optionFl = in_array('option',array_values($recomTheme['displayField']))  ? true : false;

                if($cateInfo['recomSortAutoFl'] =='y') $recomOrder = $cateInfo['recomSortType'].",g.goodsNo desc";
                else $recomOrder = "FIELD(g.goodsNo," . str_replace(INT_DIVISION, ",", $cateInfo['recomGoodsNo']) . ")";
                if ($recomTheme['soldOutDisplayFl'] == 'n') $recomOrder = "soldOut asc," . $recomOrder;
                $recomTheme['goodsDiscount'] = explode(',', $recomTheme['goodsDiscount']);
                $recomTheme['priceStrike'] = explode(',', $recomTheme['priceStrike']);
                $recomTheme['displayAddField'] = explode(',', $recomTheme['displayAddField']);

                $goods->setThemeConfig($recomTheme);
                $goodsRecom	= $goods->goodsDataDisplay('goods', $cateInfo['recomGoodsNo'], (gd_isset($recomTheme['lineCnt']) * gd_isset($recomTheme['rowCnt'])), $recomOrder, $imageType, $optionFl, $soldOutFl, $brandFl, $couponPriceFl);

                if($goodsRecom) $goodsRecom = array_chunk($goodsRecom,$recomTheme['lineCnt']);

                $this->setData('widgetGoodsList', gd_isset($goodsRecom));
                $this->setData('widgetTheme', $recomTheme);
            }


            if($cateInfo['soldOutDisplayFl'] =='n')  $displayOrder[] = "soldOut asc";

            if ($cateInfo['sortAutoFl'] == 'y') $displayOrder[] = "gl.fixSort desc," . gd_isset($cateInfo['sortType'], 'gl.goodsNo desc');
            else $displayOrder[] = "gl.fixSort desc,gl.goodsSort desc";

            // 상품 정보
            $displayCnt = gd_isset($cateInfo['lineCnt']) * gd_isset($cateInfo['rowCnt']);
            $pageNum = gd_isset($getValue['pageNum'],$displayCnt);
            $optionFl = in_array('option',array_values($cateInfo['displayField']))  ? true : false;
            $soldOutFl = (gd_isset($cateInfo['soldOutFl']) == 'y' ? true : false); // 품절상품 출력 여부
            $brandFl =  in_array('brandCd',array_values($cateInfo['displayField']))  ? true : false;
            $couponPriceFl =in_array('coupon',array_values($cateInfo['displayField']))  ? true : false;	 // 쿠폰가 출력 여부
            $goods->setThemeConfig($cateInfo);
            $goodsData = $goods->getGoodsList($cateCd, $cateMode, $pageNum,$displayOrder, gd_isset($cateInfo['imageCd']), $optionFl, $soldOutFl, $brandFl, $couponPriceFl,null,$displayCnt);


            if($goodsData['listData']) $goodsList = array_chunk($goodsData['listData'],$cateInfo['lineCnt']);
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
            unset($goodsData['listData']);
            //품절상품 설정
            $soldoutDisplay = gd_policy('soldout.mobile');

            // 카테고리 노출항목 중 상품할인가
            if (in_array('goodsDcPrice', $cateInfo['displayField'])) {
                foreach ($goodsList as $key => $val) {
                    foreach ($val as $key2 => $val2) {
                        $goodsList[$key][$key2]['goodsDcPrice'] = $goods->getGoodsDcPrice($val2);
                    }
                }
            }

            // 장바구니 설정
            if ($cateInfo['displayType'] == '11') {
                $cartInfo = gd_policy('order.cart');
                $this->setData('cartInfo', gd_isset($cartInfo));
            }

            $this->setData('cateCd', $cateCd);
            $this->setData('goodsCategoryListNm', gd_isset($goodsCategoryListNm));
            $this->setData('brandCd', $getValue['brandCd']);
            $this->setData('cateType', $cateType);
            $this->setData('themeInfo', gd_isset($cateInfo));
            $this->setData('goodsList', gd_isset($goodsList));
            $this->setData('page', gd_isset($page));
            $this->setData('naviDisplay', gd_isset($naviDisplay));
            $this->setData('soldoutDisplay', gd_isset($soldoutDisplay));
            $this->setData('mileageData', gd_isset($mileage['info']));
            $this->setData('currency', Globals::get('gCurrency'));

            if($getValue['mode'] == 'data') {
                $this->getView()->setPageName('goods/list/list_'.$getValue['displayType']);
            } else {
                $this->getView()->setDefine('goodsTemplate', 'goods/list/list_'.$cateInfo['displayType'].'.html');
            }

        } catch (\Exception $e) {
            throw new AlertRedirectException($e->getMessage(),null,null,"/");
        }
    }
}
