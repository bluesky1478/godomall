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
namespace Bundle\Controller\Admin\Order;

use Request;
use Exception;
use Component\Cart\CartAdmin;
use Session;

/**
 * Class LayerShippingAddress
 *
 * @package Bundle\Controller\Admin\Order
 * @author  <bumyul2000@godo.co.kr>
 */
class PopupSelfOrderMemberCartController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            $getValue = Request::get()->toArray();

            // 장바구니 정보
            $cart = new CartAdmin($getValue['memNo'], true);
            $cartInfo = $cart->getCartGoodsData();

            // 쿠폰 설정값 정보
            $couponConfig = gd_policy('coupon.config');
            $this->setData('couponUse', gd_isset($couponConfig['couponUseType'], 'n')); // 쿠폰 사용여부
            $this->setData('couponConfig', gd_isset($couponConfig)); // 쿠폰설정

            // 마일리지 지급 정보
            $this->setData('mileage', $cart->mileageGiveInfo['info']);

            $this->setData('cartInfo', $cartInfo);

            // 장바구니에 사용된 쿠폰의 유효성 체크
            if($cartInfo > 0) {
                $coupon = \App::load('\\Component\\Coupon\\Coupon');
                $reSetMemberCouponApply = false;
                foreach ($cartInfo as $key => $value) {
                    foreach ($value as $key1 => $value1) {
                        foreach ($value1 as $key2 => $value2) {
                            if ($value2['memberCouponNo']) {
                                $memberCouponNoArr = explode(INT_DIVISION, $value2['memberCouponNo']);
                                foreach ($memberCouponNoArr as $memberCouponNo) {
                                    $memberCouponData = $coupon->getMemberCouponInfo($memberCouponNo, 'c.couponNo, mc.memberCouponNo');
                                    if($coupon->checkCouponType($memberCouponData['couponNo'], 'y', $memberCouponNo)) {
                                        $couponApply[$value2['sno']]['couponApplyNo'][] = $memberCouponData['memberCouponNo'];
                                    } else {
                                        $reSetMemberCouponApply = true;
                                        $cart->setMemberCouponDelete($value2['sno']); // 상품적용 쿠폰 제거
                                    }
                                }
                            }
                        }
                    }
                }

                // 사용가능한 쿠폰만 다시 적용
                if($reSetMemberCouponApply) {
                    $this->setData('reSetMemberCouponApply', $reSetMemberCouponApply);
                    foreach ($couponApply as $cartKey => $couponApplyInfo) {
                        // 상품적용 쿠폰 적용 / 변경
                        $couponApplyNo = implode(INT_DIVISION, $couponApplyInfo['couponApplyNo']);
                        $cart->setMemberCouponApply($cartKey, $couponApplyNo);
                    }
                }
            }

            $this->setData('cartScmCnt', $cart->cartScmCnt); // 장바구니 SCM 수량
            $this->setData('cartScmInfo', $cart->cartScmInfo); // 장바구니 SCM 정보
            $this->setData('cartScmGoodsCnt', $cart->cartScmGoodsCnt); // 장바구니 SCM 상품 갯수
            $this->setData('setDeliveryInfo', $cart->setDeliveryInfo); // 배송비조건별 배송 정보
            $this->setData('totalScmGoodsPrice', $cart->totalScmGoodsPrice); // SCM 별 상품 총 가격
            $this->setData('totalScmGoodsDcPrice', $cart->totalScmGoodsDcPrice); // SCM 별 상품 할인 총 가격
            $this->setData('totalScmMemberDcPrice', $cart->totalScmMemberDcPrice); // scm 별 회원 그룹 추가 할인 총 가격
            $this->setData('totalScmMemberOverlapDcPrice', $cart->totalScmMemberOverlapDcPrice); // scm 별 회원 그룹 중복 할인 총 가격
            $this->setData('totalScmCouponGoodsDcPrice', $cart->totalScmCouponGoodsDcPrice); // scm 별 상품 총 쿠폰 금액
            $this->setData('totalScmGoodsDeliveryCharge', $cart->totalScmGoodsDeliveryCharge); // SCM 별 총 배송 금액
            $this->setData('totalMileage', $cart->totalMileage); // 총 적립 마일리지 (예정)
            $this->setData('totalSettlePrice', $cart->totalSettlePrice); // 총 결제 금액 (예정)
            $this->setData('totalDeliveryCharge', $cart->totalDeliveryCharge); // 상품 배송정책별 총 배송 금액
            $this->setData('totalCouponGoodsDcPrice', $cart->totalCouponGoodsDcPrice); // 상품 총 쿠폰 금액
            $this->setData('totalSumMemberDcPrice', $cart->totalSumMemberDcPrice); // 회원 할인 총 금액
            $this->setData('totalGoodsDcPrice', $cart->totalGoodsDcPrice); // 상품 할인 총 가격
            $this->setData('totalGoodsPrice', $cart->totalGoodsPrice); // 상품 총 가격
            $this->setData('cartCnt', $cart->cartCnt); // 장바구니 수량
            $this->setData('memNo', $getValue['memNo']); // 회원번호

            $this->getView()->setDefine('layout', 'layout_blank.php');
        } catch (Exception $e) {
            throw $e;
        }
    }
}
