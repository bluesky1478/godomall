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


use Component\Sms\Exception\PasswordException;
use Component\Sms\SmsSender;
use Framework\Utility\ComponentUtils;
use Vendor\Captcha\Captcha;

class LayerSmsPasswordPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $isChangePassword = $request->post()->get('mode', 'changePassword') === 'changePassword';
        if ($isChangePassword) {
            $captchaNumber = $request->post()->get('captcha');
            $captcha = new Captcha();
            $result = $captcha->verify($captchaNumber, 1);
            if ($result['code'] !== '0000') {
                $this->json(
                    [
                        'error'   => 1,
                        'message' => '자동등록 방지문자가 맞지 않습니다.',
                    ]
                );
            }
        }
        $smsPassword = $request->post()->get('password');
        $smsSender = \App::load(SmsSender::class);
        try {
            $smsSender->validPassword($smsPassword, !$isChangePassword);
            $encryptor = \App::getInstance('encryptor');
            $policy = ComponentUtils::getPolicy('sms.config');
            if ($isChangePassword) {
                $policy['authentication'] = [
                    'failLog'  => [],
                    'failCnt'  => 0,
                    'password' => $encryptor->encrypt($smsPassword),
                ];
            } else {
                $policy['authentication']['failLog'] = [];
                $policy['authentication']['failCnt'] = 0;
            }
            ComponentUtils::setPolicy('sms.config', $policy);
            $response = [
                'error'   => 0,
                'message' => 'SMS 인증번호가 변경되었습니다.',
            ];
            $this->json($response);
        } catch (PasswordException $e) {
            $message = $e->getMessage();
            if ($isChangePassword) {
                $message = 'SMS 인증번호는 영문대문자/영문소문자/숫자/특수문자 중 2가지 이상 조합, 10~16자리로만 설정할 수 있습니다.';
                $message .= '<br/>[마이고도 > 쇼핑몰 관리]에서 SMS 인증번호를 수정 후 다시 시도해주세요.';
            }

            $this->json(
                [
                    'error'   => $e->getCode(),
                    'message' => $message,
                ]
            );
        }
    }
}
