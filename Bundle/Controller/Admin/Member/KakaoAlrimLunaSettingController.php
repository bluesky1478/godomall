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
use Component\Member\KakaoAlrimLuna;
use Framework\Utility\ComponentUtils;

/**
 * 카카오 알림톡 설정
 *
 */
class KakaoAlrimLunaSettingController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        header('Access-Control-Allow-Origin:*');

        $request = \App::getInstance('request');
        // --- 메뉴 설정
        $this->callMenu('member', 'kakaoAlrim', 'kakaoAlrimSetting');

        $kakaoSetting = gd_policy('kakaoAlrimLuna.config');

        if (!$kakaoSetting) {
            $kakaoSetting['lunaCliendId'] = '';
            $kakaoSetting['lunaClientKey'] = '';
            $kakaoSetting['useFlag'] = 'n';
        }


        // 카카오알림 component
        $oKakao = new KakaoAlrimLuna;
        $smsAutoData = $oKakao->getSmsAutoData();
        $template = $oKakao->getKakaoTemplateList();
        $checked['useFlag'][$kakaoSetting['useFlag']] = 'checked="checked"';

        // 카카오 알림톡에 맞게 기본값들 재세팅
        $smsAutoList = Sms::SMS_AUTO_RECEIVE_LIST;
        unset($smsAutoList['promotion']);

        $bizKakaoSetting = gd_policy('kakaoAlrim.config');
        $this->setData('bizKakaoUseFlag', gd_isset($bizKakaoSetting['useFlag'],'n'));
        $memberJoin = ComponentUtils::getPolicy('member.join');
        $this->setData('kakaoSetting', $kakaoSetting);
        $this->setData('smsAutoList', $smsAutoList);
        $this->setData('smsAutoOrderPeriod', Sms::SMS_AUTO_ORDER_PERIOD);
        $this->setData('smsAutoOrderAfterPeriod', Sms::SMS_ORDER_AFTER_PERIOD);
        $this->setData('smsAutoReSendTime', Sms::SMS_RE_SEND_TIME);
        $this->setData('smsAutoCouponLimitPeriod', Sms::SMS_COUPON_LIMIT_PERIOD);
        $this->setData('smsAutoReservationTime', $oKakao->getKakaoAlrimReservationTime());
        $this->setData('smsAutoData', gd_htmlspecialchars($smsAutoData));
        $this->setData('template', gd_htmlspecialchars($template));
        $this->setData('checked', gd_isset($checked));
        $this->setData('useApprovalFlag', ($memberJoin['appUseFl'] != 'n' || $memberJoin['under14Fl'] == 'y'));
        if ($request->get()->get('popupMode', '') === 'yes') {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        }

        // 20181227 이후 설치솔루션은 카드 부분 취소 미노출(설치일기준)
        $globals = \App::getInstance('globals');
        $gLicense = $globals->get('gLicense');
        $this->setData('mallSettingDate', $gLicense['sdate']);

    }
}
