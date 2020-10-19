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

use Framework\Debug\Exception\Except;
use Exception;

/**
 * 무통장 입금은행 변경 레이어 페이지
 * [관리자 모드] 무통장 입금은행 변경 레이어 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerBankSelectorController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function index()
    {
        try {
            // --- 모듈 호출
            $order = \App::load('\\Component\\Order\\OrderAdmin');

            $orderNo = \Request::post()->get('orderNo');
            $orderData = $order->getOrderData($orderNo);

            $this->setData('orderNo', $orderNo);
            $this->setData('orderData', $orderData);

            // --- 무통장 입금은행
            $bank = $order->getBankInfo(null, 'y');
            foreach ($bank as $key => $val) {
                $bankData[$val['bankName'] . STR_DIVISION . $val['accountNumber'] . STR_DIVISION . $val['depositor']] = $val['bankName'] . ' ' . $val['accountNumber'] . ' ' . $val['depositor'];
            }

            // 공급사와 템플릿 공통 사용
            $this->getView()->setPageName('order/layer_bank_selector.php');

            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->setData('bankData', $bankData);

        } catch (Exception $e) {
            throw $e;
        }
    }
}
