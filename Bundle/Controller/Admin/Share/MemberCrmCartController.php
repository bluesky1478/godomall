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
namespace Bundle\Controller\Admin\Share;

use Component\Validator\Validator;
use Exception;
use Component\Cart\CartAdmin;
use Component\Wish\Wish;
use Framework\StaticProxy\Proxy\Session;
use Request;


/**
 * Class 관리자-CRM 장바구니/관심상품
 *
 * @package Bundle\Controller\Admin\Share
 * @author  Min-Ji Lee <mj2@godo.co.kr>
 * @see     \Core\Base\Interceptor\AdminLayout
 */
class MemberCrmCartController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {

            $memberData = $this->getData('memberData');
            $memberData['memberFl'] = $memberData['memberFl'] == 'business' ? __('사업자회원') : __('개인회원');
            $this->setData('memberData', gd_set_default_value($memberData, '-'));

            // 관리자 정보
            $this->setData('managerData', Session::get('manager'));

            $getValue = Request::get()->toArray();

            // 장바구니 정보
            $cart = new CartAdmin($getValue['memNo'], true);
            $cartCnt = $cart->getCartGoodsCnt();

            // 관심상품 정보
            $wish = new Wish();
            $wishCnt = $wish->getWishGoodsCnt($getValue['memNo']);

            if($getValue['navSubTabs'] == 'cart') {
                $cartInfo = $cart->getCartGoodsData();
                // 쿠폰 설정값 정보
                $couponConfig = gd_policy('coupon.config');
                $this->setData('couponUse', gd_isset($couponConfig['couponUseType'], 'n')); // 쿠폰 사용여부
                $this->setData('couponConfig', gd_isset($couponConfig)); // 쿠폰설정

                // 마일리지 지급 정보
                $this->setData('mileage', $cart->mileageGiveInfo['info']);

                $this->setData('list', $cartInfo);
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
                $this->setData('cnt', $cartCnt);
                $this->setData('colspan', 7);
            } else {
                // 관심상품 정보
                $wishInfo	= $wish->getWishGoodsData($memberData);
                $this->setData('cartScmInfo', $wish->wishScmInfo); // 장바구니 SCM 정보
                $this->setData('cartScmCnt', $wish->wishScmCnt); // 장바구니 SCM 상품 갯수
                $this->setData('list', $wishInfo);
                $this->setData('cnt', $wishCnt);
                $this->setData('colspan', 6);
                // 마일리지 지급 정보
                $mileage = gd_mileage_give_info();
                $mileage['useFl'] = $mileage['info']['useFl'];
                $this->setData('mileage', $mileage);
            }

            $this->setData('cartCnt', $cartCnt);
            $this->setData('wishCnt', $wishCnt);
            $this->setData('memNo', $getValue['memNo']); // 회원번호

            $this->getView()->setPageName('share/member_crm_cart.php');
        } catch (Exception $e) {
            throw $e;
        }
    }
}
