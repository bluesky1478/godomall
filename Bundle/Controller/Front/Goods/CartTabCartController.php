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

use App;
use Session;
use Request;
use Exception;
use Framework\Utility\ArrayUtils;
use Cookie;

/**
 * Class LayerDeliveryAddress
 *
 * @package Bundle\Controller\Front\Order
 * @author  su
 */
class CartTabCartController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {

            if (!Request::isAjax()) {
                throw new Exception('Ajax ' . __('전용 페이지 입니다.'));
            }
            //바로구매 쿠키 삭제
            Cookie::del('isDirectCart');

            // 모듈 설정
            $cart = \App::Load(\Component\Cart\Cart::class);

            // 장바구니 정보
            $cartInfo = $cart->getCartGoodsData();
            $this->setData('cartInfo', $cartInfo);

            // 쿠폰 설정값 정보
            $couponConfig = gd_policy('coupon.config');
            $this->setData('couponUse', gd_isset($couponConfig['couponUseType'], 'n')); // 쿠폰 사용여부
            $this->setData('couponConfig', gd_isset($couponConfig)); // 쿠폰설정

            // 장바구니 모드
            $this->setData('orderItemMode', 'cart');

            // 마일리지 지급 정보
            $this->setData('mileage', $cart->mileageGiveInfo['info']);

            // 상품 옵션가 표시설정 config 불러오기
            $optionPriceConf = gd_policy('goods.display');
            $this->setData('optionPriceFl', gd_isset($optionPriceConf['optionPriceFl'], 'y')); // 상품 옵션가 표시설정

            // 장바구니 객체에서 계산된 상품정보
            $this->setData('cartCnt', $cart->cartCnt); // 장바구니 수량
            $this->setData('shoppingUrl', $cart->shoppingUrl); // 쇼핑 계속하기 URL
            $this->setData('cartScmInfo', $cart->cartScmInfo); // 장바구니 SCM 정보
            $this->setData('cartScmCnt', $cart->cartScmCnt); // 장바구니 SCM 수량
            $this->setData('cartScmGoodsCnt', $cart->cartScmGoodsCnt); // 장바구니 SCM 상품 갯수
            $this->setData('totalGoodsPrice', $cart->totalGoodsPrice); // 상품 총 가격
            $this->setData('totalGoodsDcPrice', $cart->totalGoodsDcPrice); // 상품 할인 총 가격
            $this->setData('totalGoodsMileage', $cart->totalGoodsMileage); // 상품별 총 상품 마일리지
            $this->setData('totalScmGoodsPrice', $cart->totalScmGoodsPrice); // SCM 별 상품 총 가격
            $this->setData('totalScmGoodsDcPrice', $cart->totalScmGoodsDcPrice); // SCM 별 상품 할인 총 가격
            $this->setData('totalScmGoodsMileage', $cart->totalScmGoodsMileage); // SCM 별 총 상품 마일리지
            $this->setData('totalMemberDcPrice', $cart->totalMemberDcPrice); // 회원 그룹 추가 할인 총 가격
            $this->setData('totalMemberOverlapDcPrice', $cart->totalMemberOverlapDcPrice); // 회원 그룹 중복 할인 총 가격
            $this->setData('totalScmMemberDcPrice', $cart->totalScmMemberDcPrice); // scm 별 회원 그룹 추가 할인 총 가격
            $this->setData('totalScmMemberOverlapDcPrice', $cart->totalScmMemberOverlapDcPrice); // scm 별 회원 그룹 중복 할인 총 가격
            $this->setData('totalSumMemberDcPrice', $cart->totalSumMemberDcPrice); // 회원 할인 총 금액
            $this->setData('totalMemberMileage', $cart->totalMemberMileage); // 회원 그룹 총 마일리지
            $this->setData('totalScmMemberMileage', $cart->totalScmMemberMileage); // scm 별 회원 그룹 총 마일리지
            $this->setData('totalCouponGoodsDcPrice', $cart->totalCouponGoodsDcPrice); // 상품 총 쿠폰 금액
            $this->setData('totalScmCouponGoodsDcPrice', $cart->totalScmCouponGoodsDcPrice); // scm 별 상품 총 쿠폰 금액
            $this->setData('totalCouponGoodsMileage', $cart->totalCouponGoodsMileage); // 상품 총 쿠폰 마일리지
            $this->setData('totalScmCouponGoodsMileage', $cart->totalScmCouponGoodsMileage); // scm 별 상품 총 쿠폰 마일리지
            $this->setData('totalDeliveryCharge', $cart->totalDeliveryCharge); // 상품 배송정책별 총 배송 금액
            $this->setData('totalScmGoodsDeliveryCharge', $cart->totalScmGoodsDeliveryCharge); // SCM 별 총 배송 금액
            $this->setData('totalSettlePrice', $cart->totalSettlePrice); // 총 결제 금액 (예정)
            $this->setData('totalPlusMileage', $cart->totalPlusMileage); // 총 적립 마일리지 (예정)
            $this->setData('orderPossible', $cart->orderPossible); // 주문 가능 여부
            $this->setData('setDeliveryInfo', $cart->setDeliveryInfo); // 배송비조건별 배송 정보




        } catch (Exception $e) {
            $this->json([
                'error' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
