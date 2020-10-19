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

namespace Bundle\Controller\Admin\Base;

use Component\Member\Manager;
use Component\Policy\ManageSecurityPolicy;
use Component\Sms\Exception\PasswordException;
use Component\Sms\Sms;
use Component\Sms\SmsMessage;
use Component\Sms\SmsSender;
use Component\Validator\Validator;
use Framework\Debug\Exception\AlertReloadException;

/**
 * Class LoginController
 *
 * @package Bundle\Controller\Admin\Base
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerSmsAuthController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        $emptyAuthMethod = $emptySmsPoint = false;
        $session = \App::getInstance('session');
        if (!$session->has(Manager::SESSION_TEMP_MANAGER)) {
            throw new AlertReloadException('인증에 필요한 정보가 없습니다.');
        }

        $manager = $session->get(Manager::SESSION_TEMP_MANAGER);
        unset($manager['hasEmailChangeAuthorize'], $manager['hasCellPhoneChangeAuthorize']);
        $isSmsAuth = $manager['isSmsAuth'] === 'y' && Validator::required($manager['cellPhone']);
        $isEmailAuth = $manager['isEmailAuth'] === 'y' && Validator::required($manager['email']);
        $isCsAuth = array_key_exists('csSno', $manager) && $manager['csSno'] > 0;
        $isInvalidPassword = false;
        $smsPoint = Sms::getPoint(true);
        $hasSmsPoint = ($smsPoint >= 1);

        $email = $cellPhone = '';

        $securitySelect = \App::load(ManageSecurityPolicy::class)->getLoginSecuritySelect();

        if (array_key_exists('smsReSend', $securitySelect) && !$hasSmsPoint) {
            try {
                $sender = \App::load(SmsSender::class);
                $sender->setIsThrowPasswordException(true);
                $sender->setMessage(new SmsMessage('SMS 인증번호 검증'));
                $receiver = [
                    [
                        'memNo'     => '0',
                        'cellPhone' => '00000000000',
                    ],
                ];
                $sender->setReceiver($receiver);
                $sender->setSmsType('guest');
                $sender->setLogData(['disableResend' => true]);
                $sender->validPassword(\App::load(\Component\Sms\SmsUtil::class)->getPassword());
                $sender->setSmsPoint(1);
                $sender->send();
            } catch (PasswordException $e) {
                if (array_key_exists('emailSend', $securitySelect)) {
                    unset($securitySelect['smsReSend']);
                } else {
                    $isInvalidPassword = true;
                }
            }
        }

        $countSelect = \count($securitySelect);

        if ($isCsAuth) {
            $emptyAuthMethod = true;
            $securitySelect = ['emailSend' => '이메일'];
        } elseif ($countSelect === 2) {
            if ($isSmsAuth && !$isEmailAuth && !$hasSmsPoint) {
                unset($securitySelect['smsReSend']);
                $emptySmsPoint = true;
            } elseif (!$isSmsAuth && !$isEmailAuth) {
                $securitySelect = [];
            } elseif ($isSmsAuth && !$isEmailAuth) {
                unset($securitySelect['emailSend']);
            } elseif ($isEmailAuth && (!$isSmsAuth || !$hasSmsPoint)) {
                unset($securitySelect['smsReSend']);
            }
        } elseif (array_key_exists('smsReSend', $securitySelect) && $countSelect === 1) {
            if (!$hasSmsPoint) {
                $emptySmsPoint = true;
            } elseif (!$isSmsAuth && $hasSmsPoint) {
                unset($securitySelect['smsReSend']);
            }
        } elseif (array_key_exists('emailSend', $securitySelect) && $countSelect === 1 && !$isEmailAuth) {
            unset($securitySelect['emailSend']);
        }

        if ($isSmsAuth && $hasSmsPoint) {
            $phoneArr = explode('-', $manager['cellPhone']);
            $phoneLen = \strlen($phoneArr[1]);
            $s = '';
            for ($i = 1; $i <= $phoneLen; $i++) {
                $s .= '*';
            }
            $phoneArr[1] = $s;
            $phoneArr[2] = '**' . substr($phoneArr[2], 2, 2);
            $cellPhone = implode('-', $phoneArr);
        }

        if ($isEmailAuth) {
            $emailArr = explode('@', $manager['email']);
            $s = '';
            $emailLength = \strlen($emailArr[0]);
            for ($i = 2; $i < $emailLength; $i++) {
                $s .= '*';
            }
            $emailArr[0] = substr($emailArr[0], 0, 2) . $s;

            $emailDomainArr = explode('.', $emailArr[1]);

            $d = '';
            $emailDomainLength = \strlen($emailDomainArr[0]);
            for ($i = 2; $i < $emailDomainLength; $i++) {
                $d .= '*';
            }
            $emailDomainArr[0] = substr($emailDomainArr[0], 0, 2) . $d;
            $email = $emailArr[0] . '@' . implode('.', $emailDomainArr);
        }

        $cookie = \App::getInstance('cookie');
        $retry = $cookie->get('CAPTCHA_RETRY_' . strtoupper($manager['managerId']));

        if ($retry === null) {
            $retry = 1;
        }

        $this->setData('isInvalidPassword', $isInvalidPassword);
        $this->setData('isCsAuth', $isCsAuth);
        $this->setData('cellPhone', $cellPhone);
        $this->setData('email', $email);
        $this->setData('securitySelect', $securitySelect);
        $this->setData('retry', $retry);
        $this->setData('emptySmsPoint', $emptySmsPoint);
        $this->setData('emptyAuthMethod', $emptyAuthMethod);

        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
