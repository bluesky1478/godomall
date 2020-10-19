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

class CouponOfflineRegistController extends \Controller\Admin\Controller
{

    /**
     * 오프라인 쿠폰 등록
     * [관리자 모드] 오프라인 쿠폰 등록
     * @throws Except
     *
     * @author    su
     */

    public function index()
    {
        // --- 쿠폰 사용 설정 정보
        try {
            $couponData = [];

            // --- 모듈 호출
            $couponAdmin = \App::load(\Component\Coupon\CouponAdmin::class);

            // 쿠폰 리스트 페이지 번호
            $ypage = Request::get()->get('ypage');
            // 쿠폰 고유 번호
            $couponNo = Request::get()->get('couponNo');
            // 바코드 번호
            $barcodeNo = 0;
            // couponNo 가 없으면 디비 디폴트 값 설정
            if ($couponNo > 0) {
                $couponData = $couponAdmin->getCouponInfo($couponNo, '*');
                $couponData = $couponAdmin->getCouponApplyExceptData($couponData);
                if ($couponData['couponImageType'] == 'self') {
                    $couponData['couponImage'] = $couponAdmin->getCouponImageData($couponData['couponImage']);
                } else {
                    $couponData['couponImage'] = '';
                }
                if ($couponData['couponAuthType'] == 'n') {
                    $couponAuthNumber = $couponAdmin->getCouponOfflineInfo($couponNo, 'couponOfflineCodeUser');
                    $couponData['couponAuthNumber'] = $couponAuthNumber['couponOfflineCodeUser'];
                }
                $couponData['mode'] = 'modifyCouponRegist';

                //수정일 경우, 바코드가 매칭되어 있는지 체크한다.
                $barcodeAdmin   = \App::load(\Component\Promotion\BarcodeAdmin::class);
                $barcodeInfo    = $barcodeAdmin->getBarcodeInfoByNo(0, $couponNo);
                if (empty($barcodeInfo[0]['barcodeNo']) === false) {
                    $barcodeNo = $barcodeInfo[0]['barcodeNo'];
                }

            } else {
                DBTableField::setDefaultData('tableCoupon', $couponData);
                $couponData['mode'] = 'insertCouponRegist';
                $couponData['couponKind'] = 'offline';
            }
            $layer = Request::get()->get('layer');
            if($layer == 'authCode') {
                $this->setData('authCode', $layer);
            }

            gd_isset($couponData['couponUseAblePaymentType'],'all');
            gd_isset($couponData['couponProductMinOrderType'],'product');

            $checked['couponUseType'][$couponData['couponUseType']] =
            $checked['couponUsePeriodType'][$couponData['couponUsePeriodType']] =
            $checked['couponKindType'][$couponData['couponKindType']] =
            $checked['couponDeviceType'][$couponData['couponDeviceType']] =
            $checked['couponMaxBenefitType'][$couponData['couponMaxBenefitType']] =
            $checked['couponImageType'][$couponData['couponImageType']] =
            $checked['couponUseAblePaymentType'][$couponData['couponUseAblePaymentType']] =
            $checked['couponAuthType'][$couponData['couponAuthType']] =
            $checked['couponCreativeType'][$couponData['couponCreativeType']] =
            $checked['couponApplyProductType'][$couponData['couponApplyProductType']] =
            $checked['couponExceptProviderType'][$couponData['couponExceptProviderType']] =
            $checked['couponExceptCategoryType'][$couponData['couponExceptCategoryType']] =
            $checked['couponExceptBrandType'][$couponData['couponExceptBrandType']] =
            $checked['couponExceptGoodsType'][$couponData['couponExceptGoodsType']] =
            $checked['couponSaveDuplicateType'][$couponData['couponSaveDuplicateType']] =
            $checked['couponSaveDuplicateLimitType'][$couponData['couponSaveDuplicateLimitType']] =
            $checked['couponProductMinOrderType'][$couponData['couponProductMinOrderType']] =
            $checked['couponApplyDuplicateType'][$couponData['couponApplyDuplicateType']] = 'checked="checked"';

            $selected['couponBenefitType'][$couponData['couponBenefitType']] =
            $selected['couponBenefitLimit'][$couponData['couponBenefitLimit']] =
            $selected['couponBenefitLimitType'][$couponData['couponBenefitLimitType']] = 'selected="selected"';

        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        if ((Request::get()->get('couponNo'))) {
            $this->callMenu('promotion', 'coupon', 'couponOfflineModify');
        } else {
            $this->callMenu('promotion', 'coupon', 'couponOfflineRegist');
        }

        $this->addScript([
            'jquery/jquery.multi_select_box.js',
            'excelexport/jquery.table2excel.min.js',
        ]);
        $this->setData('couponData', gd_isset($couponData));
        $this->setData('checked', gd_isset($checked));
        $this->setData('selected', gd_isset($selected));
        $this->setData('ypage', gd_isset($ypage, 1));
        $this->setData('barcodeNo', gd_isset($barcodeNo, 0));

        // 상품쿠폰 주문서페이지 사용여부 패치 SRC 버전체크
        $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
        $productCouponChangeLimitVersionFl = $couponAdmin->productCouponChangeLimitVersionFl; // true 노출, false 미노출
        $this->setData('productCouponChangeLimitVersionFl', $productCouponChangeLimitVersionFl);
    }
}
