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
 * @link http://www.godo.co.kr
 */

namespace Bundle\Component\Cli;

use Component\ExchangeRate\ExchangeRate;
use Component\Godo\GodoAutoExchangeApi;
use Component\CartRemind\CartRemind;
use Component\Coupon\CouponAdmin;
use Framework\Command\JobScheduler\AbstractJob;
use App;

/**
 * 관리자 메인 접속시 처리하던 로직으로
 * 장바구니 SMS 예약 전송하기
 *
 * @usage setCronSchedule 설정 사용
 * -    -    -    -    -    -
 * |    |    |    |    |    |
 * |    |    |    |    |    + year [optional]
 * |    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
 * |    |    |    +---------- month (1 - 12)
 * |    |    +--------------- day of month (1 - 31)
 * |    +-------------------- hour (0 - 23)
 * +------------------------- min (0 - 59)
 * @run
 *      /usr/local/php/bin/php /www/s5devel140_godo_co_kr/user/route.php job --name="CartRemindJob" --calldate="2016-01-01 03"
 *
 * @author  su @ godosoft development team.
 * @package Godo_Job_Schedule
 */
class CliSaveCoupon extends AbstractJob
{
    /**
     * Job 실행 전 처리 로직
     * 반드시 있어야 하는 부분은 아니다.
     *
     * @see Framework\Command\JobScheduler\JobConfig
     * @param object $config JobConfig 객체
     */
    protected function setup($config)
    {
        $config
            ->setIgnoreUserAbort(true)
            ->setMaxExecuteTime(0)
            ->setMaxMemoryLimit('5G');
    }

    /**
     * Job 실행
     * !중요! 반드시 return 값을 넘겨야 complete 처리가 가능하다.
     *
     * @abstract
     *
     * @return boolean 실행 성공 여부
     */
    protected function execute()
    {
        if (php_sapi_name() == 'cli') {
            $aArgv = $this->setArgument();
            $couponAdmin = new CouponAdmin();
            $couponAdmin->saveMemberCouponCli($aArgv['arrData'], $aArgv['searchQuery']);
        }

        return true;
    }

    protected function setArgument()
    {
        $aArgv = array();
        $tempArgv = $_SERVER['argv'];
        foreach ($tempArgv as $k => $v) {
            if (substr($v, 0, 2) == '--') {
                $sTempVal = str_replace('--', '', $v);
                $sTempVal = str_replace('^|^', ' ', $sTempVal);
                $aTempVal = explode('=', $sTempVal);
                if ($aTempVal[0] == 'extravalue') {
                    $aArgv = json_decode($aTempVal[1], true);
                }
            }
        }

        return $aArgv;
    }

    /**
     * Job 완료 처리
     * !중요! 반드시 상황에 따라 로그메시지를 작성해야 한다.
     * 그래야 통계서버로 정확한 메시지가 전달됩니다.
     *
     * @abstract
     * @param $isSuccess
     *
     * @return mixed|void
     */
    protected function complete($isSuccess)
    {
        if ($isSuccess) {
            // 성공시 처리 메시지 반드시 작성
            $this->logMessage('OK');
        }
    }

    /**
     * Shutdown 예외처리
     * 해당 메소드가 있는 경우만 실행된다.
     *
     * @param string $code    시스템 다운 코드
     * @param string $message 시스템 다운 메시지
     */
    protected function shutdown($code, $message)
    {
        // shutdown GMS/Mail 발송
        switch ($code) {
            // 메모리부족
            case self::SHUTDOWN_NOT_ENOUGH_MEMORY:
                break;
            // Connection TIMEOUT 종료
            case self::SHUTDOWN_CONNECTION_TIMEOUT:
                break;
            // Connection Client 절단
            case self::SHUTDOWN_CONNECTION_ABORTED:
                break;
            // Connection Client 절단되고 TIMEOUT 종료
            case self::SHUTDOWN_CONNECTION_TIMEOUT_ABORTED:
                break;
            // Connection 알려지지 않은 에러
            case self::SHUTDOWN_CONNECTION_UNKNOWN_ERROR:
                break;
            // 알려지지 않은 에러
            default:
                break;
        }
    }
}
