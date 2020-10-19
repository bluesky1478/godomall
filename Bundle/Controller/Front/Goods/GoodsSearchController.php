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
namespace Bundle\Controller\Front\Goods;

use Framework\Debug\Exception\Except;
use Framework\Debug\Exception\AlertBackException;
use Component\Page\Page;
use Component\Nhn\PaycosearchApi;
use Request;
use Globals;
use Session;

class GoodsSearchController  extends \Controller\Front\Controller
{

    /**
     * 상품 검색
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright Copyright (c), Godosoft
     */
    public function index()
    {
        try {
            // 모듈 설정
            $goods = \App::load('\\Component\\Goods\\Goods');
            $paycosearch = \App::load('\\Component\\Nhn\\PaycosearchApi');

            if(!Request::get()->has('key')) {
                Request::get()->set('key','all');
            }

            $getValue = Request::get()->xss()->toArray();

            //설정
            $goodsConfig = gd_policy('search.goods');
            gd_isset($goodsConfig['pcThemeCd'],'A0000001');
            $hitKeywordConfig = gd_policy('search.hitKeyword');

            if ($getValue['mode'] =='quick' && $getValue['quickCateGoods']) {
                Request::get()->set('cateGoods',$getValue['quickCateGoods']);
            }
            if($getValue['quickBrandGoods']) {
                Request::get()->set('brand',array_pop(array_filter(array_map('trim',gd_isset($getValue['quickBrandGoods'])))));
            }

            //테마정보
            $displayConfig = \App::load('\\Component\\Display\\DisplayConfig');
            $themeInfo = $displayConfig->getInfoThemeConfig($goodsConfig['pcThemeCd']);
            $themeInfo['displayField'] = explode(",", $themeInfo['displayField']);
            $themeInfo['goodsDiscount'] = explode(",", $themeInfo['goodsDiscount']);
            $themeInfo['priceStrike'] = explode(",", $themeInfo['priceStrike']);
            $themeInfo['displayAddField'] = explode(",", $themeInfo['displayAddField']);

            $displayCnt = gd_isset($themeInfo['lineCnt']) * gd_isset($themeInfo['rowCnt']);
            $pageNum = gd_isset($getValue['pageNum'],$displayCnt);
            $optionFl = in_array('option',array_values($themeInfo['displayField']))  ? true : false;
            $soldOutFl = (gd_isset($themeInfo['soldOutFl']) == 'y' ? true : false); // 품절상품 출력 여부
            $brandFl =  in_array('brandCd',array_values($themeInfo['displayField']))  ? true : false;
            $couponPriceFl =in_array('coupon',array_values($themeInfo['displayField']))  ? true : false;	 // 쿠폰가 출력 여부
            $brandDisplayFl =in_array('brand',array_values($goodsConfig['searchType']))  ? true : false;	 // 브랜드 출력여부

            if ($themeInfo['soldOutDisplayFl'] == 'n') $goodsConfig['sort'] = "soldOut asc," . $goodsConfig['sort'];

            $paycosearchDataCheck = false;
            if ($paycosearch->paycoSearchActionPoint() === true) {
                // 페이코 서치 사용
                $paycoSearchReturnData = $paycosearch->paycoSearchDataProcess($getValue,'pc', gd_isset($themeInfo['imageCd']));
                $goodsData = $paycosearch->paycoSearchSortData($paycoSearchReturnData, $displayCnt);

                if(!empty($goodsData['listData'])) {
                    $paycosearchDataCheck = true;
                    $page = \App::load('\\Component\\Page\\Page');
                    $page->page['now'] = ($getValue['page'] ? $getValue['page'] : 1); // 페이지
                    $page->page['list'] = $pageNum; // 페이지당 리스트 수
                    $page->block['cnt'] = !Request::isMobile() ? 10 : 5; // 블록당 리스트 개수
                    $page->setPage();
                    $page->setUrl(\Request::getQueryString());
                    $page->recode['total'] = $paycoSearchReturnData['paycoSearchTotal'];
                    $page->setPage();
                    $paycosearchUse = true;
                }
            }

            if(!$paycosearchDataCheck && $paycosearch->paycoSearchActionPoint() === false) {
                // 최근 본 상품 진열
                $goods->setThemeConfig($themeInfo);
                $goodsData	= $goods->getGoodsSearchList($pageNum, gd_isset($goodsConfig['sort']), gd_isset($themeInfo['imageCd']), $optionFl , $soldOutFl , $brandFl, $couponPriceFl ,$displayCnt,$brandDisplayFl);
                $paycosearchUse = false;
            }
            if($goodsData['listData']) $goodsList = array_chunk($goodsData['listData'],$themeInfo['lineCnt']);

            //통합검색 삭제
            unset($goodsData['listSearch']['combineSearch']['all']);

            $pager = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            // 최근검색어 쿠키 저장
            if (empty($getValue['keyword']) === false) {
                $goods->getRecentKeywordSearch($getValue['keyword']);
            }
        }         // --- 오류 발생시
        catch (except $e) {
            // echo ($e->ectMessage);
            // 설정 오류 : 화면 출력용
            if ($e->ectName == 'ERROR_VIEW') {
                $item = ($e->ectMessage ? ' - ' . str_replace('\n', ' - ', gd_isset($e->ectMessage, $e->ectMessage)) : '');
                throw new AlertBackException(__('안내') . $item);

                // 시스템 오류 : 실패 메시지만 보여주고 자세한 내용은 log 참고
            } else {
                $e->actLog();
                throw new AlertBackException(__('오류') . ' - ' . __('오류가 발생 하였습니다.'));
            }
        }

        if(in_array('category',$goodsConfig['searchType']))
        {
            $this->addScript([
                'gd_multi_select_box.js',
                'jquery/validation/jquery.validate.js'
            ]);

            $cate = \App::load('\\Component\\Category\\Category');
            if(!gd_is_skin_division()) {
                $addCss = "style='width:100%;'";
            }
            $cateDisplay = $cate->getMultiCategoryBox(null,$goodsData['listSearch']['cateGoods'],$addCss,true);

            $this->setData('cateDisplay', gd_isset($cateDisplay));

        }

        if (Session::has(SESSION_GLOBAL_MALL)) {
            if(in_array('delivery',$goodsConfig['searchType'])) {
                unset($goodsConfig['searchType'][array_search('delivery', $goodsConfig['searchType'])]);
            }
        }

        //마일리지 데이터
        $mileage = gd_mileage_give_info();

        // 카테고리 노출항목 중 상품할인가
        if (in_array('goodsDcPrice', $themeInfo['displayField'])) {
            foreach ($goodsList as $key => $val) {
                foreach ($val as $key2 => $val2) {
                    $goodsList[$key][$key2]['goodsDcPrice'] = $goods->getGoodsDcPrice($val2);
                }
            }
        }

        if ($themeInfo['displayType'] == '02' || $themeInfo['displayType'] == '11') {
            $cartInfo = gd_policy('order.cart'); //장바구니설정
            $this->setData('cartInfo', gd_isset($cartInfo));
        }

        $this->setData('goodsConfig', gd_isset($goodsConfig));
        $this->setData('hitKeywordConfig', gd_isset($hitKeywordConfig));
        $this->setData('keywordConfig', gd_isset($keywordConfig));

        $this->setData('themeInfo', gd_isset($themeInfo));
        $this->setData('goodsList', gd_isset($goodsList));
        $this->setData('search', gd_isset($goodsData['listSearch']));
        $this->setData('goodsData', gd_isset($goodsData));
        $this->setData('page', gd_isset($pager));
        $this->setData('sort', gd_isset($getValue['sort']));
        $this->setData('pageNum', gd_isset($getValue['pageNum']));
        $this->setData('soldoutDisplay', gd_policy('soldout.pc'));
        $this->setData('paycosearchUse', $paycosearchUse);
        $this->setData('mileageData', gd_isset($mileage['info']));
        $this->setData('keyword', gd_isset($getValue['keyword']));

        if(in_array('color',$goodsConfig['searchType']))
        {
            $goodsColorList = $goods->getGoodsColorList();
            $this->setData('goodsColorList', gd_isset($goodsColorList));
        }

        if(in_array('icon',$goodsConfig['searchType']))
        {
            $goodsIcon = \App::load('\\Component\\Goods\\GoodsAdmin');
            $goodsIconList = $goodsIcon->getIconSearchList();
            $this->setData('goodsIconList', gd_isset($goodsIconList));
        }

        //facebook Dynamic Ads 외부 스크립트 적용
        $facebookAd = \App::Load('\\Component\\Marketing\\FacebookAd');
        $fbConfig = $facebookAd->getExtensionConfig();

        if(empty($fbConfig)===false && $fbConfig['fbUseFl'] == 'y') {
            // 상품번호 추출
            $goodsNo = [];
            foreach ($goodsList as $key => $val){
                foreach($val as $key2){
                    $goodsNo[] = $key2['goodsNo'];
                }
            }
            $fbScript = $facebookAd->getFbSearchScript($getValue['keyword'], $goodsNo);
            $this->setData('fbSearchScript', $fbScript);
        }

        $this->getView()->setDefine('goodsTemplate', 'goods/list/list_'.$themeInfo['displayType'].'.html');

    }
}

