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
use Globals;

/**
 * 배송 업체 리스트 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerDeliveryCompanyController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // --- 모듈 호출
            $delivery = \App::load('\\Component\\Delivery\\Delivery');

            // 배송 업체
            $data = $delivery->getDeliveryCompany();
            $dataCnt = count($data);

            $this->setData('data', $data);
            $this->setData('dataCnt', $dataCnt);

            $this->getView()->setDefine('layout', 'layout_layer.php');

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/layer_delivery_company.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
