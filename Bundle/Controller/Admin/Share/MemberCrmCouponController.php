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

namespace Bundle\Controller\Admin\Share;

use App;
use Request;

/**
 * Class 관리자-CRM COUPON 내역
 * @package Bundle\Controller\Admin\Share
 * @author  su
 */
class MemberCrmCouponController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('member', 'member', 'crm');

        Request::get()->set('page', Request::get()->get('page', 0));
        Request::get()->set('pageNum', Request::get()->get('pageNum', 10));

        /** @var \Bundle\Component\Coupon\CouponAdmin $coupon */
        $coupon = App::load('\\Component\\Coupon\\CouponAdmin');
        $getData = $coupon->getMemberCouponList(Request::get()->get('memNo'));
        $getConvertArrData = $coupon->convertCouponArrData($getData['data']);
        $getData['data'] = $coupon->getCouponApplyExceptArrData($getData['data']);
        $getData['data'] = $coupon->getMemberCouponUsableDisplay($getData['data']);
        $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

        $requestGetParams = Request::get()->all();
        $this->setData('data', gd_isset($getData['data']));
        $this->setData('search', $getData['search']);
        $this->setData('selected', $getData['selected']);
        $this->setData('convertArrData', gd_isset($getConvertArrData));
        $this->setData('requestGetParams', $requestGetParams);
        $this->setData('page', $page);
        $this->addScript(['member.js']);
        //        debug($page);
    }
}
