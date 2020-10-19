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
use Globals;
use Request;

class LayerCouponAuthController extends \Controller\Admin\Controller
{
    /**
     * 레이어 페이퍼 쿠폰 인증번호 관리
     *
     * [관리자 모드] 레이어 페이퍼 쿠폰 인증번호 관리
     * 설명 : 페이퍼 쿠폰 인증번호 정보가 필요한 페이지에서 선택할 인증번호의 리스트
     * @author su
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        // 페이퍼 쿠폰 인증번호 데이터
        try {
            // 모듈 호출
            $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
            // 페이퍼 쿠폰의 인증번호 리스트
            $couponNo = Request::get()->get('couponNo');
            $couponData = $couponAdmin->getCouponInfo($couponNo, 'couponNo, couponNm, couponType');
            $getData = $couponAdmin->getCouponOfflineAuthCodeAdminList($couponNo);
            $page = \App::load('\\Component\\Page\\Page');    // 페이지 재설정

            if(pathinfo(Request::getParserReferer()->path)['filename'] =='coupon_offline_list') {
                $this->setData('excelFl', 'y');
            } else {
                $this->setData('excelFl', 'n');
            }

        } catch (Exception $e) {
            throw $e;
        }

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $getValue = Request::get()->toArray();

        $this->setData('couponData', gd_isset($couponData));
        $this->setData('data', gd_isset($getData['data']));
        $this->setData('page', $page);
    }
}
