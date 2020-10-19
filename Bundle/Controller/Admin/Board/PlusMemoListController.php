<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Board;


use Component\Board\PlusMemoManager;

class PlusMemoListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('board', 'board', 'plusMemoList');
        $get = \Request::get()->all();
        $plusMemoManager = new PlusMemoManager();
        $list = $plusMemoManager->getList($get,true,true);

        $this->setData('req',$get);
        $this->setData('list',$list);
    }
}
