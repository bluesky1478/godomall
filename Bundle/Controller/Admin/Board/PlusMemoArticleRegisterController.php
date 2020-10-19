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

class PlusMemoArticleRegisterController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $get = \Request::get()->all();
        $mode = gd_isset($get['mode'],'add');
        $plusMemoManager = new PlusMemoManager();
        $plusMemoArticle = new PlusMemoArticleAdmin($get);

        if($mode == 'modify') {
            $data= $plusMemoArticle->get($get['sno']);
        }
        else {
            $data['writer'] = \Session::get('manager.managerId').'('.\Session::get('manager.managerNm').')';
        }
        $plusMemoList = $plusMemoManager->getList(null,false,false);

        $this->setData('mode',$mode);
        $this->setData('req',$get);
        $this->setData('data',$data);
        $this->setData('plusMemoList',$plusMemoList);
    }
}
