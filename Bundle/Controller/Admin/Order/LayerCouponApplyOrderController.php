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

use App;
use Session;
use Request;
use Exception;
use Framework\Utility\ArrayUtils;
use Component\Cart\CartAdmin;
use Component\Coupon\Coupon;

/**
 * 주문 쿠폰 적용
 *
 * @author  su
 */
class LayerCouponApplyOrderController extends \Controller\Admin\Controller
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

            $post = Request::post()->toArray();
            if($post['memNo'] < 1){
                throw new Exception('회원을 선택해 주세요.');
            }

            // 로그인 체크
            $cart = new CartAdmin($post['memNo']);
            $coupon = App::load(\Component\Coupon\Coupon::class);

            // 장바구니(주문)의 상품 데이터
            $cartInfo = $cart->getCartGoodsData($post['cartSno']);
            $goodsPriceArr = [
                'goodsPriceSum'=>$cart->totalPrice['goodsPrice'],
                'optionPriceSum'=>$cart->totalPrice['optionPrice'],
                'optionTextPriceSum'=>$cart->totalPrice['optionTextPrice'],
                'addGoodsPriceSum'=>$cart->totalPrice['addGoodsPrice'],
            ];
            if($post['couponApplyOrderNo']) {
                // 장바구니에 사용된 회원쿠폰 리스트
                $cartCouponNoArr = explode(INT_DIVISION,$post['couponApplyOrderNo']);
                foreach($cartCouponNoArr as $cartCouponKey => $cartCouponVal) {
                    if ($cartCouponVal) {
                        $cartCouponArrData[$cartCouponKey] = $coupon->getMemberCouponInfo($cartCouponVal);
                    }
                }
                // 장바구니에 사용된 회원쿠폰 리스트를 보기용으로 변환
                $convertCartCouponArrData = $coupon->convertCouponArrData($cartCouponArrData);
                // 장바구니에 사용된 회원쿠폰의 정율도 정액으로 계산된 금액
                $convertCartCouponPriceArrData = $coupon->getMemberCouponPrice($goodsPriceArr, $post['couponApplyOrderNo']);
                $this->setData('cartCouponArrData', $cartCouponArrData);
                $this->setData('convertCartCouponArrData', $convertCartCouponArrData);
                $this->setData('convertCartCouponPriceArrData', $convertCartCouponPriceArrData);
            }

            // 해당 상품의 사용가능한 회원쿠폰 리스트
            $memberCouponArrData = $coupon->getOrderMemberCouponList($post['memNo'], $cart->payLimit, true);
            if(is_array($memberCouponArrData['order'])){
                $memberCouponNoArr['order'] = array_column($memberCouponArrData['order'],'memberCouponNo');
                if ($memberCouponNoArr['order']) {
                    $memberCouponNoString['order'] = implode(INT_DIVISION, $memberCouponNoArr['order']);
                    // 해당 상품의 사용가능한 회원쿠폰 리스트를 보기용으로 변환
                    $convertMemberCouponArrData['order'] = $coupon->convertCouponArrData($memberCouponArrData['order']);
                    // 해당 상품의 사용가능한 회원쿠폰의 정율도 정액으로 계산된 금액
                    $convertMemberCouponPriceArrData['order'] = $coupon->getMemberCouponPrice($goodsPriceArr, $memberCouponNoString['order']);
                }
            }
            if(is_array($memberCouponArrData['delivery'])){
                $memberCouponNoArr['delivery'] = array_column($memberCouponArrData['delivery'],'memberCouponNo');
                if ($memberCouponNoArr['delivery']) {
                    $memberCouponNoString['delivery'] = implode(INT_DIVISION, $memberCouponNoArr['delivery']);
                    // 해당 상품의 사용가능한 회원쿠폰 리스트를 보기용으로 변환
                    $convertMemberCouponArrData['delivery'] = $coupon->convertCouponArrData($memberCouponArrData['delivery']);
                    // 해당 상품의 사용가능한 회원쿠폰의 정율도 정액으로 계산된 금액
                    $convertMemberCouponPriceArrData['delivery'] = $coupon->getMemberCouponPrice($goodsPriceArr, $memberCouponNoString['delivery']);
                }
            }

            $this->setData('memberCouponArrData', $memberCouponArrData);
            $this->setData('convertMemberCouponArrData', $convertMemberCouponArrData);
            $this->setData('convertMemberCouponPriceArrData', $convertMemberCouponPriceArrData);
            $this->setData('couponApplyOrderNo', $post['couponApplyOrderNo']);
            $this->setData('int_division', INT_DIVISION);

            $this->addScript(
                [
                    'numeral/numeral.min.js',
                ]
            );
            $this->getView()->setDefine('layout', 'layout_layer.php');
        } catch (Exception $e) {
            $this->json([
                'error' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
