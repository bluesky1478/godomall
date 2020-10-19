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

namespace Bundle\Controller\Front\Member;

use Component\Attendance\AttendanceCheckLogin;
use Component\Member\Exception\LoginException;
use Component\Member\Util\MemberUtil;
use Component\SiteLink\SiteLink;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Object\SimpleStorage;
use Component\Godo\GodoKakaoServerApi;
use Validation;
/**
 * Class LoginPsController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class LoginPsController extends \Controller\Front\Controller
{
    const MODE_LOGIN = 'login';

    public function index()
    {
        // 마이앱 로그인 스크립트
        $myappInfo = gd_policy('myapp.config');
        if (\Request::isMyapp() && empty($myappInfo['builder_auth']['clientId']) === false && empty($myappInfo['builder_auth']['secretKey']) === false) {
            $myappLogin = true;
        }

        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');
        try {
            //카카오 계정탈퇴 혹은 연결해제시 카카오에서 연결끊기 콜백 url 받아 카카오 연결 정보 삭제
            $kakaoUserId = $request->post()->get('user_id');
            $kakaoReferrerType = $request->post()->get('referrer_type');
            if($kakaoUserId && $kakaoReferrerType){
                $logger->channel('kakaoLogin')->info(__METHOD__.' unregister kakao member - id/referrer_type:',$kakaoUserId."/".$kakaoReferrerType);
                $kakaoApi = new GodoKakaoServerApi();
                $kakaoApi->unRegisterKakaoId($kakaoUserId, $kakaoReferrerType);
                exit();
            }

            /** @var \Bundle\Component\Member\Member $member */
            $member = \App::load('\\Component\\Member\\Member');

            $returnUrl = urldecode(MemberUtil::getLoginReturnURL());

            // returnUrl 데이타 타입 체크
            try {
                Validation::setExitType('throw');
                Validation::defaultCheck(gd_isset($returnUrl), 'url');
            } catch (\Exception $e) {
                $returnUrl = '/';
            }

            $siteLink = new SiteLink();
            $returnUrl = $siteLink->link($returnUrl);

            $memId = $request->post()->get('loginId');
            $memPw = $request->post()->get('loginPwd');
            $member->login($memId, $memPw);
            $storage = new SimpleStorage($request->post()->all());
            MemberUtil::saveCookieByLogin($storage);
            $directMsg = 'parent.location.href=\'' . $returnUrl . '\'';
            if (MemberUtil::isLogin()) {
                try {
                    \DB::begin_tran();
                    $check = new AttendanceCheckLogin();
                    $message = $check->attendanceLogin();
                    \DB::commit();
                    $msg = 'var msg=\'' . $message . '\'; alert(\'' . $message . '\');parent.location.href=\'' . $returnUrl . '\';';
                    // 에이스 카운터 로그인 스크립트
                    $acecounterScript = \App::load('\\Component\\Nhn\\AcecounterCommonScript');
                    $acecounterUse = $acecounterScript->getAcecounterUseCheck();
                    if($acecounterUse) {
                        $returnScript = $acecounterScript->getLoginScript();
                        echo $returnScript;
                        $msg = 'setTimeout(function(){ var msg=\''. $message. '\'; alert(\'' . $message . '\'); parent.location.href=\''. $returnUrl . '\';}, 200);';
                        $directMsg = 'setTimeout(function(){ parent.location.href=\'' . $returnUrl . '\'; }, 200);';
                    }

                    if ($message) {
                        $this->js($msg);
                    }
                } catch (\Exception $e) {
                    \DB::rollback();
                    $logger->info(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage());
                }
            }
            $this->js($directMsg);
        } catch (LoginException $e) {
            $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            //성인인증, 회원전용 인트로 로그인 알림창 출력
            if ($request->isAjax() === true) {
                $this->json($this->exceptionToArray($e));
            } elseif ($myappLogin === true && $request->post()->get('code') != null) {
                $myapp = \App::load('Component\\Myapp\\Myapp');
                throw new AlertRedirectException($e->getMessage(), $myapp::APP_LOGIN_ERROR_CODE, null, '/');
            } else { // 일반 회원로그인 알림창 출력
                throw new AlertOnlyException($e->getMessage(), $e->getCode(), $e);
            }
        } catch (AlertRedirectException $e) {

            $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            throw new AlertRedirectException($e->getMessage(), $e->getCode(), $e, $e->getUrl(), 'parent');
            // 무료보안서버 크로스 도메인 문제로 해당 페이지 내에 문구로 안내하는 방식에서 알럿 방식으로 변경
            // $this->js('parent.login_fail(\'' . $e->getUrl() . '\', \'' . $e->getMessage() . '\')');
        } catch (\Exception $e) {
            $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            if ($request->isAjax() === true) {
                $this->json($this->exceptionToArray($e));
            } elseif ($myappLogin === true && $request->post()->get('code') != null) {
                $myapp = \App::load('Component\\Myapp\\Myapp');
                throw new AlertRedirectException($e->getMessage(), $myapp::APP_LOGIN_ERROR_CODE, null, '/');
            } else {
                if ($e->getCode() === 500) {
                    // 무료보안서버 크로스 도메인 문제로 해당 페이지 내에 문구로 안내하는 방식에서 알럿 방식으로 변경
                    throw new AlertOnlyException($e->getMessage());
                    // $this->js('parent.login_fail(\'\', \'' . $e->getMessage() . '\')');
                }
            }
        }
    }
}