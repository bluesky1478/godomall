<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Goods;

use Component\Goods\GoodsAdmin;
use Exception;
use Globals;
use Request;

class LayerTmpGoodsInfoController extends \Controller\Admin\Controller
{
    public function index()
    {
        $imageName = Request::get()->get('imageName');
        $pagelink = Request::get()->get('pagelink');
        parse_str($pagelink,$get);
        $goods = new GoodsAdmin();
        $data = $goods->getTmpGoodsImage(['imageName'=>$imageName , 'page'=>$get['page'],'isApplyGoods'=>'y'],true,false);
        $data['pageHtml'] = $data['page']->getPage('layer_list_search(\'PAGELINK\')');;
        if(Request::isAjax()) {
            $this->getView()->setDefine('layout', 'layout_layer.php');
        }
        $this->setData('data',$data);
        $this->setData('imageName',$imageName);

    }
}
