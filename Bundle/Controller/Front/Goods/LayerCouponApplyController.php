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
use Component\Cart\CartAdmin;

/**
 * Class LayerDeliveryAddress
 *
 * @package Bundle\Controller\Front\Order
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
                $coupon = App::load(\Component\Coupon\Coupon::class);
                $post = Request::post()->toArray();

                $goodsPriceArr = [
                    'goodsCnt'=>$post['goodsCnt'],
                    'goodsPriceSum'=>$post['goodsPriceSum'],
                    'optionPriceSum'=>$post['optionPriceSum'],
                    'optionTextPriceSum'=>$post['optionTextPriceSum'],
                    'addGoodsPriceSum'=>$post['addGoodsPriceSum'],
                ];

                // 상품상세에서 다른 옵션에 적용된 쿠폰 설정
                if($post['couponApplyNotNo']) {
                    $exceptMemberCouponNoArr = array_column($post['couponApplyNotNo'],'value');
                    $exceptMemberCouponNoString = implode(INT_DIVISION,$exceptMemberCouponNoArr);
                    $exceptMemberCouponNoArr = explode(INT_DIVISION,$exceptMemberCouponNoString);
                    $exceptMemberCouponNoArr = ArrayUtils::removeEmpty($exceptMemberCouponNoArr);
                }
                if($post['couponApplyNo']) {
                    // 이번 옵션에 사용된 쿠폰
                    $nowMemberCouponNoArr = explode(INT_DIVISION,$post['couponApplyNo']);
                    // 사용된 쿠폰 배열에서 이번 옵션에 사용된 쿠폰 회원쿠폰고유번호는 제거하여 변경 시 노출되도록 함
                    $removeMemberCouponNoArr = [];
                    foreach($nowMemberCouponNoArr as $val) {
                        $removeMemberCouponNoArr[] = array_search($val,$exceptMemberCouponNoArr);
                    }
                    // 조건 필터링
                    foreach ($removeMemberCouponNoArr as $val) {
                        unset($exceptMemberCouponNoArr[$val]);
                    }
                }
                // 해당 상품의 사용가능한 회원쿠폰 리스트
                $memberCouponArrData = $coupon->getGoodsMemberCouponList($post['goodsNo'],Session::get('member.memNo'),Session::get('member.groupSno'),$exceptMemberCouponNoArr,$nowMemberCouponNoArr);
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
                // 장바구니의 해당 장바구니고유번호의 데이터
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
                $this->setData('goodsNo', $post['goodsNo']);
                $this->setData('couponApplyNo', $post['couponApplyNo']);
                $this->setData('exceptMemberCouponNoArr', $exceptMemberCouponNoArr);
                $this->setData('nowMemberCouponNoArr', $nowMemberCouponNoArr);
                $this->setData('optionKey', $post['optionKey']);
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
