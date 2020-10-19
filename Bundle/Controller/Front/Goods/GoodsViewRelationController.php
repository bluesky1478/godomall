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

use Framework\Utility\ComponentUtils;
use Framework\Debug\Exception\Except;
use Request;
use Session;
use Exception;
use FileHandler;

class GoodsViewRelationController extends \Controller\Front\Controller
{

    /**
     * 상품 상세 페이지
     *
     * @author    artherot
     * @version   1.0
     * @since     1.0
     * @copyright Copyright (c), Godosoft
     * @throws Except
     */
    public function index()
    {
        // --- 상품 설정
        try {
            // 모듈 설정
            $goods = \App::load('\\Component\\Goods\\Goods');

            // 상품 정보
            $goodsNo = Request::get()->get('goodsNo');
            $goodsView = $goods->getGoodsView(Request::get()->get('goodsNo'));

            //스킨정보
            $designSkin = ComponentUtils::getPolicy('design.skin', $sno);

            $relation = $goodsView['relation'];
            if ($relation['relationFl'] != 'n') {
                $relationConfig = gd_policy('display.relation'); // 관련상품설정
                //복수선택형 스킨 패치가 되어 있지 않을 경우 장바구니형으로 보여지도록
                if($relationConfig['displayType'] == '12' && file_exists(USERPATH_SKIN.'goods/list/list_12.html') === false){
                    $relationConfig['displayType'] = '11';
                }

                $relationConfig['line_width'] = 100 / $relationConfig['lineCnt'];
                if ($goodsView['relationGoodsDate']) {
                    $relationGoodsDate = json_decode(gd_htmlspecialchars_stripslashes($goodsView['relationGoodsDate']), true);
                }

                $relationCount = $relationConfig['lineCnt'] * $relationConfig['rowCnt'];

                $relation['relationCnt'] = gd_isset($relationCount, 4);                            // 상품 출력 갯수 - 기본 4개
                $imageType = gd_isset($relationConfig['imageCd'], 'main');                        // 이미지 타입 - 기본 'main'
                $imageTypeSetting = gd_policy('goods.image');
                $relationConfig['relationImgSize'] = $imageTypeSetting[$imageType]['size1'];

                $soldOutFl = $relationConfig['soldOutFl'] == 'y' ? true : false;            // 품절상품 출력 여부 - true or false (기본 true)
                $brandFl = in_array('brandCd', array_values($relationConfig['displayField'])) ? true : false;    // 브랜드 출력 여부 - true or false (기본 false)
                $couponPriceFl = in_array('coupon', array_values($relationConfig['displayField'])) ? true : false;        // 쿠폰가격 출력 여부 - true or false (기본 false)
                $optionFl = in_array('option', array_values($relationConfig['displayField'])) ? true : false;
                if ($relation['relationFl'] == 'a') {
                    $relationCd = $relation['cateCd'];
                } else {
                    $relationCd = $relation['relationGoodsNo'];
                    $relationGoodsNo = explode(INT_DIVISION, $relation['relationGoodsNo']);

                    foreach ($relationGoodsNo as $k => $v) {
                        if ($v) {
                            if ($relationGoodsDate[$v]['startYmd'] && $relationGoodsDate[$v]['endYmd'] && (strtotime($relationGoodsDate[$v]['startYmd']) > time() || strtotime($relationGoodsDate[$v]['endYmd']) < time())) {
                                unset($relationGoodsNo[$k]);
                            }
                        } else {
                            unset($relationGoodsNo[$k]);
                        }
                    }

                    $relationCd = implode(INT_DIVISION, $relationGoodsNo);
                }

                if ($relation['relationFl'] == 'm') {
                    $relationOrder = "FIELD(g.goodsNo," . str_replace(INT_DIVISION, ",", $relationCd) . ")";
                    if ($relationConfig['soldOutDisplayFl'] == 'n') {
                        $relationOrder = "g.soldOutFl desc," . $relationOrder;
                    }
                } else {
                    $relationOrder = null;
                }

                $relationConfig['detailSetButton']['12'] = $relationConfig['detailSetButton']['12'][0];
                $relationConfig['detailSetPosition']['12'] = $relationConfig['detailSetPosition']['12'][0];

                // 관련 상품 진열
                if(!empty($relationCd)){
                    $relationGoods = $goods->goodsDataDisplay('relation_' . $relation['relationFl'], $relationCd, $relation['relationCnt'], $relationOrder, $imageType, $optionFl, $soldOutFl, $brandFl, $couponPriceFl, null);
                }
                if($relationConfig['displayType'] == '12') {
                    foreach ($relationGoods as $rKey => $rValue) {
                        $relationGoods[$rKey] = array_merge($relationGoods[$rKey], $goods->getGoodsView($rValue['goodsNo']));
                        if($relationGoods[$rKey]['goodsNo'] == $goodsNo){
                            unset($relationGoods[$rKey]);
                        }
                    }
                }
                if ($relationGoods) {
                    $this->setData('goodsCnt', count($relationGoods));
                    $relationGoods = array_chunk($relationGoods, $relationConfig['lineCnt']);
                }

                foreach($relationGoods as $rKey => $rValue){
                    foreach($rValue as $key => $value){
                        //체크박스 enabled 여부
                        //성인인증 상품인경우
                        $relation_adult = true;
                        if ($relationGoods[$rKey][$key]['onlyAdultFl'] == 'y'){
                            if (Session::has(SESSION_GLOBAL_MALL)) {
                                if (!gd_check_login()) {
                                    $relation_adult = false;
                                }
                            } else {
                                if (gd_check_adult() === false) {
                                    $relation_adult = false;
                                }
                            }
                        }

                        //접근권한 체크
                        $relation_permission = true;
                        if($relationGoods[$rKey][$key]['goodsAccess']  != 'all' &&  (gd_check_login() != 'member' || ( (gd_check_login() == 'member' && $relationGoods[$rKey][$key]['goodsAccess']  != 'member' && !in_array(Session::get('member.groupSno'),explode(INT_DIVISION,$relationGoods[$rKey][$key]['goodsAccessGroup'])))))) {
                            $relation_permission = false;
                        }

                        //품절여부
                        $relation_soldout = false;
                        if($relationGoods[$rKey][$key]['soldOut'] == 'y'){
                            $relation_soldout = true;
                        }

                        //가격대체문구
                        $relation_goodsPriceString = false;
                        if($relationGoods[$rKey][$key]['goodsPriceString'] != ''){
                            $relation_goodsPriceString = true;
                        }

                        if($relation_adult === false || $relation_permission === false || $relation_soldout === true || $relation_goodsPriceString == true) {
                            $relationGoods[$rKey][$key]['checkable'] = 'n';
                        }else{
                            $relationGoods[$rKey][$key]['checkable'] = 'y';
                        }
                    }
                }

                // 관련상품 노출항목 중 상품할인가
                if (in_array('goodsDcPrice', $relationConfig['displayField'])) {
                    foreach ($relationGoods as $key => $val) {
                        foreach ($val as $key2 => $val2) {
                            $relationGoods[$key][$key2]['goodsDcPrice'] = $goods->getGoodsDcPrice($val2);
                        }
                    }
                }

                $this->setData('widgetGoodsList', gd_isset($relationGoods));
                $this->setData('widgetTheme', gd_isset($relationConfig));
                $this->setData('mainData', ['sno'=>'relation']);
            }
        } catch (Exception $e) {
            debug($e->getMessage());
        }

        //관련상품 없을 경우 출력 안함
        if(empty($relationGoods)){
            exit;
        }
    }
}
