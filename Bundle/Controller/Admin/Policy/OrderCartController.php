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

/**
 * 장바구니/관심상품  설정 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class OrderCartController extends \Controller\Admin\Controller
{

    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'order', 'cart');

        // --- 장바구니 설정 정보
        try {
            $data = gd_policy('order.cart');

            gd_isset($data['periodFl'], 'y');
            gd_isset($data['periodDay'], 7);
            gd_isset($data['goodsLimitFl'], 'y');
            gd_isset($data['goodsLimitCnt'], 100);
            gd_isset($data['sameGoodsFl'], 'p');
            gd_isset($data['zeroPriceOrderFl'], 'y');
            gd_isset($data['directOrderFl'], 'y');
            gd_isset($data['soldOutFl'], 'y');
            gd_isset($data['moveCartPageDeviceFl'], 'pc');

            gd_isset($data['wishLimitFl'], 'y');
            gd_isset($data['wishDay'], '100');
            gd_isset($data['moveCartPageFl'], 'y');
            gd_isset($data['wishSoldOutFl'], 'y');
            gd_isset($data['wishPageMoveDirectFl'], 'y');
            gd_isset($data['moveWishPageDeviceFl'], 'pc');

            gd_isset($data['cartTabUseFl'], 'n');

            gd_isset($data['estimateUseFl'], 'n');
            gd_isset($data['memberDiscount'], 'n');
            gd_isset($data['goodsDiscount'], 'n');

            $checked = [];
            $checked['periodFl'][$data['periodFl']] =
            $checked['goodsLimitFl'][$data['goodsLimitFl']] =
            $checked['sameGoodsFl'][$data['sameGoodsFl']] =
            $checked['zeroPriceOrderFl'][$data['zeroPriceOrderFl']] =
            $checked['moveCartPageFl'][$data['moveCartPageFl']] =
            $checked['soldOutFl'][$data['soldOutFl']] =
            $checked['directOrderFl'][$data['directOrderFl']] =
            $checked['wishLimitFl'][$data['wishLimitFl']] =
            $checked['moveWishPageFl'][$data['moveWishPageFl']] =
            $checked['cartTabUseFl'][$data['cartTabUseFl']] =
            $checked['estimateUseFl'][$data['estimateUseFl']] =
            $checked['memberDiscount'][$data['memberDiscount']] =
            $checked['goodsDiscount'][$data['goodsDiscount']] =
            $checked['wishSoldOutFl'][$data['wishSoldOutFl']] =
            $checked['wishPageMoveDirectFl'][$data['wishPageMoveDirectFl']] = 'checked="checked"';

            $selected = [];
            $selected['moveCartPageDeviceFl'][$data['moveCartPageDeviceFl']] =
            $selected['moveWishPageDeviceFl'][$data['moveWishPageDeviceFl']] = 'selected="selected"';
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        // --- 관리자 디자인 템플릿
        $this->setData('data', $data);
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
    }
}
