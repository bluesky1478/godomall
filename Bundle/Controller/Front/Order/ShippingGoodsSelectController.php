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

namespace Bundle\Controller\Front\Order;

use Component\Cart\Cart;
use Component\Delivery\Delivery;
use Component\Database\DBTableField;
use Component\Order\Order;
use Component\Mall\Mall;
use Request;

/**
 * 복수배송지 상품 선택
 */
class ShippingGoodsSelectController extends \Controller\Front\Controller
{
    public function index()
    {
        // 모듈 설정
        $cart = new Cart();
        $delivery = new Delivery();
        $postValue = Request::post()->all();

        $setData = $nowData = [];
        foreach ($postValue['selectGoods'] as $key => $value) {
            $getData = json_decode($value, true);

            foreach ($getData as $tKey => $tVal) {
                if ($tVal['goodsCnt'] > 0) {
                    if ($postValue['shippingNo'] == $key) {
                        $nowData['goods'][$tVal['goodsNo']][$tVal['sno']] = $tVal['goodsCnt'];
                    } else {
                        $setData['goods'][$tVal['goodsNo']][$tVal['sno']] += $tVal['goodsCnt'];
                    }
                }
                if (empty($tVal['addGoodsNo']) === false) {
                    foreach ($tVal['addGoodsNo'] as $k => $v) {
                        if ($postValue['shippingNo'] == $key) {
                            $nowData['addGoods'][$v][$tVal['sno']] = $tVal['addGoodsCnt'][$k];
                        } else {
                            $setData['addGoods'][$v][$tVal['sno']] += $tVal['addGoodsCnt'][$k];
                        }
                    }
                }

                $nowData['deliveryMethodFl'][$key][$tVal['goodsNo']][$tVal['sno']] = $tVal['deliveryMethodFl'];
                $nowData['deliveryCollectFl'][$key][$tVal['goodsNo']][$tVal['sno']] = $tVal['deliveryCollectFl'];
            }
        }

        $cartInfo = $cart->getCartGoodsData($postValue['cartIdx'], null, null, false, true, [], [], [], [], [], true);

        if(count($cartInfo) > 0) {
            foreach ($cartInfo as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    foreach ($dVal as $gKey => $gVal) {
                        $cartInfo[$sKey][$dKey][$gKey]['goodsDeliveryMethodFl'] = gd_isset($nowData['deliveryMethodFl'][$postValue['shippingNo']][$gVal['goodsNo']][$gVal['sno']], $gVal['goodsDeliveryMethodFl']);
                        $cartInfo[$sKey][$dKey][$gKey]['deliveryCollectFl'] = gd_isset($nowData['deliveryCollectFl'][$postValue['shippingNo']][$gVal['goodsNo']][$gVal['sno']], $gVal['deliveryCollectFl']);
                    }
                }
            }
        }
        $deliveryCollect = [
            'pre' => ['pre' => __('주문시결제') . '(' . __('선불') . ')',],
            'later' => ['later' => __('상품수령시결제') . '(' . __('착불') . ')',],
            'both' => ['pre' => __('주문시결제') . '(' . __('선불') . ')', 'later' => __('상품수령시결제') . '(' . __('착불') . ')',],
        ];
        //배송방식 기타명
        $deliveryMethodEtc = gd_policy('delivery.deliveryMethodEtc');
        $this->setData('deliveryMethodEtc', $deliveryMethodEtc['deliveryMethodEtc']);

        // 상품 옵션가 표시설정 config 불러오기
        $optionPriceConf = gd_policy('goods.display');
        $this->setData('optionPriceFl', gd_isset($optionPriceConf['optionPriceFl'], 'y')); // 상품 옵션가 표시설정

        $this->setData('cartInfo', $cartInfo);
        $this->setData('shippingNo', $postValue['shippingNo']);
        $this->setData('setData', $setData);
        $this->setData('nowData', $nowData);
        $this->setData('address', $postValue['address']);
        $this->setData('deliveryPrice', gd_isset($postValue['multiDelivery'], 0));
        $this->setData('deliveryBasicInfo', $cart->deliveryBasicInfo);
        $this->setData('deliveryBasicName', $delivery->deliveryMethodList['name']);
        $this->setData('deliveryCollect', $deliveryCollect);
        $this->setData('setDeliveryInfo', $cart->setDeliveryInfo);
    }
}
