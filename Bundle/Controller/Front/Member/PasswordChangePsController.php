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

use Component\Mail\MailMimeAuto;
use Component\Member\Member;
use Component\Member\MyPage;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Request;

/**
 * Class PasswordChangePsController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class PasswordChangePsController extends \Controller\Front\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        try {
            $referer = $request->getReferer();
            if (strpos($referer, 'password_change') > 0) {
                $referer = '/';
            }

            $mode = $request->post()->get('mode');
            $member = new Member();
            $mailAuto = new MailMimeAuto();
            $myPage = new MyPage($mailAuto, $member);
            switch ($mode) {
                case 'changePassword':
                    $memberSession = \Session::get(Member::SESSION_MEMBER_LOGIN);
                    $myPage->changePassword($request->post()->get('oldPassword'), $request->post()->get('password'));
                    // 회원정보 수정 이벤트
                    $modifyEvent = \App::load('\\Component\\Member\\MemberModifyEvent');
                    $updateData['changePasswordFl'] = 'y';
                    $resultModifyEvent = $modifyEvent->applyMemberModifyEvent($updateData, $memberSession);
                    $this->json(
                        [
                            'result'  => 'ok',
                            'url'     => $referer,
                            'message' => __('비밀번호가 변경되었습니다.'),
                            'memberModifyEvent' => str_replace('\n', "\n", $resultModifyEvent['msg']),
                        ]
                    );
                    break;
                case 'laterPassword':
                    $myPage->changePasswordLater();
                    $this->json(
                        [
                            'result' => 'ok',
                            'url'    => $referer,
                        ]
                    );
                    break;
            }
        } catch (Exception $e) {
            if ($request->isAjax()) {
                $this->json(
                    [
                        'result'  => 'fail',
                        'message' => $e->getMessage(),
                    ]
                );
            } else {
                throw new AlertOnlyException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}
