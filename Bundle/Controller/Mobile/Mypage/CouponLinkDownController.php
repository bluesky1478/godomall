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
namespace Bundle\Controller\Mobile\Mypage;

use Component\Coupon\Coupon;
use Session;
use Request;

/**
 * Class CouponLinkDownController
 * @package Bundle\Controller\Mobile\Mypage
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class CouponLinkDownController extends \Controller\Mobile\Controller
{
    /**
     * {@inheritDoc}
     */
    public function index()
    {
        try {
            $coupon = new Coupon();

            switch (Request::post()->get('mode')) {
                case 'couponDownLink':
                    $couponNo = Request::post()->get('couponCode');
                    if (Session::get('member.memNo') > 0) {
                        $coupon->setCouponLinkDown($couponNo, Session::get('member.memNo'), Session::get('member.groupSno'));
                        $result['code'] = 1;
                        $result['msg'] = __('쿠폰이 발급되었습니다.');
                        echo json_encode($result);
                    } else {
                        $result['code'] = 0;
                        $result['msg'] = __('로그인 하셔야 합니다.');
                        echo json_encode($result);
                    }
                    break;
            }
        } catch (\Exception $e) {
            $result['code'] = 9;
            $result['msg'] = $e->getMessage();
            echo json_encode($result);
        }
        exit;
    }
}
