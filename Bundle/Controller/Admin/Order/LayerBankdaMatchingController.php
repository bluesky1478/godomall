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
 * Class LayerBankdaMatchingController
 * 실시간 입금 확인 레이어
 *
 * @package Bundle\Controller\Admin\Order
 * @author  cjb3333 <cjb3333@godo.co.kr>
 */
class LayerBankdaMatchingController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            // POST 리퀘스트
            $postValue = Request::post()->toArray();
            $this->setData('data', $getData);

            $this->getView()->setDefine('layout', 'layout_layer.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
