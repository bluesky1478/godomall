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
namespace Bundle\Controller\Mobile\Mypage;

use App;

/**
 * 예치금
 * @package Bundle\Controller\Mobile\Mypage
 * @author  cjb3333
 */
class LayerDepositReasonController extends \Controller\Mobile\Controller
{
    public function index()
    {
        $front = App::load('\\Controller\\Front\\Mypage\\LayerDepositReasonController');
        $front->index();
        $this->setData($front->getData());

    }
}
