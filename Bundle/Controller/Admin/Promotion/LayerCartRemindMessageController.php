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
use Component\Sms\Sms;
use Request;

/**
 * 장바구니 알림 발송내용 보기
 *
 * @author su <surlira@godo.co.kr>
 */
class LayerCartRemindMessageController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $cartRemindNo = Request::get()->get('cartRemindNo');
        $cartRemind = new CartRemind();
        $cartRemindData = $cartRemind->getCartRemindInfo($cartRemindNo,'cartRemindNo, cartRemindSendType, cartRemindSendMessage');

        $this->setData('cartRemindData', $cartRemindData);
        $this->setData('smsStringLimit', Sms::SMS_STRING_LIMIT);
        $this->setData('lmsStringLimit', Sms::LMS_STRING_LIMIT);
        $this->setData('lmsPoint', Sms::LMS_POINT);
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
