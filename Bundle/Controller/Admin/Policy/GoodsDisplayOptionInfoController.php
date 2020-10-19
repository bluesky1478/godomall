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

use Request;
use Session;

/**
 * 상품 정보 노출 설정 페이지
 * @author atomyang
 */
class GoodsDisplayOptionInfoController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}