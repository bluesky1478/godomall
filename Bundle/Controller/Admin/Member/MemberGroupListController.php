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

namespace Bundle\Controller\Admin\Member;

use App;
use Component\Member\Group\Util as GroupUtil;
use Component\Coupon\CouponAdmin;

/**
 * Class MemberGroupListController 회원등급 리스트
 * @package Controller\Admin\Member
 * @author  yjwee
 */
class MemberGroupListController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */

        $this->callMenu('member', 'member', 'group');

        /** @var \Bundle\Component\Member\MemberGroup $memberGroup */
        $memberGroup = App::load('\\Component\\Member\\MemberGroup');

        $getData = $memberGroup->getGroupList();
        $groups = GroupUtil::getGroupName();

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
            $getData['data'][$key]['coupon'] = implode(', ', $couponInfo[$key]);
        }

        // 등급명칭/가입등급설정
        $groupData = gd_policy('member.group');
        $groupData = gd_htmlspecialchars_stripslashes($groupData);

        $checked['couponConditionComplete'] = $groupData['couponConditionComplete'] == 'y' ? 'checked' : '';
        $checked['couponConditionCompleteChange'] = $groupData['couponConditionCompleteChange'] == 'y' ? 'checked' : '';
        $checked['couponConditionManual'] = $groupData['couponConditionManual'] == 'y' ? 'checked' : '';
        $checked['couponConditionExcel'] = $groupData['couponConditionExcel'] == 'y' ? 'checked' : '';
        $checked['couponConditionExcelChange'] = $groupData['couponConditionExcelChange'] == 'y' ? 'checked' : '';

        $this->setData('memberGroup', gd_isset($memberGroup));
        $this->setData('data', gd_isset($getData['data']));
        $this->setData('checked', $checked);
        $this->setData('groups', gd_isset($groups));
        $this->setData('groupData', gd_isset($groupData));
        $this->setData('settleGbs', GroupUtil::getSettleGbStringData());

        /** set view data */
        $this->addScript(['member.js']);

    }
}
