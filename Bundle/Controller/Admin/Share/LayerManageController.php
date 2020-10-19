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

use Framework\Debug\Exception\LayerException;
use Component\Member\Manager;
use Framework\StaticProxy\Proxy\Session;
use Globals;
use Request;

class LayerManageController extends \Controller\Admin\Controller
{

    /**
     * 운영자 선택 레이어
     *
     * @author cjb3333
     */
    public function index()
    {
        $getValue = Request::get()->toArray();
        $_managerClass = new Manager();

        // 공급사관리 경우 대표운영자 조회하기
        if ($getValue['mode'] == 'insertScmRegist' || $getValue['mode'] == 'modifyScmModify') {
            $getValue['scmFl'] = 'all';
            $getValue['isSuper'] = 'y';
        }

        // 레이어에서 자바스크립트 페이징 처리시 사용되는 구문
        if (gd_isset($getValue['pagelink'])) {
            $getValue['page'] = (int) str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
        }
        gd_isset($getValue['page'], 1);

        $getData = $_managerClass->getManagerList($getValue);
        $page = \App::load('\\Component\\Page\\Page', $getValue['page']); // 페이지 재설정

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');

        $this->setData('employeeList',$_managerClass->getEmployeeList());
        $this->setData('layerFormID', $getValue['layerFormID']);
        $this->setData('parentFormID', $getValue['parentFormID']);
        $this->setData('dataFormID', $getValue['dataFormID']);
        $this->setData('dataInputNm', $getValue['dataInputNm']);
        $this->setData('mode',gd_isset($getValue['mode']));
        $this->setData('data', gd_isset($getData['data']));
        $this->setData('search', gd_isset($getData['search']));
        $this->setData('checked', gd_isset($getData['checked']));
        $this->setData('page', gd_isset($page));

        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('share/layer_manage.php');

    }
}
