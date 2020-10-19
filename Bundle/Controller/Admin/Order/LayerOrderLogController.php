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
 * 주문 상품 로그 레이어 페이지
 * [관리자 모드] 주문 상품 로그 레이어 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerOrderLogController extends \Controller\Admin\Controller
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

            $orderLog = $order->getOrderGoodsLog(Request::post()->get('orderNo'), Request::post()->get('goodsSno'));
            $goodsNm = Request::post()->has('goodsNm') ? __('주문로그 보기') : __('전체 주문로그 보기');

            // --- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

            // 공급사와 페이지 같이 사용
            $this->getView()->setPageName('order/layer_order_log.php');

            $this->setData('orderLog', $orderLog);
            $this->setData('goodsNm', $goodsNm);
            $this->setData('logType', !Request::post()->has('goodsSno') ? 'all' : 'one');

        } catch (Exception $e) {
            $this->layer($e->getMessage());
        }
    }
}
