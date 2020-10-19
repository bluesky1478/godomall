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

use Component\Board\Board;
use Component\Board\BoardBuildQuery;
use Component\Board\BoardList;
use Component\Board\BoardWrite;
use Component\Naver\NaverPay;
use Component\Page\Page;
use Component\Promotion\SocialShare;
use Component\Mall\Mall;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\Except;
use Framework\Debug\Exception\AlertBackException;
use Component\Validator\Validator;
use Message;
use Globals;
use Request;
use Logger;
use Session;
use Exception;
use Endroid\QrCode\QrCode as EndroidQrCode;
use SocialLinks\Page as SocialLink;
use FileHandler;

class GoodsViewController extends \Controller\Front\Controller
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
            $cate = \App::load('\\Component\\Category\\Category');
            $coupon = \App::load('\\Component\\Coupon\\Coupon');
            $qr = \App::load('\\Component\\Promotion\\QrCode');
            $delivery = \App::load('\\Component\\Delivery\\Delivery');
            $session = \App::getInstance('session');

            //관련상품 관련 세션 삭제
            $session->del('related_goods_order');

            // 상품 정보
            $goodsNo = Request::get()->get('goodsNo');
            if (Validator::number($goodsNo, null, null, true) === false) {
                throw new \Exception(__('잘못된 접근입니다.'));
            }
            $goodsView = $goods->getGoodsView(Request::get()->get('goodsNo'));

            if ($goodsView['optionDisplayFl'] === 'd') {
                foreach ($goodsView['option'] as $k => $goodsOptionInfo) {
                    if ($goodsOptionInfo['optionViewFl'] !== 'y') {
                        unset($goodsView['option'][$k]);
                    }
                }
                foreach ($goodsView['option'] as $k => $goodsOptionInfo) {
                    $optionArr[$k] = $goodsOptionInfo['optionValue1'];
                }
                $goodsView['optionDivision'] = array_unique($optionArr);
            }

            // 상품필수 정보에 KC인증 정보 추가
            if (empty($goodsView['goodsMustInfo']) === false) {
                $goodsView['goodsMustInfo'] = array_merge($goodsView['goodsMustInfo'], $goods->getKcmarkInfo());
            } else {
                $goodsView['goodsMustInfo'] = $goods->getKcmarkInfo();
            }

            // 상품 대표색상 치환코드 추가
            $goodsColorList = $goods->getGoodsColorList(true);
            $goodsColor = (Request::isMobile()) ? "<div class='color_chip'>" : "<div class='color'>";
            if($goodsView['goodsColor']) $goodsView['goodsColor'] = explode(STR_DIVISION, $goodsView['goodsColor']);

            if(is_array($goodsView['goodsColor'])) {
                foreach(array_unique($goodsView['goodsColor']) as $k => $v) {
                    if (!in_array($v,$goodsColorList) ) {
                        continue;
                    }
                    $goodsColorData = array_flip($goodsColorList)[$v];
                    $goodsColor .= ($v == 'FFFFFF') ? "<div style='background-color:#{$v};' title='{$goodsColorData}'></div>" : "<div style='background-color:#{$v}; border-color:#{$v};' title='{$goodsColorData}'></div>";
                }
                $goodsColor .= "</div>";
                unset($goodsView['goodsColor']);
                $goodsView['goodsColor'] = $goodsColor;
            }

            //성인인증 상품인경우
            if (Session::has(SESSION_GLOBAL_MALL)) {
                if ($goodsView['onlyAdultFl'] == 'y' && !gd_check_login()) {
                    $this->redirect('../member/login.php?returnUrl=' . urlencode("/goods/goods_view.php?goodsNo=" . $goodsNo));
                }
            } else {
                // 마이앱 로그인 스크립트
                $myappBuilderInfo = gd_policy('myapp.config')['builder_auth'];
                $myappUseQuickLogin = gd_policy('myapp.config')['useQuickLogin'];
                if (\Request::isMyapp() && empty($myappBuilderInfo['clientId']) === false && empty($myappBuilderInfo['secretKey']) === false && $myappUseQuickLogin === 'true') {
                    if ($goodsView['onlyAdultFl'] == 'y' && !gd_check_login() && gd_check_adult() === false) {
                        $myapp = \App::load('Component\\Myapp\\Myapp');
                        echo $myapp->getAppBridgeScript('adultLoginView', $goodsNo);
                        $this->js('parent.history.back()');
                        exit;
                    }
                }

                if ($goodsView['onlyAdultFl'] == 'y' && gd_check_adult() === false) {
                    $this->redirect('../intro/adult.php?returnUrl=' . urlencode("/goods/goods_view.php?goodsNo=" . $goodsNo));
                }
            }

            //접근권한 체크
            if($goodsView['goodsAccess']  != 'all' &&  (!gd_check_login() || ( (gd_check_login() && $goodsView['goodsAccess']  != 'member' && !in_array(Session::get('member.groupSno'),explode(INT_DIVISION,$goodsView['goodsAccessGroup'])))))) {
                throw new \Exception(__('해당 상품은 현재 구매가 불가한 상품입니다.'));
            }

            // 상품 QR코드
            if ($goodsView['qrCodeFl'] == 'y') {
                $goodsView['qrCodeImage'] = $qr->preview(
                    [
                        'qrSize' => 2,
                        'qrVersion' => 5,
                        'qrString' => "http://" . Request::server()->get("SERVER_NAME") . "/goods/goods_view.php?goodsNo=" . $goodsNo,
                    ]
                );
                $goodsView['qrStyle'] = "image";
                $qrCodeConfig = gd_policy('promotion.qrcode'); // QR코드 설정
                $goodsView['qrStyle'] = $qrCodeConfig['qrStyle'];
            }

            // cdn예외처리
            if (file_exists(USERPATH . 'config/app/cdn.php')) {
                $cdnConfig = require USERPATH . 'config/app/cdn.php';
                if ($cdnConfig['goods_description']) {
                    $cdnConfig['protocol'] = gd_isset($cdnConfig['protocol'], 'http');
                    $Ext = '(?<=src\=")(?:[^"])*[^"](?=")'.
                        "|(?<=src\=')(?:[^'])*[^'](?=')".
                        '|(?<=src\=\\\\")(?:[^"])*[^"](?=\\\\")'.
                        "|(?<=src\=\\\\')(?:[^'])*[^'](?=\\\\')";
                    $sPattern = '@('. $Ext .')@ix';
                    $aSplit = preg_split($sPattern, $goodsView['goodsDescription'], -1, PREG_SPLIT_DELIM_CAPTURE);
                    $iCnt = count($aSplit);
                    $aReplaceImg = array();
                    for ($i=1; $i < $iCnt; $i += 2) {
                        if (preg_match('@^https:\/\/@ix', $aSplit[$i])) {
                        } elseif (preg_match('@^http:\/\/@ix', $aSplit[$i])) {
                        } else {
                            if (!in_array($aSplit[$i], $aReplaceImg)) {
                                $goodsView['goodsDescription'] = str_replace($aSplit[$i], $cdnConfig['protocol'] . '://' . $cdnConfig['prefix'] . '.' . Request::getDefaultHost() . $aSplit[$i], $goodsView['goodsDescription']);
                                $aReplaceImg[] = $aSplit[$i];
                            }
                        }
                    }
                }
            }

            Logger::debug('$goodsView', $goodsView);

            // 오늘본 상품
            $goods->getTodayViewedGoods(Request::get()->get('goodsNo'));

            // 관련 상품
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
                if (!empty($relationCd)) {
                    $relationExceptGoodsNo = ($relation['relationFl'] == 'a') ? $goodsNo : null; // 자동 설정인 경우 상세접근 상품제외 추가
                    $goods->setThemeConfig($relationConfig);
                    $relationGoods = $goods->goodsDataDisplay('relation_' . $relation['relationFl'], $relationCd, $relation['relationCnt'], $relationOrder, $imageType, $optionFl, $soldOutFl, $brandFl, $couponPriceFl, null, false, $relationExceptGoodsNo);
                }

                if ($relationGoods) {
                    // 상품상세 접근한 상품은 관련상품 제외
                    foreach ($relationGoods as $rKey => $rValue) {
                        $relationGoods[$rKey] = array_merge($relationGoods[$rKey], $goods->getGoodsView($rValue['goodsNo']));
                        if($relationGoods[$rKey]['goodsNo'] == $goodsNo){
                            unset($relationGoods[$rKey]);
                        }
                    }

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

                        //대표색상 치환코드
                        $goodsColorList = $goods->getGoodsColorList(true);
                        $goodsColor = "<div class='color' style='width: ".$relationConfig['relationImgSize']."px'>";
                        if($relationGoods[$rKey][$key]['goodsColor']) $relationGoods[$rKey][$key]['goodsColor'] = explode(STR_DIVISION, $relationGoods[$rKey][$key]['goodsColor']);

                        if(is_array($relationGoods[$rKey][$key]['goodsColor'])) {
                            foreach(array_unique($relationGoods[$rKey][$key]['goodsColor']) as $k => $v) {
                                if (!in_array($v,$goodsColorList) ) {
                                    continue;
                                }
                                $goodsColorData = array_flip($goodsColorList)[$v];
                                $goodsColor .= ($v == 'FFFFFF') ? "<div style='background-color:#{$v};' title='{$goodsColorData}'></div>" : "<div style='background-color:#{$v}; border-color:#{$v};' title='{$goodsColorData}'></div>";
                            }
                            $goodsColor .= "</div>";
                            unset($relationGoods[$rKey][$key]['goodsColor']);
                            $relationGoods[$rKey][$key]['goodsColor'] = $goodsColor;
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

            unset($goodsView['relation']);

            // 상품 이용 안내
            $detailInfo = $goodsView['detailInfo'];
            unset($goodsView['detailInfo']);

            // 카테고리 정보
            if (empty(Request::get()->get('cateCd')) === false && preg_match('/goods_list.php/i', Request::getParserReferer()->path)) {
                $goodsCateCd = Request::get()->get('cateCd');
            } else {
                $goodsCateCd = $goodsView['cateCd'];
            }

            // 소셜공유 설정하기
            $socialShare = new SocialShare([
                SocialShare::BRAND_NAME_REPLACE_KEY => $goodsView['brandNm'],
                SocialShare::GOODS_NAME_REPLACE_KEY => $goodsView['goodsNmDetail'],
            ]);
            $data = $socialShare->getTemplateData($goodsView);
            $this->setData('snsShareUseFl', $data['useFl']);
            $this->setData('snsShareMetaTag', $data['metaTags']);
            $this->setData('snsShareButton', $data['shareBtn']);
            $this->setData('snsShareUrl', $data['shareUrl']);

            // 쿠폰 설정값 정보
            $couponConfig = gd_policy('coupon.config');
            //타임세일 상품에서 쿠폰 사용 불가인경우 체크
            if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true && $goodsView['timeSaleFl'] && $goodsView['timeSaleInfo']['couponFl'] == 'n') {
                $goodsView['couponDcPrice'] = 0;
                $couponConfig['couponUseType'] = 'n';
            }

            // 혜택 제외 설정중 상품쿠폰 포함여부 확인
            $exceptBenefit = explode(STR_DIVISION, $goodsView['exceptBenefit']);
            $exceptBenefitGroupInfo = explode(INT_DIVISION, $goodsView['exceptBenefitGroupInfo']);
            if (in_array('coupon', $exceptBenefit) === true && ($goodsView['exceptBenefitGroup'] == 'all' || ($goodsView['exceptBenefitGroup'] == 'group' && in_array(Session::get('member.groupSno'), $exceptBenefitGroupInfo) === true))) {
                $goodsView['couponDcPrice'] = 0;
                $couponConfig['couponUseType'] = 'n';
            }

            if ($couponConfig['couponUseType'] == 'y') {
                // 해당 상품의 모든 쿠폰
                $couponArrData = $coupon->getGoodsCouponDownList(Request::get()->get('goodsNo'), Session::get('member.memNo'), Session::get('member.groupSno'));
            }

            // 현재 위치 정보 (위젯 클래스에서 사용)
            $pageLocation = null;
            if (empty($goodsCateCd) == false) {
                $pageLocation = $cate->getCategoryPosition($goodsCateCd, 0, STR_DIVISION, true);
            }

            $goodsCategoryList = $cate->getCategories($goodsCateCd);

            // 마일리지 정보
            $mileage = $goodsView['mileageConf'];
            unset($goodsView['mileageConf']);

            // 상품 과세 / 비과세 설정 config 불러오기
            $taxConf = gd_policy('goods.tax');

            // 무통장 전용상품일 경우 네이버체크아웃 페이코 미노출처리
            if (!((($goodsView['payLimitFl'] == 'y' && $goodsView['payLimit'] == 'gb') || $goodsView['orderPossible'] != 'y'))) {
                // 네이버 체크아웃 버튼
                $naverPay = new NaverPay();
                $naverPayButton = $naverPay->getNaverPayView($goodsView);
                $naverPayMobileButton = $naverPay->getNaverPayView($goodsView,true);    //모바일버튼 제공
                $responseNaverPay = $naverPay->getNaverPayView($goodsView,\Request::isMobileDevice());
                // 페이코 버튼
                $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
                $paycoCheckoutbuttonImage = $payco->getButtonHtmlCode('CHECKOUT', false, 'goodsView', Request::get()->get('goodsNo'));
                $paycoCheckoutbuttonMobileImage = $payco->getButtonHtmlCode('CHECKOUT', true, 'goodsView', Request::get()->get('goodsNo'));
                $responsePaycoCheckoutbuttonImage = $payco->getButtonHtmlCode('CHECKOUT', \Request::isMobileDevice(), 'goodsView', Request::get()->get('goodsNo'));
                $this->setData('responsePayco', gd_isset($responsePaycoCheckoutbuttonImage));  //페이코 반응형 모바일버튼
                if ($paycoCheckoutbuttonImage !== false || $paycoCheckoutbuttonMobileImage !== false) {
                    $this->setData('payco', gd_isset($paycoCheckoutbuttonImage));
                    $this->setData('paycoPc', gd_isset($paycoCheckoutbuttonImage));
                    $this->setData('paycoMobile', gd_isset($paycoCheckoutbuttonMobileImage));
                }
            }

            $soldoutDisplay = gd_policy('soldout.pc');

            $cartInfo = gd_policy('order.cart'); //장바구니설정

            // 상품 무게 소수점 0 제거 (ex. 4.00 => 4, 4.40 => 4.4)
            if ($goodsView['goodsWeight'] - floor($goodsView['goodsWeight']) == 0) {
                $goodsView['goodsWeight'] = number_format($goodsView['goodsWeight']);
            } elseif ($goodsView['goodsWeight'] - (floor($goodsView['goodsWeight'] * 10) / 10) == 0) {
                $goodsView['goodsWeight'] = number_format($goodsView['goodsWeight'], 1);
            }

            // 상품 용량 소수점 0 제거 (ex. 4.00 => 4, 4.40 => 4.4)
            if ($goodsView['goodsVolume'] - floor($goodsView['goodsVolume']) == 0) {
                $goodsView['goodsVolume'] = number_format($goodsView['goodsVolume']);
            } else if ($goodsView['goodsVolume'] - (floor($goodsView['goodsVolume'] * 10) / 10) == 0) {
                $goodsView['goodsVolume'] = number_format($goodsView['goodsVolume'], 1);
            }

        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        // 멀티 상점을 위한 소수점 처리
        $currency = Globals::get('gCurrency');
        if (Session::has(SESSION_GLOBAL_MALL)) {
            $currency['decimal'] = Session::get(SESSION_GLOBAL_MALL.'.currencyConfig');
            $currency['decimal'] = $currency['decimal']['decimal'];
            if(SESSION::get(SESSION_GLOBAL_MALL.'.addGlobalCurrencyNo')) {
                $this->setData('addGlobalCurrency', gd_isset(SESSION::get(SESSION_GLOBAL_MALL.'.addGlobalCurrencyNo')));
            }
        }

        //관련상품 없을 경우 관련상품 설정 리셋
        if(empty($relationGoods)){
            $relation['relationFl'] = 'n';
        }

        // default 구매 최소 수량
        $goodsView['defaultGoodsCnt'] = 1;
        if($goodsView['fixedOrderCnt'] == 'option') {
            $goodsView['defaultGoodsCnt'] = $goodsView['minOrderCnt'];
        }
        if($goodsView['fixedSales'] != 'goods' && ($goodsView['salesUnit'] > $goodsView['defaultGoodsCnt'])) {
            $goodsView['defaultGoodsCnt'] = $goodsView['salesUnit'];
        }

        //상품 노출 필드
        $displayField = gd_policy('display.goods');
        $this->setData('displayField', $displayField['goodsDisplayField']['pc']);
        $this->setData('displayAddField', $displayField['goodsDisplayAddField']['pc']);

        if (in_array('goodsDiscount', $displayField['goodsDisplayField']['pc']) === true && empty($goodsView['goodsPriceString']) === true) {
            if (empty($displayField['goodsDiscount']['pc']) === false) {
                if (in_array('goods', $displayField['goodsDiscount']['pc']) === true) $goodsView['dcPrice'] += $goodsView['goodsDcPrice'];
                if (in_array('coupon', $displayField['goodsDiscount']['pc']) === true) $goodsView['dcPrice'] += $goodsView['couponDcPrice'];
            }
        }

        if ($goodsView['dcPrice'] >= $goodsView['goodsPrice']) {
            $goodsView['dcPrice'] = 0;
        }

        if (in_array('dcRate', $displayField['goodsDisplayAddField']['pc']) === true) {
            $goodsView['goodsDcRate'] = round((100 * gd_isset($goodsView['dcPrice'], 0)) / $goodsView['goodsPrice']);
            $goodsView['couponDcRate'] = round((100 * $goodsView['couponDcPrice']) / $goodsView['goodsPrice']);
            $goodsView['myCouponDcRate'] = round((100 * $goodsView['myCouponPrice']) / $goodsView['goodsPrice']);
        }

        // --- Template_ 출력
        // 브라우저 상단 타이틀
        $this->setData('title', gd_isset($goodsView['goodsNm']));
        $this->setData('goodsView', gd_isset($goodsView));
        $this->setData('mileageData', gd_isset($mileage['info']));
        $this->setData('goodsCateCd', gd_isset($goodsCateCd));
        $this->setData('goodsCategoryList', gd_isset($goodsCategoryList));
        $this->setData('couponArrData', gd_isset($couponArrData));
        $this->setData('couponConfig', gd_isset($couponConfig));
        $this->setData('couponUse', gd_isset($couponConfig['couponUseType'], 'n'));
        $this->setData('taxConf', gd_isset($taxConf));
        $this->setData('relation', gd_isset($relation));
        $this->setData('relationGoodsDate', gd_isset($relationGoodsDate));
        $this->setData('cyscrapBtnImage', gd_isset($cyscrapBtnImage));
        $this->setData('naverPay', gd_isset($naverPayButton));  //네이버페이PC버튼
        $this->setData('naverPayPc', gd_isset($naverPayButton));  //네이버페이PC버튼
        $this->setData('naverPayMobile', gd_isset($naverPayMobileButton));  //네이버페이 모바일버튼
        $this->setData('responseNaverPay', gd_isset($responseNaverPay));  //네이버페이 반응형 모바일버튼
        $this->setData('currency', $currency);
        $this->setData('weight', Globals::get('gWeight'));
        $this->setData('volume', Globals::get('gVolume'));
        $this->setData('soldoutDisplay', gd_isset($soldoutDisplay));
        $this->setData('cartInfo', gd_isset($cartInfo));
        $this->setData('deliveryType', gd_isset($goodsView['delivery']['basic']['fixFlText']));
        $this->setData('deliveryMethod', gd_isset($goodsView['delivery']['basic']['method']));
        $this->setData('deliveryDes', gd_isset($goodsView['delivery']['basic']['description']));

        // 상품 상세 이용안내 배송정보,AS관련,환불,교환
        $detailInfoArray = array('detailInfoDelivery','detailInfoAS','detailInfoRefund','detailInfoExchange');

        foreach($detailInfoArray as $val) {
            // 해외몰 이용안내 직접입력일 경우 해외몰 이용안내 정보 가져옴
            if (Session::has(SESSION_GLOBAL_MALL) && $goodsView[$val.'Fl'] != 'no') {
                $goodsView[$val.'Fl'] = 'selection';
                $detailInfo[$val] = \Component\Mall\Mall::GLOBAL_MALL_DETAIL_INFO[$val];
            }

            if ($goodsView[$val.'Fl'] == 'no') { //이용안내 사용안함
                $infoData = '';
            }else if($goodsView[$val.'Fl'] == 'direct') { //이용안내 직접입력
                $infoData['content'] = $goodsView[$val.'DirectInput'];
            }else if($goodsView[$val.'Fl'] == 'selection'){ //이용안내 선택입력
                if (empty($detailInfo[$val]) === false && strlen($detailInfo[$val]) == 6) {
                    $infoData = gd_buyer_inform($detailInfo[$val]);
                }else{
                    $infoData = '';
                }
            }else{
                $infoData = '';
            }
            $this->setData(str_replace('detailInfo','info',$val), gd_isset($infoData['content']));
        }


        if (FileHandler::isExists( USERPATH_SKIN.'js/bxslider/dist/jquery.bxslider.min.js')) {
            $addScript[] =  'bxslider/dist/jquery.bxslider.min.js';
        }
        if (FileHandler::isExists( USERPATH_SKIN.'js/slider/slick/slick.js')) {
            $addScript[] =  'slider/slick/slick.js';
        }
        $addScript[] = 'gd_goods_view.js';

        if (!Request::isMobile() && !Request::isMobileDevice()) {
            $addScript[] = 'jquery/chosen-imageselect/src/ImageSelect.jquery.js';
        }

        if ($goodsView['imgDetailViewFl'] == 'y') {
            $addScript[] = 'imagezoom/jquery.elevatezoom.js';
        }

        $this->addScript($addScript);
        $this->addCss(['../js/jquery/chosen-imageselect/src/ImageSelect.css']);

        $goodsReviewList = new BoardList(['bdId' => Board::BASIC_GOODS_REIVEW_ID, 'goodsNo' => $goodsNo]);
        if ($goodsReviewList->canUsePc()) {
            $goodsReviewAuthList = $goodsReviewList->canList();
            $goodsReviewCount = 0;
            if ($goodsReviewAuthList == 'y') {
                $goodsReviewCount = $goodsReviewList->getCount();
            }
        }

        $goodsQaList = new BoardList(['bdId' => Board::BASIC_GOODS_QA_ID, 'goodsNo' => $goodsNo]);
        if ($goodsQaList->canUsePc()) {
            $goodsQaAuthList = $goodsQaList->canList();
            $goodsQaCount = 0;
            if ($goodsQaAuthList == 'y') {
                $goodsQaCount = $goodsQaList->getCount();
            }
        }

        $this->setData('goodsReviewAuthList', $goodsReviewAuthList);

        $this->setData('goodsReviewAuthWrite', $goodsReviewList->canWrite());
        $this->setData('goodsReviewCount', $goodsReviewCount);
        $this->setData('goodsQaAuthList', $goodsQaAuthList);
        $this->setData('goodsQaAuthWrite', $goodsQaList->canWrite());
        $this->setData('goodsQaCount', $goodsQaCount);

        $this->setData('bdGoodsReviewId', Board::BASIC_GOODS_REIVEW_ID);
        $this->setData('bdGoodsQaId', Board::BASIC_GOODS_QA_ID);

        // 취소선 관련값들 처리
        $fixedPriceTag = '';
        $fixedPriceTag2 = '';
        if ((in_array('couponPrice', $displayField['goodsDisplayField']['pc']) && $goodsView['couponPrice'] > 0) && $couponConfig['couponUseType'] == 'y' && ($goodsView['timeSaleInfo']['couponFl'] == 'n' || !$goodsView['timeSaleInfo']) || (in_array('goodsDiscount', $displayField['goodsDisplayField']['pc']) && $goodsView['dcPrice'] > 0 && !$goodsView['timeSaleInfo'])) {
            $goodsPriceTag = '<b>';
            $goodsPriceTag2 = '</b>';
        } else {
            $goodsPriceTag = '<strong>';
            $goodsPriceTag2 = '</strong>';
        }

        // 패치 이전에 저장한상태라 db에 strikefield 설정값이 없는경우는 우선 정가는 체크되어있는것으로 간주하기위함
        if (empty($displayField['goodsDisplayStrikeField']['pc']) === true) {
            $fixedPriceTag = '<del>';
            $fixedPriceTag2 = '</del>';
        } else {
            foreach ($displayField['goodsDisplayStrikeField']['pc'] as $val) {
                if ($val == 'fixedPrice') {
                    $fixedPriceTag = '<del>';
                    $fixedPriceTag2 = '</del>';
                }
                if ($val == 'goodsPrice' && $goodsPriceTag == '<b>') {
                    $goodsPriceTag = $goodsPriceTag . '<del>';
                    $goodsPriceTag2 = '</del>' . $goodsPriceTag2;
                }
            }
        }

        // 상품 무게 및 용량 노출
        if (gd_isset($goodsView['goodsWeight']) > 0 && gd_isset($goodsView['goodsVolume']) > 0) {
            $displayField['defaultField']['goodsWeight'] = '상품 무게/용량';
        } else {
            if (gd_isset($goodsView['goodsWeight']) > 0) {
                $displayField['defaultField']['goodsWeight'] = '상품 무게';
            }

            if (gd_isset($goodsView['goodsVolume']) > 0) {
                $displayField['defaultField']['goodsWeight'] = '상품 용량';
            }
        }

        $this->setData('fixedPriceTag', $fixedPriceTag);
        $this->setData('fixedPriceTag2', $fixedPriceTag2);
        $this->setData('goodsPriceTag', $goodsPriceTag);
        $this->setData('goodsPriceTag2', $goodsPriceTag2);
        $this->setData('displayDefaultField', $displayField['defaultField']);

        //facebook Dynamic Ads 외부 스크립트 적용
        $currency = gd_isset(Mall::getSession('currencyConfig')['code'], 'KRW');
        $facebookAd = \App::Load('\\Component\\Marketing\\FacebookAd');
        $fbScript = $facebookAd->getFbGoodsViewScript($goodsNo, $goodsView['goodsPrice'], $currency);
        $this->setData('fbGoodsViewScript', $fbScript);

        // 상품 옵션가 표시설정 config 불러오기
        $optionPriceConf = gd_policy('goods.display');
        $this->setData('optionPriceFl', gd_isset($optionPriceConf['optionPriceFl'], 'y'));

        //상품 품절 설정 코드 불러오기
        $code = \App::load('\\Component\\Code\\Code');
        $optionSoldOutCode = $code->getGroupItems('05002');
        $optionSoldOutCode['n'] = $optionSoldOutCode['05002002'];
        $this->setData('optionSoldOutCode', $optionSoldOutCode);

        //상품 배송지연 설정 코드 불러오기
        $code = \App::load('\\Component\\Code\\Code');
        $optionDeliveryDelayCode = $code->getGroupItems('05003');
        $this->setData('optionDeliveryDelayCode', $optionDeliveryDelayCode);

        // 마이앱 상품 추가 혜택
        if (empty($goodsView['goodsPriceString'])) {
            $myapp = \App::load('Component\\Myapp\\Myapp');
            $myappGoodsBenefit = $myapp->getOrderAdditionalBenefit($goodsView);
            if (empty($myappGoodsBenefit['replaceCode']['goodsView']) === false) {
                $this->setData('myappGoodsBenefitMessage', $myappGoodsBenefit['replaceCode']['goodsView']);
                $this->addCss([
                    'myapp.css'
                ]);
            }
        }
    }
}
