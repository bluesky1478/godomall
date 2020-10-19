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
namespace Bundle\Controller\Admin\Base;

use Exception;
use Globals;
use Request;
use Session;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\AlertRedirectException;


/**
 * Class LoginController
 *
 * @package Bundle\Controller\Admin\Base
 * @author  Lee Hun <akari2414@godo.co.kr>
 */
class LayerScreenSecurityController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            $screenSecurityType = Session::get('manager.screenSecurityType');
            if(empty($screenSecurityType)) {
                exit('잘못된 접근입니다.');
            }

            $manager = \App::load('\\Component\\Member\\Manager');
            $managerInfo = $manager->getManagerInfo(Session::get('manager.sno'));

            foreach($screenSecurityType as $type) {
                switch($type) {
                    case 'smsSend' :
                        //휴대폰번호 설정
                        $phoneArr = explode('-', $managerInfo['cellPhone']);
                        $phoneLen = strlen($phoneArr[1]);
                        $s = '';
                        for ($i = 1; $i <= $phoneLen; $i++) {
                            $s .= '*';
                        }
                        $phoneArr[1] = $s;
                        $phoneArr[2] = '**' . substr($phoneArr[2], 2, 2);
                        $cellPhone = implode('-', $phoneArr);

                        $securitySelect[$type] = __('휴대폰');
                        break;
                    case 'smsChangeEmailSend' :
                        throw new LayerException(__('잔여 SMS포인트가 부족하여 이메일로 인증을 진행합니다.'), null, null, null, 1000, true);

                    case 'emailSend':
                        $emailArr = explode('@', $managerInfo['email']);
                        $s = '';
                        for($i = 2; $i < strlen($emailArr[0]); $i++) {
                            $s .= '*';
                        }
                        $emailArr[0] = substr($emailArr[0], 0, 2).$s;

                        $emailDomainArr = explode('.', $emailArr[1]);

                        $d = '';
                        for($i = 2; $i < strlen($emailDomainArr[0]); $i++) {
                            $d .= '*';
                        }
                        $emailDomainArr[0] = substr($emailDomainArr[0], 0, 2).$d;
                        $email = $emailArr[0].'@'.implode('.', $emailDomainArr);
                        //이메일주소 설정

                        $securitySelect['emailSend'] = '이메일';
                        break;

                    case 'checkSmsCharge' :
                        $parseReferer = Request::getParserReferer();
                        $path = explode('/', substr(substr($parseReferer->path, 1, strlen($parseReferer->path)), 0, -4));
                        $fileName = explode('_', $path[1]);

                        $arraySessionKey = ['screen'.ucfirst($path[0])];
                        foreach($fileName as $text) {
                            $arraySessionKey[] = ucfirst($text);
                        }
                        $arraySessionKey[] = 'Fl';
                        $sessionKey = implode('', $arraySessionKey);
                        Session::set('manager.'.$sessionKey, 'y');

                        $superSmsMessage = __('잔여 SMS포인트가 부족하여 SMS인증으로 보안접속할 수 없습니다. SMS포인트를 충전하시겠습니까?');
                        break;
                }
            }

            krsort($securitySelect);

            $retry = Session::get('manager.captchaRetry');
            gd_isset($retry, '1');

            // --- 관리자 디자인 템플릿
            $this->setData('cellPhone', $cellPhone);
            $this->setData('email', $email);
            $this->setData('securitySelect', $securitySelect);
            $this->setData('retry', $retry);
            if($superSmsMessage) $this->setData('superSmsMessage', $superSmsMessage);
            $this->getView()->setDefine('layout', 'layout_layer.php');

            $this->getView()->setPageName('base/layer_screen_security.php');

        } catch (LayerException $e) {
            throw $e;
        } catch (AlertRedirectException $e) {
            throw $e;
        } catch (Exception $e) {
            Logger::warning($e->getMessage() . ', ' . $e->getFile() . ', ' . $e->getLine(), $e->getTrace());
            throw new LayerException(__('로그인 정보 오류로 접속 실패하였습니다.') . '<br>' . $e->getMessage(), null, null, null, 3000);
        }
    }
}
