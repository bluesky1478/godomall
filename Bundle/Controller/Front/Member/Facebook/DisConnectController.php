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

/**
 * Class DisConnectController
 * @package Bundle\Controller\Front\Member\Facebook
 * @author  yjwee
 */
class DisConnectController extends \Controller\Front\Controller
{
    public function index()
    {
        $session = \App::getInstance('session');
        $logger = \App::getInstance('logger');
        $member = $session->get(Member::SESSION_MEMBER_LOGIN);
        if ($member['snsJoinFl'] == 'y') {
            $this->json(
                [
                    'error'   => 'facebook',
                    'message' => __('페이스북으로 가입한 회원님은 연결을 해제 할 수 없습니다.'),
                ]
            );
        }

        try {
            $policy = \App::load('Component\\Policy\\SnsLoginPolicy');
            $memberSnsService = \App::load('Component\\Member\\MemberSnsService');
            $memberSnsService->setThirdPartyAppType($policy::FACEBOOK);
            $facebook = new Facebook();
            if ($policy->useGodoAppId()) {
                $facebook->clearSession();
                $memberSnsService->disconnectSns($member['memNo']);
                $this->json(
                    [
                        'message' => __('페이스북 연결이 해제되었습니다.'),
                        'url'     => '../mypage/my_page.php',
                    ]
                );
            } else {
                $response = $facebook->disConnect();
                if ($response->getHttpStatusCode() == 200) {
                    $facebook->clearSession();
                    $memberSnsService->disconnectSns($member['memNo']);
                    $this->json(
                        [
                            'message' => __('페이스북 연결이 해제되었습니다.'),
                            'url'     => '../mypage/my_page.php',
                        ]
                    );
                }
            }
        } catch (\Throwable $e) {
            $logger->error($e->getTraceAsString());
            $this->json(
                [
                    'error'   => 'facebook',
                    'message' => __('페이스북 연결해제에 필요한 정보를 찾을 수 없습니다.'),
                    'url'     => '../mypage/my_page_password.php',
                ]
            );
        }
    }
}
