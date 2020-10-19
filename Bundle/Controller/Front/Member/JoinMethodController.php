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

use Bundle\Component\Policy\KakaoLoginPolicy;
use Component\Facebook\Facebook;
use Component\Godo\GodoPaycoServerApi;
use Component\Godo\GodoNaverServerApi;
use Component\Godo\GodoWonderServerApi;
use Component\Member\Util\MemberUtil;
use Component\Policy\PaycoLoginPolicy;
use Component\Policy\NaverLoginPolicy;
use Component\Policy\WonderLoginPolicy;
use Component\Policy\SnsLoginPolicy;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Request;

/**
 * Class 회원가입 방법 선택
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class JoinMethodController extends \Controller\Front\Controller
{
    public function index()
    {
        try {
            $request = \App::getInstance('request');
            $session = \App::getInstance('session');
            $session->del(GodoPaycoServerApi::SESSION_ACCESS_TOKEN);
            $scripts = ['gd_payco.js'];

            $paycoPolicy = new PaycoLoginPolicy();
            $naverPolicy = new NaverLoginPolicy();
            $snsLoginPolicy = new SnsLoginPolicy();
            $kakaoLoginPolicy = new KakaoLoginPolicy();
            $wonderLoginPolicy = new WonderLoginPolicy();
            $usePaycoLogin = $paycoPolicy->usePaycoLogin();
            $useNaverLogin = $naverPolicy->useNaverLogin();
            $useFacebook = $snsLoginPolicy->useFacebook();
            $usekakaoLogin = $kakaoLoginPolicy->useKakaoLogin();
            $useWonderLogin = $wonderLoginPolicy->useWonderLogin();
            if ($usePaycoLogin === false && $useNaverLogin === false && $useFacebook === false && $usekakaoLogin === false && $useWonderLogin === false) {
                $this->redirect('../member/join_agreement.php');
            }
            // 2017-03-28 yjwee 회원가입 화면 진입시 소셜로그인 정보 세션에서 제거
            if ($usePaycoLogin) {
                MemberUtil::logoutPayco();
            }

            if($useNaverLogin) {
                MemberUtil::logoutNaver();
                $scripts[] = 'gd_naver.js';
            }

            if ($useFacebook) {
                $scripts[] = 'gd_sns.js';
                $facebook = new Facebook();
                $facebook->clearSession();
                /* 정상적인 리다이렉트가 되지 않아서 주석처리
                if (($logoutUrl = $facebook->getLogoutUrl('/member/join_method.php')) !== false) {
                    $this->redirect($logoutUrl);
                }
                 */
                if ($snsLoginPolicy->useGodoAppId()) {
                    $this->setData('facebookUrl', $facebook->getGodoJoinUrl());
                } else {
                    $this->setData('facebookUrl', $facebook->getJoinUrl());
                }
            }

            if($usekakaoLogin) {
                MemberUtil::logoutKakao();
                $scripts[] = 'gd_kakao.js';
                $this->setData('returnUrl', $request->getRequestUri());
            }

            if($useWonderLogin) {
                MemberUtil::logoutWonder();
                $wonder = new GodoWonderServerApi();
                $scripts[] = 'gd_wonder.js';
                $this->setData('wonderReturnUrl', $wonder->getAuthUrl('login', 'join_method'));
            }

            $this->setData('join', gd_policy('member.join'));
            $this->setData('joinItem', gd_policy('member.joinitem'));
            $this->setData('usePaycoLogin', $usePaycoLogin);
            $this->setData('useNaverLogin', $useNaverLogin);
            $this->setData('useFacebookLogin', $useFacebook);
            $this->setData('useKakaoLogin', $usekakaoLogin);
            $this->setData('useWonderLogin', $useWonderLogin);
            $this->addScript($scripts);
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
