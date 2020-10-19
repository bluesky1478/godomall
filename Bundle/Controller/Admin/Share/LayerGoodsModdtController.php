<?php

namespace Bundle\Controller\Admin\Share;

use App;
use Request;

/**
 * @author  <cseun555@godo.co.kr>
 */
class LayerGoodsModdtController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
