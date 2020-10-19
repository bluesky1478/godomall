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
use Exception;
use Request;
use Framework\Debug\Exception\LayerNotReloadException;
use Framework\Debug\Exception\LayerException;

/**
 * 주문 상태 변경 처리 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  su
 */
class OrderChangePsController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws LayerException
     */
    public function index()
    {
        // --- 모듈 호출
        $orderReorderCalculation = App::load(\Component\Order\ReOrderCalculation::class);
        $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
        $paycoConfig = $dbUrl->getConfig('payco', 'config');

        $postValue = Request::post()->toArray();
        switch ($postValue['mode']) {
            // 송장일괄등록 샘플 다운로드
            case 'cancel': // 상품 취소
                try {
                    // 취소 메세지
                    $cancelMsg = $postValue['cancelMsg'];

                    // 취소 상품 데이터
                    $cancel['orderNo'] = $postValue['orderNo'];
                    $orderGoodsCancelSno = explode('||', $postValue['orderGoodsCancelSno']);
                    $orderGoodsCancelCnt = explode('||', $postValue['orderGoodsCancelCnt']);
                    foreach ($orderGoodsCancelSno as $key => $val) {
                        $cancel['orderGoods'][$val] = $orderGoodsCancelCnt[$key];
                    }

                    // sms 개선(배송비 취소 여부 넘기기 위함)
                    $cancel['cancelPriceBySmsSend'] = $postValue['cancelPriceBySmsSend'];

                    // 취소 설정 데이터
                    $cancelPrice['settle'] = $postValue['appointmentSettlePrice'];
                    $cancelPrice['orderCouponDcCancel'] = gd_isset($postValue['totalCancelOrderCouponDc'], 0);
                    $cancelPrice['useDepositCancel'] = gd_isset($postValue['totalCancelDeposit'], 0);
                    $cancelPrice['useMileageCancel'] = gd_isset($postValue['totalCancelMileage'], 0);
                    $cancelPrice['deliveryCancel'] = gd_isset($postValue['totalCancelDeliveryPrice'], 0);
                    $cancelPrice['areaDeliveryCancel'] = gd_isset($postValue['totalCancelAreaDeliveryPrice'], 0);
                    $cancelPrice['overseaDeliveryCancel'] = gd_isset($postValue['totalCancelOverseaDeliveryPrice'], 0);
                    $cancelPrice['addDelivery'] = gd_isset($postValue['totalCancelAddDeliveryPrice'], 0);
                    $cancelPrice['deliveryCouponDcCancel'] = gd_isset($postValue['totalCancelDeliveryCouponDc'], 0);
                    $cancelPrice['deliveryMemberDcCancel'] = gd_isset($postValue['totalCancelDeliveryMemberDc'], 0);
                    $cancelPrice['deliveryMemberDcCancelFl'] = gd_isset($postValue['cancelDeliveryMemberDcFl'], 0);

                    // 취소 후 주문 결제 예정금액이 마이너스면 리턴
                    if ($cancelPrice['settle'] < 0) {
                        $this->layerNotReload(__('취소 후 주문 결제 예정금액이 0보다 작은 경우 취소처리가 불가합니다.'));
                    }

                    // 재고 복원 함 y
                    $return['stockFl'] = $postValue['returnStockFl'];
                    // 쿠폰 복원 함 y
                    $return['couponFl'] = $postValue['returnCouponFl'];
                    if ($postValue['returnCouponFl'] == 'y') {
                        foreach ($postValue['returnCoupon'] as $memberCouponNo => $returnFl) {
                            if ($returnFl == 'y') {
                                $return['coupon'][] = $memberCouponNo;
                            }
                        }
                    }
                    // 사은품 지급 안함 n
                    $return['giftFl'] = $postValue['returnGiftFl'];
                    if ($postValue['returnGiftFl'] == 'n') {
                        foreach ($postValue['returnGift'] as $orderGiftNo => $returnFl) {
                            if ($returnFl == 'n') {
                                $return['gift'][] = $orderGiftNo;
                            }
                        }
                    }

                    $return = \DB::transaction(function () use ($orderReorderCalculation, $cancel, $cancelMsg, $cancelPrice, $return) {
                        return $orderReorderCalculation->setCancelOrderGoods($cancel, $cancelMsg, $cancelPrice, $return);
                    });

                    if ($return) {
                        $script = "parent.opener.location.href='./order_view.php?orderNo=" . $postValue['orderNo'] . "';parent.self.close();";
                        $this->layerNotReload(__('취소 처리가 완료 되었습니다.'), $script);
                    } else {
                        $this->layerNotReload(__('취소 처리가 실패 되었습니다.'));
                    }
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage(), null, null, null, 5000);
                }
                break;

            case "add" : // 상품추가
                try {
                    // 추가 설정 데이터
                    $addData['settle'] = $postValue['appointmentSettlePrice'];
                    $addData['addDeliveryFl'] = $postValue['addDeliveryFl'];
                    $addData['enuri'] = $postValue['enuri'];
                    $addData['multiShippingOrderInfoCd'] = $postValue['multiShippingOrderInfoCd'];

                    $return = \DB::transaction(function () use ($orderReorderCalculation, $addData, $postValue) {
                        return $orderReorderCalculation->setAddOrderGoods($postValue['orderNo'], $addData, true);
                    });

                    if ($return) {
                        $script = "parent.opener.location.href='./order_view.php?orderNo=" . $postValue['orderNo'] . "';parent.self.close();";
                        $this->layerNotReload(__('추가 처리가 완료 되었습니다.'), $script);
                    } else {
                        $this->layerNotReload(__('추가 처리가 실패 되었습니다.'));
                    }
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage(), null, null, null, 5000);
                }
                break;

            case "set_order_cancel_restore" : // 취소 복원
                try {

                    $return = \DB::transaction(function () use ($orderReorderCalculation, $postValue) {
                        return $orderReorderCalculation->setOrderRestore($postValue['orderNo'], 'c');
                    });
                    if ($return) {
                        echo "ok";
                    } else {
                        echo "false";
                    }
                } catch (Exception $e) {
                    $this->json($e->getMessage());
                }
                break;

            case "back" : // 반품
                try {
                    $return = \DB::transaction(function () use ($orderReorderCalculation, $postValue) {
                        return $orderReorderCalculation->setBackRefundOrderGoods($postValue, 'back');
                    });
                    $script = "parent.opener.location.href='./order_view.php?orderNo=".$postValue['orderNo']."';parent.self.close();";
                    $this->layerNotReload(__('반품 접수가 되었습니다.'), $script);
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            case "refund" : // 환불 접수
                try {
                    $return = \DB::transaction(function () use ($orderReorderCalculation, $postValue) {
                        return $orderReorderCalculation->setBackRefundOrderGoods($postValue, 'refund');
                    });
                    if($postValue['orderChannelFl'] == 'naverpay') {
                        $script = "parent.opener.location.href='./order_view.php?orderNo=".$postValue['orderNo']."';parent.self.close();";
                        $this->layerNotReload(__('환불 처리가 완료되었습니다.'), $script);
                    }
                    else {
                        if(!gd_is_provider()){
                            $orderHandleData = $orderReorderCalculation->getOrderHandleData($postValue['orderNo'], 'oh.sno DESC', '', 0, 'r');
                            $order = App::load(\Component\Order\OrderAdmin::class);
                            $orderData = $order->getOrderView($postValue['orderNo']);

                            if((int)$orderHandleData[0]['sno'] > 0){
                                if (gd_date_format('Y-m-d H:i', $orderData['regDt']) < gd_date_format('Y-m-d H:i', '2019-07-10 07:40:00')) {
                                    $script = "parent.opener.window.open('./refund_view.php?orderNo=".$postValue['orderNo']."&isAll=1&statusFl=1&handleSno=".$orderHandleData[0]['sno']."', 'popupRefund', 'width=1200px, height=800px, scrollbars=yes, resizable=yes');";
                                } else {
                                    $script = "parent.opener.window.open('./refund_view_new.php?orderNo=".$postValue['orderNo']."&isAll=1&statusFl=1&handleSno=".$orderHandleData[0]['sno']."', 'popupRefund', 'width=1200px, height=800px, scrollbars=yes, resizable=yes');";
                                }
                             }
                        }

                        $script .= "parent.opener.location.href='./order_view.php?orderNo=".$postValue['orderNo']."';parent.self.close();";
                        $this->layerNotReload(__('환불 접수가 되었습니다.'), $script);
                    }
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            case "refund_complete" : // 환불 완료
                try {
                    if (Request::get()->get('channel') == 'naverpay') {
                        $order = App::load(\Component\Order\OrderAdmin::class);
                        $orderGoodsData = $order->getOrderGoods(null,Request::get()->get('sno'),null,null,null)[0];
                        $checkoutData = $orderGoodsData['checkoutData'];
                        $naverPayApi = new NaverPayAPI();
                        $data = $naverPayApi->changeStatus($orderGoodsData['orderNo'],Request::get()->get('sno'),'r3');
                        if($data['result'] == false) {
                            throw new LayerNotReloadException($data['error']);
                        }
                        else {
                            throw new LayerException(__('환불처리가 완료되었습니다.\n 자세한 환불내역은 네이버페이 센터에서 확인하시기 바랍니다.'),null,null,null,10000);
                        }
                    } else {
                        $smsAuto = \App::load('Component\\Sms\\SmsAuto');
                        $smsAuto->setUseObserver(true);
                        \DB::transaction(
                            function () use ($orderReorderCalculation, $paycoConfig) {
                                $orderReorderCalculation->setRefundCompleteOrderGoods(Request::post()->toArray());

                                if ($paycoConfig['paycoFl'] == 'y') {
                                    // 페이코쇼핑 결제데이터 전달
                                    $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
                                    $payco->paycoShoppingRequest(Request::post()->get('orderNo'));
                                }
                            }
                        );
                        $smsAuto->notify();
                    }

                    throw new LayerException(__('환불 완료 일괄 처리가 완료 되었습니다.'), null, null, 'parent.close();parent.opener.location.reload()', 2000);
                } catch (LayerException $e) {
                    throw $e;
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new LayerNotReloadException($e->getMessage());
                    }
                }
                break;

            case "refund_complete_new" : // 환불 완료
                try {
                    if (Request::get()->get('channel') == 'naverpay') {
                        $order = App::load(\Component\Order\OrderAdmin::class);
                        $orderGoodsData = $order->getOrderGoods(null,Request::get()->get('sno'),null,null,null)[0];
                        $checkoutData = $orderGoodsData['checkoutData'];
                        $naverPayApi = new NaverPayAPI();
                        $data = $naverPayApi->changeStatus($orderGoodsData['orderNo'],Request::get()->get('sno'),'r3');
                        if($data['result'] == false) {
                            throw new LayerNotReloadException($data['error']);
                        }
                        else {
                            throw new LayerException(__('환불처리가 완료되었습니다.\n 자세한 환불내역은 네이버페이 센터에서 확인하시기 바랍니다.'),null,null,null,10000);
                        }
                    } else {
                        $smsAuto = \App::load('Component\\Sms\\SmsAuto');
                        $smsAuto->setUseObserver(true);
                        \DB::transaction(
                            function () use ($orderReorderCalculation, $paycoConfig) {
                                $orderReorderCalculation->setRefundCompleteOrderGoodsNew(Request::post()->toArray());

                                if ($paycoConfig['paycoFl'] == 'y') {
                                    // 페이코쇼핑 결제데이터 전달
                                    $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
                                    $payco->paycoShoppingRequest(Request::post()->get('orderNo'));
                                }
                            }
                        );
                        $smsAuto->notify();
                    }

                    throw new LayerException(__('환불 완료 일괄 처리가 완료 되었습니다.'), null, null, 'parent.close();parent.opener.location.reload()', 2000);
                } catch (LayerException $e) {
                    throw $e;
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new LayerNotReloadException($e->getMessage());
                    }
                }
                break;

            //맞교환
            case 'sameExchange' :
                try {
                    $return = \DB::transaction(function () use ($orderReorderCalculation, $postValue) {
                        return $orderReorderCalculation->setSameExchangeOrderGoods($postValue);
                    });

                    if ($return) {
                        $script = "parent.opener.location.href='./order_view.php?orderNo=".$postValue['orderNo']."';parent.self.close();";
                        $this->layerNotReload(__('동일상품교환 처리가 완료 되었습니다.'), $script);
                    } else {
                        $this->layerNotReload(__('동일상품교환 처리가 실패 되었습니다.'));
                    }
                }
                catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            //다른상품교환
            case 'anotherExchange' :
                try {
                    $return = \DB::transaction(function () use ($orderReorderCalculation, $postValue) {
                        return $orderReorderCalculation->setAnotherExchangeOrderGoods($postValue);
                    });

                    if ($return) {
                        $script = "parent.opener.location.href='./order_view.php?orderNo=".$postValue['orderNo']."';parent.self.close();";
                        $this->layerNotReload(__('다른상품교환 처리가 완료 되었습니다.'), $script);
                    } else {
                        $this->layerNotReload(__('다른상품교환 처리가 실패 되었습니다.'));
                    }
                }
                catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            //교환철회
            case 'exchangeCancel' :
                try {
                    $return = \DB::transaction(function () use ($orderReorderCalculation, $postValue) {
                        return $orderReorderCalculation->restoreExchangeCancel($postValue);
                    });

                    if ($return) {
                        $script = "parent.opener.location.href='./order_view.php?orderNo=".$postValue['orderNo']."';parent.self.close();";
                        $this->layerNotReload(__('교환철회가 완료 되었습니다.'), $script);
                    } else {
                        $this->layerNotReload(__('교환철회가 실패 되었습니다.'));
                    }
                }
                catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            //상품 상세페이지 에누리 셋팅
            case 'set_enuri_order_view' :
                try {
                    $return = \DB::transaction(function () use ($orderReorderCalculation, $postValue) {
                        return $orderReorderCalculation->setEnuriOrderView($postValue);
                    });

                    if ($return) {
                        $script = "parent.location.href='./order_view.php?orderNo=".$postValue['orderNo']."';";
                        $this->layerNotReload(__('운영자 할인 설정이 완료 되었습니다.'), $script);
                    } else {
                        $this->layerNotReload(__('운영자 할인 설정을 실패하였습니다.'));
                    }
                }
                catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            //묶음배송 처리
            case 'set_packet' :
                try {
                    $return = \DB::transaction(function () use ($orderReorderCalculation, $postValue) {
                        return $orderReorderCalculation->setPacket($postValue);
                    });

                    if ($return) {
                        $script = "parent.opener.location.href='./order_list_goods.php'; parent.self.close();";
                        $this->layerNotReload(__('묶음배송처리가 완료 되었습니다.'), $script);
                    } else {
                        $this->layerNotReload(__('묶음배송처리를 실패 하였습니다.'));
                    }
                }
                catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            //묶음배송 해제
            case 'unset_packet' :
                try {
                    $return = \DB::transaction(function () use ($orderReorderCalculation, $postValue) {
                        return $orderReorderCalculation->unsetPacket($postValue);
                    });

                    echo $return;
                }
                catch (Exception $e) {
                    echo $e->getMessage();
                }
                break;

                //묶음배송 수령자 정보 수정
            case 'update_receiver_info' :
                try {
                    $order = App::load(\Component\Order\OrderAdmin::class);
                    \DB::transaction(
                        function () use ($order, $postValue) {
                            $order->updateOrderReceiverInfo($postValue);
                        }
                    );

                    $this->layer(__('수령자 정보변경이 완료되었습니다.'), null, 1000);
                }
                catch (Exception $e) {
                    throw new LayerException($e->getMessage());
                }

                break;
        }
    }
}
