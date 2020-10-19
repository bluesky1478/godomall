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

namespace Bundle\Controller\Front\Member;

use Component\Mail\MailMimeAuto;
use Component\Member\Member;
use Component\Member\MemberSleep;
use Component\Sms\Code;
use Component\Sms\SmsAutoCode;
use Component\Validator\Validator;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Security\Otp;
use Framework\Utility\UrlUtils;
use Globals;
use Request;
use Session;

/**
 * Class WakePsController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class WakePsController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        $logger = \App::getInstance('logger');
        $session = \App::getInstance('session');
        $request = \App::getInstance('request');
        $memberSleep = \App::getInstance('MemberSleep');
        $coupon = \App::load('\\Component\\Coupon\\Coupon');
        if (!$session->has(MemberSleep::SESSION_WAKE_INFO)) {
            throw new Exception(__('휴면회원 해제에 필요한 정보를 찾을 수 없습니다.'));
        }
        $wakeInfo = $session->get(MemberSleep::SESSION_WAKE_INFO);
        if (!is_object($memberSleep)) {
            $memberSleep = new \Component\Member\MemberSleep();
        }
        try {
            switch ($request->post()->get('mode')) {
                case 'normal':
                    $memberData = $memberSleep->wake($wakeInfo['sleepNo']);
                    $coupon->setAutoCouponMemberSave('wake', $wakeInfo['memNo'], $memberData[0]['groupSno']);
                    $this->json('../member/wake_complete.php');
                    break;
                case 'info':
                    $selectWakeInfo = $request->post()->get('selectWakeInfo');
                    $wakeCheckInfo = $request->post()->get('wakeCheckInfo');
                    if (!Validator::required($wakeCheckInfo)) {
                        throw new Exception(__('가입 시 입력하신 회원정보를 입력해 주시기 바랍니다.'));
                    }
                    $checkEmail = $selectWakeInfo == 'email' && $wakeInfo['email'] == $wakeCheckInfo;
                    $checkCellPhone = $selectWakeInfo == 'cellPhone' && str_replace('-', '', $wakeInfo['cellPhone']) == $wakeCheckInfo;
                    if ($checkEmail || $checkCellPhone) {
                        $memberData = $memberSleep->wake($wakeInfo['sleepNo']);
                        $coupon->setAutoCouponMemberSave('wake', $wakeInfo['memNo'], $memberData[0]['groupSno']);
                        $this->json('../member/wake_complete.php');
                    } else {
                        throw new Exception(__('입력하신 정보와 일치하지 않습니다.'));
                    }
                    break;
                case 'authEmail':
                    $mailMimeAuto = \App::getInstance('MailMimeAuto');
                    if (!is_object($mailMimeAuto)) {
                        $mailMimeAuto = new \Component\Mail\MailMimeAuto();
                    }
                    $otp = \App::load('\\Framework\\Security\\Otp');
                    $wakeInfo['certificationCode'] = $otp->getOtp(8);
                    $wakeInfo['authType'] = 'authEmail';
                    $result = $mailMimeAuto->init(MailMimeAuto::WAKE_MEMBER, $wakeInfo)->autoSend();
                    if ($result !== true) {
                        throw new AlertOnlyException(__('메일 발송 중 오류가 발생하였습니다.'));
                    }
                    $session->set(MemberSleep::SESSION_WAKE_INFO, $wakeInfo);
                    $this->json('../member/wake_certification.php?authType=authEmail');
                    break;
                // 휴면회원해제 인증번호 발송
                case 'authSms':
                    $sms = \App::getInstance('Sms');
                    if (!is_object($sms)) {
                        $sms = new \Component\Sms\Sms();
                    }
                    // 인증번호 생성
                    $wakeInfo['certificationCode'] = Otp::getOtp(8);
                    $logger->debug(__METHOD__, $wakeInfo);
                    $aBasicInfo = gd_policy('basic.info');
                    $smsResult = $sms->smsAutoSend(SmsAutoCode::MEMBER, Code::SLEEP_AUTH, str_replace('-', '', $wakeInfo['cellPhone']), ['rc_certificationCode' => $wakeInfo['certificationCode'], 'rc_mallNm' => Globals::get('gMall.mallNm'), 'shopUrl' => $aBasicInfo['mallDomain']]);
                    if ($smsResult[0]['success'] !== 1) {
                        throw new Exception(__('발송이 실패하였습니다.'));
                    }
                    $wakeInfo['authType'] = 'authSms';
                    $session->set(MemberSleep::SESSION_WAKE_INFO, $wakeInfo);
                    $this->json('../member/wake_certification.php?authType=authSms');
                    break;
                // 휴면회원해제 인증번호 확인
                case 'certificationAuthNumber':
                    if ($session->has(MemberSleep::SESSION_WAKE_INFO) === false) {
                        throw new AlertRedirectException(__('본인인증 필수 값이 없습니다. 다시 시도하세요.'), null, null, '../member/login.php');
                    }
                    $wakeInfo = $session->get(MemberSleep::SESSION_WAKE_INFO);
                    $inputCertify = $request->post()->get('number', -1);

                    // 인증번호 비교
                    if ($wakeInfo['certificationCode'] != $inputCertify) {
                        throw new Exception(__("인증번호가 틀렸습니다."));
                    }
                    $logger->debug(__METHOD__, $wakeInfo);
                    $memberData = $memberSleep->wake($wakeInfo['sleepNo']);
                    $coupon->setAutoCouponMemberSave('wake', $wakeInfo['memNo'], $memberData[0]['groupSno']);
                    unset($wakeInfo['certificationCode']);
                    $this->json('../member/wake_complete.php');
                    break;
                case 'wakeMember':
                    // 휴면회원 해제(아이핀, 드림시큐리티)
                    $wakeInfo = $session->get(MemberSleep::SESSION_WAKE_INFO);
                    $memberData = $memberSleep->wake($wakeInfo['sleepNo']);
                    $coupon->setAutoCouponMemberSave('wake', $wakeInfo['memNo'], $memberData[0]['groupSno']);
                    unset($wakeInfo['certificationCode']);
                    $this->json(__('휴면회원 해제가 완료되었습니다.'));
                    break;
                default:
                    $session->del(Member::SESSION_IPIN);
                    $session->del(Member::SESSION_DREAM_SECURITY);
                    $session->del(MemberSleep::SESSION_WAKE_INFO);
                    throw new Exception(__('요청을 처리할 페이지를 찾을 수 없습니다.'), 404);
                    break;
            }
        } catch (Exception $e) {
            $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            if ($request->isAjax() === true) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new AlertOnlyException($e->getMessage());
            }
        }
    }
}
