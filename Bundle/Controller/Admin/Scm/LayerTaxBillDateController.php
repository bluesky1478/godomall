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
namespace Bundle\Controller\Admin\Scm;

use Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;

class LayerTaxBillDateController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 상품 데이터
        try {
            $postValue = Request::post()->toArray();

            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->setData('postData', $postValue);
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }
    }
}
