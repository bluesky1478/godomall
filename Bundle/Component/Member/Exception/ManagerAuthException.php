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

namespace Bundle\Component\Member\Exception;


use Throwable;

class ManagerAuthException extends \InvalidArgumentException
{
    /** @var int 보안 로그인에 사용할 수 있는 인증 수단이 없음 */
    const CODE_NOT_FOUND_AUTH = 100;
    /** @var int 인증 시 사용한 휴대폰번호와 상이함 */
    const CODE_NOT_EQUALS_CELLPHONE = 200;
    /** @var int 인증 시 사용한 이메일과 상이함 */
    const CODE_NOT_EQUALS_EMAIL = 300;


    public function __construct(int $code = 0, Throwable $previous = null)
    {
        $session = \App::getInstance('session');
        switch ($code) {
            case self::CODE_NOT_FOUND_AUTH:
                $session->del(\Component\Member\Member::SESSION_USER_CERTIFICATION);
                $session->del(\Component\Member\Member::SESSION_USER_MAIL_CERTIFICATION);
                $message = '보안 로그인에 사용할 휴대폰번호 또는 이메일 정보를 인증해 주세요.';
                break;
            case self::CODE_NOT_EQUALS_CELLPHONE:
                $session->del(\Component\Member\Member::SESSION_USER_CERTIFICATION);
                $message = '인증 시 사용한 휴대폰번호와 상이합니다. 재인증 해주시기 바랍니다.';
                break;
            case self::CODE_NOT_EQUALS_EMAIL:
                $session->del(\Component\Member\Member::SESSION_USER_MAIL_CERTIFICATION);
                $message = '인증 시 사용한 이메일 정보와 상이합니다. 재인증 해주시기 바랍니다.';
                break;
            default:
                $message = '관리자 보안인증 오류';
                break;
        }

        parent::__construct($message, $code, $previous);
    }
}
