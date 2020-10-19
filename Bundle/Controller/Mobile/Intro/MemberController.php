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
namespace Bundle\Controller\Mobile\Intro;

use Bundle\Component\Godo\GodoNaverServerApi;
use Bundle\Component\Godo\GodoWonderServerApi;
use Bundle\Component\Policy\NaverLoginPolicy;
use Bundle\Component\Policy\KakaoLoginPolicy;
use Bundle\Component\Policy\WonderLoginPolicy;
use Component\Facebook\Facebook;
use Component\Member\Util\MemberUtil;
use Component\Policy\PaycoLoginPolicy;
use Component\Policy\SnsLoginPolicy;
use Framework\Debug\Exception\AlertOnlyException;

/**
 * 인트로 - 회원 전용
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class MemberController extends \Controller\Mobile\Controller
{
    /**
     * index
     */
    public function index()
    {
        // 마이앱 로그인뷰 스크립트
        $myappBuilderInfo = gd_policy('myapp.config')['builder_auth'];
        $myappUseQuickLogin = gd_policy('myapp.config')['useQuickLogin'];
        if (\Request::isMyapp() && empty($myappBuilderInfo['clientId']) === false && empty($myappBuilderInfo['secretKey']) === false && !MemberUtil::isLogin() && $myappUseQuickLogin === 'true') {
            $myapp = \App::load('Component\\Myapp\\Myapp');
            echo $myapp->getAppBridgeScript('loginView');
            exit;
        }

        $scripts = ['gd_payco.js'];
        try {
            $returnUrl = MemberUtil::getLoginReturnURL();
            $this->setData('returnUrl', $returnUrl);

            $paycoPolicy = new PaycoLoginPolicy();
            $this->setData('usePaycoLogin', $paycoPolicy->usePaycoLogin());

            $snsLoginPolicy = new SnsLoginPolicy();
            $useFacebook = $snsLoginPolicy->useFacebook();
            $this->setData('useFacebookLogin', $useFacebook);

            if ($useFacebook) {
                $facebook = new Facebook();
                $scripts[] = 'gd_sns.js';
                if ($snsLoginPolicy->useGodoAppId()) {
                    $this->setData('facebookUrl', $facebook->getGodoLoginUrl($returnUrl));
                } else {
                    $this->setData('facebookUrl', $facebook->getLoginUrl($returnUrl));
                }
            }

            $naverLoginPolicy = new NaverLoginPolicy();
            $useNaver = $naverLoginPolicy->useNaverLogin();
            if($useNaver) {
                $naver = new GodoNaverServerApi();
                $scripts[] = 'gd_naver.js';
                $this->setData('naverUrl', $naver->getLoginURL());
            }
            $this->setData('useNaverLogin', $useNaver);

            $kakaoPolicy = new KakaoLoginPolicy();
            $useKakao = $kakaoPolicy->useKakaoLogin();
            if($useKakao) {
                $scripts[] = 'gd_kakao.js';
            }
            $this->setData('useKakaoLogin', $useKakao);

            $wonderPolicy = new WonderLoginPolicy();
            $useWonderLogin = $wonderPolicy->useWonderLogin();
            if ($useWonderLogin) {
                $wonder = new GodoWonderServerApi();
                $scripts[] = 'gd_wonder.js';
                $this->setData('useWonderLogin', $useWonderLogin);
                $this->setData('wonderReturnUrl', $wonder->getAuthUrl('login'));
            }

            // 마이앱 로그인 Url
            $loginActionUrl = '../member/login_ps.php';
            if (\Request::isMyapp()) {
                $loginActionUrl ='../member/myapp/myapp_login.php';
                $this->setData('returnUrl', '/');
            }
            $this->setData('loginActionUrl', $loginActionUrl);

        } catch (\Exception $e) {
            \Logger::error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            throw new AlertOnlyException($e->getMessage(), $e->getCode(), $e);
        } finally {
            $this->addScript($scripts);
        }
    }
}
