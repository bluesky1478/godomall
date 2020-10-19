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

namespace Bundle\Controller\Admin\Order;

use App;
use Session;


use Framework\Utility\ArrayUtils;

use Component\Coupon\Coupon;


use Component\Cart\CartAdmin;
use Request;
use Exception;

/**
 * 주문 쿠폰 적용
 *
 * @author  su
 */
class LayerMultishippingGoodsSelectController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            if (!Request::isAjax()) {
                throw new Exception('Ajax ' . __('전용 페이지 입니다.'));
            }

            $postValue = Request::post()->toArray();

            $cart = new CartAdmin($postValue['memNo']);
            $delivery = \App::load('\\Component\\Delivery\\Delivery');

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

            $cartInfo = $cart->getCartGoodsData($postValue['cartSno'], null, null, false, true, [], [], [], [], [], true);
            $deliveryCollect = [
                'pre' => ['pre' => '주문시결제(선결제)',],
                'later' => ['later' => '상품수령시결제(착불)',],
                'both' => ['pre' => '주문시결제(선결제)', 'later' => '상품수령시결제(착불)',],
            ];
            //배송방식 기타명
            $deliveryMethodEtc = gd_policy('delivery.deliveryMethodEtc');
            $this->setData('deliveryMethodEtc', $deliveryMethodEtc['deliveryMethodEtc']);

            $this->setData('cartInfo', $cartInfo);
            $this->setData('shippingNo', $postValue['shippingNo']);
            $this->setData('setData', $setData);
            $this->setData('nowData', $nowData);
            $this->setData('deliveryPrice', gd_isset($postValue['multiDelivery'], 0));
            $this->setData('address', $postValue['address']);
            $this->setData('deliveryBasicInfo', $cart->deliveryBasicInfo);
            $this->setData('deliveryBasicName', $delivery->deliveryMethodList['name']);
            $this->setData('deliveryCollect', $deliveryCollect);
            $this->setData('setDeliveryInfo', $cart->setDeliveryInfo);

            $this->getView()->setDefine('layout', 'layout_layer.php');
        } catch (Exception $e) {
            $this->json([
                'error' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
