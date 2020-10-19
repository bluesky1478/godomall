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
use Component\Coupon\CouponAdmin;
use Component\Member\Group\Util as GroupUtil;
use Component\Sms\Sms;
use Component\Database\DBTableField;
use Framework\Debug\Exception;
use Framework\Debug\Exception\LayerException;
use Framework\Utility\ArrayUtils;
use Request;

class CartRemindRegistController extends \Controller\Admin\Controller
{

    /**
     * 장바구니 알림 등록
     * [관리자 모드] 장바구니 알림 등록
     *
     * @author    su
     * @version   1.0
     * @since     1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     *
     * @param array $get
     * @param array $post
     * @param array $files
     *
     * @throws Except
     */

    public function index()
    {
        try {
            // 장바구니 알림용 쿠폰
            $couponAdmin = new CouponAdmin();
            $cartRemindCouponData = $couponAdmin->getCouponCartRemind();

            $cartRemind = new CartRemind();
            $cartRemindData = [];
            $cartRemindNo = Request::get()->get('cartRemindNo');
            if ($cartRemindNo > 0) {
                $cartRemindData = $cartRemind->getCartRemindInfo($cartRemindNo, '*');
                $cartRemindApplyMemberGroup = ArrayUtils::objectToArray(json_decode($cartRemindData['cartRemindApplyMemberGroup']));
                unset($cartRemindData['cartRemindApplyMemberGroup']);
                if (is_array($cartRemindApplyMemberGroup)) {
                    if ($cartRemindApplyMemberGroup) {
                        foreach ($cartRemindApplyMemberGroup as $memkey => $memval) {
                            $groupNm = GroupUtil::getGroupName('sno=' . $memval);
                            $cartRemindData['cartRemindApplyMemberGroup'][$memkey]['no'] = $memval;
                            $cartRemindData['cartRemindApplyMemberGroup'][$memkey]['name'] = $groupNm[$memval];
                        }
                    }
                }
                $this->callMenu('promotion', 'cartRemind', 'cartRemindModify');
                $cartRemindData['mode'] = 'modifyCartRemind';
            } else {
                $totalCartRemind = $cartRemind->getCartRemindCount();
                if ($totalCartRemind >= LIMIT_CART_REMIND_AMOUNT) {
                    throw new Exception\AlertBackException(sprintf(__('장바구니 알림은 최대 %d 개까지만 등록할 수 있습니다. 기존 알림을 수정하거나 삭제 후 등록해주세요.'),LIMIT_CART_REMIND_AMOUNT));
                }
                DBTableField::setDefaultData('tableCartRemind', $cartRemindData);
                $this->callMenu('promotion', 'cartRemind', 'cartRemindRegist');
                $cartRemindData['mode'] = 'insertCartRemind';
            }

            $checked['cartRemindType'][$cartRemindData['cartRemindType']] =
            $checked['cartRemindGoodsSellFl'][$cartRemindData['cartRemindGoodsSellFl']] =
            $checked['cartRemindGoodsDisplayFl'][$cartRemindData['cartRemindGoodsDisplayFl']] =
            $checked['cartRemindGoodsSoldOutFl'][$cartRemindData['cartRemindGoodsSoldOutFl']] = 'checked="checked"';

            $selected['cartRemindPeriod'][$cartRemindData['cartRemindPeriod']] =
            $selected['cartRemindAutoSendTime'][$cartRemindData['cartRemindAutoSendTime']] =
            $selected['cartRemindGoodsStockSel'][$cartRemindData['cartRemindGoodsStockSel']] =
            $selected['cartRemindCoupon'][$cartRemindData['cartRemindCoupon']] = 'selected="selected"';

        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        // --- 메뉴 설정
        $this->setData('cartRemindData', $cartRemindData);
        $this->setData('cartRemindCouponData', $cartRemindCouponData);
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
        $this->setData('smsStringLimit', Sms::SMS_STRING_LIMIT);
        $this->setData('lmsStringLimit', Sms::LMS_STRING_LIMIT);
        $this->setData('lmsPoint', Sms::LMS_POINT);
    }
}
