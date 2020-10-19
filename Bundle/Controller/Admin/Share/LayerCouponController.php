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
use Framework\Utility\GodoUtils;

use Exception;

/**
 * 주문 쿠폰 복원 레이어 페이지
 * [관리자 모드] 주문 쿠폰 로그 레이어 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 * @see order/layer_order_coupon.php
 */
class LayerCouponController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // 리퀘스트
            $getValue = \Request::get()->toArray();

            // --- 모듈 호출
            $coupon = \App::load('\\Component\\Coupon\\CouponAdmin');
            $addWhere = ($getValue['couponSaveType']) ? $getValue['couponSaveType'] : '';
            $mode = ($getValue['couponKind']) ? $getValue['couponKind'] : '';
            $getData = $coupon->getCouponAdminList($mode, $addWhere);

            $this->setData('couponTypeY', $getValue['couponTypeY']);
            // 페이지
            $page = \App::load('\\Component\\Page\\Page');
            $this->setData('page', $page);

            // 쿠폰유형 셀렉트 박스
            $couponUseType = [
                '' => '= ' . __('전체') . ' =',
                'product' => __('상품적용쿠폰'),
                'order' => __('주문적용쿠폰'),
                'delivery' =>  __('배송비적용쿠폰'),
            ];
            $this->setData('couponSaveType', $getValue['couponSaveType']);
            $this->setData('couponUseType', $couponUseType);

            // 쿠폰 사용범위
            $couponDeviceType = [
                '' => '= ' . __('전체') . ' =',
                'all' => __('PC+모바일'),
                'pc' => __('PC'),
                'mobile' => __('모바일'),
            ];
            $this->setData('couponDeviceType', $couponDeviceType);

            // 발급상태.
            $couponType = [
                '' => '= ' . __('전체') . ' =',
                'y' => __('발급중'),
                'n' => __('일시중지'),
                'f' => __('발급종료'),
            ];
            $this->setData('couponType', $couponType);

            // 페이퍼쿠폰 사용 여부
            $offlineCouponUse = GodoUtils::isPlusShop(PLUSSHOP_CODE_COUPONOFFLINE);
            $this->setData('offlineCouponUse', $offlineCouponUse);
            $checked['couponKind'][$getValue['couponKind']] = 'checked=checked';
            $this->setData('checked', $checked);

            // 템플릿 변수
            $this->setData('couponKindFl', $getValue['couponKindFl']);
            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('parentFormID', $getValue['parentFormID']);
            $this->setData('dataFormID', $getValue['dataFormID']);
            $this->setData('dataInputNm', $getValue['dataInputNm']);
            $this->setData('mode', gd_isset($getValue['mode'], 'search'));
            $this->setData('disabled', gd_isset($getValue['disabled'], ''));
            $this->setData('callFunc', gd_isset($getValue['callFunc'], ''));
            $this->setData('couponSaveType', gd_isset($addWhere, ''));
            $this->setData('couponAdmin', $coupon);
            $this->setData('data', gd_isset($coupon->convertCouponAdminArrData($getData['data'])));
            $this->setData('search', gd_isset($getData['search']));
            $this->setData('page', $page);

            // 레이어 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
