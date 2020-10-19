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

use Request;
use Exception;

/**
 * 주문 쿠폰 로그 레이어 페이지
 * [관리자 모드] 주문 쿠폰 로그 레이어 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerOrderCouponController extends \Controller\Admin\Controller
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

            $orderCoupon = $order->getOrderCoupon(Request::post()->get('orderNo'));
            $this->setData('orderCoupon', $orderCoupon);

            // 레이어 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        } catch (Exception $e) {
            throw $e;
        }
    }
}
