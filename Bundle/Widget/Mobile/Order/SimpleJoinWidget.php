<?php
/**
 * *
 *  * This is commercial software, only users who have purchased a valid license
 *  * and accept to the terms of the License Agreement can install and use this
 *  * program.
 *  *
 *  * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 *  * versions in the future.
 *  *
 *  * @copyright ⓒ 2016, NHN godo: Corp.
 *  * @link http://www.godo.co.kr
 *
 */

namespace Bundle\Widget\Mobile\Order;

use Component\Agreement\BuyerInformCode;
use Component\Coupon\Coupon;
use Session;
use UserFilePath;
/**
 * Class SimpleJoinWidget
 * @package Bundle\Widget\Mobile\Order
 * @author  mj2
 */
class SimpleJoinWidget extends \Widget\Mobile\Widget
{
    /**
     * @inheritdoc
     */
    public function index()
    {

        // 쿠폰 설정값 정보
        $couponConfig = gd_policy('coupon.config');
        $this->setData('couponUse', gd_isset($couponConfig['couponUseType'], 'n')); // 쿠폰 사용여부
        $this->setData('couponConfig', gd_isset($couponConfig)); // 쿠폰설정
        $this->setData('productCouponChangeLimitType', $couponConfig['productCouponChangeLimitType']); // 상품쿠폰 주문서 제한여부


        $joinEventOrder = gd_policy('member.joinEventOrder');
        gd_isset($joinEventOrder['useFl'], 'n');
        if($joinEventOrder['useFl'] == 'y' && gd_is_plus_shop(PLUSSHOP_CODE_SIMPLEJOIN) === true) {
            if ($joinEventOrder['bannerImageType'] == 'self') {
                $joinEventOrder['joinEventOrderImage'] = UserFilePath::data('join_event')->www() . '/' . $joinEventOrder['bannerImageMobile'];
            }
            gd_isset($couponConfig['couponUseType'], 'n');
            if ($couponConfig['couponUseType'] == 'n') {
                $joinEventOrder['title'] = '주문정보로 간단하게 가입하기!';
            } else {
                $coupon = new Coupon();
                $joinEventOrderCoupon = $coupon->getJoinEventCouponList();
                $joinEventOrderCoupon = $coupon->convertCouponArrData($joinEventOrderCoupon);
                if (count($joinEventOrderCoupon) == 0) {
                    $joinEventOrder['title'] = '주문정보로 간단하게 가입하기!';
                } else if (count($joinEventOrderCoupon) == 1) {
                    $joinEventOrder['title'] = '주문정보로 간단 가입 시 [' . $joinEventOrderCoupon[0]['couponBenefit'] . ' ' . $joinEventOrderCoupon[0]['couponKindType'] . '] 쿠폰 지급!';
                } else {
                    $joinEventOrder['title'] = '주문정보로 간단 가입 시 [' . $joinEventOrderCoupon[0]['couponBenefit'] . ' ' . $joinEventOrderCoupon[0]['couponKindType'] . ' 외 ' . (count($joinEventOrderCoupon) - 1) . '장] 쿠폰 지급!';
                }
            }

            /** @var  \Bundle\Component\Agreement\BuyerInform $inform */
            $inform = \App::load('\\Component\\Agreement\\BuyerInform');
            // 개인정보 수집 및 이용
            $privateApproval = $inform->getInformData(BuyerInformCode::PRIVATE_APPROVAL);
            $this->setData('privateApproval', $privateApproval);
            // 이용약관
            $agreementInfo = $inform->getAgreementWithReplaceCode(BuyerInformCode::AGREEMENT);
            $this->setData('agreementInfo', $agreementInfo);
        }
        $this->setData('joinEventOrder', $joinEventOrder);
        $this->setData('simpleJoin', Session::get('simpleJoin'));
        Session::del('simpleJoin');
    }
}
