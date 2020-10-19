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

namespace Bundle\Controller\Admin\Share;

use Framework\Utility\DateTimeUtils;
use Vendor\Captcha\Captcha;

/**
 * Class LayerExcelAuthPsController
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class LayerExcelAuthPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $logger = \App::getInstance('logger');
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $cookie = \App::getInstance('cookie');
        $adminLog = \App::load('Component\\Admin\\AdminLogDAO');
        $manager = $session->get(\Component\Member\Manager::SESSION_MANAGER_LOGIN);
        $logger->info(__CLASS__, [$request->request()->all()]);
        $targetName = '엑셀';
        switch ($request->post()->get('mode')) {
            case 'authSmsGodo':
                $godoApi = \App::load('Component\\Godo\\MyGodoSmsServerApi');
                $return = $godoApi->getSmsAuthKey();
                $isSuccess = $return == 'Y';
                $this->json(
                    [
                        'error'   => $isSuccess ? 0 : 1,
                        'message' => $isSuccess ? '인증번호가 발송되었습니다.' : 'SMS 인증번호 전송에 실패하였습니다. 다시 시도해 주세요',
                    ]
                );
                break;
            case 'checkAuthSmsGodo':
                if ($cookie->get('CAPTCHA_RETRY_' . strtoupper($manager['managerId'])) > 4) {
                    $captchaNumber = strtoupper($request->post()->get('capchaNumber'));
                    //자동입력 방지문구 체크
                    $captcha = new Captcha();
                    $rst = $captcha->verify($captchaNumber, 1);
                    if ($rst['code'] != '0000') {
                        $this->json(
                            [
                                'error'   => 1,
                                'message' => '자동등록 방지문자가 맞지 않습니다.',
                            ]
                        );
                    }
                }
                $godoApi = \App::load('Component\\Godo\\MyGodoSmsServerApi');
                $key = $request->request()->get('smsAuthNumber');
                $return = $godoApi->checkSmsAuthKey($key);
                if ($return == 'Y' && $session->get(\Component\Excel\ExcelForm::SESSION_SECURITY_AUTH . '.requireExcelAuthSmsGodo', false)) {
                    $session->set(
                        \Component\Excel\ExcelForm::SESSION_SECURITY_AUTH, [
                            'hasExcelAuthSmsGodo'         => true,
                            'hasExcelAuthSmsGodoDateTime' => DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now'),
                        ]
                    );
                    $cookie->del('CAPTCHA_RETRY_' . strtoupper($manager['managerId']));
                    if ($request->post()->get('subject') == 'crema') {
                        $targetName = '파일';
                    }
                    $this->json(
                        [
                            'error'   => 0,
                            'message' => $targetName . '다운로드가 승인되었습니다.',
                        ]
                    );
                } else {
                    $message = sprintf(__CLASS__ . ', checkAuthSmsGodo mode check sms auth key return is %s, user input auth key is %s', $return, $key);
                    $logger->info($message, $session->get(\Component\Excel\ExcelForm::SESSION_SECURITY_AUTH, []));
                    $this->json(
                        [
                            'error'   => 1,
                            'message' => $return,
                        ]
                    );
                }
                break;
            case 'getRestTime':
                $hasInputTime = $session->has(\Component\Godo\MyGodoSmsServerApi::KEY_SESS_INPUT_TIME);
                $inputTime = $session->get(\Component\Godo\MyGodoSmsServerApi::KEY_SESS_INPUT_TIME) - time();
                $this->json(
                    [
                        'error' => $hasInputTime ? 0 : 1,
                        'time'  => $hasInputTime ? $inputTime : -1,
                    ]
                );
                break;
            case 'authSms':
                $componentManager = \App::load('Component\\Member\\Manager');
                $smsAuth = $componentManager->sendSmsAuthNumber($manager);
                $manager['authMethod'] = 'authSms';
                $manager['authNumber'] = $smsAuth['smsAuthNumber'];
                $manager['isSmsLogin'] = $smsAuth['message'];
                $session->set(\Component\Member\Manager::SESSION_MANAGER_LOGIN, $manager);
                $isSuccess = $manager['isSmsLogin'] == 'OK';
                // 인증번호 발송시 로그 작성
                if ($isSuccess) {
                    $request->post()->set('authTarget', $manager['cellPhone']);
                    $adminLog->setAdminLog();
                }
                $this->json(
                    [
                        'error'   => $isSuccess ? 0 : 1,
                        'message' => $isSuccess ? '인증번호가 발송되었습니다.' : $manager['isSmsLogin'],
                    ]
                );
                break;
            case 'authEmail':
                $mailMimeAuto = \App::load('Component\\Mail\\MailMimeAuto');
                $otp = \App::load('\\Framework\\Security\\Otp');
                $securityInfo['certificationCode'] = $otp->getOtp(8);
                $securityInfo['authType'] = 'authEmail';
                $securityInfo['email'] = $manager['email'];
                $result = $mailMimeAuto->init(\Component\Mail\MailMimeAuto::ADMIN_SECURITY, $securityInfo, DEFAULT_MALL_NUMBER)->autoSend();
                $manager['authMethod'] = 'authEmail';
                $manager['authNumber'] = $securityInfo['certificationCode'];
                $session->set(\Component\Member\Manager::SESSION_MANAGER_LOGIN, $manager);
                $isSuccess = $result === true;
                // 인증번호 발송시 로그 작성
                if ($isSuccess) {
                    $request->post()->set('authTarget', $manager['email']);
                    $adminLog->setAdminLog();
                }
                $this->json(
                    [
                        'error'   => $isSuccess ? 0 : 1,
                        'message' => $isSuccess ? '인증번호전송성공' : '메일 발송 중 오류가 발생하였습니다.',
                    ]
                );
                break;
            case 'checkSmsNumber':
                if ($cookie->get('CAPTCHA_RETRY_' . strtoupper($manager['managerId'])) > 4) {
                    $captchaNumber = strtoupper($request->post()->get('capchaNumber'));
                    //자동입력 방지문구 체크
                    $captcha = new Captcha();
                    $rst = $captcha->verify($captchaNumber, 1);
                    if ($rst['code'] != '0000') {
                        $this->json(
                            [
                                'error'   => 1,
                                'message' => '자동등록 방지문자가 맞지 않습니다.',
                            ]
                        );
                    }
                }

                if (trim($request->post()->get('smsAuthNumber')) == $manager['authNumber']) {
                    if ($session->get(\Component\Excel\ExcelForm::SESSION_SECURITY_AUTH . '.requireExcelAuthSms', false)) {
                        $session->set(
                            \Component\Excel\ExcelForm::SESSION_SECURITY_AUTH, [
                                'hasExcelAuthSms'         => true,
                                'hasExcelAuthSmsDateTime' => DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now'),
                            ]
                        );
                    } elseif ($session->get(\Component\Excel\ExcelForm::SESSION_SECURITY_AUTH . '.requireExcelAuthEmail', false)) {
                        $session->set(
                            \Component\Excel\ExcelForm::SESSION_SECURITY_AUTH, [
                                'hasExcelAuthEmail'         => true,
                                'hasExcelAuthEmailDateTime' => DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now'),
                            ]
                        );
                    }
                    $cookie->del('CAPTCHA_RETRY_' . strtoupper($manager['managerId']));
                    if ($request->post()->get('subject') == 'crema') {
                        $targetName = '파일';
                    }
                    $this->json(
                        [
                            'error'   => 0,
                            'message' => $targetName . '다운로드가 승인되었습니다.',
                        ]
                    );
                } else {
                    $retry = $cookie->get('CAPTCHA_RETRY_' . strtoupper($manager['managerId'])) + 1;
                    $cookie->set('CAPTCHA_RETRY_' . strtoupper($manager['managerId']), $retry, 0);
                    $this->json(
                        [
                            'error'   => 1,
                            'message' => '관리자 인증번호가 맞지 않습니다.',
                            'retry'   => $retry,
                        ]
                    );
                }
                break;
            default:
                $logger->info('not found mode');
        }
    }
}
