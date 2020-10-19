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

namespace Bundle\Controller\Mobile\Member;


use App;
use Component\Mail\MailMimeAuto;
use Component\Member\Member;
use Component\Sms\Code;
use Component\Sms\SmsAutoCode;
use Exception;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertReloadException;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\UrlUtils;
use Logger;
use Globals;
use Request;
use Session;

/**
 * Class UserCertificationPsController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class UserCertificationPsController extends \Controller\Mobile\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            Logger::info(__METHOD__);
            $mode = Request::post()->get('mode');
            switch ($mode) {
                case 'requestAuth':
                    $authType = Request::post()->get('authType', '');
                    $userCertificationSession = Session::get(Member::SESSION_USER_CERTIFICATION);
                    if (ArrayUtils::isEmpty($userCertificationSession) === true) {
                        throw new Exception(__('본인인증에 필요한 정보를 찾을 수 없습니다.'));
                    }
                    switch ($authType) {
                        case 'authEmail':
                            if (ComponentUtils::isPasswordAuthEmail() !== true) {
                                throw new Exception(__("이메일 인증은 사용하지 않습니다."));
                            }

                            /** @var \Bundle\Component\Mail\MailMimeAuto $mailMimeAuto */
                            $mailMimeAuto = App::load('\\Component\\Mail\\MailMimeAuto');
                            /** @var \Framework\Security\Encryptor $encryptor */
                            $otp = App::load('\\Framework\\Security\\Otp');
                            $userCertificationSession['certificationCode'] = $otp->getOtp(8);
                            $userCertificationSession['authType'] = 'authEmail';
                            $userCertificationSession['limitTime'] = strtotime('+3 minutes');
                            $userCertificationSession['limitCount'] = 5;
                            if ($userCertificationSession['certificationType'] === 'find_password') {
                                $result = $mailMimeAuto->init(MailMimeAuto::FIND_PASSWORD, $userCertificationSession)->autoSend();
                                if ($result === false) {
                                    throw new AlertOnlyException(__('메일 발송 중 오류가 발생하였습니다.'));
                                }
                            } else {
                                throw new Exception(__('메일 발송 중 오류가 발생하였습니다.'));
                            }
                            Session::set(Member::SESSION_USER_CERTIFICATION, $userCertificationSession);
                            $this->json(__("메일이 발송되었습니다."));
                            break;
                        case 'authSms':
                            if (ComponentUtils::isPasswordAuthSms() === true) {
                                /** @var \Bundle\Component\Sms\Sms $sms */
                                $sms = \App::load('\\Component\\Sms\\Sms');
                                // 인증번호 생성
                                $otp = \App::load('\\Framework\\Security\\Otp');
                                $userCertificationSession['certificationCode'] = $otp->getOtp(8);
                                $userCertificationSession['limitCount'] = 10;
                                $aBasicInfo = gd_policy('basic.info');
                                $smsResult = $sms->smsAutoSend(SmsAutoCode::MEMBER, Code::PASS_AUTH, $userCertificationSession['cellPhone'], ['rc_certificationCode' => $userCertificationSession['certificationCode'], 'rc_mallNm' => Globals::get('gMall.mallNm'), 'shopUrl' => $aBasicInfo['mallDomain']]);
                                if ($smsResult[0]['success'] === 1) {
                                    $userCertificationSession['authType'] = 'authSms';
                                    $userCertificationSession['limitTime'] = strtotime('+3 minutes');
                                    Session::set(Member::SESSION_USER_CERTIFICATION, $userCertificationSession);
                                    $this->json(__("SMS가 발송되었습니다."));
                                }
                                throw new Exception(__('발송이 실패하였습니다.'));
                            }
                            throw new Exception(__("SMS 인증은 사용하지 않습니다."));
                            break;
                        default:
                            throw new Exception(__("인증수단을 선택해주세요."));
                            break;
                    }
                    break;
                case 'certificationFindPassword':
                    if (Session::has(Member::SESSION_USER_CERTIFICATION) === false) {
                        throw new AlertRedirectException(__('본인인증 필수 값이 없습니다. 다시 시도하세요.'), null, null, '../member/find_password.php');
                    }
                    $userCertificationSession = Session::get(Member::SESSION_USER_CERTIFICATION);
                    if ($userCertificationSession['limitTime'] < time()) {
                        $this->json(
                            [
                                'code'   => 100,
                                'message' => __('인증시간이 만료되었습니다. 인증번호 다시받기를 눌러주세요.'),
                            ]
                        );
                    }
                    $inputCertify = Request::post()->get('inputCertify', -1);
                    // 인증번호 비교
                    if ($userCertificationSession['certificationCode'] === $inputCertify) {
                        unset($userCertificationSession['certificationCode']);
                        Session::set('certificationFindPassword', 'y'); // 보안 취약점 개선요청 사항
                        $this->json(__('인증되었습니다.'));
                    }

                    // 인증번호 sms(10회) email(5회) 실패시
                    $userCertificationSession['isManagerLoginLimit'] = $userCertificationSession['isManagerLoginLimit'] + 1;
                    Session::set(Member::SESSION_USER_CERTIFICATION, $userCertificationSession);

                    if ($userCertificationSession['isManagerLoginLimit'] > $userCertificationSession['limitCount']) {
                        throw new AlertRedirectException(__('본인인증을 ' . $userCertificationSession['limitCount'] . ' 회 이상 실패하였습니다. 다시 시도하세요.'), null, null, '../member/find_password.php');
                    }
                    throw new Exception(__("인증번호가 틀렸습니다."));
                    break;
                case 'certificationWakeMember':
                    if (Session::has(Member::SESSION_USER_CERTIFICATION) === false) {
                        throw new AlertRedirectException(__('본인인증 필수 값이 없습니다. 다시 시도하세요.'), null, null, '../member/login.php');
                    }
                    /** @see \Bundle\Component\Member\Member $userCertificationSession */
                    $userCertificationSession = Session::get(Member::SESSION_USER_CERTIFICATION);
                    $inputCertify = Request::post()->get('inputCertify', -1);
                    // 인증번호 비교
                    if ($userCertificationSession['certificationCode'] === $inputCertify) {
                        /** @var \Bundle\Component\Member\MemberSleep $memberSleep */
                        $memberSleep = App::load('\\Component\\Member\\MemberSleep');
                        $memberSleep->wakeUp($userCertificationSession);
                        unset($userCertificationSession['certificationCode']);
                        $this->json(__('인증되었습니다.'));
                    }
                    throw new Exception(__("인증번호가 틀렸습니다."));
                    break;
                default:
                    throw new Exception(__("요청을 찾을 수 없습니다."));
                    break;
            }
        } catch (AlertReloadException $e) {
            throw $e;
        } catch (\Exception $e) {
            if (Request::isAjax()) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new AlertCloseException($e->getMessage());
            }
        }
    }
}
