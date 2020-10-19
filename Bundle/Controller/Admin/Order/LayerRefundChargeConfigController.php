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
use Globals;

/**
 * 환불 수수료 설정 레이어 페이지
 * [관리자 모드] 환불 수수료 설정 레이어 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerRefundChargeConfigController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // --- 환불 수수료 설정 config 불러오기
            $data = gd_policy('order.refundCharge');
            $this->setData('data', $data);

            // 환불 수수료 초기화
            gd_isset($data['refundCharge'], 0);

            // --- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
