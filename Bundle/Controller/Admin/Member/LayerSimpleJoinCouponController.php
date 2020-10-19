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
namespace Bundle\Controller\Admin\Member;

use Component\Coupon\CouponAdmin;
use Request;
use Exception;

/**
 * 주문 쿠폰 로그 레이어 페이지
 * [관리자 모드] 주문 쿠폰 로그 레이어 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerSimpleJoinCouponController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            $postValue = Request::post()->toArray();
            gd_isset($postValue['page'], 1);
            gd_isset($postValue['pageNum'], 10);

            $coupon = new CouponAdmin();
            $getData = $coupon->getMemberSimpleJoinCouponList(Request::post()->get('memNo'), Request::post()->get('couponNo'), 'c.regDt DESC', true, $postValue);
            $data = $coupon->getMemberCouponUsableDisplay($getData);
            $getData = $coupon->convertCouponArrData($getData);

            // 레이어에서 자바스크립트 페이징 처리시 사용되는 구문
            if (gd_isset($postValue['pagelink'])) {
                $postValue['page'] = (int) str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($postValue['pagelink'])));
            }
            $page = \App::load('\\Component\\Page\\Page', $postValue['page']); // 페이지 재설정

            $this->setData('memNo', Request::post()->get('memNo'));
            $this->setData('list', $getData);
            $this->setData('data', $data);
            $this->setData('page', gd_isset($page));
            $this->setData('amount', $postValue['amount']);

            // 레이어 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        } catch (Exception $e) {
            throw $e;
        }
    }
}
