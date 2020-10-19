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

namespace Bundle\Controller\Admin\Design;

use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ComponentUtils;
use Request;

class CodePreviewController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws AlertBackException
     */
    public function index()
    {
        $getValue = Request::get()->toArray();

        $data = ComponentUtils::getDesignCode('', $getValue['sno']);

        $this->setData('data', $data);
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}