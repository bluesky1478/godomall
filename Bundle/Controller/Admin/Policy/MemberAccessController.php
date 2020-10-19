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
namespace Bundle\Controller\Admin\Policy;

use Framework\Debug\Exception\AlertBackException;

/**
 * 쇼핑몰 이용 설정 (방문/구매 및 인트로, 로그아웃 설정)
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class MemberAccessController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            $this->callMenu('policy', 'management', 'access');
            $data = gd_policy('member.access');

            // 본인 확인 서비스 사용여부
            $adultIdentifyFl = 'n';
            if (gd_use_ipin() === true || gd_use_auth_cellphone() === true) {
                $adultIdentifyFl = 'y';
            }

            gd_isset($data['introFrontUseFl'], 'n');
            gd_isset($data['introMobileUseFl'], 'n');
            gd_isset($data['introFrontAccess'], 'free');
            gd_isset($data['introMobileAccess'], 'free');
            gd_isset($data['buyAuthGb'], 'free');
            gd_isset($data['sessTimeUseFl'], 'n');
            gd_isset($data['sessTime'], 60);
            gd_isset($data['chooseMileageCoupon'], 'n');
            gd_isset($data['guestUnder14Fl'], 'n');
            $checked['introFrontUseFl'][$data['introFrontUseFl']] =
            $checked['introMobileUseFl'][$data['introMobileUseFl']] =
            $checked['introFrontAccess'][$data['introFrontAccess']] =
            $checked['introMobileAccess'][$data['introMobileAccess']] =
            $checked['buyAuthGb'][$data['buyAuthGb']] =
            $checked['sessTimeUseFl'][$data['sessTimeUseFl']] =
            $checked['chooseMileageCoupon'][$data['chooseMileageCoupon']] =
            $checked['guestUnder14Fl'][$data['guestUnder14Fl']]= 'checked="checked"';


            // --- 관리자 디자인 템플릿
            $this->setData('data', gd_htmlspecialchars($data));
            $this->setData('adultIdentifyFl', $adultIdentifyFl);
            $this->setData('checked', $checked);
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
