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

namespace Bundle\Controller\Front\Member;

use App;
use Component\Member\Member;
use Component\Member\MemberSleep;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Request;
use Session;

/**
 * Class WakeController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class WakeCompleteController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        \Logger::info(__METHOD__);
        try {
            $wakeInfo = Session::get(MemberSleep::SESSION_WAKE_INFO);

            /** @var \Bundle\Component\Member\Member $member */
            $member = App::load('\\Component\\Member\\Member');
            $memberSns = App::load('\\Component\\Member\\MemberSnsService');
            $request = \App::getInstance('request');
            $request->get()->set('couponEventType', 'wake');
            $coupon = \App::load('\\Component\\Coupon\\Coupon');
            $wakeCoupon = $coupon->getMemberCouponList($wakeInfo['memNo']);
            $this->setData("wakeCoupon", $wakeCoupon['data']);
            $memberSnsData = $memberSns->getMemberSns($wakeInfo['memNo']);
            $loginFl = 'n';
            if ($memberSnsData['snsJoinFl'] != 'y') {
                if($wakeInfo['memPw']) { // 로그인 이후 휴면회원 해제할 경우에만 로그인 처리
                    $member->login($wakeInfo['memId'], $wakeInfo['memPw']);
                    $loginFl = 'y';
                }
            } else {
                $memberSns->loginBySns($memberSnsData['uuid']);
            }
            $this->setData("loginFl", $loginFl);
        } catch (Exception $e) {
            throw new AlertRedirectException($e->getMessage(), 0, null, '../member/login.php');
        } finally {
            Session::del(MemberSleep::SESSION_WAKE_INFO);
            Session::del(Member::SESSION_DREAM_SECURITY);
            Session::del(Member::SESSION_IPIN);
        }
    }
}
