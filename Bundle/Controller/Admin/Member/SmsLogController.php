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

namespace Bundle\Controller\Admin\Member;

use Component\Sms\Sms;
use Component\Sms\SmsLog;

/**
 * SMS 발송내역
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SmsLogController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('member', 'sms', 'log');

        $smsLog = new SmsLog();

        $logData = $smsLog->getSmsLogList();

        //예약문자 결과수신 예외처리
        if (empty($logData) === false) {
            $nowDate = date('Y-m-d H:i:s');

            for ($l = 0; $l < count($logData['data']); $l++) {
                if ($logData['data'][$l]['sendStatus'] == 'r' && $logData['data'][$l]['reserveDt'] > $nowDate) {
                    $logData['data'][$l]['sendStatus'] = 's';
                }
            }
        }

        $this->setData('memGroupNm', gd_htmlspecialchars(gd_member_groups()));
        $this->setData('logData', gd_isset($logData['data']));
        $this->setData('selected', gd_isset($logData['selected']));
        $this->setData('checked', gd_isset($logData['checked']));
        $this->setData('search', gd_isset($logData['search']));
        $this->setData('page', gd_isset($logData['page']));
        $this->setData('listCnt', gd_isset($listCnt));
        $this->setData('lmsPoint', Sms::LMS_POINT);
        $this->setData('smsSendType', Sms::SMS_SEND_TYPE);
        $this->setData('smsSendStatus', Sms::SMS_SEND_STATUS);
        $this->addScript(['member.js']);
    }
}
