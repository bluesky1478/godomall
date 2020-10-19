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

use Component\Policy\KakaoLoginPolicy;
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
use Globals;
use Request;
use Session;

/**
 * 인트로 - 성인
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class AdultController extends \Controller\Front\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $scripts = ['gd_payco.js'];
        try {
            /** @var \Bundle\Controller\Front\Controller $this */
            $returnUrl = MemberUtil::getLoginReturnURL();
            $siteLink = new SiteLink();
            $this->setData('loginActionUrl', $siteLink->link('../member/member_ps.php', 'ssl'));
            $this->setData('returnUrl', $returnUrl);        // 스킨에서 오류가 나서 추가
            $this->setData('domainUrl', Request::getDomainUrl());
            $this->setData('ipinFl', gd_use_ipin());
            $this->setData('authCellphoneFl', gd_use_auth_cellphone());
            $this->setData('authShopUrl', URI_AUTH_PHONE_MODULE);
            $this->setData('authDataCpCode', gd_get_auth_cellphone());
            $this->setData(
                [
                    'script' => [
                        ['url' => DEPRECATED_PATH_MODULE_SCRIPT . 'jquery.number_only.js']
                        ,
                        ['url' => DEPRECATED_PATH_MODULE_SCRIPT . 'identification.js'],
                    ],
                ]
            );
            if (Session::has('member')) {
                $this->getView()->setPageName('intro/adult_member');
            } else {
                $this->getView()->setPageName('intro/adult_guest');
                $paycoPolicy = new PaycoLoginPolicy();
                $usePaycoLogin = $paycoPolicy->usePaycoLogin();
                $this->setData('usePaycoLogin', $usePaycoLogin);

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

            }
        } catch (\Exception $e) {
            \Logger::error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            throw new AlertOnlyException($e->getMessage(), $e->getCode(), $e);
        } finally {
            $this->addScript($scripts);
        }
    }
}
