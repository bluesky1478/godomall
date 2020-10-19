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
class AjaxOrderViewStatusAddController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            $postValue = Request::post()->toArray();

            if ($postValue['mode'] == 'get_select_order_goods_add_data') {
                $order = App::load(\Component\Order\Order::class);
                $orderReorderCalculation = App::load(\Component\Order\ReOrderCalculation::class);
                $addData = $orderReorderCalculation->getSelectOrderGoodsAddData($postValue);
                $this->setData('addData', $addData);

                if ($addData['settleStatus']) { // 주문이 입금 후 라면 추가 결제 정보 제공
                    // 입금은행 정보
                    $bank = $order->getBankInfo(null, 'y');
                    foreach ($bank as $key => $val) {
                        $bankData[$val['sno']] = $val['bankName'] . ' ' . $val['accountNumber'] . ' ' . $val['depositor'];
                    }
                    $this->setData('bankData', $bankData);

                    // 추가결제 계좌 은행
                    $bankNmCode = gd_array_change_key_value(gd_code('04002'));
                    $this->setData('bankNm', gd_isset($bankNmCode));
                }

                // --- 템플릿 정의
                $this->getView()->setDefine('layout', 'layout_layer.php');
            } else {

            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
