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
class LayerOrderInvoiceController extends \Controller\Admin\Controller
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

            $groupCd = Request::get()->get('groupCd');
            $this->setData('groupCd', $groupCd);

            // 성공여부
            $completeFl = Request::get()->get('completeFl', '');
            $checked['completeFl'][$completeFl] = 'checked="checked"';
            $this->setData('completeFl', $completeFl);
            $this->setData('checked', $checked);

            // 송장일괄등록 데이터
            $orderInvoice = $order->getOrderInvoiceView($groupCd, $completeFl);
            $this->setData('data', $orderInvoice['data']);
            $this->setData('info', $orderInvoice['info']);

            // 레이어 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        } catch (Exception $e) {
            throw $e;
        }
    }
}
