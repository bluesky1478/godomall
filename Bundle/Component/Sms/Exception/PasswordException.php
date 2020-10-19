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

namespace Bundle\Component\Sms\Exception;


use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Throwable;

class PasswordException extends \Exception
{
    const VALIDATION_FAIL = 100;
    const WRONG_PASSWORD = 200;
    const LIMIT_PASSWORD = 300;
    const INVALID_PASSWORD = 400;

    protected $script = '';

    public function __construct(int $code = 0, Throwable $previous = null)
    {
        $config = ComponentUtils::getPolicy('sms.config');
        $configPoint = $config['point'];
        $configSmsPass = $config['smsPass'];
        $configSmsCallNum = $config['smsCallNum'];
        $authentication = $config['authentication'];
        $authentication['failCnt']++;
        $authentication['failLog'][] = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');
        if ($authentication['failCnt'] > 9) {
            $code = self::LIMIT_PASSWORD;
        }
        $authentication['password'] = $config['authentication']['password'];
        ComponentUtils::setPolicy('sms.config', ['point' => $configPoint, 'smsPass' => $configSmsPass, 'smsCallNum' => $configSmsCallNum, 'authentication' => $authentication]);
        $message = '비밀번호 검증 오류 입니다.';
        switch ($code) {
            case self::VALIDATION_FAIL:
                $message = 'SMS 인증번호는 10 ~ 16자리로만 사용하실 수 있습니다.';
                $message .= '<br/>[마이고도 > 쇼핑몰관리]와 SMS 설정 페이지에서 SMS 인증번호 변경 후 다시 시도해주세요.';
                break;
            case self::INVALID_PASSWORD:
                $message = 'SMS 인증번호가 일치하지 않습니다.<br/>[마이고도 > 쇼핑몰관리]에서 SMS 인증번호를 확인해주세요.';
                break;
            case self::LIMIT_PASSWORD:
                $message = '마이고도와 쇼핑몰 관리자의 SMS 인증번호가 10회 이상 틀려, SMS를 발송할 수 없습니다.<br/>SMS 설정 페이지와 [마이고도 > 쇼핑몰관리]에서 SMS 인증번호를 확인해주세요.';
                $this->script = 'top.limit_password_callback();';
                break;
            case self::WRONG_PASSWORD:
                $message = 'SMS 설정 메뉴에 저장된 SMS 인증번호와 일치하지 않습니다.<br/>SMS 설정 페이지에서 SMS 인증번호를 먼저 확인해주세요.';
                break;
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getScript(): string
    {
        return $this->script;
    }
}
