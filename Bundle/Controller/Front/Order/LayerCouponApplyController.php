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

use App;
use Session;
use Request;
use Exception;
use Framework\Utility\ArrayUtils;
use Component\Cart\CartAdmin;

/**
 * 쿠폰 적용
 *
 * @author  su
 */
class LayerCouponApplyController extends \Controller\Front\Controller
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

            // 로그인 체크
            if(Session::has('member')) {
                $post = Request::post()->toArray();
                $cart = new CartAdmin(Session::get('member.memNo'), true);
                $coupon = App::load(\Component\Coupon\Coupon::class);

                // 장바구니의 해당 장바구니고유번호의 데이터
                $cartInfo = $cart->getCartGoodsData($post['cartSno']);
                $scmCartInfo = array_shift($cartInfo);
                $goodsCartInfo =  array_shift($scmCartInfo);
                $goodsPriceArr = [
                    'goodsCnt'=>$goodsCartInfo[0]['goodsCnt'],
                    'goodsPriceSum'=>$goodsCartInfo[0]['price']['goodsPriceSum'],
                    'optionPriceSum'=>$goodsCartInfo[0]['price']['optionPriceSum'],
                    'optionTextPriceSum'=>$goodsCartInfo[0]['price']['optionTextPriceSum'],
                    'addGoodsPriceSum'=>$goodsCartInfo[0]['price']['addGoodsPriceSum'],
                ];
                if($goodsCartInfo[0]['memberCouponNo']) {
                    // 장바구니에 사용된 회원쿠폰 리스트
                    $cartCouponNoArr = explode(INT_DIVISION,$goodsCartInfo[0]['memberCouponNo']);
                    foreach($cartCouponNoArr as $cartCouponKey => $cartCouponVal) {
                        if ($cartCouponVal) {
                            $cartCouponArrData[$cartCouponKey] = $coupon->getMemberCouponInfo($cartCouponVal);
                            $nowMemberCouponNoArr[] = $cartCouponArrData[$cartCouponKey]['couponNo'];
                        }
                    }
                    // 장바구니에 사용된 회원쿠폰 리스트를 보기용으로 변환
                    $convertCartCouponArrData = $coupon->convertCouponArrData($cartCouponArrData);
                    // 장바구니에 사용된 회원쿠폰의 정율도 정액으로 계산된 금액
                    $convertCartCouponPriceArrData = $coupon->getMemberCouponPrice($goodsPriceArr, $goodsCartInfo[0]['memberCouponNo']);
                    $this->setData('cartCouponArrData', $cartCouponArrData);
                    $this->setData('convertCartCouponArrData', $convertCartCouponArrData);
                    $this->setData('convertCartCouponPriceArrData', $convertCartCouponPriceArrData);
                }

                // 해당 상품의 사용가능한 회원쿠폰 리스트
                $memberCouponArrData = $coupon->getGoodsMemberCouponList($goodsCartInfo[0]['goodsNo'],Session::get('member.memNo'),Session::get('member.groupSno'),null,$nowMemberCouponNoArr, 'cart');
                if(is_array($memberCouponArrData)){
                    $memberCouponNoArr = array_column($memberCouponArrData,'memberCouponNo');
                    if ($memberCouponNoArr) {
                        $memberCouponNoString = implode(INT_DIVISION, $memberCouponNoArr);
                        // 해당 상품의 사용가능한 회원쿠폰 리스트를 보기용으로 변환
                        $convertMemberCouponArrData = $coupon->convertCouponArrData($memberCouponArrData);
                        // 해당 상품의 사용가능한 회원쿠폰의 정율도 정액으로 계산된 금액
                        $convertMemberCouponPriceArrData = $coupon->getMemberCouponPrice($goodsPriceArr, $memberCouponNoString);
                    }
                }

                // 장바구니에서 다른 상품에 이미 적용된 혜택금액을 가져온다
                unset($cart);
                $cart = new CartAdmin(Session::get('member.memNo'), true);
                $cartInfo = $cart->getCartGoodsData();
                foreach ($cartInfo as $key => $value) {
                    foreach ($value as $key1 => $value1) {
                        foreach ($value1 as $key2 => $value2) {
                            if ($value2['memberCouponNo']) {
                                $memberCouponApplyNo = explode(INT_DIVISION, $value2['memberCouponNo']);
                                foreach ($memberCouponNoArr as $memberCouponNo) {
                                    if (in_array($memberCouponNo, $memberCouponApplyNo)) {
                                        $cartUseMemberCouponPriceArrData['memberCouponSalePrice'][$memberCouponNo] = ($value2['coupon'][$memberCouponNo]['couponKindType'] == 'sale') ? $value2['coupon'][$memberCouponNo]['couponGoodsDcPrice'] : $value2['coupon'][$memberCouponNo]['couponGoodsMileage'];
                                    }
                                }
                            }
                        }
                    }
                }

                if ($cartUseMemberCouponPriceArrData) {
                    $this->setData('cartUseMemberCouponPriceArrData', $cartUseMemberCouponPriceArrData);
                }

                $this->setData('memberCouponArrData', $memberCouponArrData);
                $this->setData('convertMemberCouponArrData', $convertMemberCouponArrData);
                $this->setData('convertMemberCouponPriceArrData', $convertMemberCouponPriceArrData);
                $this->setData('goodsNo', $goodsCartInfo[0]['goodsNo']);
                $this->setData('memberCouponNo', $goodsCartInfo[0]['memberCouponNo']);
                $this->setData('cartSno', $post['cartSno']);
                $this->setData('action', $post['action']);
            } else {
                $this->js("alert('" . __('로그인하셔야 해당 서비스를 이용하실 수 있습니다.') . "'); top.location.href = '../member/login.php';");
            }
        } catch (Exception $e) {
            $this->json([
                'error' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
