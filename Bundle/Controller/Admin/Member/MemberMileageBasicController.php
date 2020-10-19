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

use Component\Sms\Code;
use Framework\Utility\ComponentUtils;
use Globals;
use Exception;

/**
 * 회원의 마일리지 기본 설정 관리 페이지
 *
 * @author Ahn Jong-tae <qnibus@godo.co.kr>
 * @author Shin Donggyu <artherot@godo.co.kr>
 * @author Wee Yeongjong <yeongjong.wee@godo.co.kr>
 */
class MemberMileageBasicController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        try {
            $this->callMenu('member', 'point', 'mileageBasic');

            $mileageBasic = gd_policy('member.mileageBasic');

            gd_isset($mileageBasic['name'], __('마일리지'));
            gd_isset($mileageBasic['unit'], __('원'));
            gd_isset($mileageBasic['expiryFl'], 'n');
            gd_isset($mileageBasic['expiryDays'], '365');
            gd_isset($mileageBasic['expiryBeforeDays'], '30');
            gd_isset($mileageBasic['expirySms'], 1);
            gd_isset($mileageBasic['expiryEmail'], 1);
            gd_isset($mileageBasic['goodsPrice'], 1);
            gd_isset($mileageBasic['optionPrice'], 0);
            gd_isset($mileageBasic['addGoodsPrice'], 0);
            gd_isset($mileageBasic['textOptionPrice'], 0);
            gd_isset($mileageBasic['goodsDcPrice'], 0);
            gd_isset($mileageBasic['memberDcPrice'], 0);
            gd_isset($mileageBasic['couponDcPrice'], 0);
            gd_isset($mileageBasic['payUsableFl'], 'y');

            /** @var string $smsMemberSend SMS 자동발송 정책 가져오기 (마일리지 소멸) */
            $smsMemberSend = function () {
                $config = ComponentUtils::getPolicy('sms.smsAuto');

                return $config['member'][Code::MILEAGE_EXPIRE]['memberSend'];
            };

            // 이메일 자동발송 정책 가져오기 (마일리지 소멸)
            $typeConfig = gd_policy('mail.configAuto');
            $typeConfig = $typeConfig['point']['deletemileage'];
            $typeAutoSendRadio = empty($typeConfig['autoSendFl']) ? 'y' : $typeConfig['autoSendFl'];

            /** set checkbox, select property */
            $checked = [];
            $checked['payUsableFl'][$mileageBasic['payUsableFl']] =
            $checked['expiryFl'][$mileageBasic['expiryFl']] =
            $checked['expirySms'][1] =
            $checked['expiryEmail'][1] =
            $checked['optionPrice'][1] =
            $checked['addGoodsPrice'][1] =
            $checked['textOptionPrice'][1] =
            $checked['goodsDcPrice'][1] =
            $checked['memberDcPrice'][1] =
            $checked['couponDcPrice'][1] = 'checked="checked"';

            /** set view mileageBasic */
            $this->setData('smsMemberSend', $smsMemberSend());
            $this->setData('mailMemberSend', $typeAutoSendRadio);
            $this->setData('data', $mileageBasic);
            $this->setData('checked', $checked);

        } catch (Exception $e) {
            throw $e;
        }
    }
}
