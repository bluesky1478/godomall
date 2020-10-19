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

namespace Bundle\Controller\Admin\Share;

use Component\Godo\GodoSmsServerApi;
use Component\Sms\Sms;

/**
 * SMS 결과 수신 처리
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SmsSendResultController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        // --- SMS 모듈
        $godoSms = new GodoSmsServerApi();

        // _POST 데이터
        $request = \App::getInstance('request');
        $postValue = $request->request()->toArray();
        $getResult = 'r';

        // SMS Log Component
        $componentSmsLog = \App::load('\\Component\\Sms\\SmsLog');
        if ($request->post()->has('smsLogSno')) {
            // SMS LOG 데이터
            $smsLog = $componentSmsLog->getSmsLog('*', $postValue['smsLogSno']);
            if ($request->post()->has('kakaoFl') == 'y') {

            } else {
                if ($smsLog['replaceCodeType'] == 'none') {
                    // SMS 결과 수신 처리
                    $getResult = $godoSms->getSmsSendResult($postValue['smsLogSno'], 'result');
                } else {
                    // SMS 결과 수신 처리
                    $getResult = $godoSms->getSmsSendListResult($postValue['smsLogSno'], 'result');
                }
            }

            if (empty($getResult) === true) {
                $getResult = 'r';
            }
        }
        echo Sms::SMS_SEND_STATUS[$getResult];
        exit();
    }
}
