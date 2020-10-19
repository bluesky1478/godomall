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
use Component\Database\DBTableField;
use Component\Page\Page;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Session;

/**
 * Class CouponController
 *
 * @package Bundle\Controller\Front\Mypage
 * @author  su <surlira@godo.co.kr>
 */
class CouponController extends \Controller\Mobile\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // 로그인 체크
            if(Session::has('member')) {
                $locale = \Globals::get('gGlobal.locale');
                $this->addScript([
                    'moment/moment.js',
                    'moment/locale/' . $locale . '.js',
                ]);
                $coupon = new Coupon();
                // 모바일은 모바일에서 사용할 수 있는 쿠폰만 노출
                $getData = $coupon->getMemberCouponList(Session::get('member.memNo'), 'mobile');
                $getConvertArrData = $coupon->convertCouponArrData($getData['data']);
                $getData['data'] = $coupon->getCouponApplyExceptArrData($getData['data']);
                $getData['data'] = $coupon->getMemberCouponUsableDisplay($getData['data']);
            } else {
                throw new AlertBackException(__('로그인하셔야 해당 서비스를 이용하실 수 있습니다.'));
            }
        } catch(\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        foreach ($getConvertArrData as $index => $convertData) {
            $getConvertArrData[$index]['couponMaxBenefit'] = str_replace('최대 할인액 :  ', '최대 ', $getConvertArrData[$index]['couponMaxBenefit']);
            if ($getConvertArrData[$index]['couponKindType'] == '마일리지적립') {
                $getConvertArrData[$index]['couponMaxBenefit'] = str_replace(' 원', '', $getConvertArrData[$index]['couponMaxBenefit']);
            }
        }

        $couponConfig = gd_policy('coupon.config');
        $depositConfig = \Globals::get('gSite.member.depositConfig');
        $mileageConfig = \Globals::get('gSite.member.mileageBasic');
        $this->setData('mileageConfig', gd_isset($mileageConfig, array('name' => '마일리지', 'unit' => '마일')));
        $this->setData('depositConfig', gd_isset($depositConfig, array('name' => '예치금', 'unit' => '원')));
        $this->setData('data', gd_isset($getData['data']));
        $this->setData('convertArrData', gd_isset($getConvertArrData));
        $this->setData('selected', $getData['selected']);
    }
}
