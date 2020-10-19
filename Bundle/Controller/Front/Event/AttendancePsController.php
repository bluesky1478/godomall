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

namespace Bundle\Controller\Front\Event;


use Component\Attendance\AttendanceCheck;
use Component\Member\Util\MemberUtil;
use Component\Sms\Sms;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Request;

/**
 * Class AttendStampPsController
 * @package Bundle\Controller\Front\Event
 * @author  yjwee
 */
class AttendancePsController extends \Controller\Front\Controller
{
    const INSERT_STAMP = 'INSERT_STAMP';
    const UPDATE_STAMP = 'UPDATE_STAMP';
    const INSERT_REPLY = 'INSERT_REPLY';
    const INSERT_LOGIN = 'INSERT_LOGIN';

    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            if (!MemberUtil::isLogin()) {
                throw new AlertRedirectException(__('로그인 후 참여하실 수 있습니다.'), null, null, '../member/login.php');
            }
            if (Request::post()->get('sno', '') == '') {
                throw new \Exception(__('참여하실 이벤트 번호가 없습니다.'));
            }

            $check = new AttendanceCheck();
            $result = '';
            try {
                \DB::begin_tran();
                $result = $check->attendance(Request::post()->get('mode'));
                \DB::commit();
            } catch (Exception $e) {
                \DB::rollback();
            }

            $smsReceivers = $check->getSmsReceiversByCouponBenefit();
            if (count($smsReceivers) > 0) {
                $sms = new Sms();
                foreach ($smsReceivers as $index => $smsReceiver) {
                    $sms->smsAutoSend('promotion', $smsReceiver['smsCode'], $smsReceiver);
                }
            }

            $this->json($result);
        } catch (Exception $e) {
            if (Request::isAjax() === true) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new AlertOnlyException($e->getMessage());
            }
        }
    }
}
