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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Provider\Share;

use Exception;
use Request;

/**
 * @author  <kookoo135@godo.co.kr>
 */
class LayerGoodsSaleController extends \Controller\Admin\Share\LayerGoodsSaleController
{
    public function index()
    {
        parent::index();

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setPageName('share/layer_goods_sale.php');
    }
}
