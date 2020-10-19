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
 * 재고환원 처리 레이어
 * 직접 처리하지 않으며 스크립트를 이용해 본문에서 저장시 처리된다.
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerReturnStockController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // POST 리퀘스트
            $postValue = Request::post()->toArray();

            // 모듈 설정
            $order = \App::load('\\Component\\Order\\OrderAdmin');
            $this->setData('order', $order);

            // 주문 상품 정보
            $orderGoods = $order->getOrderView($postValue['orderNo'], $postValue['orderGoodsNo'], null, 'r');
            $this->setData('data', $orderGoods);

            // 레이어용 레이아웃 설정
            $this->getView()->setDefine('layout', 'layout_layer.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
