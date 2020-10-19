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
namespace Bundle\Controller\Admin\Share;


use Request;

class DepositPreviewController extends \Controller\Admin\Controller
{

    public function index()
    {
        $get = Request::get()->all();
        $orderReorderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');
        $handleData = $orderReorderCalculation->getOrderHandleData($get['orderNo'], null, null, $get['handleSno']);
        $data = $orderReorderCalculation->getOrderDepositHandleData($handleData[0]);
        $this->setData('data', $data);
        $this->getView()->setDefine('layout', 'layout_layer.php');

    }
}
