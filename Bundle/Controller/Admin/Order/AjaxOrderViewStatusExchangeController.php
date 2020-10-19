<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2017, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Order;

use App;
use Exception;
use Request;

/**
 * Class AjaxOrderViewStatusController
 *
 * @package Bundle\Controller\Admin\Order
 * @author by
 */
class AjaxOrderViewStatusExchangeController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            $postValue = Request::post()->toArray();

            if ($postValue['mode'] == 'get_select_order_goods_exchange_data') {
                $order = App::load(\Component\Order\Order::class);
                $orderReorderCalculation = App::load(\Component\Order\ReOrderCalculation::class);

                $exchangeData = $orderReorderCalculation->getSelectOrderGoodsExchangeData($postValue);
                $this->setData('exchangeData', $exchangeData);

                // 입금은행 정보
                $bank = $order->getBankInfo(null, 'y');
                foreach ($bank as $key => $val) {
                    $bankData[$val['sno']] = $val['bankName'] . ' ' . $val['accountNumber'] . ' ' . $val['depositor'];
                }
                $this->setData('bankData', $bankData);

                //교환시 마이너스 금액에 대한 환불처리 방식
                $this->setData('ehRefundMethodArr', $orderReorderCalculation->exchangeRefundMethodName);

                // 환불 계좌 은행
                $bankNmCode = gd_array_change_key_value(gd_code('04002'));
                $this->setData('bankNm', gd_isset($bankNmCode));

                // --- 템플릿 정의
                $this->getView()->setDefine('layout', 'layout_layer.php');

                // 공급사와 동일한 페이지 사용
                $this->getView()->setPageName('order/ajax_order_view_status_exchange.php');
            } else {

            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
