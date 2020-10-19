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

use App;
use Component\Member\Member;
use Component\Member\MemberValidation;
use Request;
use Session;

/**
 * Class FindPasswordResetPsController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class FindPasswordResetPsController extends \Controller\Front\Controller
{
    public function index()
    {
        $session = \App::getInstance('session');
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');

        try {
            /** @var  \Bundle\Component\Member\Member $member */
            $member = App::load('\\Component\\Member\\Member');

            if ($session->has(Member::SESSION_USER_CERTIFICATION) == false) {
                $this->json(
                    [
                        'error' => [
                            'message' => __('잘못된 경로로 접근하셨습니다.'),
                            'url'     => '../member/find_password.php',
                        ],
                    ]
                );
            }

            $sessionByCertification = $session->get(Member::SESSION_USER_CERTIFICATION);
            $memId = $sessionByCertification['memId'];
            $memPw = $request->post()->get('memPw');
            MemberValidation::validateMemberPassword($memPw);
            $member->updatePassword($memId, $memPw);
            $session->del(Member::SESSION_USER_CERTIFICATION);

            $this->json(
                [
                    'message' => __('비밀번호가 변경되었습니다.'),
                    'url'     => '../member/find_password_complete.php',
                ]
            );
        } catch (\Exception $e) {
            $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
        }
    }
}
