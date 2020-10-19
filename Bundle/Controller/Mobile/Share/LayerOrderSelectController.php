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
namespace Bundle\Controller\Mobile\Share;

use Component\Board\BoardConfig;

class LayerOrderSelectController extends \Controller\Mobile\Controller
{
    public function index()
    {
        $bdId = \Request::post()->get('bdId');
        $boardConfig = new BoardConfig($bdId);
        $bdSno = \Request::post()->get('bdSno');
        $this->setData('orderDuplication',$boardConfig->cfg['orderDuplication']);
        $this->setData('bdId',$bdId);
        $this->setData('bdSno',$bdSno);
    }
}
