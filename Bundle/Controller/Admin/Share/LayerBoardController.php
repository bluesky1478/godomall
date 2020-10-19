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

namespace Bundle\Controller\Admin\Share;

use App;
use Component\Board\BoardAdmin;

/**
 * Class LayerBoardController
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class LayerBoardController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $boardAdmin = new BoardAdmin();

        $dataList = $boardAdmin->getBoardList(\Request::get()->all(),true, null, false);
        $list = $dataList['data'];
        $search = $dataList['search'];
        $checked = $dataList['checked'];
        $selected = $dataList['selected'];
        $page = App::load('\\Component\\Page\\Page');    // 페이지 재설정

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->setData('list', $list);
        $this->setData('search', $search);
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
        $this->setData('page', $page);
    }
}
