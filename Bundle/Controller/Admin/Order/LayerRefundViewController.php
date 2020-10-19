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
namespace Bundle\Controller\Admin\Order;

use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;
use Exception;

/**
 * 주문상세의 환불접수 리스트내 수정 레이어
 * [관리자 모드] 환불접수내용 수정
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerRefundViewController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // --- 모듈 호출
            $order = \App::load('\\Component\\Order\\OrderAdmin');

            if (!Request::post()->has('orderNo')) {
                throw new LayerException(__('주문번호가 없습니다.'));
            }

            // 환불상품 정보
            $handleData = $order->getOrderGoodsData(Request::post()->get('orderNo'), null, Request::post()->get('handleSno'), null, 'admin', false);
            $this->setData('handleData', $handleData);

            // 취소사유
            $cancelReasonCode = gd_array_change_key_value(gd_code('04001'));

            $cancelReasonCode = array_merge(array('' => '=' . __('사유선택') . '='), $cancelReasonCode);
            $this->setData('refundReason', gd_isset($cancelReasonCode));

            // 환불수단
            $refundMethodCode = gd_array_change_key_value(gd_code('04003'));
            $refundMethodCode = array_merge(array('' => '=' . __('환불수단 선택') . '='), $refundMethodCode);
            $this->setData('refundMethod', gd_isset($refundMethodCode));

            // 레이어 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setPageName('order/layer_refund_view.php');

        } catch (Exception $e) {
            throw $e;
        }


    }
}
