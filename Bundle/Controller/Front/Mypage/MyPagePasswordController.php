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

namespace Bundle\Controller\Front\Mypage;


use Component\Facebook\Facebook;
use Component\Member\Member;
use Component\Member\MyPage;
use Component\Member\Util\MemberUtil;
use Component\Godo\GodoWonderServerApi;
use Component\Policy\SnsLoginPolicy;
use Framework\Http\Client;
use Framework\Utility\ArrayUtils;
use Framework\Utility\HttpUtils;
use GuzzleHttp\Psr7\Request;
use Session;

/**
 * Class MyPagePasswordController
 * @package Bundle\Controller\Front\Mypage
 * @author  yjwee
 */
class MyPagePasswordController extends \Controller\Front\Controller
{
    public function index()
    {
        $session = \App::getInstance('session');
        $scripts = ['gd_payco.js'];
        $scripts[] = 'gd_naver.js';
        $scripts[] = 'gd_kakao.js';

        $memberUtil = \App::load('Component\\Member\\Util\\MemberUtil');
        $memberUtil::logoutPayco();
        $memberUtil::logoutNaver();
        $memberUtil::logoutKakao();
        $memberUtil::logoutWonder();
        $facebook = \App::load('Component\\Facebook\\Facebook');
        $facebook->clearSession();

        $session->del(MyPage::SESSION_MY_PAGE_PASSWORD);
        $messages['info'] = __('회원님의 정보를 안전하게 보호하기 위해 비밀번호를 다시 한번 확인해 주세요.');

        $joinField = MemberUtil::getJoinField();
        $memberService = new Member();
        $member = $memberService->getMyPagePassword($session->get(Member::SESSION_MEMBER_LOGIN . '.memNo'));

        $usePayco = $member['snsTypeFl'] == 'payco';
        $useNaver = $member['snsTypeFl'] == 'naver';
        $useFacebook = $member['snsTypeFl'] == SnsLoginPolicy::FACEBOOK;
        $useKakao = $member['snsTypeFl'] == 'kakao';
        $useWonder = $member['snsTypeFl'] == 'wonder';
        if ($usePayco || $useNaver || $useFacebook || $useKakao || $useWonder) {
            $messages['info'] = __('회원님의 정보를 안전하게 보호하기 위해 계정을 %s재인증%s 해주세요.', '<span class="c-red">', '</span>');
        }
        if ($useFacebook) {
            $scripts[] = 'gd_sns.js';
            $facebook = new Facebook();
            if (\Component\Policy\SnsLoginPolicy::getInstance()->useGodoAppId()) {
                $this->setData('facebookReAuthenticationUrl', $facebook->getGodoReAuthenticationUrl());
            } else {
                $this->setData('facebookReAuthenticationUrl', $facebook->getReAuthenticationUrl());
            }
        }

        if ($useWonder) {
            $wonder = new GodoWonderServerApi();
            $scripts[] = 'gd_wonder.js';
            $this->setData('wonderReturnUrl', $wonder->getAuthUrl('login', 'my_page_password'));
        }

        ArrayUtils::unsetDiff($joinField, ['memPw']);
        ArrayUtils::unsetDiff($member, ['memId']);

        $this->setData('joinField', $joinField);
        $this->setData('data', $member);
        $this->setData('usePayco', $usePayco);
        $this->setData('useNaver', $useNaver);
        $this->setData('useFacebook', $useFacebook);
        $this->setData('useKakao', $useKakao);
        $this->setData('useWonder', $useWonder);
        $this->setData('messages', $messages);
        $this->setData('naverType', gd_isset(\Request::get()->get('type'), 'my_page_password'));
        $this->setData('kakaoType', gd_isset(\Request::get()->get('type'), 'my_page_password'));
        $this->setData('wonderType', gd_isset(\Request::get()->get('type'), 'my_page_password'));
        $this->addScript($scripts);

        //        debug(\Session::get('member'));
    }
}
