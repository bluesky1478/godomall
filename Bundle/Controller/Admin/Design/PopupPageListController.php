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

use Component\Design\DesignPopup;
use Component\Page\Page;
use Exception;
use Request;

/**
 *
 * @package Bundle\Controller\Admin\Design
 * @author  Bag YJ <kookoo135@godo.co.kr>
 */
class PopupPageListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $getValue = Request::get()->all();
        $designPopup = new DesignPopup();
        if (gd_isset($getValue['pagelink'])) {
            $getValue['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
        } else {
            $getValue['page'] = 1;
        }
        $getValue['page'] = \Request::get()->get('page', $getValue['page']);
        $getValue['pageNum'] = gd_isset($getValue['pageNum'], 10);

        $getData = $designPopup->getPopupPage($getValue);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수
        $page->recode['amount'] = $getData['total'];
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 검색 레코드 수
        $page->recode['total'] = $getData['total'];
        $page->setPage();

        $page = \App::load('\\Component\\Page\\Page');

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->setData('getValue', $getValue);
        $this->setData('data', $getData['data']);
        $this->setData('page', $page);
    }
}
