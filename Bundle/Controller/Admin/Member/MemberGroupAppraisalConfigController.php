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
 * @link      http://www.godo.co.kr
 */
namespace Bundle\Controller\Admin\Member;

use Component\Member\MemberGroup;
use Exception;
use Component\Coupon\CouponAdmin;

class MemberGroupAppraisalConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('member', 'member', 'groupAppraisalConfig');
        try {
            $memberGroupService = new MemberGroup();
            $getData = $memberGroupService->getGroupList();

            // 등급명칭/가입등급설정
            $groupData = gd_policy('member.group');
            $groupData = gd_htmlspecialchars_stripslashes($groupData);

            //쿠폰 관련 데이터 가져오기
            $coupon = new CouponAdmin();
            foreach($getData['data'] as $key => $value){
                $couponList = explode(INT_DIVISION, $value['groupCoupon']);
                if(!empty($couponList[0])) {
                    foreach ($couponList as $couponCode) {
                        $tmp = $coupon->getCouponInfo($couponCode);
                        $couponInfo[$key][] = $tmp['couponNm'];
                    }
                }
            }

            foreach($getData['data'] as $key => $data){
                $getData['data'][$key]['coupon'] = implode('<br />', $couponInfo[$key]);
            }

            gd_isset($groupData['downwardAdjustment'], 'y');
            gd_isset($groupData['automaticFl'], 'n');
            gd_isset($groupData['apprSystem'], 'figure');
            gd_isset($groupData['calcPeriodFl'], 'n');
            gd_isset($groupData['calcPeriodBegin'], '');
            gd_isset($groupData['calcPeriodMonth'], '');
            gd_isset($groupData['calcCycleMonth'], '');
            gd_isset($groupData['calcCycleDay'], '');
            gd_isset($groupData['calcKeep'], '');

            $checked['appraisalPointOrderPriceFl'][$groupData['appraisalPointOrderPriceFl']] =
            $checked['appraisalPointOrderRepeatFl'][$groupData['appraisalPointOrderRepeatFl']] =
            $checked['appraisalPointReviewRepeatFl'][$groupData['appraisalPointReviewRepeatFl']] =
            $checked['appraisalPointLoginRepeatFl'][$groupData['appraisalPointLoginRepeatFl']] =
            $checked['automaticFl'][$groupData['automaticFl']] =
            $checked['apprSystem'][$groupData['apprSystem']] =
            $checked['downwardAdjustment'][$groupData['downwardAdjustment']] =
            $checked['calcPeriodFl'][$groupData['calcPeriodFl']] = 'checked="checked"';
            $selected['calcPeriodBegin'][$groupData['calcPeriodBegin']] = $selected['calcPeriodMonth'][$groupData['calcPeriodMonth']] = $selected['calcCycleMonth'][$groupData['calcCycleMonth']] = $selected['calcCycleDay'][$groupData['calcCycleDay']] = $selected['calcKeep'][$groupData['calcKeep']] = 'selected="selected"';

            $this->setData('data', gd_isset($getData['data']));
            $this->setData('cnt', gd_isset($getData['cnt']));
            $this->setData('groupData', gd_isset($groupData));
            $this->setData('checked', $checked);
            $this->setData('selected', $selected);

        } catch (Exception $e) {
            throw $e;
        }
    }
}
