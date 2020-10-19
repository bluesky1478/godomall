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

use Globals;
use Request;

class LayerDisplayMainController extends \Controller\Admin\Controller
{

    /**
     * 메인페이지 분류 등록
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @param array $files
     */
    public function index()
    {
        // --- 모듈 호출
        $getValue = Request::get()->toArray();

        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
        $getData = $goods->getAdminListDisplayTheme('main','layer');

        $page = \App::load('\\Component\\Page\\Page');    // 페이지 재설정

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');


        $this->setData('layerFormID', $getValue['layerFormID']);
        $this->setData('parentFormID', $getValue['parentFormID']);
        $this->setData('dataFormID', $getValue['dataFormID']);
        $this->setData('dataInputNm', $getValue['dataInputNm']);
        $this->setData('mode',gd_isset($getValue['mode']));
        $this->setData('callFunc', gd_isset($getValue['callFunc'],''));
        $this->setData('scmFl',gd_isset($getValue['scmFl']));
        $this->setData('scmNo',gd_isset($getValue['scmNo']));


        $this->setData('data', gd_isset($getData['data']));
        $this->setData('checked', $getData['checked']);
        $this->setData('search', gd_isset($getData['search']));
        $this->setData('page', $page);


        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('share/layer_display_main.php');
    }
}
