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
namespace Bundle\Controller\Front\Mypage;

use Component\Coupon\Coupon;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertReloadException;
use App;
use Session;
use Request;

/**
 * Class CouponPsController
 * @package Bundle\Controller\Front\Mypage
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class CouponPsController extends \Controller\Front\Controller
{
    /**
     * {@inheritDoc}
     */
    public function index()
    {
        try {
            $coupon = new Coupon();

            switch (Request::post()->get('mode')) {
                case 'couponOfflineRegist':
                    $couponOfflineNumber = Request::post()->get('couponOfflineNumber');
                    $coupon->setOfflineCouponMemberSave($couponOfflineNumber, Session::get('member.memNo'), Session::get('member.groupSno'));
                    throw new AlertReloadException(__('쿠폰이 정상 발급되었습니다.'), null, null, 'parent');
                    break;
                case 'couponGiftUse':
                    $memberCouponNo = Request::post()->get('cno');
                    if (empty($memberCouponNo)) {
                        echo json_encode(array('result' => 'F', 'msg' => '잘못된 쿠폰번호입니다. 다시 시도해주세요.'));
                    } else {
                        $aResult = $coupon->useGiftCoupon($memberCouponNo, Session::get('member.memNo'));
                        echo json_encode($aResult);
                    }
                    break;
                case 'couponDown' :
                case 'couponDownAll' :
                    try {
                        $result = $coupon->setCouponzoneDown(Request::post()->get('couponNo'), Session::get('member.memNo'), Session::get('member.groupSno'));

                        if ($result['success'] == 0) {
                            $message = __('발급 가능 쿠폰이 없습니다.');
                        } else {
                            if(Request::post()->get('mode') == 'couponDown') {
                                $message = __('쿠폰이 발급되었습니다.');
                            } else {
                                $message = __('총 %s개 중 %s개 쿠폰이 발급되었습니다.', Request::post()->get('total'), $result['success']);
                            }
                        }

                        $this->json([
                            'code' => 200,
                            'message' => $message,
                        ]);
                    } catch (Exception $e) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    }
                    break;
            }
        } catch (AlertReloadException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new AlertOnlyException($e->getMessage()); //새로고침안됨
        }
        exit;
    }
}
