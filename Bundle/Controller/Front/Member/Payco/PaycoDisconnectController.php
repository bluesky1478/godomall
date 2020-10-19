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

namespace Bundle\Controller\Front\Member\Payco;

use Component\Godo\GodoPaycoServerApi;
use Component\Member\Member;
use Component\Member\MemberSnsService;
use Session;

/**
 * Class PaycoDisconnectController
 * @package Bundle\Controller\Front\Member\Payco
 * @author  yjwee
 */
class PaycoDisconnectController extends \Controller\Front\Controller
{
    public function index()
    {
        $logger = \App::getInstance('logger');
        $session = \App::getInstance('session');
        $logger->info('payco disconnect start');
        $member = $session->get(Member::SESSION_MEMBER_LOGIN);
        if ($member['snsJoinFl'] == 'y') {
            $logger->info('Impossible disconnect member joined by payco');
            $this->json(
                [
                    'error'   => 'payco',
                    'message' => __('페이코로 가입한 회원님은 연결을 해제 할 수 없습니다.'),
                ]
            );
        }

        if ($session->has(GodoPaycoServerApi::SESSION_ACCESS_TOKEN)) {
            $logger->info('Has payco access token');
            $paycoToken = $session->get(GodoPaycoServerApi::SESSION_ACCESS_TOKEN, []);
            $logger->debug('session access token', $paycoToken);
            $session->del(GodoPaycoServerApi::SESSION_ACCESS_TOKEN);
            $paycoApi = new GodoPaycoServerApi();
            $paycoApi->removeServiceOff($paycoToken['access_token']);
            $memberSnsService = new MemberSnsService();
            $memberSnsService->disconnectSns($member['memNo']);
            $logger->info('Disconnect payco');
            $this->json(
                [
                    'message' => __('페이코 연결이 해제되었습니다.'),
                    'url'     => '../mypage/my_page.php',
                ]
            );
        } else {
            $logger->info('Disconnect payco fail. not found disconnect information');
            $this->json(
                [
                    'error'   => 'payco',
                    'message' => __('페이코 연결해제에 필요한 정보를 찾을 수 없습니다.'),
                    'url'     => '../mypage/my_page_password.php',
                ]
            );
        }
    }
}
