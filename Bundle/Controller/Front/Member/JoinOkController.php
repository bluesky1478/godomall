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

use Component\Facebook\Facebook;
use Component\Member\Member;
use Component\Member\Util\MemberUtil;
use Framework\Debug\Exception\AlertRedirectException;
use Session;

/**
 * Class 회원가입완료
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class JoinOkController extends \Controller\Front\Controller
{
    public function index()
    {
        $logger = \App::getInstance('logger');
        $session = \App::getInstance('session');
        $request = \App::getInstance('request');
        if (!($session->has(Member::SESSION_NEW_MEMBER) || $request->request()->has('memNo'))) {
            $logger->info(sprintf('has not %s session or request params memNo', \Component\Member\Member::SESSION_NEW_MEMBER));
            throw new AlertRedirectException(__('회원정보를 찾을 수 없습니다.'), 200, null, '../member/login.php');
        }

        $memberNo = $session->get(Member::SESSION_NEW_MEMBER, $request->request()->get('memNo', ''));

        // 마이앱 접속 및 사용시에는 신규회원 세션키 삭제안함
        $useMyapp = gd_policy('myapp.config')['useMyapp'] && \Request::isMyapp();
        if (!$useMyapp) {
            $session->del(Member::SESSION_NEW_MEMBER);
        }

        if (MemberUtil::isLogin()) {
            $logger->info('member is login redirect root');
            $this->redirect('/');
        }

        $memberService = new Member();
        $memberInfo = $memberService->getJoinDataWithCheckJoinComplete($memberNo);

        if ($memberInfo['appFl'] === 'n') {
            $logger->info('redirect wait page');
            $this->redirect('../member/join_wait.php', __("회원승인대기"));
        }

        //facebook Dynamic Ads 외부 스크립트 적용
        $facebookAd = \App::Load('\\Component\\Marketing\\FacebookAd');
        $fbScript = $facebookAd->getFbCompleteRegistrationScript();
        $this->setData('fbCompleteRegistrationScript', $fbScript);

        $this->setData('memNo', $memberNo);
        $this->setData('memNm', $memberInfo['memNm']);
    }
}
