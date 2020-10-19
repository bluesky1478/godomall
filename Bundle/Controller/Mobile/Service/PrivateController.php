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

namespace Bundle\Controller\Mobile\Service;


use App;

class PrivateController extends \Controller\Mobile\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Front\Controller $front */
        $front = App::load('\\Controller\\Front\\Service\\PrivateController');
        $front->index();

        $this->setData($front->getData());
    }
}
