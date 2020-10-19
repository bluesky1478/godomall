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

use Component\Member\ManagerCs;
use Component\Member\Member;
use Framework\Debug\Exception\AlertBackException;
use Globals;
use Request;
use Session;
use Component\Member\Manager;
use Component\Policy\JoinItemPolicy;

/**
 * 운영자 관리 등록 수정
 *
 * @author Lee Namju <lnjts@godo.co.kr>
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class ManageRegisterController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $session->del(Member::SESSION_USER_CERTIFICATION);
        $session->del(Member::SESSION_USER_MAIL_CERTIFICATION);
        // --- 페이지 데이터
        try {
            $_managerClass = \App::load(Manager::class);

            $data = $_managerClass->getManagerData($request->get()->get('sno'));
            $mode = $data['data']['mode'];
            $isModify = $mode === 'modify';
            if ($data['data']['isSuper'] == 'y' && $data['data']['scmNo'] != DEFAULT_CODE_SCMNO) {
                $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
                $scmData = $scmAdmin->getScm($data['data']['scmNo']);

                // 공급사 기능 권한
                $functionAuth = json_decode($scmData['functionAuth'], true);
                if (is_array($functionAuth)) {
                    foreach ($functionAuth['functionAuth'] as $functionKey => $functionVal) {
                        if ($functionKey == 'goodsStockModify') {
                            $this->setData('goodsStockModify', $functionVal);
                        } else {
                            $data['checked']['functionAuth'][$functionKey][$functionVal] = 'checked="checked"';
                        }
                    }
                }

            }

            if ($isModify && gd_is_provider() && $data['data']['scmNo'] !== $session->get('manager.scmNo')) {
                throw new \RuntimeException(__('잘못된 경로로 접근하셨습니다.'));
            }

            if ($isModify && strpos($data['data']['managerId'], ManagerCs::PREFIX_CS_ID) === 0) {
                $managerCs = \App::load(ManagerCs::class);
                if ($managerCs->isCustomerService($request->get()->get('sno'))) {
                    throw new \RuntimeException(__('CS 계정 관리를 이용하시기 바랍니다.'));
                }
            }

            if ($mode === 'register') {
                $this->callMenu('policy', 'management', 'register');
            } elseif ($isModify) {
                $this->callMenu('policy', 'management', 'modify');
            }

            // 메일도메인
            $emailDomain = gd_array_change_key_value(gd_code('01004'));
            $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);
            $isgPlusGift = false;

            // 부서/직급/직책
            $department = gd_code('02001'); // 부서
            $position = gd_code('02002'); // 직급
            $duty = gd_code('02003'); // 직책

            // 고도몰5pro 사용여부 체크
            $globals = \App::getInstance('globals');
            $license = $globals->get('gLicense');
            $this->setData('useGodoPro', $license['ecCode'] === 'rental_mx_pro');

            $smsAutoReceiveKind = $_managerClass->smsAutoReceiveKind;

            // 대표관리자 전용값 설정
            if ($data['data']['isSuper'] !== 'y' || $data['data']['scmNo'] !== '1') {
                //대표관리자가 아닌 경우 sms 자동발송 수신함 - 관리자 보안 제거
                unset($smsAutoReceiveKind['smsAutoAdmin']);
            }

            // 공급사로 접속한 경우
            if ($isModify && gd_is_provider() && empty($data['data']['scmNo']) === false) {
                $this->setData('scmNo', $data['data']['scmNo']);
            }

            // 운영자 권한 설정
            if ($this->getData('managerPermissionMethodExist') === true && $this->getData('adminMenuPermissionMethodExist') === true) {
                $repack = $_managerClass->getRepackManagerRegisterPermission($data['data']);
                if ($repack !== null) {
                    $data['data']['permissionSetupMode'] = 'managePermission';
                    $data['data'] = array_merge((array)$data['data'], (array)$repack);

                    $this->addCss([
                        'managePermissionStyle.css?'.time(), // 운영자 권한 설정 CSS
                    ]);
                    $this->addScript([
                        'managePermission.js?'.time(), // 운영자 권한 설정 JS
                    ]);
                }
            }

            // --- 관리자 디자인 템플릿
            $this->setData('isgPlusGift', $isgPlusGift);
            $this->setData('data', $data['data']);
            $this->setData('checked', $data['checked']);
            $this->setData('disabled', $data['disabled']);
            $this->setData('emailDomain', $emailDomain);
            $this->setData('department', $department);
            $this->setData('position', $position);
            $this->setData('duty', $duty);
            $this->setData('smsAutoReceiveKind', $smsAutoReceiveKind);
            $this->setData('policy', \App::load(JoinItemPolicy::class)->getStandardValidation());
            $this->setData('isCs', $request->get()->get('isCs', false));
            $this->getView()->setDefine('layoutFunctionAuth', 'policy/_manage_function_auth_scm.php');// 리스트폼
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}
