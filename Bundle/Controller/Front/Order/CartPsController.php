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

namespace Bundle\Controller\Front\Order;

use Component\Cart\Cart;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertReloadException;
use Framework\Debug\Exception\Except;
use Request;
use Respect\Validation\Validator as v;
use Bundle\Component\Database\DBTableField;
use Session;

/**
 * 장바구니 처리 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class CartPsController extends \Controller\Front\Controller
{
    /**
     * index
     *
     * @throws Except
     */
    public function index()
    {
        // 장바구니 class
        $cart = \App::Load(\Component\Cart\Cart::class);
        $session = \App::getInstance('session');

        $request = \App::getInstance('request');
        // _POST , _GET 정보
        $postValue = Request::request()->toArray();
        $getValue = Request::get()->toArray();

        // 각 모드별 처리
        switch (Request::request()->get('mode')) {
            // 장바구니 추가
            case 'cartIn':
                try {
                    \Cookie::del("NPAY_BUY_UNIQUE_KEY");
                    //관련상품 관련 세션 삭제
                    if($session->get('related_goods_order') == 'y') {
                        $session->del('related_goods_order');
                    }
                    $cart->setDeleteDirectCartCont();
                    // 메인 상품 진열 통계 처리
                    if (empty($postValue['mainSno']) === false && $postValue['mainSno'] > 0) {
                        $goods = \App::load('\\Component\\Goods\\Goods');
                        $getData = $goods->getDisplayThemeInfo($postValue['mainSno']);
                        $postValue['linkMainTheme'] = htmlentities($getData['sno'] . STR_DIVISION . $getData['themeNm'] . STR_DIVISION . $getData['mobileFl']);
                    } else {
                        $referer = $request->getReferer();
                        unset($mtn);
                        parse_str($referer);
                        gd_isset($mtn);
                        if (empty($mtn) === false) {
                            $postValue['linkMainTheme'] = $mtn;
                        }
                    }

                    // 이미 사용중인 쿠폰이 있을 경우 중복 사용 체크
                    if (empty($postValue['couponApplyNo']) === false && count($postValue['couponApplyNo']) > 0) {
                        if (method_exists($cart, 'validateApplyCoupon') === true) {
                            $resValidateApplyCoupon = $cart->validateApplyCoupon($postValue['couponApplyNo']);
                            if ($resValidateApplyCoupon['status'] === false) {
                                throw new Exception($resValidateApplyCoupon['msg']);
                            }
                        }
                    }

                    // 장바구니 추가
                    $cart->saveInfoCart($postValue);
                    if ($request->isAjax()) {
                        $this->json([
                            'error' => 0,
                            'message' => __('성공'),
                        ]);
                    } else {
                        // 처리별 이동 경로
                        if (gd_isset($postValue['cartMode']) == 'd') {
                            $returnUrl = './order.php';
                        } else {
                            $returnUrl = './cart.php';
                        }
                        $this->redirect($returnUrl, null, 'parent');
                    }

                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'error' => 1,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new AlertBackException($e->getMessage());
                    }
                }
                break;

            // 여러개 상품 한번에 장바구니 담기
            case 'cartInMulti':
                \Cookie::del("NPAY_BUY_UNIQUE_KEY");
                $cart->setDeleteDirectCartCont();
                if($postValue['cartMode'] == 'd') {
                    $session->set('related_goods_order', 'y');
                }else{
                    $session->set('related_goods_order', '');
                }
                // 메인 상품 진열 통계 처리
                unset($mtn);
                if (empty($postValue['mainSno']) === false && $postValue['mainSno'] > 0) {
                    $goods = \App::load('\\Component\\Goods\\Goods');
                    $getData = $goods->getDisplayThemeInfo($postValue['mainSno']);
                    $mtn = htmlentities($getData['sno'] . STR_DIVISION . $getData['themeNm'] . STR_DIVISION . $getData['mobileFl']);
                } else {
                    $referer = $request->getReferer();
                    parse_str($referer);
                    gd_isset($mtn);
                }
                foreach($postValue['chkRelatedGoods'] as $value){
                    $cartValue['mode'] = $postValue['mode'.$value];
                    $cartValue['scmNo'] = $postValue['scmNo'.$value];
                    $cartValue['cartMode'] = $postValue['cartMode'.$value];
                    $cartValue['set_goods_price'] = $postValue['set_goods_price'.$value];
                    $cartValue['set_goods_fixedPrice'] = $postValue['set_goods_fixedPrice'.$value];
                    $cartValue['set_goods_mileage'] = $postValue['set_goods_mileage'.$value];
                    $cartValue['set_goods_stock'] = $postValue['set_goods_stock'.$value];
                    $cartValue['set_coupon_dc_price'] = $postValue['set_coupon_dc_price'.$value];
                    $cartValue['set_goods_total_price'] = $postValue['set_goods_total_price'.$value];
                    $cartValue['set_option_price'] = $postValue['set_option_price'.$value];

                    $cartValue['set_option_text_price'] = $postValue['set_option_text_price'.$value];
                    $cartValue['set_add_goods_price'] = $postValue['set_add_goods_price'.$value];
                    $cartValue['set_total_price'] = $postValue['set_total_price'.$value];
                    $cartValue['mileageFl'] = $postValue['mileageFl'.$value];
                    $cartValue['mileageGoods'] = $postValue['mileageGoods'.$value];
                    $cartValue['mileageGoodsUnit'] = $postValue['mileageGoodsUnit'.$value];
                    $cartValue['goodsDiscountFl'] = $postValue['goodsDiscountFl'.$value];
                    $cartValue['goodsDiscount'] = $postValue['goodsDiscount'.$value];
                    $cartValue['goodsDiscountUnit'] = $postValue['goodsDiscountUnit'.$value];
                    $cartValue['taxFreeFl'] = $postValue['taxFreeFl'.$value];

                    $cartValue['taxPercent'] = $postValue['taxPercent'.$value];
                    $cartValue['brandCd'] = $postValue['brandCd'.$value];
                    $cartValue['cateCd'] = $postValue['cateCd'.$value];
                    $cartValue['optionFl'] = $postValue['optionFl'.$value];
                    $cartValue['useBundleGoods'] = $postValue['useBundleGoods'.$value];
                    if (empty($mtn) === false) {
                        $cartValue['linkMainTheme'] = $mtn;
                    }
                    if($postValue['mainGoods'] == $value){
                        $cartValue['deliveryCollectFl'] = $postValue['deliveryCollectFl'];
                        $cartValue['deliveryMethodFl'] = $postValue['deliveryMethodFl'];
                    }else{
                        $relateDeliveryConfig = $cart->getGoodsDeliveryConfig($value);
                        //배송비 선불 후불
                        if($relateDeliveryConfig['deliveryCollectFl'] == 'both'){
                            $cartValue['deliveryCollectFl'] = $postValue['deliveryCollectFl'];
                        }else{
                            $cartValue['deliveryCollectFl'] = $relateDeliveryConfig['deliveryCollectFl'];
                        }
                        //배송 방법
                        if(in_array($postValue['deliveryMethodFl'], $relateDeliveryConfig['deliveryMethodFlEtc'])){
                            $cartValue['deliveryMethodFl'] = $postValue['deliveryMethodFl'];
                        }else{
                            $cartValue['deliveryMethodFl'] = $relateDeliveryConfig['deliveryMethodFl'];
                        }
                    }
                    $cartValue['optionSnoInput'] = $postValue['optionSnoInput'.$value];
                    $cartValue['optionCntInput'] = $postValue['optionCntInput'.$value];
                    $cartValue['goodsNo'] = $postValue['goodsNo'.$value];

                    //옵션텍스트
                    for($i=0; $i <= 100; $i++){
                        if(empty($postValue['optionTextInput'.$value.'_'.$i])) break;
                        $tmp[$postValue['optionTextSno'.$value.'_'.$i]] = $postValue['optionTextInput'.$value.'_'.$i];
                        $tmpOption[] = $tmp;
                        unset($tmp);
                    }

                    if(empty($tmpOption)){
                        $tmpOption = $postValue['optionText'.$value];
                    }

                    $cartValue['optionText'] = $tmpOption;
                    unset($tmpOption);

                    $cartValue['addGoodsInput0'] = $postValue['addGoodsInput'.$value.'0'];
                    $cartValue['addGoodsPriceSum'] = $postValue['addGoodsPriceSum'.$value];
                    $cartValue['addGoodsNo'] = $postValue['addGoodsNo'.$value];
                    $cartValue['addGoodsCnt'] = $postValue['addGoodsCnt'.$value];

                    $cartValue['optionSno'] = $postValue['optionSno'.$value];
                    $cartValue['goodsPriceSum'] = $postValue['goodsPriceSum'.$value];
                    $cartValue['addGoodsPriceSum'] = $postValue['addGoodsPriceSum'.$value];
                    $cartValue['displayOptionkey'] = $postValue['displayOptionkey'.$value];
                    $cartValue['couponApplyNo'] = $postValue['couponApplyNo'.$value];
                    $cartValue['couponSalePriceSum'] = $postValue['couponSalePriceSum'.$value];
                    $cartValue['couponAddPriceSum'] = $postValue['couponAddPriceSum'.$value];
                    $cartValue['goodsCnt'] = $postValue['goodsCnt'.$value];
                    foreach($postValue['displayOptionkey'.$postValue] as $val2){
                        $cartValue['option_price_'.$val2] = $postValue['option_price'.$postValue.'_'.$val2];
                    }
                    $cartValue['optionPriceSum'] = $postValue['optionPriceSum'.$value];

                    if($postValue['cartMode'] == 'd'){
                        $directBuy = 'y';
                    }else{
                        $directBuy = 'n';
                    }

                    // 장바구니 추가
                    try {
                        $returnData = $cart->saveInfoCart($cartValue, $directBuy, 'related');
                    }catch (Exception $e) {
                        if (Request::isAjax()) {
                            $this->json([
                                'error' => 1,
                                'message' => $e->getMessage(),
                            ]);
                        } else {
                            throw new AlertBackException($e->getMessage());
                        }
                    }
                    //추가된 상품 목록 생성
                    foreach($returnData as $returnKey => $returnVal){
                        $addedGoods[] = $returnData[$returnKey];
                        $addedGoodsQuote[] = '"'.$returnData[$returnKey].'"';
                    }
                    unset($cartValue);
                }
                // 처리별 이동 경로
                if (gd_isset($postValue['cartMode']) == 'd') {
                    $goodsList = implode(',', $addedGoods);
                    $goodsListQuote = implode(',', $addedGoodsQuote);
                    //장바구니에서 보이지 않도록 조치 시작
                    $this->db = \App::load('DB');
                    $where = 'sno IN (\''.str_replace(',', '\',\'', $goodsList.'\')');
                    $arrUpdate['directCart'] = 'y';
                    $arrBind = $this->db->get_binding(DBTableField::tableCart(), $arrUpdate, 'update', array_keys($arrUpdate));
                    $this->db->set_update_db(DB_CART, $arrBind['param'], $where, $arrBind['bind']);
                    //장바구니에서 보이지 않도록 조치 종료

                    $goodsListQuote = '['.$goodsListQuote.']';
                    $goodsListQuote = urlencode($goodsListQuote);

                    $returnUrl = './order.php?cartIdx='.$goodsListQuote;
                } else {
                    $returnUrl = './cart.php';
                }

                if ($request->isAjax()) {
                    $this->json([
                        'error' => 0,
                        'message' => __('성공'),
                    ]);
                } else {
                    $this->redirect($returnUrl, null, 'parent');
                }
                break;

            // 장바구니 옵션수정
            case 'cartUpdate':
                try {
                    $cart->updateInfoCart($postValue);
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'error' => 1,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new AlertBackException($e->getMessage());
                    }
                }
                break;

            // 장바구니 상품 수량 변경
            case 'cartCnt':
                try {
                    $postValue['cart']['useBundleGoods'] = $postValue['useBundleGoods'];
                    $cart->setCartCnt($postValue['cart']);
                    throw new AlertReloadException(null, null, null, 'parent');
                } catch (AlertReloadException $e) {
                    throw $e;
                } catch (Exception $e) {
                    throw new AlertOnlyException($e->getMessage());
                }
                break;

            // 장바구니 상품 삭제
            case 'cartDelete':
                try {
                    \Cookie::del("NPAY_BUY_UNIQUE_KEY");
                    $cart->setCartDelete($postValue['cartSno']);
                    throw new AlertReloadException(null, null, null, 'parent');
                } catch (AlertReloadException $e) {
                    throw $e;
                } catch (Exception $e) {
                    throw new AlertOnlyException($e->getMessage());
                }
                break;

            // 장바구니 상품 찜
            case 'cartToWish':
                try {
                    $cart->setCartToWish($postValue['cartSno']);
                    $message = count($postValue['cartSno']) . __('개의 상품이 찜리스트에 저장되었습니다.');
                    throw new AlertReloadException($message, null, null, 'parent');
                } catch (AlertReloadException $e) {
                    throw $e;
                } catch (Exception $e) {
                    throw new AlertOnlyException($e->getMessage());
                }
                break;

            // 장바구니 비우기
            case 'remove':
                try {
                    \Cookie::del("NPAY_BUY_UNIQUE_KEY");
                    $cart->setCartRemove();
                    throw new AlertReloadException(null, null, null, 'parent');
                } catch (AlertReloadException $e) {
                    throw $e;
                } catch (Exception $e) {
                    throw new AlertOnlyException($e->getMessage());
                }
                break;

            // 장바구니 선택 상품 주문
            case 'orderSelect':
                try {
                    $cartIdx = $cart->setOrderSelect($postValue['cartSno']);
                    $returnUrl = '../order/order.php?cartIdx=' . $cartIdx;
                    throw new AlertRedirectException(null, null, null, $returnUrl, 'parent');
                } catch (Exception $e) {
                    throw $e;
                }
                break;

            // 장바구니 선택 상품의 총 결제금액 계산
            case 'cartSelectCalculation':
                try {
                    if ($postValue['cartSno']) {
                        $cart->getCartGoodsData($postValue['cartSno']);
                        $setData = [
                            'cartCnt' => $cart->cartCnt,
                            'totalGoodsPrice' => $cart->totalGoodsPrice,
                            'totalGoodsDcPrice' => $cart->totalGoodsDcPrice,
                            'totalGoodsMileage' => $cart->totalGoodsMileage,
                            'totalMemberDcPrice' => $cart->totalSumMemberDcPrice,
                            'totalMemberOverlapDcPrice' => $cart->totalMemberOverlapDcPrice,
                            'totalMemberMileage' => $cart->totalMemberMileage,
                            'totalCouponGoodsDcPrice' => $cart->totalCouponGoodsDcPrice,
                            'totalMyappDcPrice' => $cart->totalMyappDcPrice,
                            'totalCouponGoodsMileage' => $cart->totalCouponGoodsMileage,
                            'totalDeliveryCharge' => $cart->totalDeliveryCharge,
                            'totalSettlePrice' => $cart->totalSettlePrice,
                            'totalMileage' => $cart->totalMileage,
                        ];
                    } else {
                        $setData = [
                            'cartCnt' => 0,
                            'totalGoodsPrice' => 0,
                            'totalGoodsDcPrice' => 0,
                            'totalGoodsMileage' => 0,
                            'totalMemberDcPrice' => 0,
                            'totalMemberOverlapDcPrice' => 0,
                            'totalMemberMileage' => 0,
                            'totalCouponGoodsDcPrice' => 0,
                            'totalMyappDcPrice' => 0,
                            'totalCouponGoodsMileage' => 0,
                            'totalDeliveryCharge' => 0,
                            'totalSettlePrice' => 0,
                            'totalMileage' => 0,
                        ];
                    }

                    $this->json($setData);
                    exit;
                } catch (Exception $e) {
                    $this->json($e->getMessage());
                    exit;
                }
                break;

            // 장바구니 쿠폰 취소
            case 'couponDelete':
                try {
                    $cart->setMemberCouponDelete($postValue['cart']['cartSno']);
                    throw new AlertReloadException(null, null, null, 'parent');
                } catch (AlertReloadException $e) {
                    throw $e;
                } catch (Exception $e) {
                    throw new AlertOnlyException($e->getMessage());
                }
                break;

            // 장바구니 쿠폰 적용
            case 'couponApply':
                try {
                    // 이미 사용중인 쿠폰이 있을 경우 중복 사용 체크
                    // 스킨패치가 되어있을 경우 이미 front에서 상품쿠폰 해제했으므로 이슈 없음.
                    if (empty($postValue['cart']['couponApplyNo']) === false) {
                        $coupon = \App::load('\\Component\\Coupon\\Coupon');
                        if (method_exists($coupon, 'couponOverlapCheck') === true) {
                            $coupon->couponOverlapCheck($postValue['cart']['couponApplyNo'], $postValue['cart']['cartSno']);
                        }
                    }
                    $cart->setMemberCouponApply($postValue['cart']['cartSno'], $postValue['cart']['couponApplyNo']);
                    throw new AlertReloadException(null, null, null, 'parent');
                } catch (AlertReloadException $e) {
                    throw $e;
                } catch (Exception $e) {
                    throw new AlertOnlyException($e->getMessage());
                }
                break;

            // 지역별 배송비 계산하기 (바로구매시 바로구매 쿠키가 사라지는 증상으로 인해 order_ps에서 이동 처리)
            case 'check_area_delivery':
                try {
                    // 장바구니내 지역별 배송비 처리를 위한 주소 값
                    $address = Request::post()->get('receiverAddress');

                    // 장바구니 정보 (해당 프로퍼티를 가장 먼저 실행해야 계산된 금액 사용 가능)
                    $cart->getCartGoodsData($postValue['cartSno'], $address);

                    // 주문서 작성시 발생되는 금액을 장바구니 프로퍼티로 설정하고 최종 settlePrice를 산출 (사용 마일리지/예치금/주문쿠폰)
                    $orderPrice = $cart->setOrderSettleCalculation($postValue);

                    $mileageUse = [];
                    $memInfo = $this->getData('gMemberInfo');
                    if(count($memInfo) > 0){
                        // 마일리지 정책
                        // '마일리지 정책에 따른 주문시 사용가능한 범위 제한' 에 사용되는 기준금액 셋팅
                        $setMileagePriceArr = [
                            'totalCouponOrderDcPrice' => gd_isset($postValue['totalCouponOrderDcPrice'], 0),
                        ];
                        $mileagePrice = $cart->setMileageUseLimitPrice($setMileagePriceArr);
                        // 마일리지 정책에 따른 주문시 사용가능한 범위 제한
                        $mileageUse = $cart->getMileageUseLimit(gd_isset($memInfo['mileage'], 0), $mileagePrice);
                    }

                    $this->json([
                        'areaDelivery' => array_sum($orderPrice['totalGoodsDeliveryAreaCharge']),
                        'mileageUse' => $mileageUse,
                    ]);

                } catch (Exception $e) {
                    $this->json([
                        'error' => 1,
                        'message' => $e->getMessage(),
                    ]);
                }
                break;

            // 지역별 배송비 계산하기 (바로구매시 바로구매 쿠키가 사라지는 증상으로 인해 order_ps에서 이동 처리)
            case 'check_country_delivery':
                try {
                    // 국가코드가 잘못된 경우 배송비 0원 처리
                    if (v::countryCode()->validate($postValue['countryCode']) === false) {
                        $this->json([
                            'overseasDelivery' => 0,
                            'overseasInsuranceFee' => 0,
                        ]);
                    }

                    // 장바구니 정보 (해당 프로퍼티를 가장 먼저 실행해야 계산된 금액 사용 가능)
                    $cart->getCartGoodsData($postValue['cartSno'], $postValue['countryCode']);

                    // 주문서 작성시 발생되는 금액을 장바구니 프로퍼티로 설정하고 최종 settlePrice를 산출 (사용 마일리지/예치금/주문쿠폰)
                    $orderPrice = $cart->setOrderSettleCalculation($postValue);

                    $this->json([
                        'overseasDelivery' => $orderPrice['totalDeliveryCharge'],
                        'overseasInsuranceFee' => $orderPrice['totalDeliveryInsuranceFee'],
                    ]);

                } catch (Exception $e) {
                    $this->json([
                        'error' => 1,
                        'message' => $e->getMessage(),
                    ]);
                }
                exit();
                break;

            case 'multi_shipping_delivery':
                try {
                    $selectGoods = json_decode($postValue['selectGoods'], true);

                    $cartIdx = $setGoodsCnt = $setAddGoodsCnt = $setDeliveryMethodFl = $setDeliveryCollectFl = [];
                    foreach ($selectGoods as $key => $val) {
                        if ($val['goodsCnt'] > 0) {
                            $cartIdx[] = $val['sno'];
                            $setGoodsCnt[$val['sno']]['goodsCnt'] = $val['goodsCnt'];
                            if (empty($val['deliveryMethodFl']) === false) $setDeliveryMethodFl[$val['sno']]['deliveryMethodFl'] = $val['deliveryMethodFl'];
                            if (empty($val['deliveryCollectFl']) === false) $setDeliveryCollectFl[$val['sno']]['deliveryCollectFl'] = $val['deliveryCollectFl'];
                        }
                        if (empty($val['addGoodsNo']) === false) {
                            foreach ($val['addGoodsNo'] as $aKey => $aVal) {
                                $setAddGoodsCnt[$val['sno']][$aVal] = $val['addGoodsCnt'][$aKey];
                            }
                        }
                    }

                    $data = $cart->getCartGoodsData($cartIdx, $postValue['address'], null, false, true, $postValue, $setGoodsCnt, $setAddGoodsCnt, $setDeliveryMethodFl, $setDeliveryCollectFl);
                    $deliverInfo = [];
                    $parentCartSno = '';
                    if ($postValue['useDeliveryInfo'] == 'y') {
                        $deliveryCollect = ['pre' => __('선결제'), 'later' => __('착불'),];
                        $setDeliveryFl = [];

                        foreach ($data as $scmNo => $sVal) {
                            foreach($sVal as $deliverySno => $dVal) {
                                foreach ($dVal as $key => $val) {
                                    if ($val['goodsDeliveryFl'] == 'y' || ($val['goodsDeliveryFl'] != 'y' && $val['sameGoodsDeliveryFl'] == 'y')) {
                                        if ($val['goodsDeliveryFl'] == 'y') {
                                            $deliveryPrice = $val['goodsDeliveryCollectFl'] == 'pre' ? $cart->setDeliveryInfo[$deliverySno]['goodsDeliveryPrice'] : $cart->setDeliveryInfo[$deliverySno]['goodsDeliveryCollectPrice'];

                                            if (empty($setDeliveryFl[$deliverySno]) === false) {
                                                $val['parentCartSno'] = $setDeliveryFl[$deliverySno];
                                            } else {
                                                $setDeliveryFl[$deliverySno] = $val['parentCartSno'];
                                            }
                                        } else {
                                            $deliveryPrice = $val['goodsDeliveryCollectFl'] == 'pre' ? $cart->setDeliveryInfo[$deliverySno][$val['goodsNo']]['goodsDeliveryPrice'] + $cart->setDeliveryInfo[$deliverySno][$val['goodsNo']]['goodsDeliveryAreaPrice'] : $cart->setDeliveryInfo[$deliverySno][$val['goodsNo']]['goodsDeliveryCollectPrice'];
                                        }

                                        if ($parentCartSno != $val['parentCartSno']) $parentCartSno = $val['parentCartSno'];
                                        $deliverInfo[$parentCartSno]['rowspan'] += (1 + array_sum($setAddGoodsCnt[$val['sno']]));
                                        $deliverInfo[$parentCartSno]['goodsDeliveryMethod'] = $val['goodsDeliveryMethod'];
                                        $deliverInfo[$parentCartSno]['deliveryPrice'] = $deliveryPrice;
                                        $deliverInfo[$parentCartSno]['deliveryMethodFl'] = $val['goodsDeliveryMethodFlText'];
                                        $deliverInfo[$parentCartSno]['goodsDeliveryCollectFl'] = $deliveryCollect[$val['goodsDeliveryCollectFl']];
                                    } else {
                                        $deliverInfo[$val['sno']] = [
                                            'rowspan' => 1 + array_sum($setAddGoodsCnt[$val['sno']]),
                                            'goodsDeliveryMethod' => $val['goodsDeliveryMethod'],
                                            'deliveryPrice' => $val['goodsDeliveryCollectFl'] == 'pre' ? $val['price']['goodsDeliveryPrice'] + $val['price']['goodsDeliveryAreaPrice'] : $val['price']['goodsDeliveryCollectPrice'],
                                            'deliveryMethodFl' => $val['goodsDeliveryMethodFlText'],
                                            'goodsDeliveryCollectFl' => $deliveryCollect[$val['goodsDeliveryCollectFl']],
                                        ];
                                    }
                                }
                            }
                        }
                    }

                    $this->json([
                        'deliveryCharge' => array_sum($cart->totalGoodsDeliveryPolicyCharge),
                        'deliveryAreaPrice' => array_sum($cart->totalGoodsDeliveryAreaPrice),
                        'deliveryInfo' => $deliverInfo
                    ]);
                } catch (Exception $e) {
                    $this->json([
                        'error' => 1,
                        'message' => $e->getMessage(),
                    ]);
                }
                exit();
                break;

            case 'check_multi_area_delivery':
                // 회원 정보
                $memInfo = $this->getData('gMemberInfo');
                $tmpMileagePrice = [];

                parse_str($postValue['data'], $data);
                $addressHead = 'receiver';
                if (empty($data['tmpDeliverTab']) === false) {
                    $addressHead = $data['tmpDeliverTab'];
                }
                $areaDelivery = $policyCharge = 0;
                foreach ($data['selectGoods'] as $key => $val) {
                    if (empty($val) === false) {
                        $setData = [];
                        $cartIdx = $setGoodsCnt = $setAddGoodsCnt = [];
                        $selectGoods = json_decode($val, true);


                        foreach ($selectGoods as $tKey => $tVal) {
                            if ($tVal['goodsCnt'] > 0) {
                                $cartIdx[] = $tVal['sno'];
                                $setGoodsCnt[$tVal['sno']] = $tVal['goodsCnt'];
                                $setData[$tVal['scmNo']][$tVal['deliverySno']][$tKey] = [
                                    'goodsNo' => $tVal['goodsNo'],
                                    'goodsCnt' => $tVal['goodsCnt'],

                                ];
                            }
                            if (empty($tVal['addGoodsNo']) === false) {
                                foreach ($tVal['addGoodsNo'] as $aKey => $aVal) {
                                    $setAddGoodsCnt[$tVal['sno']][$aVal] = $tVal['addGoodsCnt'][$aKey];
                                }
                            }
                        }

                        if ($key == 0) {
                            $address = $data[$addressHead . 'Address'];
                        } else {
                            $address = gd_isset($data['receiverAddressAdd'][$key], $data['shippingAddressAdd'][$key]);
                        }

                        $cart->getCartGoodsData($cartIdx, $address, null, false, true, $postValue, $setGoodsCnt, $setAddGoodsCnt);

                        $policyCharge += array_sum($cart->totalGoodsDeliveryPolicyCharge);
                        $areaDelivery += array_sum($cart->totalGoodsDeliveryAreaPrice);
                        unset($cart->totalGoodsDeliveryAreaPrice);
                        unset($cart->totalPrice, $cart->totalGoodsDcPrice, $cart->totalMemberDcPrice, $cart->totalMemberOverlapDcPrice, $cart->totalCouponGoodsDcPrice, $cart->totalDeliveryCharge, $cart->totalGoodsDeliveryPolicyCharge);
                    }
                }

                $mileageUse = [];
                if(count($memInfo) > 0) {
                    $cart->getCartGoodsData($data['cartSno'], null, null, false, $postValue);

                    // 마일리지 정책
                    // '마일리지 정책에 따른 주문시 사용가능한 범위 제한' 에 사용되는 기준금액 셋팅
                    $setMileagePriceArr = [
                        'totalDeliveryCharge' => $policyCharge + $areaDelivery,
                        'totalGoodsDeliveryAreaPrice' => $areaDelivery,
                    ];
                    $mileagePrice = $cart->setMileageUseLimitPrice($setMileagePriceArr);
                    // 마일리지 정책에 따른 주문시 사용가능한 범위 제한
                    $mileageUse = $cart->getMileageUseLimit(gd_isset($memInfo['mileage'], 0), $mileagePrice);
                }

                $this->json([
                    'areaDelivery' => $areaDelivery,
                    'maximumLimit' => gd_isset($mileageUse['maximumLimit'], 0), //레거시보존
                    'mileageUse' => $mileageUse,
                ]);
                exit();
                break;

            case 'set_mileage' :
                $memInfo = $this->getData('gMemberInfo');

                $cart->getCartGoodsData($postValue['cartSno'], null, null, false, $postValue);

                // 마일리지 정책
                // '마일리지 정책에 따른 주문시 사용가능한 범위 제한' 에 사용되는 기준금액 셋팅
                $setMileagePriceArr = [
                    'totalDeliveryCharge' => $postValue['totalDeliveryCharge'] + $postValue['deliveryAreaCharge'],
                    'totalGoodsDeliveryAreaPrice' => $postValue['deliveryAreaCharge'],
                    'totalCouponOrderDcPrice' => gd_isset($postValue['totalCouponOrderDcPrice'], 0),
                ];
                $mileagePrice = $cart->setMileageUseLimitPrice($setMileagePriceArr);
                // 마일리지 정책에 따른 주문시 사용가능한 범위 제한
                $mileageUse = $cart->getMileageUseLimit(gd_isset($memInfo['mileage'], 0), $mileagePrice);

                $this->json([
                    'mileageUse' => $mileageUse,
                ]);
                exit;
                break;

            // 상품 쿠폰 주문 적용 / 변경 / 삭제
            case 'goodsCouponOrderApply':
                try {
                    if($postValue['cartIdx']) {
                        // 상품적용 쿠폰 제거
                        sort($postValue['cartIdx']);
                        foreach ($postValue['cartIdx'] as $delKey => $delVal) {
                            if (array_key_exists($delVal, $postValue['cart']) == false) {
                                $cart->setMemberCouponDelete($delVal);
                            }
                        }
                    }
                    if($postValue['cart']) {
                        // 상품적용 쿠폰 적용 / 변경
                        foreach ($postValue['cart'] as $cartKey => $cartApplyData) {
                            if ($cartApplyData) {
                                $cart->setMemberCouponApply($cartKey, $cartApplyData);
                            }
                        }
                    }

                } catch (Exception $e) {
                    $this->json([
                        'error' => 1,
                        'message' => $e->getMessage(),
                    ]);
                }
                break;

            // 상품 쿠폰 주문 페이지에서 사용 가능 여부 체크
            case 'goodsCouponOrderApplyUseCheck':
                try {
                    if($postValue['memberCouponNo']) {
                        $coupon = \App::load('\\Component\\Coupon\\Coupon');
                        $cartInfo = $cart->getCartGoodsData($postValue['cartAllSno'], null, null, false, false); // 상품쿠폰 주문에서 적용 시 사용
                        $useAbleFlag = $coupon->getProductCouponUsableCheck($postValue['memberCouponNo'], $postValue['cartSno'], $cartInfo, $cart->totalPrice);
                        echo $useAbleFlag;
                    }
                } catch (Exception $e) {
                    $this->json([
                        'error' => 1,
                        'message' => $e->getMessage(),
                    ]);
                }
                break;
            // 옵션이 같은 경우 재고 체크
            case 'cartSelectStock':
                $stock = $cart->cartSelectStock($postValue['sno']);
                echo $stock;
                break;
            // 장바구니 사용 상태의 쿠폰 적용 취소 및 재적용
            case 'UserCartCouponDel':
                $cartInfo = $cart->getCartGoodsData();
                if($cartInfo > 0) {
                    foreach ($cartInfo as $key => $value) {
                        foreach ($value as $key1 => $value1) {
                            foreach ($value1 as $key2 => $value2) {
                                // 장바구니 쿠폰 재정의
                                $memberCouponNoArr = explode(INT_DIVISION, $value2['memberCouponNo']);
                                $chkMemberCouponNoArr = explode(INT_DIVISION, $postValue['memberCouponNo']);
                                if ($value2['memberCouponNo']) {
                                    $newMemberCouponNoArr[$value2['sno']] = implode(INT_DIVISION, array_diff($memberCouponNoArr, $chkMemberCouponNoArr));
                                }
                            }
                        }
                    }
                }

                // 기존에 쿠폰이 적용된 장바구니 쿠폰 재정의
                if ($newMemberCouponNoArr) {
                    foreach ($newMemberCouponNoArr as $cartSno => $memberCouponNo) {
                        $cart->setMemberCouponApply($cartSno, $memberCouponNo);
                    }
                }
                break;

            // 쿠폰상태 확인 및 상태값 변경 (쿠폰 적용 레이어)
            case 'checkCouponType':
                $getValue = Request::post()->toArray();
                if($getValue['mileageGiveFl']) {
                    $mileageGive = gd_policy('member.mileageGive');
                    if($mileageGive['giveFl'] == 'n') {
                        $this->json(array('isSuccess'=>false));
                        break;
                    }
                }
                // 모듈 호출
                $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
                $return = $couponAdmin->checkCouponTypeArr($getValue['couponNo']);
                $this->json(array('isSuccess'=>$return));
                break;

            // 쿠폰상태 확인 및 상태값 변경
            case 'CheckCouponTypeArr':
                $coupon = \App::load('\\Component\\Coupon\\Coupon');
                $reSetMemberCouponApply = false;
                $couponApply = [];
                $resetCouponApply = [];
                $resetMemberCouponSalePrice = 0;
                $resetMemberCouponAddMileage = 0;
                $setOrderCouponArr = [];
                $setCouponApplyOrderNo  = '';
                $resetOrderCouponArr = [];
                $resetCouponApplyOrderNo  = '';

                // 주문 및 배송비 적용 쿠폰 사용기간 체크
                if ($postValue['couponApplyOrderNo']) {
                    $memberCouponNoArr = explode(INT_DIVISION, $postValue['couponApplyOrderNo']);
                    foreach ($memberCouponNoArr as $memberCouponNo) {
                        if (gd_isset($memberCouponNo)) {
                            $memberCouponData = $coupon->getMemberCouponInfo($memberCouponNo, 'c.couponNo, mc.memberCouponNo, c.couponKindType');
                            if($coupon->checkCouponType($memberCouponData['couponNo'], 'y', $memberCouponNo)) {
                                $setOrderCouponArr[] = $memberCouponNo;
                                $setCouponApplyOrderNo = implode(INT_DIVISION, $setOrderCouponArr);
                            } else {
                                $reSetMemberCouponApply = true;
                                $resetOrderCouponArr[] = $memberCouponNo;
                                $resetCouponApplyOrderNo = implode(INT_DIVISION, $resetOrderCouponArr);
                            }
                        }
                    }
                }

                $mileageGive = gd_policy('member.mileageGive');
                // 상품적용 쿠폰 사용기간 체크에 따른 처리
                foreach($postValue['cartSno'] as $key => $cartIdx){
                    $cartInfo = $cart->getCartGoodsData($cartIdx);
                    if($cartInfo > 0) {
                        foreach ($cartInfo as $key => $value) {
                            foreach ($value as $key1 => $value1) {
                                foreach ($value1 as $key2 => $value2) {
                                    if ($value2['memberCouponNo']) {
                                        // 장바구니에 사용된 회원쿠폰의 정율도 정액으로 계산된 금액
                                        $convertCartCouponPriceArrData = $coupon->getMemberCouponPrice($value2['price'], $value2['memberCouponNo']);
                                        $memberCouponNoArr = explode(INT_DIVISION, $value2['memberCouponNo']);
                                        foreach ($memberCouponNoArr as $memberCouponNo) {
                                            $memberCouponData = $coupon->getMemberCouponInfo($memberCouponNo, 'c.couponNo, mc.memberCouponNo, c.couponKindType');
                                            if($coupon->checkCouponType($memberCouponData['couponNo'], 'y', $memberCouponNo)) {
                                                if($memberCouponData['couponKindType'] == 'add' && $mileageGive['giveFl'] == 'n') {
                                                    $reSetMemberCouponApply = true;
                                                    $resetCouponApply[$value2['sno']]['couponApplyNo'][] = $memberCouponData['memberCouponNo'];
                                                    $resetCouponApply[$value2['sno']]['memberCouponSalePrice'][] =  $convertCartCouponPriceArrData['memberCouponSalePrice'][$memberCouponData['memberCouponNo']];
                                                    $resetCouponApply[$value2['sno']]['memberCouponAddMileage'][] =  $convertCartCouponPriceArrData['memberCouponAddMileage'][$memberCouponData['memberCouponNo']];
                                                    $cart->setMemberCouponDelete($value2['sno']); // 상품적용 쿠폰 제거
                                                } else {
                                                    $couponApply[$value2['sno']]['couponApplyNo'][] = $memberCouponData['memberCouponNo'];
                                                    $couponApply[$value2['sno']]['memberCouponSalePrice'][] =  $convertCartCouponPriceArrData['memberCouponSalePrice'][$memberCouponData['memberCouponNo']];
                                                    $couponApply[$value2['sno']]['memberCouponAddMileage'][] =  $convertCartCouponPriceArrData['memberCouponAddMileage'][$memberCouponData['memberCouponNo']];
                                                }
                                            } else {
                                                $reSetMemberCouponApply = true;
                                                $resetCouponApply[$value2['sno']]['couponApplyNo'][] = $memberCouponData['memberCouponNo'];
                                                $resetCouponApply[$value2['sno']]['memberCouponSalePrice'][] =  $convertCartCouponPriceArrData['memberCouponSalePrice'][$memberCouponData['memberCouponNo']];
                                                $resetCouponApply[$value2['sno']]['memberCouponAddMileage'][] =  $convertCartCouponPriceArrData['memberCouponAddMileage'][$memberCouponData['memberCouponNo']];
                                                $cart->setMemberCouponDelete($value2['sno']); // 상품적용 쿠폰 제거
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // 사용가능한 쿠폰만 다시 적용
                if($reSetMemberCouponApply) {
                    foreach ($couponApply as $cartKey => $couponApplyInfo) {
                        // 상품적용 쿠폰 적용 / 변경
                        $couponApplyNo = implode(INT_DIVISION, $couponApplyInfo['couponApplyNo']);
                        $cart->setMemberCouponApply($cartKey, $couponApplyNo);
                    }

                    foreach ($resetCouponApply as $cartKey => $resetCouponApplyInfo) {
                        // 사용 불가능한 쿠폰의 할인금액 및 마일리지 합계
                        $resetMemberCouponSalePrice += array_sum($resetCouponApply[$cartKey]['memberCouponSalePrice']);
                        $resetMemberCouponAddMileage += array_sum($resetCouponApply[$cartKey]['memberCouponAddMileage']);
                    }
                    $this->json(array('isSuccess'=>$reSetMemberCouponApply, 'memberCouponSalePrice'=>$resetMemberCouponSalePrice, 'memberCouponAddMileage'=>$resetMemberCouponAddMileage, 'setCouponApplyOrderNo'=>$setCouponApplyOrderNo, 'resetCouponApplyOrderNo'=>$resetCouponApplyOrderNo));
                }
                break;
        }
        exit();
    }
}
