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

namespace Bundle\Controller\Admin\Policy;

use Component\Order\OrderAdmin;
use Framework\Debug\Exception\AlertOnlyException;
use Request;

/**
 * Class LayerOrderAddFieldController
 * @package Bundle\Controller\Admin\Policy
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class LayerOrderAddFieldController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        // ---
        $postValue = Request::post()->toArray();

        // --- 모듈 호출
        $orderAdmin = new OrderAdmin();
        try {
            $getData = $orderAdmin->getOrderAddFieldApplyExcept($postValue['mode'], $postValue['type'], $postValue['no']);
        } catch (Exception $e) {
            throw new AlertOnlyException($e->getMessage());
        }

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');

        $this->setData('getData', $getData);
    }
}
