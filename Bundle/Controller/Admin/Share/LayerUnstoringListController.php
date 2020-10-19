<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2018 NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Share;

use Exception;
use Request;

class LayerUnstoringListController extends \Controller\Admin\Controller
{

    public function index()
    {

        $request = \App::getInstance('request');

        $getValue = $request->get()->toArray();

        $unstoring = \App::load('\\Component\\Delivery\\Unstoring');
        $gLicense = \Globals::get('gLicense');
        $ecKindCount = ($gLicense['ecKind'] == 'standard') ? 3 : 10;

        if ($getValue['subTitle'] == '출고지') {
            $addressFl = 'unstoring';
        } else if ($getValue['subTitle'] == '반품/교환지') {
            $addressFl = 'return';
        }

        if (!$request->get()->has('page') || empty($request->get()->get('page'))) {
            $request->get()->set('page', 1);
        } else {
            $request->get()->set('page', $getValue['page']);
        }
        if (!$request->get()->has('pageNum')) {
            $request->get()->set('pageNum', 10);
        }

        $this->setData('mallInputDisp', $getValue['mallSno'] == 1 ? false : true);

        $cnt = $unstoring->getCountAddressRow($addressFl, $getValue['mallFl']);

        $page = new \Component\Page\Page($request->get()->get('page'), $cnt, $cnt);

        $data = $unstoring->getUnstoringInfoListBy($addressFl, $getValue['mallFl'], $request->get()->get('page'), $request->get()->get('pageNum'), $getValue['unstoringNo']);

        // 주소 목록 중에서 사용중인 주소 체크
        $checkedData = $unstoring->getCheckedUnstoringInfoList($data, $addressFl, $getValue['mallFl']);

        $this->setData('subTitle', $getValue['subTitle']);
        $this->setData('parentFormID', $getValue['parentFormID']);
        $this->setData('tableID', $getValue['tableID']);
        $this->setData('ecKindCount', $ecKindCount);
        $this->setData('mallName', $getValue['mallName']);
        $this->setData('mallFl', $getValue['mallFl']);
        $this->setData('checkedNo', $getValue['unstoringNo']);
        $this->setData('data', $checkedData);
        $this->setData('page', $page);

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');

    }

}
