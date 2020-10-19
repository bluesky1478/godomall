<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Mobile\Mypage;

use Request;
use Session;
use Framework\Debug\Exception\AlertBackException;
use Component\Coupon\Coupon;

class LayerCouponDetailController extends \Controller\Mobile\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            // 로그인 체크
            if (Session::has('member')) {
                $getValue = Request::post()->toArray();
                $memberCouponNo = $getValue['memberCouponNo'];
                $coupon = new Coupon();

                $getData = $coupon->getMemberCouponList(Session::get('member.memNo'), 'mobile');
                $getConvertArrData = $coupon->convertCouponArrData($getData['data']);
                $getData['data'] = $coupon->getCouponApplyExceptArrData($getData['data']);
                $getData['data'] = $coupon->getMemberCouponUsableDisplay($getData['data']);

                foreach ($getData['data'] as $index => $couponInfo) {
                    if ($couponInfo['memberCouponNo'] == $memberCouponNo) {
                        $getDetailData = $getData['data'][$index];
                    }
                }

                foreach ($getConvertArrData as $index => $convertData) {
                    if ($convertData['couponNo'] == $getDetailData['couponNo']) {
                        $getDetailConvertArrData = $getConvertArrData[$index];
                    }
                }

                $this->setData('data', gd_isset($getDetailData));
                $this->setData('convertArrData', gd_isset($getDetailConvertArrData));
            } else {
                throw new AlertBackException(__('로그인하셔야 해당 서비스를 이용하실 수 있습니다.'));
            }
        } catch(\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}