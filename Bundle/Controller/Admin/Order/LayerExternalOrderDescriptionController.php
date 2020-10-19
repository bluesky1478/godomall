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
 * 외부채널 주문 엑셀 항목설명
 *
 * @package Bundle\Controller\Admin\Order
 * @author  <bumyul2000@godo.co.kr>
 */
class LayerExternalOrderDescriptionController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            $externalOrder = \App::load('\\Component\\Order\\ExternalOrder');
            $descriptionList = $externalOrder->getExcelDescriptionList();

            $this->setData('descriptionList', $descriptionList);

            $this->getView()->setDefine('layout', 'layout_layer.php');
        } catch (Exception $e) {
            throw $e;
        }
    }
}
