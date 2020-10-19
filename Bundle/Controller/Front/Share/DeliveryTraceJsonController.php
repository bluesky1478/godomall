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
namespace Bundle\Controller\Front\Share;

use Component\Delivery\Delivery;
use Request;

/**
 * 배송 추적 페이지 - json 형식
 * !중요! 수정시 프론트/모바일 share도 동일하게 변경처리 해야 한다.
 *
 * @package Bundle\Controller\Front\Share
 * @author  <bumyul2000@godo.co.kr>
 */
class DeliveryTraceJsonController extends \Controller\Front\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            $getValue = Request::get()->all();

            $delivery = \App::load('\\Component\\Delivery\\Delivery');
            $traceUrl = $delivery->getDeliveryTrace($getValue['invoiceCompanySno'], $getValue['invoiceNo']);
            $deliveryTraceData = $delivery->getDeliveryTraceJsonData($getValue['invoiceCompanySno'], $traceUrl);

            $this->setData('deliveryTraceData', $deliveryTraceData);
        }
        catch (Exception $e) {
            throw $e;
        }
    }
}
