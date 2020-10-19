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


use Component\Board\PlusMemoArticleAdmin;
use Component\Board\PlusMemoManager;

class PlusMemoArticleListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('board', 'board', 'plusMemoArticleList');
        $get = \Request::get()->all();
        $plusMemoManager = new PlusMemoManager();
        $plusMemoArticle = new PlusMemoArticleAdmin();
        $plusMemoList = $plusMemoManager->getList(null,false,false);
        $list = $plusMemoArticle->getList($get);
        $this->setData('req',$get);
        $this->setData('plusMemoList',$plusMemoList);
        $this->setData('list',$list);
    }
}
