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
namespace Bundle\Controller\Admin\Promotion;

use Bundle\Component\Coupon\CouponAdmin;
use Framework\Debug\Exception\LayerException;
use Framework\Utility\GodoUtils;

class CouponConfigController extends \Controller\Admin\Controller
{

    /**
     * 쿠폰 설정 및 생성
     * [관리자 모드] 쿠폰 설정 및 생성
     *
     * @author su
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     */
    public function index()
    {

        // --- 메뉴 설정
        $this->callMenu('promotion', 'coupon', 'couponConfig');

        try {
            // --- 설정값 정보
            $couponConfig = gd_policy('coupon.config');
            if ($couponConfig) {
                $mode = 'modifyCouponConfig';
            } else {
                $mode = 'insertCouponConfig';
            }
            // --- 기본값 설정
            gd_isset($couponConfig['couponUseType'],'y');
            gd_isset($couponConfig['chooseCouponMemberUseType'],'all');
            gd_isset($couponConfig['couponDisplayType'],'all');
            gd_isset($couponConfig['couponOptPriceType']);
            gd_isset($couponConfig['couponAddPriceType']);
            gd_isset($couponConfig['couponTextPriceType']);
//            gd_isset($couponConfig['couponApplyDuplicateType'],'no');
            gd_isset($couponConfig['couponAutoRecoverType'],'y');
            gd_isset($couponConfig['couponOfflineDisplayType'],'n');
            gd_isset($couponConfig['productCouponChangeLimitType'],'y');
            gd_isset($couponConfig['birthdayCouponReserveType'],'days');
            gd_isset($couponConfig['birthdayCouponReserveDays'],0);
            gd_isset($couponConfig['couponBarcodeDisplayType'], 'n');

            $checked['couponUseType'][$couponConfig['couponUseType']] =
            $checked['chooseCouponMemberUseType'][$couponConfig['chooseCouponMemberUseType']] =
            $checked['couponDisplayType'][$couponConfig['couponDisplayType']] =
            $checked['couponOptPriceType'][$couponConfig['couponOptPriceType']] =
            $checked['couponAddPriceType'][$couponConfig['couponAddPriceType']] =
            $checked['couponTextPriceType'][$couponConfig['couponTextPriceType']] =
//            $checked['couponApplyDuplicateType'][$couponConfig['couponApplyDuplicateType']] =
            $checked['couponAutoRecoverType'][$couponConfig['couponAutoRecoverType']] =
            $checked['productCouponChangeLimitType'][$couponConfig['productCouponChangeLimitType']] =
            $checked['couponOfflineDisplayType'][$couponConfig['couponOfflineDisplayType']] =
            $checked['birthdayCouponReserveType'][$couponConfig['birthdayCouponReserveType']] = 'checked="checked"';
            $offlineCouponUse = GodoUtils::isPlusShop(PLUSSHOP_CODE_COUPONOFFLINE);
        } catch (\Exception $e) {
            throw new LayerException($e->getMessage());
        }

        $this->setData('mode', $mode);
        $this->setData('checked', $checked);
        $this->setData('offlineCouponUse', $offlineCouponUse);
        $this->setData('birthdayCouponReserveDaysList', CouponAdmin::BIRTHDAY_COUPON_RESERVE_DAYS_LIST);
        $this->setData('birthdayCouponReserveMonthList', CouponAdmin::BIRTHDAY_COUPON_RESERVE_MONTH_LIST);
        $this->setData('couponConfig', $couponConfig);

        // 상품쿠폰 주문서페이지 사용여부 패치 SRC 버전체크
        $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
        $productCouponChangeLimitVersionFl = $couponAdmin->productCouponChangeLimitVersionFl; // true 노출, false 미노출
        $this->setData('productCouponChangeLimitVersionFl', $productCouponChangeLimitVersionFl);
    }
}
