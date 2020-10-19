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
namespace Bundle\Controller\Admin\Provider\Policy;

use Framework\Debug\Exception\LayerException;
use Component\Member\Manager;
use Globals;
use Session;
use Request;

/**
 * 운영자 관리 리스트
 *
 * @author Lee Namju <lnjts@godo.co.kr>
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class ManageListController extends \Controller\Admin\Policy\ManageListController
{

    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
//        $this->callMenu('policy', 'management', 'list');
//
//        // --- 관리자 데이터
//        try {
//            $scmNo = Session::get('manager.scmNo');
//            if ($scmNo != DEFAULT_CODE_SCMNO) {
//                Request::get()->set('scmNo', $scmNo);
//                Request::get()->set('scmFl', 'y');
//            }
//            $_managerClass = new Manager();
//            $getData = $_managerClass->getManagerList(Request::get()->toArray());
//            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
//            $department = gd_code('02001'); // 부서
//            $position = gd_code('02002'); // 직급
//            $duty = gd_code('02003'); // 직책
//
//            // SMS 자동발송 수신여부 관련 체크
//            $smsAutoReceiveKind = $_managerClass->smsAutoReceiveKind;
//            $smsAutoReceiveKind = array_merge(['all' => '전체보기'], ['n' => 'SMS 수신안함'], $smsAutoReceiveKind);
//        } catch (\Exception $e) {
//            throw new LayerException($e->getMessage());
//        }
//
//        // --- 관리자 디자인 템플릿
//
//        $this->setData('data', $getData['data']);
//        $this->setData('search', $getData['search']);
//        $this->setData('sort', $getData['sort']);
//        $this->setData('checked', $getData['checked']);
//        $this->setData('page', $page);
//        $this->setData('department', $department);
//        $this->setData('position', $position);
//        $this->setData('duty', $duty);
//        $this->setData('smsAutoReceiveKind', $smsAutoReceiveKind);
        parent::index();
        $this->callMenu('policy', 'management', 'list');
        $this->getView()->setPageName('policy/manage_list.php');
    }
}
