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

use Exception;
use Framework\Debug\Exception\LayerException;
use Globals;
use Request;

class LayerCouponInfoController extends \Controller\Admin\Controller
{
    /**
     * 레이어 쿠폰정보
     *
     * [관리자 모드] 레이어 쿠폰정보
     * 설명 : 쿠폰정보
     * @author su
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        // 쿠폰정보
        try {
            // 모듈 호출
            $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
            // 페이퍼 쿠폰의 인증번호 리스트
            $couponNo = Request::get()->get('couponNo');
            $couponData = $couponAdmin->getCouponInfo($couponNo, '*');
            $couponData = $couponAdmin->getCouponApplyExceptData($couponData);
        } catch (\Exception $e) {
            throw new LayerException($e->getMessage());
        }

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->setData('couponData', gd_isset($couponData));
    }
}
