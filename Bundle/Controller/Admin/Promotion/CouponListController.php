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
namespace Bundle\Controller\Admin\Promotion;

use Exception;
use Framework\Debug\Exception\LayerException;
use Request;

class CouponListController extends \Controller\Admin\Controller
{

    /**
     * 쿠폰 리스트
     * [관리자 모드] 쿠폰 리스트
     *
     * @author su
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @throws LayerException
     * @internal param array $get
     * @internal param array $post
     * @internal param array $files
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('promotion', 'coupon', 'couponList');

        // --- 모듈 호출
        try {
            $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
            // 쿠폰 종류 - online 온라인 , offline 페이퍼
            $couponKind = Request::get()->get('couponKind');
            $getData = $couponAdmin->getCouponAdminList($couponKind);
            $getMemberCouponCount = $couponAdmin->getMemberCouponArrTotalCount($getData['data']);
            $getConvertArrData = $couponAdmin->convertCouponAdminArrData($getData['data']);
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->setData('data', gd_isset($getData['data']));
        $this->setData('convertArrData', gd_isset($getConvertArrData));
        $this->setData('countMemberCouponArrData', gd_isset($getMemberCouponCount));
        $this->setData('list', gd_isset($getData['list']));
        $this->setData('search', $getData['search']);
        $this->setData('checked', $getData['checked']);
        $this->setData('page', $page);
    }
}
