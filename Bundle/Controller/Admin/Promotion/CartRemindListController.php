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
namespace Bundle\Controller\Admin\Promotion;

use Component\CartRemind\CartRemind;
use Component\Godo\GodoSmsServerApi;
use Component\Sms\Sms;
use Component\Sms\SmsAdmin;
use Request;

/**
 * 장바구니 알림 리스트
 *
 * @author su <surlira@godo.co.kr>
 */
class CartRemindListController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('promotion', 'cartRemind', 'cartRemindList');

        $smsAdmin = new SmsAdmin();
        $smsAutoData = $smsAdmin->getSmsAutoData();

        // SMS 발신번호 사전 등록 번호 정보
        $godoSms = new GodoSmsServerApi();
        $smsPreRegister = $godoSms->checkSmsCallNumber($smsAutoData['smsCallNum']);

        $searchData = Request::get()->all();
        $cartRemind = new CartRemind();
        $getData = $cartRemind->getCartRemindList($searchData);
        $convertArrData = $cartRemind->convertCartRemindArrData($getData['data']);
        $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

        $this->setData('smsAutoList', Sms::SMS_AUTO_RECEIVE_LIST);
        $this->setData('smsAutoOrderPeriod', Sms::SMS_AUTO_ORDER_PERIOD);
        $this->setData('smsAutoData', gd_htmlspecialchars($smsAutoData));
        $this->setData('smsPreRegister', $smsPreRegister);
        $this->setData('cartRemindData', gd_isset($getData['data']));
        $this->setData('convertArrData', gd_isset($convertArrData));
        $this->setData('page', $page);
    }
}
