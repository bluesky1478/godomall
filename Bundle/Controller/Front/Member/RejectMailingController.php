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

use Framework\Debug\Exception\AlertCloseException;

/**
 * 이메일 수신거부 처리 컨트롤러
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class RejectMailingController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            $request = \App::getInstance('request');
            $email = $request->get()->get('email', '');
            $encryptor = \App::getInstance('encryptor');
            $email = $encryptor->decrypt(base64_decode(strtr($email, '-_', '+/')));
            if (!empty($email)) {
                $member = \App::load('Component\\Member\\Member');
                $member->rejectMailing($email);
                $this->alert(__('이메일 수신거부가 정상 처리되었습니다. 다시 메일을 수신하시려면 마이페이지>회원정보변경에서 광고성 이메일 수신에 체크해주시기 바랍니다.'));
            } else {
                throw new AlertCloseException(__('잘못된 이메일 주소입니다.'));
            }
        } catch (\Throwable $e) {
            throw new AlertCloseException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
