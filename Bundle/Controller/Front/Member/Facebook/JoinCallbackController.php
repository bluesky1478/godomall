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
use Framework\Debug\Exception\AlertCloseException;

/**
 * Class JoinCallbackController
 * @package Bundle\Controller\Front\Member\Facebook
 * @author  yjwee
 */
class JoinCallbackController extends CallbackController
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
            $memberSns = $memberSnsService->getMemberSnsByUUID($tokenMetadata->getUserId());

            if ($memberSnsService->validateMemberSns($memberSns)) {
                $memberSnsService->saveToken($tokenMetadata->getUserId(), $session->get(Facebook::SESSION_ACCESS_TOKEN), '');
                $memberSnsService->loginBySns($tokenMetadata->getUserId());

                $js = 'if (opener) {' . PHP_EOL;
                $js .= 'opener.alert(\'' . __('이미 가입한 회원입니다.') . '\');' . PHP_EOL;
                $js .= 'self.close();opener.location.href=\'../../main/index.php\';' . PHP_EOL;
                $js .= '} else {' . PHP_EOL;
                $js .= 'alert(\'' . __('이미 가입한 회원입니다.') . '\');' . PHP_EOL;
                $js .= 'location.href=\'../../main/index.php\';' . PHP_EOL;
                $js .= '}' . PHP_EOL;
                $this->js($js);
            }
            $js = 'if (opener) {' . PHP_EOL;
            $js .= 'self.close();opener.location.href=\'../join_agreement.php\';' . PHP_EOL;
            $js .= '} else {' . PHP_EOL;
            $js .= 'location.href=\'../join_agreement.php\';' . PHP_EOL;
            $js .= '}' . PHP_EOL;
            $this->js($js);
        } catch (\Throwable $e) {
            throw new AlertCloseException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
