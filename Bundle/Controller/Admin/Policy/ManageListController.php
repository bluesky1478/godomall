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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Policy;

use Component\Member\Manager;
use Component\Member\ManagerCs;
use Component\Page\Page;
use Component\Scm\Scm;
use Framework\Debug\Exception\LayerException;
use Framework\Utility\GodoUtils;
use Framework\Utility\StringUtils;
use Globals;
use Request;

/**
 * 운영자 관리 리스트
 *
 * @author Lee Namju <lnjts@godo.co.kr>
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class ManageListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('policy', 'management', 'list');

        $request = \App::getInstance('request');
        try {
            $component = \App::load(Manager::class);
            $getData = $component->getManagerList($request->get()->toArray());
            $page = \App::load(Page::class); // 페이지 재설정
            $department = gd_code('02001'); // 부서
            $position = gd_code('02002'); // 직급
            $duty = gd_code('02003'); // 직책

            // SMS 자동발송 수신여부 관련 체크
            $smsAutoReceiveKind = $component->smsAutoReceiveKind;
            $smsAutoReceiveKind = array_merge(['all' => __('전체')], ['n' => __('SMS 수신안함')], $smsAutoReceiveKind);
        } catch (\Exception $e) {
            throw new LayerException($e->getMessage());
        }

        $scm = \App::load(Scm::class);
        $scmList = StringUtils::htmlSpecialCharsStripSlashes($scm->selectOperationScmList());
        $scmList[0]['companyNm'] .= '(본사)';
        $managerCs = \App::load(ManagerCs::class);

        $this->setData('useAppCodes', GodoUtils::getUsePlusShopCodes());
        $this->setData('employeeList', $component->getEmployeeList());
        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $getData['checked']);
        $this->setData('page', $page);
        $this->setData('department', $department);
        $this->setData('position', $position);
        $this->setData('duty', $duty);
        $this->setData('smsAutoReceiveKind', $smsAutoReceiveKind);
        $this->setData('scmList', $scmList);
        $this->setData('csList', $managerCs->getDecryptListAll());
        $this->addCss(['layer.css']);
        $this->addScript(['layer_manager_cs.js?'.time()]);
    }
}
