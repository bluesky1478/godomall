<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2017, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Order;

use App;
use Exception;
use Globals;
use Request;

/**
 * Class AjaxOrderViewStatusController
 *
 * @package Bundle\Controller\Admin\Order
 * @author su
 */
class AjaxOrderViewStatusReturnController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            $postValue = Request::post()->toArray();

            if ($postValue['mode'] == 'get_select_order_return') {

                $couponTruncPolicy = Globals::get('gTrunc.coupon');
                $mileageTruncPolicy = Globals::get('gTrunc.mileage');

                // 상품의 주문 정보
                $orderReOrderCalculation = App::load(\Component\Order\ReOrderCalculation::class);
                $returnData = $orderReOrderCalculation->getSelectOrderReturnData($postValue['orderNo'], $postValue['statusMode'], $postValue['exchangeMode'], $postValue['sameExchangeOrderGoodsSno']);
                foreach ($returnData['coupon'] as $key => $val) {
                    if ($val['minusCouponFl'] == 'y' && $val['minusRestoreCouponFl'] == 'y') {
                        unset($returnData['coupon'][$key]);
                        continue;
                    }
                    if ($val['plusCouponFl'] == 'y' && $val['plusRestoreCouponFl'] == 'y') {
                        unset($returnData['coupon'][$key]);
                        continue;
                    }
                    if ($val['couponUseType'] == 'product') {
                        $returnData['coupon'][$key]['couponUseType'] = '상품쿠폰';
                    } else if ($val['couponUseType'] == 'order') {
                        $returnData['coupon'][$key]['couponUseType'] = '주문쿠폰';
                    } else if ($val['couponUseType'] == 'delivery') {
                        $returnData['coupon'][$key]['couponUseType'] = '배송비쿠폰';
                    }
                    if ($val['couponPrice'] > 0) {
                        $returnData['coupon'][$key]['couponPrice'] = gd_money_format(gd_number_figure($val['couponPrice'], $couponTruncPolicy['unitPrecision'], $couponTruncPolicy['unitRound']));
                    } else {
                        $returnData['coupon'][$key]['couponPrice'] = '';
                    }
                    if ($val['couponMileage'] > 0) {
                        $returnData['coupon'][$key]['couponMileage'] = gd_money_format(gd_number_figure($val['couponMileage'], $mileageTruncPolicy['unitPrecision'], $mileageTruncPolicy['unitRound']));
                    } else {
                        $returnData['coupon'][$key]['couponMileage'] = '';
                    }
                }
                $orderPolicy = gd_policy('order.basic');
                if($postValue['statusMode'] == 'e') {
                    $tmp['returnCouponFl'] = gd_isset($orderPolicy['e_returnCouponFl'], 'n');
                    $tmp['returnGiftFl'] = gd_isset($orderPolicy['e_returnGiftFl'], 'y');
                    $tmp['supplyMileage'] = gd_isset($orderPolicy['e_returnMileageFl'], 'y');
                    $tmp['supplyCouponMileage'] = gd_isset($orderPolicy['e_returnCouponMileageFl'], 'y');
                } else {
                    $tmp['returnStockFl'] = gd_isset($orderPolicy['c_returnStockFl'], 'y');
                    $tmp['returnCouponFl'] = gd_isset($orderPolicy['c_returnCouponFl'], 'n');
                    $tmp['returnGiftFl'] = gd_isset($orderPolicy['c_returnGiftFl'], 'y');
                }
                $checked['returnStockFl'][$tmp['returnStockFl']] =
                $checked['returnCouponFl'][$tmp['returnCouponFl']] =
                $checked['returnGiftFl'][$tmp['returnGiftFl']] =
                $checked['supplyMileage'][$tmp['supplyMileage']] =
                $checked['supplyCouponMileage'][$tmp['supplyCouponMileage']] = 'checked = checked';
                $this->setData('checked', gd_isset($checked));
                unset($tmp);

                if($postValue['statusMode'] === 'e'){
                    $this->setData('exchangeMode', $postValue['exchangeMode']);
                }
                $this->setData('statusMode', $postValue['statusMode']);
                $this->setData('couponData', $returnData['coupon']);
                $this->setData('giftData', $returnData['gift']);
                $this->setData('exchangeMileageApplyFl', $returnData['exchangeMileageApplyFl']);

                // --- 템플릿 정의
                $this->getView()->setDefine('layout', 'layout_layer.php');

                // 공급사와 동일한 페이지 사용
                $this->getView()->setPageName('order/ajax_order_view_status_return.php');
            } else {

            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
