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

use Exception;
use Request;

/**
 * 교환 차액 상세페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  <bumyul2000@godo.co.kr>
 */
class LayerOrderExchangePriceDetailController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // --- 모듈 호출
            $orderReorderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');

            $orderNo = Request::post()->get('orderNo');
            $handleSno = Request::post()->get('handleSno');

            $exchangeHandleData = $orderReorderCalculation->getOrderExchangeHandle($orderNo, null, $handleSno)[0];
            $this->setData('exchangeHandleData', $exchangeHandleData);

            $this->setData('exchangeRefundMethodName', $orderReorderCalculation->exchangeRefundMethodName);

            // --- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

            // 공급사와 페이지 같이 사용
            $this->getView()->setPageName('order/layer_order_exchange_price_detail.php');

        } catch (Exception $e) {
            $this->layer($e->getMessage());
        }
    }
}
