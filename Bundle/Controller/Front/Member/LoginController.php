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
use Component\Godo\GodoNaverServerApi;
use Component\Godo\GodoWonderServerApi;
use Component\Goods\Goods;
use Component\Member\Util\MemberUtil;
use Component\Policy\NaverLoginPolicy;
use Component\Policy\PaycoLoginPolicy;
use Component\Policy\WonderLoginPolicy;
use Component\Policy\SnsLoginPolicy;
use Component\SiteLink\SiteLink;
use Framework\Debug\Exception\AlertBackException;
use Request;
use Validation;

/**
 * Class 프론트-로그인 컨트롤러
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class LoginController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        /** @var \Bundle\Controller\Front\Controller $this */
        try {
            $request = \App::getInstance('request');
            $logger = \App::getInstance('logger');
            $scripts = ['gd_payco.js'];
            $urlDefaultCheck = true;

            $memberUtil = \App::load('Component\\Member\\Util\\MemberUtil');
            $memberUtil::logoutPayco();
            $memberUtil::logoutNaver();
            $memberUtil::logoutKakao();
            $memberUtil::logoutWonder();
            $facebook = \App::load('Component\\Facebook\\Facebook');
            $facebook->clearSession();

            if ($request->request()->has('returnUrl')) {
                // returnUrl 데이타 타입 체크
                try {
                    Validation::setExitType('throw');
                    Validation::defaultCheck(gd_isset($request->request()->get('returnUrl')), 'url');
                } catch (\Exception $e) {
                    $urlDefaultCheck = false;
                    $returnUrl = $request->getReferer();
                }

                if ($urlDefaultCheck) {
                    $returnUrl = $request->getReturnUrl();// url의 특이한 형태로 인해 치환코드 설정
                }
                $logger->info(sprintf('Request has return url %s', $returnUrl));
            } else {
                $returnUrl = $request->getReferer();
                $logger->info(sprintf('Request has not return url. get referer %s', $returnUrl));
            }

            $snsLoginPolicy = new SnsLoginPolicy();
            $useFacebook = $snsLoginPolicy->useFacebook();
            if ($useFacebook) {
                $scripts[] = 'gd_sns.js';
                if ($snsLoginPolicy->useGodoAppId()) {
                    $this->setData('facebookUrl', $facebook->getGodoLoginUrl($returnUrl));
                } else {
                    $this->setData('facebookUrl', $facebook->getLoginUrl($returnUrl));
                }
            }

            $naverLoginPolicy = new NaverLoginPolicy();
            $useNaver = $naverLoginPolicy->useNaverLogin();
            if ($useNaver) {
                $naver = new GodoNaverServerApi();
                $scripts[] = 'gd_naver.js';
                $this->setData('naverUrl', $naver->getLoginURL($returnUrl));
            }

            $kakaoPolicy = new KakaoLoginPolicy();
            $useKakaoLogin = $kakaoPolicy->useKakaoLogin();
            if ($useKakaoLogin) {
                $scripts[] = 'gd_kakao.js';
                //$this->setData('kakaoReturnUrl', $request->getRequestUri()); // 기존 페이지로 넘어 가지 않아 수정
                $this->setData('kakaoReturnUrl', $returnUrl);
                $logger->info(sprintf('kakao has return url %s', $returnUrl));
            }

            $wonderPolicy = new WonderLoginPolicy();
            $useWonderLogin = $wonderPolicy->useWonderLogin();
            if ($useWonderLogin) {
                \Request::get()->set('returnUrl', $returnUrl);
                $wonder = new GodoWonderServerApi();
                $scripts[] = 'gd_wonder.js';
                $this->setData('wonderReturnUrl', $wonder->getAuthUrl('login'));
            }

            if (MemberUtil::isLogin()) {
                $this->redirect(URI_HOME);
            }

            $data = MemberUtil::getCookieByLogin();

            $hasGuestOrder = $request->get()->has('guestOrder');
            $isMemberOrder = false;
            $data['guestLogin'] = 'y';
            if (count($request->request()->all()) == 0) {
                $isMemberOrder = true;
            }
            if ($request->get()->get('goodsNo', '') != '') {
                $goods = new Goods();
                $goodsView = $goods->getGoodsView($request->get()->get('goodsNo'));

                $isMemberOrder = $goodsView['goodsPermission'] != 'all';
            }
            if (gd_policy('member.access.buyAuthGb') == 'member') {
                $isMemberOrder = true;
                $data['guestLogin'] = 'n';
            }
            if ($hasGuestOrder == false) {
                $data['guestLogin'] = 'n';
            }

            // --- 본인인증 없는 휴면해제 전환의 경우 설정 값 초기화
            $data['wakeType'] = $request->get()->get('wakeType', 'normal');
            $sleepPolicy = gd_policy('member.sleep');
            if ($data['wakeType'] !== 'normal' && $sleepPolicy['wakeType'] !== $data['wakeType']) {
                foreach ($sleepPolicy as $key => $value) {
                    $sleepPolicy[$key] = '';
                }
            }

            // 로그인 아이디 저장 쿠키
            if ($data[MemberUtil::COOKIE_LOGIN_ID]) {
                $data['loginId'] =  $data[MemberUtil::COOKIE_LOGIN_ID];
            }

            // 마이앱 로그인 url
            $siteLink = new SiteLink();
            $loginActionUrl = $siteLink->link('../member/login_ps.php', 'ssl');
            if ($request->isMyapp()) {
                $loginActionUrl = $siteLink->link('../member/myapp/myapp_login.php', 'ssl');
            }

            $paycoPolicy = new PaycoLoginPolicy();
            $this->setData('loginActionUrl', $loginActionUrl);
            $this->setData('sleepPolicy', $sleepPolicy);
            $this->setData('data', $data);
            $this->setData('saveId', MemberUtil::KEY_SAVE_LOGIN_ID);
            $this->setData('returnUrl', urlencode($returnUrl));
            $this->setData('modeLogin', LoginPsController::MODE_LOGIN);
            $this->setData('usePaycoLogin', $paycoPolicy->usePaycoLogin());
            $this->setData('useKakaoLogin', $kakaoPolicy->useKakaoLogin());
            $this->setData('useFacebookLogin', $useFacebook);
            $this->setData('useNaverLogin', $useNaver);
            $this->setData('useWonderLogin', $useWonderLogin);
            $this->setData('hasGuestOrder', $hasGuestOrder);
            $this->setData('isMemberOrder', $isMemberOrder);
            $this->setData('orderNoMaxLength', 16);
            $this->addScript($scripts);
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}
