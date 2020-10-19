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
use Component\Page\Page;
use Component\Database\DBTableField;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ArrayUtils;
use Cookie;
use Request;
use Exception;
use Session;

/**
 * Class CouponController
 *
 * @package Bundle\Controller\Front\Mypage
 * @author  su <surlira@godo.co.kr>
 */
class CouponController extends \Controller\Front\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            if(Session::has(SESSION_GLOBAL_MALL)){
                throw new AlertBackException(__('잘못된 접근입니다.'));
            }
            // 로그인 체크
            if(Session::has('member')) {
                $locale = \Globals::get('gGlobal.locale');
                // 날짜 픽커를 위한 스크립트와 스타일 호출
                $this->addCss([
                    'plugins/bootstrap-datetimepicker.min.css',
                    'plugins/bootstrap-datetimepicker-standalone.css',
                ]);
                $this->addScript([
                    'moment/moment.js',
                    'moment/locale/' . $locale . '.js',
                    'jquery/datetimepicker/bootstrap-datetimepicker.min.js',
                ]);
                $coupon = new Coupon();
                $getData = $coupon->getMemberCouponList(Session::get('member.memNo'));
                $getConvertArrData = $coupon->convertCouponArrData($getData['data']);
                $getData['data'] = $coupon->getCouponApplyExceptArrData($getData['data']);
                $getData['data'] = $coupon->getMemberCouponUsableDisplay($getData['data']);
                $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
            } else {
                throw new AlertBackException(__('로그인하셔야 해당 서비스를 이용하실 수 있습니다.'));
            }
        } catch(\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        $depositConfig = \Globals::get('gSite.member.depositConfig');
        $mileageConfig = \Globals::get('gSite.member.mileageBasic');
        $this->setData('mileageConfig', gd_isset($mileageConfig, array('name' => '마일리지', 'unit' => '마일')));
        $this->setData('depositUConfig', gd_isset($depositConfig, array('name' => '예치금', 'unit' => '원')));
        $this->setData('data', gd_isset($getData['data']));
        $this->setData('convertArrData', gd_isset($getConvertArrData));
        $this->setData('list', gd_isset($getData['list']));
        $this->setData('search', $getData['search']);
        $this->setData('selected', $getData['selected']);
        $this->setData('page', $page);
    }
}
