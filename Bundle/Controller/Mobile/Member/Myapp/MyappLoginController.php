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

namespace Bundle\Controller\Mobile\Member\Myapp;

use Component\Attendance\AttendanceCheckLogin;
use Component\Member\Util\MemberUtil;
use Component\SiteLink\SiteLink;
use Bundle\Component\Policy\SnsLoginPolicy;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertOnlyException;
use Exception;

/**
 * Class LoginController
 * @package Bundle\Controller\Mobile\Member
 * @author  Jongchan Na
 */
class MyappLoginController extends \Controller\Mobile\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $request = \App::getInstance('request');
        $myapplogger = \App::getInstance('logger')->channel('myapp');
        $myapp = \App::load('Component\\Myapp\\Myapp');
        $myappInfo = gd_policy('myapp.config');
        if ($request->isMyapp() && empty($myappInfo['builder_auth']['clientId']) === false && empty($myappInfo['builder_auth']['secretKey']) === false) {
            $myappLogin = true;
        } else {
            $myappLogin = false;
        }
        try {
            if (MemberUtil::isLogin()) {
                MemberUtil::logoutWithCookie();
            }
            if ($myappLogin === false) {
                throw new Exception("올바른 로그인 경로가 아닙니다.");
            }

            // 페이스북 로그인 처리
            if($request->get()->get('socialLogin') == 'facebook') {
                // 페이스북 로그인 url
                $facebookLoginPolicy = new SnsLoginPolicy();
                $facebookLoginUseFl = $facebookLoginPolicy->getSnsLoginUse()['facebook'];

                if ($facebookLoginUseFl == 'y') {
                    $snsLoginPolicy = new SnsLoginPolicy();

                    $facebook = \App::load('Bundle\\Component\\Facebook\\Facebook');
                    $useFacebook = $snsLoginPolicy->useFacebook();

                    if ($request->request()->has('returnUrl')) {
                        $returnUrl = $request->getReturnUrl();
                    } else {
                        $returnUrl = urlencode(URI_MOBILE);
                    }

                    if ($useFacebook) {
                        if ($snsLoginPolicy->useGodoAppId()) {
                            $facebookLogin = $facebook->getGodoLoginUrl($returnUrl);
                        } else {
                            $facebookLogin = $facebook->getLoginUrl($returnUrl);
                        }
                        throw new AlertRedirectException(null, 456, null, $facebookLogin);
                    }
                }
            }

            // 로그인 jwt 처리
            $serverAuthCode = $request->post()->get('code');
            if ($serverAuthCode) {
                $memberInfo = $myapp->checkAuthCode($serverAuthCode);

                if (empty($memberInfo) === false && $memberInfo['error'] == null) {
                    $request->post()->set('loginId', $memberInfo['loginId']);
                    $request->post()->set('loginPwd', $memberInfo['loginPwd']);
                } elseif ($memberInfo['error'] != null) {
                    throw new AlertRedirectException($memberInfo['error'], $myapp::APP_LOGIN_ERROR_CODE, null, '/');
                }
                else {
                    throw new Exception("일시적 서버에러입니다.\n잠시후 다시 시도해주세요.");
                }
            }

            // 마이앱 솔루션 자동로그인
            if ($request->post()->get('saveAutoLogin') == 'y' && $myappLogin === true && $myappInfo['useQuickLogin'] != 'true') {
                $myapp = \App::load('Component\\Myapp\\Myapp');
                $authcode = $myapp->getAuthCode();
                $refresh = $myapp->getRefreshCode($authcode);
                if (!$refresh) {
                    throw new AlertRedirectException("로그인 토큰이 만료되었습니다.\n다시 시도해주세요.", $myapp::APP_LOGIN_ERROR_CODE, null, '/');
                }
                \Session::set('refresh', $refresh);
                if ($serverAuthCode) {
                    $request->post()->set('saveAutoLogin', 'n');
                }
            }

            $front = \App::load('\\Controller\\Front\\Member\\LoginPsController');
            $front->index();

        } catch (Exception $e) {
            $myapplogger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            if($e->getCode() == 456) {
                throw new AlertRedirectException($e->getMessage(), null, null, $facebookLogin);
            } else if($e->getCode() == $myapp::APP_LOGIN_ERROR_CODE) {
                $bridge = $myapp->getAppBridgeScript('initLoginInfo');
                echo $bridge;
                throw new AlertRedirectException($e->getMessage(), null, null, '/');
            } else {
                throw new AlertOnlyException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}