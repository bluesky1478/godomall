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
namespace Bundle\Controller\Mobile\Share;

use Component\Delivery\Delivery;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertRedirectException;
use Request;

/**
 * 배송 추적 페이지
 * !중요! 수정시 프론트/모바일 share도 동일하게 변경처리 해야 한다.
 *
 * @package Bundle\Controller\Front\Share
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class DeliveryTraceController extends \Controller\Mobile\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // 요청 정보
            $getValue = Request::get()->all();

            $delivery = \App::load('\\Component\\Delivery\\Delivery');
            $traceJsonType = $delivery->checkDeliveryTraceJsonType($getValue['invoiceCompanySno']);
            if($traceJsonType === true){
                throw new AlertRedirectException(null, null, null, '../Share/delivery_trace_json.php?invoiceCompanySno='.$getValue['invoiceCompanySno'].'&invoiceNo='.$getValue['invoiceNo']);
                exit;
            }

            // --- 배송업체 정보 정보
            $traceUrl = $delivery->getDeliveryTrace($getValue['invoiceCompanySno'], $getValue['invoiceNo']);
            if ($traceUrl != false) {
                throw new AlertRedirectException(null, null, null, $traceUrl);
            } else {
                throw new AlertCloseException(__('배송추적을 할수 없습니다.'));
            }

        } catch (Exception $e) {
            throw $e;
        }


    }
}
