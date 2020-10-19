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

use Component\Database\DBTableField;
use Component\Sms\Sms;
use Exception;
use Framework\Debug\Exception\LayerException;
use Framework\Utility\GodoUtils;
use Request;

class ComebackCouponRegistController extends \Controller\Admin\Controller
{

    /**
     * 쿠폰 등록
     * [관리자 모드] 쿠폰 등록
     *
     * @author su
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     */

    public function index()
    {

        // --- 쿠폰 사용 설정 정보
        try {
            $couponData = array();

            // --- 모듈 호출
            $couponAdmin = \App::load(\Component\Coupon\CouponAdmin::class);

            // 쿠폰 리스트 페이지 번호
            $ypage = Request::get()->get('ypage');
            // 컴백쿠폰 발송 고유 번호
            $sno = Request::get()->get('sno');

            // sno 가 없으면 디비 디폴트 값 설정
            if ($sno > 0) {
                $couponData = $couponAdmin->getComebackCouponInfo($sno, '*');
                if ($couponData['targetGoodGoods'] != '') {
                    $couponData['couponApplyProductType'] = 'goods';
                    $couponData['couponApplyGoods'] = $couponData['targetGoodGoods'];
                    $couponData = $couponAdmin->getCouponApplyExceptData($couponData);
                }
                if ($couponData['couponNo'] > 0) {
                    $couponNoData = $couponAdmin->getCouponInfo($couponData['couponNo'], 'couponNm, couponType');
                    $couponData['couponNoNm'] = $couponNoData['couponNm'];
                    $couponData['couponType'] = $couponNoData['couponType'];
                }
                $couponData['targetOrderDay'] = ($couponData['targetOrderDay'] == 0) ? null : (int) $couponData['targetOrderDay'];
                $couponData['targetOrderPriceMin'] = ($couponData['targetOrderPriceMin'] == 0 && $couponData['targetOrderPriceMax'] == 0) ? null : (int) $couponData['targetOrderPriceMin'];
                $couponData['targetOrderPriceMax'] = ($couponData['targetOrderPriceMax'] == 0) ? null : (int) $couponData['targetOrderPriceMax'];
                $couponData['targetGoodDay'] = ($couponData['targetGoodDay'] == 0) ? null : (int) $couponData['targetGoodDay'];
                $couponData['mode'] = 'modifyComebackCoupon';

                if ($couponData['sendDt'] == null || $couponData['sendDt'] == '0000-00-00 00:00:00') {
                    $this->callMenu('promotion', 'coupon', 'comebackCouponModify');
                } else {
                    $this->callMenu('promotion', 'coupon', 'comebackCouponView');
                }

            } else {
                $this->callMenu('promotion', 'coupon', 'comebackCouponRegist');
                DBTableField::setDefaultData('tableComebackCoupon', $couponData);
                $couponData['smsFl'] = 'y';
                $couponData['mode'] = 'registComebackCoupon';
                $couponData['smsContents'] = "(광고)
[{rc_mallNm}]
구매하신 상품은 마음에 드셨나요? 특별한 고객님만을 위한 컴백 할인 쿠폰 발급!
지금 바로 확인하세요!";
                $couponData['smsContents'] .= "\n".GodoUtils::shortUrl('http://'.\Request::getDefaultHost());
            }

            $checked['targetFl'][$couponData['targetFl']] =
            $checked['smsFl'][$couponData['smsFl']] =
            $checked['smsSpamFl'][$couponData['smsSpamFl']] = 'checked="checked"';

            $selected['targetOrderFl'][$couponData['targetOrderFl']] =
            $selected['targetGoodFl'][$couponData['targetGoodFl']] = 'selected="selected"';

            // 수정불가능한상태 체크
            if ($couponData['sendDt'] == null || $couponData['sendDt'] == '0000-00-00 00:00:00') {
                $checkMode = 'canModify';
            } else {
                $checkMode = 'cantModify';
            }
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        if (\Request::get()->get('popupMode', '') === 'yes') {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        }

        // --- 메뉴 설정
        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);
        $this->setData('smsStringLimit', Sms::SMS_STRING_LIMIT);
        $this->setData('lmsStringLimit', Sms::LMS_STRING_LIMIT);
        $this->setData('smsForbidTime', Sms::SMS_FORBID_TIME);
        $this->setData('lmsPoint', Sms::LMS_POINT);
        $this->setData('checkMode', gd_isset($checkMode));
        $this->setData('couponData', gd_isset($couponData));
        $this->setData('checked', gd_isset($checked));
        $this->setData('selected', gd_isset($selected));
        $this->setData('ypage', gd_isset($ypage,1));
        $this->setData('callback', Request::get()->get('callback', ''));
        $this->setData('sms080Policy', gd_policy('sms.sms080'));
    }
}
