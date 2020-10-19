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

namespace Bundle\Controller\Front\Intro;

use Bundle\Component\Godo\GodoKakaoServerApi;
use Bundle\Component\Policy\KakaoLoginPolicy;
use Component\Facebook\Facebook;
use Component\Member\Util\MemberUtil;
use Component\Policy\PaycoLoginPolicy;
use Component\Policy\SnsLoginPolicy;
use Component\Policy\NaverLoginPolicy;
use Component\Policy\WonderLoginPolicy;
use Component\Godo\GodoNaverServerApi;
use Component\Godo\GodoWonderServerApi;
use Component\SiteLink\SiteLink;
use Framework\Debug\Exception\AlertOnlyException;
use Request;

/**
 * 인트로 - 회원 전용
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class MemberController extends \Controller\Front\Controller
{
    /**
     * index
     */
    public function index()
    {
        $session = \App::getInstance('session');
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');

        $scripts = ['gd_payco.js'];
        try {
            if ($request->request()->has('returnUrl')) {
                $joinItemPolicy = gd_policy('member.joinitem');
                $loginPolicy = [
                    'loginId'  => $joinItemPolicy['memId'],
                    'loginPwd' => $joinItemPolicy['memPw'],
                ];
                $this->setData('loginPolicy', $loginPolicy);
            }
            $paycoPolicy = new PaycoLoginPolicy();
            $this->setData('usePaycoLogin', $paycoPolicy->usePaycoLogin());

            $snsLoginPolicy = new SnsLoginPolicy();
            $useFacebook = $snsLoginPolicy->useFacebook();
            $this->setData('useFacebookLogin', $useFacebook);

            $returnUrl = MemberUtil::getLoginReturnURL();
            $this->setData('returnUrl', $returnUrl);
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
            $siteLink = new SiteLink();

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

            $this->setData('loginActionUrl', $siteLink->link('../member/login_ps.php', 'ssl'));

            $data = MemberUtil::getCookieByLogin();
            // 로그인 아이디 저장 쿠키
            if ($data[MemberUtil::COOKIE_LOGIN_ID]) {
                $data['loginId'] =  $data[MemberUtil::COOKIE_LOGIN_ID];
                $data['checkedId'] = 'checked="checked"';
                $this->setData('data', $data);
            }
        } catch (\Exception $e) {
            $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            throw new AlertOnlyException($e->getMessage(), $e->getCode(), $e);
        } finally {
            $this->addScript($scripts);
        }
    }
}
