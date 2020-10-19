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


use Component\Sms\Sms;
use Framework\Utility\StringUtils;

/**
 * Class LayerExcelAuthController
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class LayerExcelAuthController extends \Controller\Admin\Controller
{
    public function index()
    {
        $logger = \App::getInstance('logger');
        $logger->info(__CLASS__);
        $cookie = \App::getInstance('cookie');
        $session = \App::getInstance('session');
        $manager = $session->get(\Component\Member\Manager::SESSION_MANAGER_LOGIN);
        $auth = $session->get(\Component\Excel\ExcelForm::SESSION_SECURITY_AUTH);
        StringUtils::strIsSet($auth['requireExcelAuthSmsGodo'], false);
        StringUtils::strIsSet($auth['requireExcelAuthSms'], false);
        StringUtils::strIsSet($auth['requireExcelAuthEmail'], false);
        $securitySelect = [];
        $authInformation = [
            'email'     => '',
            'cellPhone' => '',
        ];
        $isExcelAuthSms = ($auth['requireExcelAuthSms']) && $manager['isSmsAuth'] == 'y' && $manager['cellPhone'];
        $isExcelAuthEmail = $auth['requireExcelAuthEmail'] && $manager['isEmailAuth'] == 'y' && $manager['email'];
        $hasSmsPoint = (Sms::getPoint() >= 1);
        $getValue = \Request::get()->all();
        if ($isExcelAuthSms) {
            if ($hasSmsPoint) {
                $securitySelect['authSms'] = '휴대폰';
                $phoneArr = explode('-', $manager['cellPhone']);
                $phoneLen = strlen($phoneArr[1]);
                $s = '';
                for ($i = 1; $i <= $phoneLen; $i++) {
                    $s .= '*';
                }
                $phoneArr[1] = $s;
                $phoneArr[2] = '**' . substr($phoneArr[2], 2, 2);
                $authInformation['cellPhone'] = implode('-', $phoneArr);
            } else {
                if ($isExcelAuthEmail) {
                    $this->setData('message', __('잔여 SMS포인트가 부족하여 이메일로 인증을 진행합니다.'));
                } else {
                    $this->setData('superAlertMessage', __('SMS 잔여 포인트가 부족하여 SMS 인증을 할 수 없습니다. 포인트를 충전해주시기 바랍니다.'));
                }
            }
        }
        if ($auth['requireExcelAuthSmsGodo']) {
            $securitySelect['authSmsGodo'] = '고도회원 휴대폰번호';
        }
        if ($isExcelAuthEmail) {
            $securitySelect['authEmail'] = '이메일';
            $emailArr = explode('@', $manager['email']);
            $s = '';
            for ($i = 2; $i < strlen($emailArr[0]); $i++) {
                $s .= '*';
            }
            $emailArr[0] = substr($emailArr[0], 0, 2) . $s;
            $emailDomainArr = explode('.', $emailArr[1]);
            $d = '';
            for ($i = 2; $i < strlen($emailDomainArr[0]); $i++) {
                $d .= '*';
            }
            $emailDomainArr[0] = substr($emailDomainArr[0], 0, 2) . $d;
            $authInformation['email'] = $emailArr[0] . '@' . implode('.', $emailDomainArr);
        }
        $retry = $cookie->get('CAPTCHA_RETRY_' . strtoupper($manager['managerId']), 1);
        $this->setData('cellPhone', $authInformation['cellPhone']);
        $this->setData('email', $authInformation['email']);
        $this->setData('securitySelect', $securitySelect);
        $this->setData('retry', $retry);
        $this->setData('getValue', gd_isset($getValue, ''));
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setPageName('share/layer_excel_auth.php');
    }
}
