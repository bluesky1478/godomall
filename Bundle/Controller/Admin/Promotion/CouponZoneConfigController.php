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

use Bundle\Component\Coupon\CouponAdmin;
use Framework\Debug\Exception\LayerException;
use Framework\Utility\GodoUtils;

class CouponZoneConfigController extends \Controller\Admin\Controller
{

    /**
     * 쿠폰 설정 및 생성
     * [관리자 모드] 쿠폰 설정 및 생성
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

        // --- 메뉴 설정
        $this->callMenu('promotion', 'coupon', 'couponzone');

        try {
            $coupon = new CouponAdmin();
            // --- 설정값 정보
            $couponConfig = gd_policy('coupon.couponzone');

            // --- 기본값 설정
            gd_isset($couponConfig['useFl'],'n');
            gd_isset($couponConfig['autoDisplayFl'],'y');
            gd_isset($couponConfig['couponImageType'],'basic');
            gd_isset($couponConfig['groupNm'], array(1=>''));


            $checked['useFl'][$couponConfig['useFl']] =
            $checked['autoDisplayFl'][$couponConfig['autoDisplayFl']] =
            $checked['couponImageType'][$couponConfig['couponImageType']] =
            $checked['descriptionSameFl'][$couponConfig['descriptionSameFl']] = 'checked="checked"';

            $unexposedCoupon = array();
            foreach($couponConfig['unexposedCoupon'] as $val) {
                $unexposedCoupon[$val] = $coupon->getCouponInfo($val, 'couponNm')['couponNm'];
            }

            $couponConfig['groupCoupon'] = $coupon->getCouponZoneGroupCoupon($couponConfig['groupCoupon']);
            if($couponConfig['pcCouponImage']) $couponConfig['pcCouponImagePath'] = $coupon->getCouponImageData($couponConfig['pcCouponImage']);
            if($couponConfig['mobileCouponImage']) $couponConfig['mobileCouponImagePath'] = $coupon->getCouponImageData($couponConfig['mobileCouponImage']);

        } catch (\Exception $e) {
            throw new LayerException($e->getMessage());
        }
       // debug($couponConfig);
        $this->setData('checked', $checked);
        $this->setData('unexposedCoupon', $unexposedCoupon);
        $this->setData('couponzoneOrderByList', CouponAdmin::COUPONZONE_ORDER_BY_LIST);
        $this->setData('couponConfig', $couponConfig);


        // 상품쿠폰 주문서페이지 사용여부 패치 SRC 버전체크
        $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
        $productCouponChangeLimitVersionFl = $couponAdmin->productCouponChangeLimitVersionFl; // true 노출, false 미노출
        $this->setData('productCouponChangeLimitVersionFl', $productCouponChangeLimitVersionFl);
    }
}
