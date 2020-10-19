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
namespace Bundle\Widget\Front\Mypage;

use Component\Member\Util\MemberUtil;
use Framework\Utility\ArrayUtils;
use Session;

/**
 *
 * @author Lee Seungjoo <slowj@godo.co.kr>
 */
class MemberSummaryWidget extends \Widget\Front\Widget
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        // 쿠폰설정
        $coupon = \App::load(\Component\Coupon\Coupon::class);
        $memberCouponCount = $coupon->getMemberCouponUsableCount(null, Session::get('member.memNo'));
        $this->setData('myCouponCount', $memberCouponCount);

        $this->setData('checkLogin', MemberUtil::checkLogin());
    }
}
