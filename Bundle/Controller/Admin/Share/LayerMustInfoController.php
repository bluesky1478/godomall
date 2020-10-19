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
use Session;

class LayerMustInfoController extends \Controller\Admin\Controller
{

    /**
     * 상품 필수 정보 선택
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

        if($getValue['scmNo']) Request::get()->set('scmNo',[$getValue['scmNo'],'0']);
        else Request::get()->set('scmNo',[(string)Session::get('manager.scmNo'),'0']);

        $mustInfo = \App::load('\\Component\\Goods\\GoodsMustInfo');
        $getData = $mustInfo->getAdminListMustInfo(null,'layer');

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
        $this->setData('search', gd_isset($getData['search']));
        $this->setData('page', $page);


        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('share/layer_must_info.php');
    }
}
