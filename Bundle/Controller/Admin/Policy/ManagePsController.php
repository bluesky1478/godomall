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

use Component\Admin\AdminMenu;
use Component\Godo\GodoDemoApi;
use Component\Mail\MailMimeAuto;
use Component\Member\Manager;
use Component\Member\Member;
use Component\Policy\ManageSecurityPolicy;
use Component\Policy\Policy;
use Component\Scm\ScmAdmin;
use Component\Sms\Sms;
use Component\Sms\SmsMessage;
use Component\Sms\SmsSender;
use Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Framework\Security\Otp;
use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;

/**
 * 운영자 등록/수정 처리 페이지
 *
 * @author Lee Namju <lnjts@godo.co.kr>
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class ManagePsController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $_managerClass = \App::load(Manager::class);
        // 각 모드에 따른 처리
        switch ($request->request()->get('mode')) {
            case 'modify':  // --- 관리자 등록 / 수정
                if (gd_is_provider()) {
                    $managerData = $_managerClass->getManagerData($request->post()->get('sno'));
                    if ($managerData['data']['scmNo'] !== $session->get('manager.scmNo')) {
                        throw new \RuntimeException(__('잘못된 경로로 접근하셨습니다.'));
                    }
                }
            case 'register':
                try {
                    // 데모인 경우 패스워드 수정 금지
                    $demo = \App::load(GodoDemoApi::class);
                    if ($demo->checkDomain() === true && $request->request()->get('mode') === 'modify') {
                        $request->post()->del('isModManagerPw');
                    }
                    if ($request->post()->get('permissionSetupMode') == 'managePermission' && $this->getData('managerPermissionMethodExist') === true && $this->getData('adminMenuPermissionMethodExist') === true) {
                        $postData = $request->post()->toArray();
                        if (method_exists($_managerClass, 'setManagerWorkDebugPermissionFl')) {
                            $_managerClass->setManagerWorkDebugPermissionFl($postData);
                        }
                        $managerSno = $_managerClass->saveManagerData($postData, $request->files()->toArray());
                        $_managerClass->saveManagerWritePermissionData($postData, $managerSno);
                    } else {
                        $_managerClass->saveManagerData($request->post()->toArray(), $request->files()->toArray());
                    }
                    $this->layer(__('저장이 완료되었습니다.'), 'parent.location.replace("manage_list.php")');
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage(), $e->getCode(), $e, null, 3000);
                }
                break;

            // --- 관리자 삭제
            case 'delete':
                try {
                    foreach ($request->post()->get('chk') as $sno) {
                        $_managerClass->setManagerDelete($sno);
                    }
                    $this->layer(__('삭제 되었습니다.'), 'parent.location.reload()');
                } catch (Exception $e) {
                    $this->layer($e->getMessage());
                }
                break;

            // --- 관리자 아이디 중복확인
            case 'overlapManagerId':
                try {
                    $result = $_managerClass->overlapManagerId($request->get()->get('managerId'));
                    if ($result) {
                        $this->json(
                            [
                                'result' => 'fail',
                                'msg'    => sprintf(__('%s는 이미 등록된 %s 입니다'), $request->get()->get('managerId'), '아이디'),
                            ]
                        );
                    } else {
                        $this->json(
                            [
                                'result' => 'ok',
                                'msg'    => __('사용 가능한 아이디입니다.'),
                            ]
                        );
                    }
                } catch (Exception $e) {
                    $this->json(
                        [
                            'result' => 'fail',
                            'msg'    => $e->getMessage(),
                        ]
                    );
                }
                break;

            case 'getAuthLayer':
                if ($request->get()->get('rMode') === 'register') {
                    if ($request->get()->get('scmFl') === 'n') { // 본사에서 본사 체크시
                        $adminMenuType = 'd';
                    } else { // 본사에서 공급사 체크시 or 공급사에서 아무런 값이 없을 시
                        $adminMenuType = 's';
                        if ($request->get()->get('scmNo') > 0) {
                            $thisScmNo = $request->get()->get('scmNo');
                        } else {
                            $thisScmNo = $session->get('manager.scmNo');
                        }
                    }
                } elseif ($request->get()->get('rMode') === 'modify') {
                    $scmNo = $request->get()->get('scmNo');
                    if (((int) $scmNo === DEFAULT_CODE_SCMNO) || !Manager::useProvider()) {
                        $adminMenuType = 'd';
                    } else {
                        $adminMenuType = 's';
                        $thisScmNo = $scmNo;
                    }
                }

                $adminMenu = new AdminMenu();
                $menuList = $adminMenu->getAdminMenuList($adminMenuType); // 메뉴 리스트(본사,공급사)
                $menuTreeList = $adminMenu->getAdminMenuTreeList($menuList);

                // 본사는 운영자의 기능 권한 설정만 적용
                // 공급사는 공급사의 기능 권한 설정안에서 운영자의 기능 권한 설정만 적용
                if ($adminMenuType === 's') { // 운영자 등록/수정에서 선택한 형태가 공급사 일 때
                    $scmAdmin = new ScmAdmin();
                    $scmFunctionAuth = $scmAdmin->getScmFunctionAuth($thisScmNo); // 공급사의 기능 권한
                    if (\is_array($scmFunctionAuth)) {
                        foreach ($scmFunctionAuth['functionAuth'] as $scmFunctionKey => $scmFunctionVal) {
                            if ($scmFunctionVal === 'y') {
                                $disabledScript[$scmFunctionKey] = 'false';
                            }
                        }
                        $this->setData('disabledScript', $disabledScript);
                    }
                }
                if ($request->get()->get('sno')) {
                    // 접근 권한 메뉴
                    $dataPermissionMenu = $_managerClass->getManagerPermissionMenu($request->get()->get('sno'));
                    if (\is_array($dataPermissionMenu) && $dataPermissionMenu !== 'all') {
                        // 전체 권한 아니고 일부 권한이고 일부 권한 설정이 있을 경우
                        foreach ($dataPermissionMenu['permission_1'] as $key_1 => $val_1) { // 1차 메뉴
                            $checked['permission_1'][$val_1] = 'checked="checked"';
                        }
                        foreach ($dataPermissionMenu['permission_2'] as $key_1 => $val_1) { // 2차 메뉴
                            foreach ($val_1 as $key_2 => $val_2) { // 2차 메뉴
                                $checked['permission_2'][$key_1][$val_2] = 'checked="checked"';
                            }
                        }
                        foreach ($dataPermissionMenu['permission_3'] as $key_2 => $val_2) { // 3차 메뉴
                            foreach ($val_2 as $key_3 => $val_3) { // 2차 메뉴
                                $checked['permission_3'][$key_2][$val_3] = 'checked="checked"';
                            }
                        }
                    }
                    // 운영자의 기능 권한
                    $dataFunctionAuth = $_managerClass->getManagerFunctionAuth($request->get()->get('sno'));
                    if (\is_array($dataFunctionAuth) && $dataFunctionAuth['permissionRange'] !== 'all') {
                        foreach ($dataFunctionAuth['functionAuth'] as $functionKey => $functionVal) {
                            $checked['functionAuth'][$functionKey][$functionVal] = 'checked="checked"';
                        }
                    }
                    $this->setData('dataFunctionAuth', $dataFunctionAuth);
                    $this->setData('checked', $checked);
                }

                if (strpos($request->getHeaders()->get('ACCEPT', ''), 'application/json') !== false) {
                    $this->json(
                        [
                            'menuTreeList'     => $menuTreeList,
                            'disabledScript'   => $this->getData('disabledScript'),
                            'dataFunctionAuth' => $this->getData('dataFunctionAuth'),
                        ]
                    );
                }

                // 페이코서치 사용 여부(메뉴 노출 체크 용도)
                $paycoSearch = \App::load('\\Component\\Nhn\\Paycosearch');
                $this->setData('paycosearchHide', $paycoSearch->neSearchConfigIsset == 'Y' ? false : true);

                $this->getView()->setDefine('layout', 'layout_layer.php');
                $this->getView()->setDefine('layoutContent', 'policy/_manage_register_auth.php');
                $this->setData('menuTreeList', $menuTreeList);
                $this->setData('adminMenuType', $adminMenuType);
                break;

            // --- 운영자 권한 설정> 기존 운영자 권한 불러오기 + 권한 초기화
            case 'getMenuListLayout':
                $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);

                // 실행 조건 검증
                if ($this->getData('managerPermissionMethodExist') !== true || $this->getData('adminMenuPermissionMethodExist') !== true) {
                    echo '<div class="text-orange-red center bold mgt24">정상적으로 서비스가 제공되지 않습니다. 잠시 후 다시 시도해주세요.<br>문제가 지속될 경우 1:1문의 게시판에 문의해주세요.</div>';
                    break;
                }

                // mode 정의
                if ($request->get()->get('type') == 'init') { // 권한 초기화 경우
                    if ($request->get()->get('sno') != '') {
                        $mode = 'modify';
                    } else {
                        $mode = 'register';
                    }
                } else { // 기존 운영자 권한 불러오기
                    $mode = 'modify';
                }

                // 운영자 정보
                if ($request->get()->get('sno') == '') {
                    $data['scmNo'] = $request->get()->get('scmNo');
                    $scmNo = $request->get()->get('scmNo');
                    $isSuper = $request->get()->get('isSuper');
                } else {
                    $data = $_managerClass->getManagerInfo($request->get()->get('sno'));
                    $scmNo = $data['scmNo'];
                    $isSuper = $data['isSuper'];
                }

                // 본사 + 공급사 구분 정의
                if ($request->get()->get('sno') == '') {
                    if ($request->get()->get('scmFl') !== 'y') { // 본사에서 본사 체크시
                        $adminMenuType = 'd';
                    } else { // 본사에서 공급사 체크시 or 공급사에서 아무런 값이 없을 시
                        $adminMenuType = 's';
                    }
                    if (((int) $scmNo === DEFAULT_CODE_SCMNO) || !Manager::useProvider()) {
                        $adminMenuType = 'd';
                    }
                } else {
                    if ((int) $scmNo === DEFAULT_CODE_SCMNO) {
                        $adminMenuType = 'd';
                    } else {
                        $adminMenuType = 's';
                    }
                }

                // 권한 정의
                if ($request->get()->get('type') == 'init') { // 권한 초기화 경우
                    if (method_exists($_managerClass, 'getRepackManagerRegisterPermission') === true) {
                        $existingPermission = $_managerClass->getRepackManagerRegisterPermission($data);
                    }
                } else { // 기존 운영자 권한 불러오기
                    $fromData = $_managerClass->getManagerInfo($request->get()->get('fromManageSno'));
                    if (method_exists($_managerClass, 'getRepackManagerRegisterPermission') === true) {
                        $existingPermission = $_managerClass->getRepackManagerRegisterPermission($fromData);
                    }
                }

                // 메뉴 리스트(본사,공급사)
                $adminMenu = new AdminMenu();
                $menuList = $adminMenu->getAdminMenuList($adminMenuType);
                $menuTreeList = $adminMenu->getAdminMenuTreeList($menuList);

                // 메뉴 리스트 필터
                if (method_exists($adminMenu, 'getMenuTreeListFilter') === true) {
                    $menuTreeList = $adminMenu->getMenuTreeListFilter($menuTreeList);
                }

                // 설정된 권한정보(permissionFl,permissionMenu, functionAuth, writeEnabledMenu)로 selected 정의
                if (method_exists($adminMenu, 'getAdminMenuPermissionSelected') === true) {
                    $selected = $adminMenu->getAdminMenuPermissionSelected($existingPermission, $menuTreeList);
                }

                // 기능 리스트(본사,공급사)
                if (method_exists($adminMenu, 'getMenuFunction') === true) {
                    $functionList = $adminMenu->getMenuFunction($adminMenuType);
                }
                $checked = [];
                foreach ($existingPermission['functionAuth']['functionAuth'] as $functionKey => $functionVal) {
                    $checked['functionAuth'][$functionKey][$functionVal] = 'checked="checked"';
                }

                // 공급사 부운영자 등록/수정 일 경우
                if ($adminMenuType == 's' && $isSuper != 'y') {
                    // 공급사(대표운영자) 기능 리스트
                    $scmFunctionAuth = $scmAdmin->getScmFunctionAuth($request->get()->get('scmNo'));
                    $this->setData('scmFunctionAuth', $scmFunctionAuth['functionAuth']);
                    // 공급사 부운영자 메뉴권한 설정범위 정의
                    if (method_exists($adminMenu, 'getAdminMenuScmPermissionDisabled') === true) {
                        $scmSuperData = $scmAdmin->getScmSuperManager($scmNo);
                        $adminMenu->getAdminMenuScmPermissionDisabled($scmSuperData, $menuTreeList, $selected);
                    }
                }

                // 1차 메뉴 목록
                $menuTopList = [];
                foreach($menuTreeList['top'] as $key => $val) {
                    $menuTopList[$key] = $val['name'];
                    unset($key, $val);
                }

                // 권한 범위 및 설정 기능 disabled 여부
                // 본사 최고운영자 또는 공급사 ADMIN 대표운영자 수정 경우 권한 범위 disabled
                if (($adminMenuType == 'd' && $isSuper == 'y') || ($adminMenuType == 's' && $isSuper == 'y' && gd_is_provider())) {
                    $disabled['permissionFl'] = 'disabled="disabled"';
                }
                // 본사 최고운영자 또는 공급사 ADMIN 대표운영자 수정 또는 전체권한 경우 설정 기능 disabled
                if (($adminMenuType == 'd' && $isSuper == 'y') || ($adminMenuType == 's' && $isSuper == 'y' && gd_is_provider()) || $existingPermission['permissionFl'] == 's') {
                    $disabled['settingItem'] = 'disabled="disabled"';
                }

                $this->getView()->setDefine('layout', 'layout_layer.php');
                $this->getView()->setDefine('layoutContent', 'policy/_manage_permission_menu_list.php');
                $this->setData('mode', $mode);
                $this->setData('adminMenuType', $adminMenuType);
                $this->setData('isSuper', $isSuper);
                $this->setData('menuTopList', $menuTopList);
                $this->setData('menuTreeList', $menuTreeList);
                $this->setData('functionList', $functionList);
                $this->setData('permissionFl', $existingPermission['permissionFl']);
                $this->setData('selected', $selected);
                $this->setData('checked', $checked);
                $this->setData('disabled', $disabled);
                $this->setData('changeType', $request->get()->get('type'));
                break;

            // --- 운영자 권한 설정> 운영자 검색
            case 'getManagerListLayout':
                $getValue = $request->get()->toArray();
                $_managerClass = new Manager();

                // 레이어에서 자바스크립트 페이징 처리시 사용되는 구문
                if (gd_isset($getValue['pagelink'])) {
                    $getValue['page'] = (int) str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
                }
                gd_isset($getValue['page'], 1);
                gd_isset($getValue['pageNum'], 40);

                $getData = $_managerClass->getManagerList($getValue);
                $page = \App::load('\\Component\\Page\\Page', $getValue['page']); // 페이지 재설정

                // --- 관리자 디자인 템플릿
                $this->getView()->setDefine('layout', 'layout_layer.php');
                $this->getView()->setDefine('layoutContent', 'policy/_manage_permission_manager_list.php');

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
                break;

            // --- 운영자 권한 설정 저장
            case 'setManagePermission':
                try {
                    if ($this->getData('managerPermissionMethodExist') !== true || $this->getData('adminMenuPermissionMethodExist') !== true) {
                        throw new \RuntimeException(__('정상적으로 서비스가 제공되지 않습니다. 잠시 후 다시 시도해주세요.<br>문제가 지속될 경우 1:1문의 게시판에 문의해주세요.'));
                    }
                    $_managerClass->saveManagersPermission($request->post()->toArray());
                    $this->layerNotReload(__('저장이 완료되었습니다.'), 'parent.save_after_manger();');
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage(), null, null, null, 5000);
                }
                break;

            // --- 기능권한 초기값 리턴
            case 'getFunctionAuthInit':
                $functionAuth = '';
                if (method_exists($_managerClass, 'getFunctionAuthInit') === true) {
                    $functionAuth = $_managerClass->getFunctionAuthInit($request->request()->get('scmNo'), $request->request()->get('isSuper'));
                }
                $this->json($functionAuth);
                break;

            // --- 운영 보안 설정 저장
            case 'insertManageSecurity':
            case 'modifyManageSecurity':
                try {
                    // _POST 데이터
                    $request = \App::getInstance('request');
                    $postValue = $request->post()->toArray();
                    $policy = \App::load(Policy::class);
                    $policy->saveManageSecurity($postValue);
                    $managerSecurityPolicy = \App::load(ManageSecurityPolicy::class)->getValue();
                    StringUtils::strIsSet($managerSecurityPolicy['smsSecurity'], 'n');
                    StringUtils::strIsSet($managerSecurityPolicy['ipAdminSecurity'], 'n');
                    $securityAgreementPolicy = ComponentUtils::getPolicy('manage.securityAgreement');
                    StringUtils::strIsSet($securityAgreementPolicy['guide'], 'y');

                    if ($securityAgreementPolicy['guide'] === 'y' && ($managerSecurityPolicy['smsSecurity'] !== 'y'
                            && $managerSecurityPolicy['ipAdminSecurity'] !== 'y')) {
                        $this->js('top.godo.layer.confirm_manage_security.show();');
                    } else {
                        $this->layer(__('저장이 완료되었습니다.'));
                    }
                } catch (Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            case 'authSms':
                $session->del(Member::SESSION_USER_CERTIFICATION);
                $smsPoint = Sms::getPoint();
                if ($smsPoint < 1) {
                    $this->json(
                        [
                            'error'   => 100,
                            'message' => __('SMS 포인트가 부족합니다. 인증번호 발송을 위해 SMS 포인트를 충전해주세요'),
                        ]
                    );
                }
                // 인증번호 생성
                $userCertificationSession['certificationCode'] = Otp::getOtp(8);
                $cellPhone = $request->post()->get('cellPhone');
                $receiver[] = ['cellPhone' => $cellPhone];
                $logData['reserve']['dbTable'] = $userCertificationSession;

                $msg = sprintf(__('인증번호는 [%s]입니다. 정확히 입력해주세요.'), $userCertificationSession['certificationCode']);
                $smsSender = \App::load(SmsSender::class);
                $smsSender->validPassword(\App::load(\Component\Sms\SmsUtil::class)->getPassword());
                $smsSender->setSmsPoint($smsPoint);
                $smsSender->setMessage(new SmsMessage($msg));
                $smsSender->setSmsType('user');
                $smsSender->setReceiver($receiver);
                $smsSender->setLogData(['disableResend' => true]);
                $smsSender->setContentsMask([$userCertificationSession['certificationCode']]);
                $smsResult[] = $smsSender->send();
                if ($smsResult[0]['success'] === 1) {
                    $userCertificationSession['authType'] = 'authSms';
                    $certData = [
                        'certNo'        => $userCertificationSession['certificationCode'],
                        'authCellPhone' => $cellPhone,
                        'limitTime'     => strtotime('+3 minutes'),
                    ];
                    $session->set(Member::SESSION_USER_CERTIFICATION, $certData);
                    $this->json(
                        [
                            'error'   => 0,
                            'message' => __('SMS 가 발송되었습니다.'),
                        ]
                    );
                }
                $exitMessage = '등록된 SMS 발신번호가 없어 인증번호를 SMS로 발송할 수 없습니다.';
                $exitMessage .= ' 발신번호 사전등록제에 따라 SMS를 발송하려면 발신번호가 먼저 등록되어 있어야 합니다.';
                $exitMessage .= '<br />고도몰 홈페이지의 마이고도>SMS발신번호 등록/관리에서 발신번호 등록 후';
                $exitMessage .= ' 관리자의 회원>SMS 관리>자동 SMS 설정에서 발신번호를 설정할 수 있습니다.';
                $this->json(
                    [
                        'error'   => 200,
                        'message' => __($exitMessage),
                    ]
                );
                break;
            case 'authCheckSms':
                $certData = $session->get(Member::SESSION_USER_CERTIFICATION);
                if ($certData['limitTime'] < time()) {
                    $this->json(
                        [
                            'error'   => 100,
                            'message' => __('인증시간이 만료되었습니다. 재전송을 눌러주세요.'),
                        ]
                    );
                }
                if ($request->post()->get('cellPhoneAuthNo', '') === $certData['certNo']) {
                    unset($certData['limitTime']);
                    $certData['isAdminSmsAuth'] = true;
                    $session->set(Member::SESSION_USER_CERTIFICATION, $certData);
                    $this->json(
                        [
                            'error'         => 0,
                            'message'       => __('인증이 완료되었습니다.'),
                            'notice_danger' => __('페이지 [저장] 버튼 클릭 시 인증정보가 저장됩니다.'),
                        ]
                    );
                }
                $this->json(
                    [
                        'error'   => 200,
                        'message' => __('휴대폰 인증번호가 정확하지 않습니다. 확인 후 다시 입력해주세요.'),
                    ]
                );
                break;
            case 'authEmail':
                $session->del(Member::SESSION_USER_MAIL_CERTIFICATION);
                try {
                    $mailMimeAuto = \App::load(MailMimeAuto::class);
                    $securityInfo['certificationCode'] = Otp::getOtp(8);
                    $securityInfo['authType'] = 'authEmail';
                    $securityInfo['email'] = $request->post()->get('email');
                    $certData = [
                        'certNo'    => $securityInfo['certificationCode'],
                        'authEmail' => $securityInfo['email'],
                        'limitTime' => strtotime('+3 minutes'),
                    ];
                    $session->set(Member::SESSION_USER_MAIL_CERTIFICATION, $certData);
                    // 운영자 이메일 인증은 항상 기준몰 설정을 따름
                    $mailMimeAuto->init(MailMimeAuto::ADMIN_SECURITY, $securityInfo, DEFAULT_MALL_NUMBER);
                    $mailMimeAuto->checkRequiredValue();
                    $result = $mailMimeAuto->autoSend();

                    if ($result !== true) {
                        exit(__('메일 발송 중 오류가 발생하였습니다.'));
                    }

                    exit('success');
                } catch (Exception $e) {
                    switch ($e->getCode()) {
                        case 200:
                            $exitMessage = '이메일 발송에 필요한 정보가 없어 인증번호를 이메일로 발송할 수 없습니다.';
                            $exitMessage .= '<br>회원>메일관리>자동메일설정의 관리자 보안 인증메일 내 발송자이메일 정보가 먼저 등록되어 있어야 이메일 발송이 가능합니다.';
                            exit(__($exitMessage));
                            break;
                        case 300:
                            $exitMessage = '이메일 발송에 필요한 정보가 없어 인증번호를 이메일로 발송할 수 없습니다.';
                            $exitMessage .= '<br>기본설정>기본정책>기본정보설정의 쇼핑몰 도메인 정보가 먼저 등록되어 있어야 이메일 발송이 가능합니다.';
                            exit(__($exitMessage));
                            break;
                    }
                    exit;
                }
                break;
            case 'authCheckEmail':
                $certData = $session->get(Member::SESSION_USER_MAIL_CERTIFICATION);
                if ($certData['limitTime'] < time()) {
                    $this->json(
                        [
                            'error'   => 100,
                            'message' => __('인증시간이 만료되었습니다. 재전송을 눌러주세요.'),
                        ]
                    );
                }
                if ($request->post()->get('cellEmailAuth', '') === $certData['certNo']) {
                    unset($certData['limitTime']);
                    $certData['isAdminEmailAuth'] = true;
                    $session->set(Member::SESSION_USER_MAIL_CERTIFICATION, $certData);
                    $this->json(
                        [
                            'error'         => 0,
                            'message'       => __('인증이 완료되었습니다.'),
                            'notice_danger' => __('페이지 [저장] 버튼 클릭 시 인증정보가 저장됩니다.'),
                        ]
                    );
                }
                $this->json(
                    [
                        'error'   => 200,
                        'message' => __('이메일 인증번호가 정확하지 않습니다. 확인 후 다시 입력해주세요.'),
                    ]
                );
                break;
        }
    }
}
