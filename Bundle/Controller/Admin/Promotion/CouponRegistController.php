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

use Component\Database\DBTableField;
use Exception;
use Framework\Debug\Exception\LayerException;
use Request;

class CouponRegistController extends \Controller\Admin\Controller
{

    /**
     * 쿠폰 등록
     * [관리자 모드] 쿠폰 등록
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

        // --- 쿠폰 사용 설정 정보
        try {
            $couponData = array();

            // --- 모듈 호출
            $couponAdmin = \App::load(\Component\Coupon\CouponAdmin::class);
            // 쿠폰 리스트 페이지 번호
            $ypage = Request::get()->get('ypage');
            // 쿠폰 고유 번호
            $couponNo = Request::get()->get('couponNo');
            // 쿠폰 종류 - online 온라인 , offline 페이퍼
            $couponKind = Request::get()->get('couponKind');
            // 바코드 번호
            $barcodeNo = 0;
            // couponNo 가 없으면 디비 디폴트 값 설정
            if ($couponNo > 0) {
                $couponData = $couponAdmin->getCouponInfo($couponNo, '*');
                $couponData = $couponAdmin->getCouponApplyExceptData($couponData);
                if($couponData['couponImageType'] == 'self') {
                    $couponData['couponImage'] = $couponAdmin->getCouponImageData($couponData['couponImage']);
                } else {
                    $couponData['couponImage'] = '';
                }
                $couponData['mode'] = 'modifyCouponRegist';

                //수정일 경우, 바코드가 매칭되어 있는지 체크한다.
                $barcodeAdmin   = \App::load(\Component\Promotion\BarcodeAdmin::class);
                $barcodeInfo    = $barcodeAdmin->getBarcodeInfoByNo(0, $couponNo);
                if (empty($barcodeInfo[0]['barcodeNo']) === false) {
                    $barcodeNo = $barcodeInfo[0]['barcodeNo'];
                }

                // online 온라인 , offline 페이퍼
                if ($couponData['couponKind'] == 'online') {
                    $this->callMenu('promotion', 'coupon', 'couponModify');
                } else {
                    $this->callMenu('promotion', 'coupon', 'couponOfflineModify');
                }
            } else {
                DBTableField::setDefaultData('tableCoupon', $couponData);
                $couponData['mode'] = 'insertCouponRegist';
                $couponData['couponKind'] = $couponKind ? $couponKind : 'online';
                if(Request::get()->get('couponSaveType','') == 'auto'){
                    $couponData['couponSaveType'] = 'auto';
                }
                if(Request::get()->get('couponEventType','') == 'attend'){
                    $couponData['couponEventType'] = 'attend';
                } else if (Request::get()->get('couponEventType','') == 'memberModifyEvent') {
                    $couponData['couponEventType'] = 'memberModifyEvent';
                }
                // online 온라인 , offline 페이퍼
                if ($couponData['couponKind'] == 'online') {
                    $this->callMenu('promotion', 'coupon', 'couponRegist');
                } else {
                    $this->callMenu('promotion', 'coupon', 'couponOfflineRegist');
                }
            }
            gd_isset($couponData['couponLimitSmsFl'],'n');
            gd_isset($couponData['couponUseAblePaymentType'],'all');
            gd_isset($couponData['couponProductMinOrderType'],'product');

            $checked['couponSaveType'][$couponData['couponSaveType']] =
            $checked['couponUseType'][$couponData['couponUseType']] =
            $checked['couponUsePeriodType'][$couponData['couponUsePeriodType']] =
            $checked['couponKindType'][$couponData['couponKindType']] =
            $checked['couponDeviceType'][$couponData['couponDeviceType']] =
            $checked['couponBenefitFixApply'][$couponData['couponBenefitFixApply']] =
            $checked['couponMaxBenefitType'][$couponData['couponMaxBenefitType']] =
            $checked['couponDisplayMemberType'][$couponData['couponDisplayMemberType']] =
            $checked['couponDisplayType'][$couponData['couponDisplayType']] =
            $checked['couponImageType'][$couponData['couponImageType']] =
            $checked['couponLimitSmsFl'][$couponData['couponLimitSmsFl']] =
            $checked['couponUseAblePaymentType'][$couponData['couponUseAblePaymentType']] =
            $checked['couponAmountType'][$couponData['couponAmountType']] =
            $checked['couponSaveDuplicateType'][$couponData['couponSaveDuplicateType']] =
            $checked['couponSaveDuplicateLimitType'][$couponData['couponSaveDuplicateLimitType']] =
            $checked['couponApplyMemberGroupDisplayType'][$couponData['couponApplyMemberGroupDisplayType']] =
            $checked['couponApplyProductType'][$couponData['couponApplyProductType']] =
            $checked['couponExceptProviderType'][$couponData['couponExceptProviderType']] =
            $checked['couponExceptCategoryType'][$couponData['couponExceptCategoryType']] =
            $checked['couponExceptBrandType'][$couponData['couponExceptBrandType']] =
            $checked['couponExceptGoodsType'][$couponData['couponExceptGoodsType']] =
            $checked['couponApplyGoodsAmountType'][$couponData['couponApplyGoodsAmountType']] =
            $checked['couponApplyOrderPayType'][$couponData['couponApplyOrderPayType']] =
            $checked['couponApplyDuplicateType'][$couponData['couponApplyDuplicateType']] =
            $checked['couponAutoRecoverType'][$couponData['couponAutoRecoverType']] =
            $checked['couponEventType'][$couponData['couponEventType']] =
            $checked['couponEventOrderFirstType'][$couponData['couponEventOrderFirstType']] =
            $checked['couponEventOrderSmsType'][$couponData['couponEventOrderSmsType']] =
            $checked['couponEventFirstSmsType'][$couponData['couponEventFirstSmsType']] =
            $checked['couponEventBirthSmsType'][$couponData['couponEventBirthSmsType']] =
            $checked['couponEventMemberSmsType'][$couponData['couponEventMemberSmsType']] =
            $checked['couponEventAttendanceSmsType'][$couponData['couponEventAttendanceSmsType']] =
            $checked['couponProductMinOrderType'][$couponData['couponProductMinOrderType']] =
            $checked['couponEventMemberModifySmsType'][$couponData['couponEventMemberModifySmsType']] =
            $checked['couponEventWakeSmsType'][$couponData['couponEventWakeSmsType']] = 'checked="checked"';

            $selected['couponBenefitType'][$couponData['couponBenefitType']] =
            $selected['couponBenefitLimit'][$couponData['couponBenefitLimit']] =
            $selected['couponBenefitLimitType'][$couponData['couponBenefitLimitType']] = 'selected="selected"';

        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        // --- 메뉴 설정
        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);
        $this->setData('couponData', gd_isset($couponData));
        $this->setData('checked', gd_isset($checked));
        $this->setData('selected', gd_isset($selected));
        $this->setData('ypage', gd_isset($ypage,1));
        $this->setData('callback', Request::get()->get('callback', ''));
        $this->setData('barcodeNo', gd_isset($barcodeNo, 0));

        // 상품쿠폰 주문서페이지 사용여부 패치 SRC 버전체크
        $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
        $productCouponChangeLimitVersionFl = $couponAdmin->productCouponChangeLimitVersionFl; // true 노출, false 미노출
        $this->setData('productCouponChangeLimitVersionFl', $productCouponChangeLimitVersionFl);
    }
}
