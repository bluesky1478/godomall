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

namespace Bundle\Controller\Front\Member\Facebook;

use Component\Facebook\Facebook;
use Component\Member\Member;
use Component\Member\MyPage;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Utility\UrlUtils;

/**
 * Class ReAuthenticationCallbackController
 * @package Bundle\Controller\Front\Member\Facebook
 * @author  yjwee
 */
class ReAuthenticationCallbackController extends CallbackController
{
    public function index()
    {
        $session = \App::getInstance('session');
        $logger = \App::getInstance('logger');
        $request = \App::getInstance('request');
        try {
            parent::index();
            /** @var \Facebook\Authentication\AccessTokenMetadata $tokenMetadata */
            $tokenMetadata = $session->get(Facebook::SESSION_METADATA, []);
            $memberSnsService = \App::load('Component\\Member\\MemberSnsService');
            $memberSnsService->setThirdPartyAppType($this->snsPolicy::FACEBOOK);
            $memberSns = $memberSnsService->getMemberSnsByUUID($tokenMetadata->getUserId());
            if ($memberSnsService->validateMemberSns($memberSns)) {
                if ($session->has(Member::SESSION_MEMBER_LOGIN)) {
                    $logger->info('Has member login session', $request->get()->all());
                    if ($memberSns['memNo'] != $session->get(Member::SESSION_MEMBER_LOGIN . '.memNo', 0)) {
                        $logger->info('Not equal. memNo(es_memberSns), memNo(session)');
                        $js = 'if (opener) {' . PHP_EOL;
                        $js .= 'opener.alert(\'' . __('로그인 시 인증한 정보와 다릅니다.') . '\');' . PHP_EOL;
                        $js .= 'opener.location.href=\'' . UrlUtils::appendSubDomain('/mypage/my_page_password.php') . '\';self.close();' . PHP_EOL;
                        $js .= '} else {' . PHP_EOL;
                        $js .= 'alert(\'' . __('로그인 시 인증한 정보와 다릅니다.') . '\');' . PHP_EOL;
                        $js .= 'location.href=\'' . UrlUtils::appendSubDomain('/mypage/my_page_password.php') . '\';' . PHP_EOL;
                        $js .= '}' . PHP_EOL;
                        $this->js($js);
                    }
                    $session->set(MyPage::SESSION_MY_PAGE_PASSWORD, true);
                    $js = 'if (opener) {' . PHP_EOL;
                    $js .= 'self.close();opener.location.href=\'' . UrlUtils::appendSubDomain('/mypage/my_page.php') . '\';' . PHP_EOL;
                    $js .= '} else {' . PHP_EOL;
                    $js .= 'location.href=\'' . UrlUtils::appendSubDomain('/mypage/my_page.php') . '\';' . PHP_EOL;
                    $js .= '}' . PHP_EOL;
                    $this->js($js);
                }
            }

            $js = 'if (opener) {' . PHP_EOL;
            $js .= 'opener.alert(\'' . __('가입된 계정이 아닙니다. 가입하신 계정으로 재인증 진행해주세요.') . '\');' . PHP_EOL;
            $js .= 'self.close();' . PHP_EOL;
            $js .= '} else {' . PHP_EOL;
            $js .= 'alert(\'' . __('가입된 계정이 아닙니다. 가입하신 계정으로 재인증 진행해주세요.') . '\');' . PHP_EOL;
            $js .= 'location.href=\'' . UrlUtils::appendSubDomain('/mypage/my_page_password.php') . '\';' . PHP_EOL;
            $js .= '}' . PHP_EOL;
            $this->js($js);
        } catch (\Throwable $e) {
            throw new AlertCloseException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
