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
use Framework\Debug\Exception\AlertCloseException;
use Framework\Utility\UrlUtils;

/**
 * Class ConnectCallbackController
 * @package Bundle\Controller\Front\Member\Facebook
 * @author  yjwee
 */
class ConnectCallbackController extends CallbackController
{
    public function index()
    {
        try {
            parent::index();
            $session = \App::getInstance('session');
            /** @var \Facebook\Authentication\AccessTokenMetadata $tokenMetadata */
            $tokenMetadata = $session->get(Facebook::SESSION_METADATA, []);
            $memberSnsService = \App::load('Component\\Member\\MemberSnsService');
            $memberSnsService->setThirdPartyAppType($this->snsPolicy::FACEBOOK);
            $uuid = $tokenMetadata->getUserId();
            if ($memberSnsService->hasSnsMember($uuid)) {
                $location = UrlUtils::appendSubDomain('/mypage/my_page.php');
                $js = 'if (opener) {' . PHP_EOL;
                $js .= 'opener.alert(\'' . __('이미 다른 회원정보와 연결된 계정입니다. 다른 계정을 이용해주세요.') . '\');' . PHP_EOL;
                $js .= 'self.close();opener.location.href=\'' . $location . '\';' . PHP_EOL;
                $js .= '} else {' . PHP_EOL;
                $js .= 'alert(\'' . __('이미 다른 회원정보와 연결된 계정입니다. 다른 계정을 이용해주세요.') . '\');' . PHP_EOL;
                $js .= 'location.href=\'' . $location . '\';' . PHP_EOL;
                $js .= '}' . PHP_EOL;
                $this->js($js);
            }
            $memberSnsService->connectSns($session->get(Member::SESSION_MEMBER_LOGIN . '.memNo'), $uuid, $session->get(Facebook::SESSION_ACCESS_TOKEN, ''), $this->snsPolicy::FACEBOOK);
            $js = 'if (opener) {' . PHP_EOL;
            $js .= 'opener.alert(\'' . __('계정 연결이 완료되었습니다. 로그인 시 연결된 계정으로 로그인 하실 수 있습니다.') . '\');' . PHP_EOL;
            $js .= 'self.close();opener.location.href=\'' . UrlUtils::appendSubDomain('/mypage/my_page.php') . '\';' . PHP_EOL;
            $js .= '} else {' . PHP_EOL;
            $js .= 'alert(\'' . __('계정 연결이 완료되었습니다. 로그인 시 연결된 계정으로 로그인 하실 수 있습니다.') . '\');' . PHP_EOL;
            $js .= 'location.href=\'' . UrlUtils::appendSubDomain('/mypage/my_page.php') . '\';' . PHP_EOL;
            $js .= '}' . PHP_EOL;
            $this->js($js);
        } catch (\Throwable $e) {
            throw new AlertCloseException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
