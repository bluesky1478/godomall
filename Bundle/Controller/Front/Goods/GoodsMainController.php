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

use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\Framework\Debug\Exception;
use Framework\Utility\DateTimeUtils;
use Message;
use Globals;
use Request;
use Session;

class GoodsMainController extends \Controller\Front\Controller
{

    /**
     * 메인리스트
     *
     * @author artherot, sunny
     * @version 1.0
     * @since 1.0
     * @copyright Copyright (c), Godosoft
     * @throws Except
     */
    public function index()
    {
        $getValue = gd_htmlspecialchars(Request::get()->toArray());

        $goods = \App::load('\\Component\\Goods\\Goods');
        $getData = $goods->getDisplayThemeInfo($getValue['sno']);
        $mainLinkData = [
            'mainThemeSno' => $getData['sno'],
            'mainThemeNm' => $getData['themeNm'],
            'mainThemeDevice' => $getData['mobileFl'],
        ];
        Request::get()->set('mainLinkData',$mainLinkData);
        //기획전 그룹형 그룹정보 로드
        if((int)$getValue['groupSno'] > 0) {
            $eventGroup = \App::load('\\Component\\Promotion\\EventGroupTheme');
            $getData = $eventGroup->replaceEventData($getValue['groupSno'], $getData);
        }
        Request::get()->set('isMain',true);

        if($getData && $getData['displayFl'] =='y') {
            if ($this->getData('isMobile') == 'y') {
                $themeCd = $getData['mobileThemeCd'];
            }
            else {
                $themeCd = $getData['themeCd'];
            }
            $displayConfig = \App::load('\\Component\\Display\\DisplayConfig');
            if (empty($themeCd) === true) {
                $themeInfo = $displayConfig->getInfoThemeConfigCate('B')[0];
            } else {
                $themeInfo = $displayConfig->getInfoThemeConfig($themeCd);
            }


            if ($themeInfo['detailSet']) $themeInfo['detailSet'] = unserialize($themeInfo['detailSet']);
            $themeInfo['displayField'] = explode(",", $themeInfo['displayField']);

            if($getValue['mode'] == 'get_main') $displayCnt = gd_isset($themeInfo['lineCnt']) * gd_isset($themeInfo['rowCnt'])*$getValue['more'];
            else $displayCnt = "20";
            $pageNum = gd_isset($getValue['pageNum'],$displayCnt);

            $imageType = gd_isset($themeInfo['imageCd'], 'main');                        // 이미지 타입 - 기본 'main'
            $soldOutFl = $themeInfo['soldOutFl'] == 'y' ? true : false;            // 품절상품 출력 여부 - true or false (기본 true)
            $brandFl = in_array('brandCd', array_values($themeInfo['displayField'])) ? true : false;    // 브랜드 출력 여부 - true or false (기본 false)
            $couponPriceFl = in_array('coupon', array_values($themeInfo['displayField'])) ? true : false;        // 쿠폰가격 출력 여부 - true or false (기본 false)
            $optionFl = in_array('option', array_values($themeInfo['displayField'])) ? true : false;
            $themeInfo['goodsDiscount'] = explode(',', $themeInfo['goodsDiscount']);
            $themeInfo['priceStrike'] = explode(',', $themeInfo['priceStrike']);
            $themeInfo['displayAddField'] = explode(',', $themeInfo['displayAddField']);
            $goods->setThemeConfig($themeInfo);

            $goodsNoData = implode(INT_DIVISION,array_filter(explode(STR_DIVISION,  $getData['goodsNo'])));
            if($getData['sortAutoFl'] =='n') {
                if($getData['goodsNo'] && $goodsNoData) {
                    Request::get()->set('goodsNo',explode(INT_DIVISION,$goodsNoData));
                    // PC 기획전 일반형/그룹형 정렬
                    if($getData['kind'] === 'event' && trim($getData['sort']) !== ''){
                        $mainOrder = $getData['sort'];
                        if(preg_match('/goodsPrice|orderCnt|hitCnt/', $getData['sort'])){
                            $sortType = explode(" ", $getData['sort']);
                            $mainOrder .= ', g.goodsNo '.$sortType[1];
                        }
                    }
                    else {
                        $mainOrder = "FIELD(g.goodsNo," . str_replace(INT_DIVISION, ",", $goodsNoData) . ")";
                    }
                    if ($themeInfo['soldOutDisplayFl'] == 'n') $mainOrder = "soldOut asc," . $mainOrder;
                    $goodsData = $goods->getGoodsSearchList($pageNum , $mainOrder, $imageType, $optionFl, $soldOutFl, $brandFl, $couponPriceFl  ,$displayCnt);
                    Request::get()->del('goodsNo');

                } else {
                    $goodsData['multiple'] = ['10'];
                }

            } else {

                $mainOrder = $getData['sort'];
                if ($themeInfo['soldOutDisplayFl'] == 'n') $mainOrder = "soldOut asc,".$mainOrder ;
                Request::get()->set('offsetGoodsNum','500');
                if($getData['exceptGoodsNo']) Request::get()->set('exceptGoodsNo',explode(INT_DIVISION, $getData['exceptGoodsNo']));
                if($getData['exceptCateCd']) Request::get()->set('exceptCateCd',explode(INT_DIVISION, $getData['exceptCateCd']));
                if($getData['exceptBrandCd']) Request::get()->set('exceptBrandCd',explode(INT_DIVISION, $getData['exceptBrandCd']));
                if($getData['exceptScmNo']) Request::get()->set('exceptScmNo',explode(INT_DIVISION, $getData['exceptScmNo']));

                if($getValue['mode'] != 'get_main') $imageType = "main";
                $goodsData = $goods->getGoodsSearchList($pageNum, $mainOrder, $imageType, $optionFl, $soldOutFl, $brandFl, $couponPriceFl ,$displayCnt );

                Request::get()->del('exceptGoodsNo');
                Request::get()->del('exceptCateCd');
                Request::get()->del('exceptBrandCd');
                Request::get()->del('exceptScmNo');

            }

            if($goodsData['listData']) $goodsList = array_chunk($goodsData['listData'],$themeInfo['lineCnt']);
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
            unset($goodsData['listData']);

            // 마일리지 정보
            $mileage = gd_mileage_give_info();

            // 카테고리 노출항목 중 상품할인가
            if (in_array('goodsDcPrice', $themeInfo['displayField'])) {
                foreach ($goodsList as $key => $val) {
                    foreach ($val as $key2 => $val2) {
                        $goodsList[$key][$key2]['goodsDcPrice'] = $goods->getGoodsDcPrice($val2);
                    }
                }
            }

            // 장바구니 설정
            if ($themeInfo['displayType'] == '02' || $themeInfo['displayType'] == '11') {
                $cartInfo = gd_policy('order.cart');
                $this->setData('cartInfo', gd_isset($cartInfo));
            }

            $this->setData('goodsList', gd_isset($goodsList));
            $this->setData('goodsData', gd_isset($goodsData));
            $this->setData('pageNum', gd_isset($pageNum));
            $this->setData('themeInfo', $themeInfo);
            $this->setData('page', gd_isset($page));
            $this->setData('sort', gd_isset($getValue['sort']));
            $this->setData('soldoutDisplay', gd_policy('soldout.pc'));
            $this->setData('mileageData', gd_isset($mileage['info']));
            $this->setData('mainData', gd_isset($getData));
        }

        if($getValue['mode'] == 'get_main') {
            $this->getView()->setPageName('goods/list/list_'.$themeInfo['displayType']);
        }

        $this->getView()->setDefine('goodsTemplate', 'goods/list/list_01.html');
        $this->setData('displayMain', $getData);
        $this->setData('sno', $getValue['sno']);
        $this->setData('groupSno', $getValue['groupSno']);

    }
}
