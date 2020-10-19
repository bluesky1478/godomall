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
namespace Bundle\Controller\Admin\Share;

use Component\Sms\Sms;
use Exception;

/**
 * 컴백쿠폰 SMS발송내용 정보보기/수정 레이어 페이지
 *
 */
class LayerComebackCouponSmsController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // 리퀘스트
            $getValue = \Request::get()->toArray();

            // --- 모듈 호출
            $coupon = \App::load('\\Component\\Coupon\\CouponAdmin');
            $getData = $coupon->getComebackCouponInfo($getValue['dataSno'], '*');

            // 템플릿 변수
            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('parentFormID', $getValue['parentFormID']);
            $this->setData('dataFormID', $getValue['dataFormID']);
            $this->setData('dataInputNm', $getValue['dataInputNm']);
            $this->setData('disabled', gd_isset($getValue['disabled'], ''));
            $this->setData('callFunc', gd_isset($getValue['callFunc'], ''));
            $this->setData('smsStringLimit', Sms::SMS_STRING_LIMIT);
            $this->setData('lmsStringLimit', Sms::LMS_STRING_LIMIT);
            $this->setData('smsForbidTime', Sms::SMS_FORBID_TIME);
            $this->setData('lmsPoint', Sms::LMS_POINT);
            $this->setData('data', $getData);

            // 레이어 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
