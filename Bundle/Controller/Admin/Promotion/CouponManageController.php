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

use Component\Member\Group\Util as GroupUtil;
use Component\Database\DBTableField;
use Exception;
use Framework\Debug\Exception\LayerException;
use Request;

class CouponManageController extends \Controller\Admin\Controller
{

    /**
     * 쿠폰 관리
     * [관리자 모드] 쿠폰 관리
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
            // --- 모듈 호출
            $couponAdmin = \App::load(\Component\Coupon\CouponAdmin::class);
            // 쿠폰 고유 번호
            $couponNo = Request::get()->get('couponNo');
            // couponNo 가 없으면 디비 디폴트 값 설정
            if ($couponNo > 0) {
                $getData = $couponAdmin->getCouponInfo($couponNo, 'couponNo, couponNm, couponDescribed, couponUseType, couponSaveType, couponUsePeriodType, couponUsePeriodStartDate, couponUsePeriodEndDate, couponUsePeriodDay, couponUseDateLimit, couponBenefit, couponBenefitType, couponBenefitFixApply, couponKindType');
                $getConvertData = $couponAdmin->convertCouponData($getData);
                $getMemberData = $couponAdmin->getMemberCouponAdminList();
                $getGroupData = GroupUtil::getGroupName();
                $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
            } else {
                // 쿠폰 정보가 없다면
                throw new LayerException(__('쿠폰 정보가 없습니다.'));
            }
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        // --- 메뉴 설정
        $this->callMenu('promotion', 'coupon', 'couponManage');

        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);
        $this->setData('getData', gd_isset($getData));
        $this->setData('getConvertData', gd_isset($getConvertData));
        $this->setData('getGroupData', gd_isset($getGroupData));
        $this->setData('getMemberData', gd_isset($getMemberData['data']));
        $this->setData('getMemberBarcode', gd_isset($getMemberData['barcode']));
        $this->setData('search', gd_isset($getMemberData['search']));
        $this->setData('page', $page);
    }
}
