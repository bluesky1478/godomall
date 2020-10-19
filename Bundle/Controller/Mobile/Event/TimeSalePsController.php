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
namespace Bundle\Controller\Mobile\Event;

use Framework\Http\Response;
use Logger;
use Message;
use Request;

class TimeSalePsController extends \Controller\Mobile\Controller
{

    /**
     * 상품 상세 페이지 처리
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright Copyright (c), Godosoft
     */
    public function index()
    {
        // --- 각 배열을 trim 처리
        $postValue = Request::post()->toArray();
        //$postValue = Request::get()->toArray();

        // --- 각 모드별 처리
        switch ($postValue['mode']) {
            case 'get_time_sale_list':
            case 'get_more':
                try {

                    // 모듈 설정
                    $goods = \App::load('\\Component\\Goods\\Goods');
                    $displayConfig = \App::load('\\Component\\Display\\DisplayConfig');
                    $timeSale = \App::load('\\Component\\Promotion\\TimeSale');

                    Request::get()->set('sort',$postValue['sort']);

                    $getData = $timeSale->getInfoTimeSale($postValue['sno']);
                    $themeInfo = $displayConfig->getInfoThemeConfig($getData['mobileThemeCd']);

                    if ($themeInfo['detailSet']) $themeInfo['detailSet'] = unserialize($themeInfo['detailSet']);
                    $themeInfo['displayField'] = explode(",", $themeInfo['displayField']);

                    $imageType = gd_isset($themeInfo['imageCd'], 'main');                        // 이미지 타입 - 기본 'main'
                    $soldOutFl = $themeInfo['soldOutFl'] == 'y' ? true : false;            // 품절상품 출력 여부 - true or false (기본 true)
                    $brandFl = in_array('brandCd', array_values($themeInfo['displayField'])) ? true : false;    // 브랜드 출력 여부 - true or false (기본 false)
                    $couponPriceFl = in_array('coupon', array_values($themeInfo['displayField'])) ? true : false;        // 쿠폰가격 출력 여부 - true or false (기본 false)
                    $optionFl = in_array('option', array_values($themeInfo['displayField'])) ? true : false;


                    if($postValue['sort']) {
                        $mainOrder =$postValue['sort'];
                        if (method_exists($goods, 'getSortMatch')) {
                            $mainOrder = $goods->getSortMatch($mainOrder);
                        }
                    } else {
                        if($getData['sort']) $mainOrder = $getData['sort'];
                        else $mainOrder = "FIELD(g.goodsNo," . str_replace(INT_DIVISION, ",", $getData['goodsNo']) . ")";
                    }
                    if ($themeInfo['soldOutDisplayFl'] == 'n') $mainOrder = "g.soldOutFl desc," . $mainOrder;

                    //타임세일 더보기 추가
                    if ($getData['moreBottomFl'] === 'y') {
                        if ($postValue['mode'] == 'get_more') $displayCnt = gd_isset($themeInfo['lineCnt']) * gd_isset($themeInfo['rowCnt'])*$postValue['more'];
                        else $displayCnt = (gd_isset($themeInfo['lineCnt']) * gd_isset($themeInfo['rowCnt']));
                    } else {
                        $displayCnt = count(explode('||', $getData['goodsNo']));
                    }

                    $tmpGoodsList = $goods->goodsDataDisplay('goods',$getData['goodsNo'], $displayCnt, $mainOrder, $imageType, $optionFl, $soldOutFl, $brandFl, $couponPriceFl, null, $getData['moreBottomFl'] == 'y' ? true : false);

                    $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

                    if ($tmpGoodsList) {
                        $this->setData('goodsListCnt', $page->recode['total']);
                        $goodsList = array_chunk($tmpGoodsList, $themeInfo['lineCnt']);
                        $showCnt = gd_isset($themeInfo['lineCnt']) * gd_isset($themeInfo['rowCnt']);
                        $totalPage = ($showCnt ? ceil($page->recode['total'] / $showCnt) : 0);
                    }

                    //품절상품 설정
                    $soldoutDisplay = gd_policy('soldout.mobile');
                    $mileage = gd_mileage_give_info();

                    // 장바구니 설정
                    if ($postValue['displayType'] == '11') {
                        $cartInfo = gd_policy('order.cart');
                        $this->setData('cartInfo', gd_isset($cartInfo));
                    }

                    $this->getView()->setPageName('goods/list/list_'.$postValue['displayType']);
                    $this->setData('totalPage', gd_isset($totalPage,1));
                    $this->setData('goodsList', gd_isset($goodsList));
                    $this->setData('soldoutDisplay', gd_isset($soldoutDisplay));
                    $this->setData('themeInfo', gd_isset($themeInfo));
                    $this->setData('timeSaleInfo', gd_isset($getData));
                    $this->setData('mileageData', gd_isset($mileage['info']));

                } catch (Exception $e) {

                    echo json_encode(array('message' => $e->getMessage()));

                }

                break;
        }
    }
}
