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

use App;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\LayerException;
use Logger;
use Message;
use Request;
use Session;
use Component\Mail\MailMimeAuto;
use Component\Member\Manager;
use Vendor\Captcha\Captcha;

class ScreenSecurityPsController extends \Controller\Admin\Controller
{
    /** 화면보안인증 정보 세션키 */
    const SESSION_SCREEN_INFO = 'SESSION_SCREEN_INFO';

    /**
     * {@inheritdoc}
     *
     * @throws AlertRedirectException
     * @throws LayerException
     * @author Lee Hun <akari2414@godo.co.kr>
     */
    public function index()
    {
        try {
            /** @var \Bundle\Component\Member\Manager $manager */
            $manager = App::getInstance('Manager');

            // back history시 아이프레임이 아닌 경우가 발생한다. 이에 request 값이 없는 경우 관리자 메인으로 이동 처리
            if (!Request::request()->has('mode')) {
                throw new AlertRedirectException(null, null, null, '/base/index.php');
            }

            // 모드값에 따른 처리
            switch (Request::request()->get('mode')) {
                // 인증번호 체크
                case 'checkSecurityNumber':
                    // 임시 세션 정보
                    $managerInfo = Session::get('manager');
                    gd_isset($managerInfo['captchaRetry'], '1');

                    $captchaNumber = strtoupper(trim(Request::post()->get('capchaNumber')));
                    if($captchaNumber) {
                        //자동입력 방지문구 체크
                        $captcha = new Captcha();
                        $rst = $captcha->verify($captchaNumber, 1);
                        if ($rst['code'] != '0000') {
                            $message = '자동등록 방지문자가 맞지 않습니다.';
                            throw new AlertRedirectException($message, null, null, \Request::getReferer(), 'top');
                        }
                    }

                    $sessionScreenInfo = Session::get(self::SESSION_SCREEN_INFO);

                    $checkCode = $sessionScreenInfo['certificationCode'];//이메일 인증번호
                    if($checkCode == '') $checkCode = $sessionScreenInfo['smsAuthNumber'];//휴대폰 인증번호

                    $authNumber = trim(Request::post()->get('authNumber'));
                    if($authNumber == '' || $checkCode == '') {
                        $message = '관리자 인증번호가 맞지 않습니다.';
                        Session::set('manager.captchaRetry', ($managerInfo['captchaRetry'] + 1));
                        throw new AlertRedirectException($message, null, null, \Request::getReferer(), 'top');
                    }

                    if ($authNumber == $checkCode) {
                        $parseReferer = Request::getParserReferer();
                        $path = explode('/', substr(substr($parseReferer->path, 1, strlen($parseReferer->path)), 0, -4));

                        if($path[0] == 'provider') {
                            $fileName = explode('_', $path[2]);
                            $arraySessionKey = ['screen' . ucfirst($path[1])];
                        } else {
                            $fileName = explode('_', $path[1]);
                            $arraySessionKey = ['screen' . ucfirst($path[0])];
                        }
                        foreach($fileName as $text) {
                            $arraySessionKey[] = ucfirst($text);
                        }
                        $arraySessionKey[] = 'Fl';
                        $sessionKey = implode('', $arraySessionKey);

                        Session::set('manager.'.$sessionKey, 'y');
                        Session::del('manager.captchaRetry');

                        $addScript = 'top.location.href="' . Request::getReferer() . '"';
                        throw new LayerException(__('처리중 입니다. 잠시만 기다려 주세요.'), null, null, gd_isset($addScript), 1000, true);
                    } else {
                        $message = '관리자 인증번호가 맞지 않습니다.';
                        Session::set('manager.captchaRetry', ($managerInfo['captchaRetry'] + 1));
                        throw new AlertRedirectException($message, null, null, \Request::getReferer(), 'top');
                    }
                    break;

                // sms 인증번호 발송
                case 'smsSend':
                    // 임시 세션 정보
                    $manager = App::load('\\Component\\Member\\Manager');
                    $managerInfo = $manager->getManagerInfo(Session::get('manager.sno'));
                    $smsAuth = $manager->sendSmsAuthNumber($managerInfo);

                    if ($smsAuth['message'] == 'OK') {
                        $retry = Session::get('Manager.authRetry');
                        if(empty($retry)) $retry = 1;
                        else $retry++;

                        Session::set('Manager.authRetry', $retry);
                        Session::set(self::SESSION_SCREEN_INFO, $smsAuth);

                        $this->json(
                            [
                                'error'   => 0,
                                'message' => __('인증번호전송성공'),
                            ]
                        );
                    } else {
                        if($smsAuth['message'] == 'SMS Point Fail') {
                            if(Session::get('manager.isEmailAuth') != 'y') {
                                $this->json(
                                    [
                                        'error' => 3,
                                        'message' => sprintf(__('SMS 포인트가 소진되어 인증수단이 이메일로 자동 전환됩니다.%1$s 이메일 인증정보가 등록되지 않아 본 화면에 접속할 수 없으니 대표운영자에게 문의 바랍니다.'), '<br>'),
                                    ]
                                );

                                Session::set('manager.screenSecurityAuthFail', 'y');
                            } else {
                                $this->json(
                                    [
                                        'error' => 1,
                                        'message' => $smsAuth['message'],
                                    ]
                                );
                            }
                        } else {
                            $this->json(
                                [
                                    'error' => 1,
                                    'message' => $smsAuth['message'],
                                ]
                            );
                        }
                    }
                    break;

                // email 인증번호 발송
                case 'emailSend' :
                    $manager = App::load('\\Component\\Member\\Manager');
                    $managerInfo = $manager->getManagerInfo(Session::get('manager.sno'));

                    if($managerInfo['isEmailAuth'] != 'y') {
                        $this->json(
                            [
                                'error' => 3,
                                'message' => __('인증정보가 등록되지 않아 본 화면에 접속할 수 없으니 대표운영자에게 문의 바랍니다.'),
                            ]
                        );

                        Session::set('manager.screenSecurityAuthFail', 'y');
                    } else {
                        $mailMimeAuto = App::load('\\Component\\Mail\\MailMimeAuto');
                        $otp = App::load('\\Framework\\Security\\Otp');
                        $securityInfo['certificationCode'] = $otp->getOtp(8);
                        $securityInfo['authType'] = 'authEmail';
                        $securityInfo['email'] = $managerInfo['email'];

                        Session::set(self::SESSION_SCREEN_INFO, $securityInfo);

                        $result = $mailMimeAuto->init(MailMimeAuto::ADMIN_SECURITY, $securityInfo, DEFAULT_MALL_NUMBER)->autoSend();
                        if ($result === true) {

                            Session::set(self::SESSION_SCREEN_INFO, $securityInfo);

                            $this->json(
                                [
                                    'error' => 0,
                                    'message' => __('인증번호전송성공'),
                                ]
                            );
                        } else {
                            $this->json(
                                [
                                    'error' => 2,
                                    'message' => __('메일 발송 중 오류가 발생하였습니다.'),
                                ]
                            );
                        }
                    }
                    break;

                case 'getCaptcha' ://자동입력방지
                    $captcha = new CaptchaModule();
                    $captcha->output('', '');
                    break;
            }

        } catch (AlertRedirectException $e) {
            throw $e;
        } catch (LayerException $e) {
            throw $e;
        }
    }
}
