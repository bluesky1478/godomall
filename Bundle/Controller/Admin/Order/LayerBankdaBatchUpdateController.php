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
 * Class LayerBankdaBatchUpdateController
 * 뱅크다 일괄 수정 레이어
 *
 * @package Bundle\Controller\Admin\Order
 * @author  cjb3333 <cjb3333@godo.co.kr>
 */
class LayerBankdaBatchUpdateController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            // GET 리퀘스트
            $getValue = Request::get()->toArray();
            $this->setData('getValue', $getValue);

            $this->getView()->setDefine('layout', 'layout_layer.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
