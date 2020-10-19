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
use Framework\Utility\ComponentUtils;

/**
 * SMS 자동 발송 설정
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SmsAutoController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        $request = \App::getInstance('request');
        // --- 메뉴 설정
        $this->callMenu('member', 'sms', 'auto');

        $smsAdmin = \App::load('Component\\Sms\\SmsAdmin');
        $smsAutoData = $smsAdmin->getSmsAutoData();

        // SMS 발신번호 사전 등록 번호 정보
        $godoSms = \App::load('Component\\Godo\\GodoSmsServerApi');
        $smsPreRegister = $godoSms->checkSmsCallNumber($smsAutoData['smsCallNum']);

        $checked = $smsAutoData['checked'];

        // 카카오 설정값
        $kakaoSetting = gd_policy('kakaoAlrim.config');
        if (!$kakaoSetting) {
            $kakaoSetting['useFlag'] = 'n';
        } else {
            $kakaoDetilaSetting = gd_policy('kakaoAlrim.kakaoAuto');
            if (!$kakaoDetilaSetting) {
                $kakaoSetting['orderUseFlag'] = 'n';
                $kakaoSetting['memberUseFlag'] = 'n';
                $kakaoSetting['boardUseFlag'] = 'n';
            } else {
                $kakaoSetting['orderUseFlag'] = $kakaoDetilaSetting['orderUseFlag'];
                $kakaoSetting['memberUseFlag'] = $kakaoDetilaSetting['memberUseFlag'];
                $kakaoSetting['boardUseFlag'] = $kakaoDetilaSetting['boardUseFlag'];
            }
        }

        // 카카오 설정값(루나)
        $kakaoLunaSetting = gd_policy('kakaoAlrimLuna.config');
        if (!$kakaoLunaSetting) {
            $kakaoLunaSetting['useFlag'] = 'n';
        } else {
            $kakaoLunaDetilaSetting = gd_policy('kakaoAlrimLuna.kakaoAuto');
            if (!$kakaoLunaDetilaSetting) {
                $kakaoLunaSetting['orderUseFlag'] = 'n';
                $kakaoLunaSetting['memberUseFlag'] = 'n';
                $kakaoLunaSetting['boardUseFlag'] = 'n';
            } else {
                $kakaoLunaSetting['orderUseFlag'] = $kakaoLunaDetilaSetting['orderUseFlag'];
                $kakaoLunaSetting['memberUseFlag'] = $kakaoLunaDetilaSetting['memberUseFlag'];
                $kakaoLunaSetting['boardUseFlag'] = $kakaoLunaDetilaSetting['boardUseFlag'];
            }
        }

        // SMS 포인트 Sync
        Sms::saveSmsPoint();
        $memberJoin = ComponentUtils::getPolicy('member.join');
        $this->setData('smsAutoList', Sms::SMS_AUTO_RECEIVE_LIST);
        $this->setData('smsAutoOrderPeriod', Sms::SMS_AUTO_ORDER_PERIOD);
        $this->setData('smsAutoOrderAfterPeriod', Sms::SMS_ORDER_AFTER_PERIOD);
        $this->setData('smsAutoReSendTime', Sms::SMS_RE_SEND_TIME);
        $this->setData('smsAutoCouponLimitPeriod', Sms::SMS_COUPON_LIMIT_PERIOD);
        $this->setData('smsAutoReservationTime', $smsAdmin->getSmsReservationTime());
        $this->setData('smsAutoData', gd_htmlspecialchars($smsAutoData));
        $this->setData('checked', gd_isset($checked));
        $this->setData('smsPreRegister', $smsPreRegister);
        $this->setData('policy', ComponentUtils::getPolicy('sms.sms080'));
        $this->setData('memberJoin', ComponentUtils::getPolicy('member.join'));
        $this->setData('useApprovalFlag', ($memberJoin['appUseFl'] != 'n' || $memberJoin['under14Fl'] == 'y'));
        $this->setData('kakaoSetting', $kakaoSetting);
        $this->setData('kakaoLunaSetting', $kakaoLunaSetting);
        if ($request->get()->get('popupMode', '') === 'yes') {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        }

        // 20181227 이후 설치솔루션은 카드 부분 취소 미노출(설치일기준)
        $globals = \App::getInstance('globals');
        $gLicense = $globals->get('gLicense');
        $this->setData('mallSettingDate', $gLicense['sdate']);

    }
}
