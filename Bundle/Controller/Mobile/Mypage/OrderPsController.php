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

namespace Bundle\Controller\Mobile\Mypage;

use Component\Sms\Code;
use Component\Sms\SmsAutoOrder;
use Component\Database\DBTableField;
use Component\Goods\GoodsCate;
use Component\Page\Page;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertReloadException;
use Message;
use Request;
use Session;

/**
 * Class MypageQnaController
 *
 * @package Bundle\Controller\Mobile\Mypage
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class OrderPsController extends \Controller\Mobile\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // 리퀘스트 처리
            $postValue = Request::post()->xss()->toArray();

            if($postValue['guest'] == 'y') {
                $redirectUrl = '../mypage/order_view.php?orderNo='.$postValue['orderNo'].'&guest='.$postValue['guest'];
            } else {
                $redirectUrl = '../mypage/order_list.php';
            }

            // 모듈 설정
            $order = \App::load('\\Component\\Order\\OrderAdmin');
            $originOrder = \App::load('\\Component\\Order\\Order');

            switch ($postValue['mode']) {
                // 주문취소 처리
                case 'cancelRegist':
                    $orderData = $originOrder->getOrderData($postValue['orderNo']);

                    //회원 로그인 하여 주문한 주문건은 세션 확인
                    if (empty(Session::get('member.memNo')) || Session::get('member.memNo') == 0) {
                        if (Session::get('guest.orderNo') != $orderData['orderNo']) {
                            $this->json(
                                [
                                    'code'    => 200,
                                    'message' => __('주문을 취소할 수 없습니다.'),
                                ]
                            );
                            break;
                        }
                    } else {
                        //로그인 한 경우 자기 주문인지 확인
                        if($orderData['memNo'] != Session::get('member.memNo')){
                            $this->json(
                                [
                                    'code'    => 200,
                                    'message' => __('주문을 취소할 수 없습니다.'),
                                ]
                            );
                            break;
                        }
                    }

                    if (substr($orderData['orderStatus'], 0, 1) !== 'o') {
                        $this->json(
                            [
                                'code'    => 200,
                                'message' => __('주문을 취소할 수 없습니다.'),
                            ]
                        );
                        break;
                    }
                    $orderViewData = $order->getOrderView($postValue['orderNo']);
                    if (substr($orderViewData['orderStatus'], 0, 1) == 'o') {
                        $reOrderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');
                        $param = [];
                        foreach ($orderViewData['goods'] as $value) {
                            foreach ($value as $val) {
                                foreach ($val as $goodsData) {
                                    $param[$goodsData['sno']] = $goodsData['goodsCnt'];
                                }
                            }
                        }
                        $cancelMsg = [
                            'orderStatus' => 'c4',
                            'handleDetailReason' => __('고객요청에 의해 취소 처리'),
                        ];

                        $order->setAutoCancel($postValue['orderNo'], $param, $reOrderCalculation, $cancelMsg);
                    } else {
                        $order->setStatusChangeCancel($postValue['orderNo']);
                    }
                    $this->json(
                        [
                            'code'    => 200,
                            'message' => __('주문이 정상취소 되었습니다.'),
                        ]
                    );
                    break;

                // 구매확정
                case 'settleRegist':
                    $order->setStatusChangeSettle($postValue['orderNo'], $postValue['orderGoodsNo']);
                    $this->json(
                        [
                            'code'    => 200,
                            'message' => __('구매확정이 정상처리 되었습니다.'),
                        ]
                    );
                    break;

                //구매확정 일괄적용
                case 'settleRegistAll':
                    foreach ($postValue['orderGoodsNo'] as $orderGoodsNo) {
                        $order->setStatusChangeSettle($postValue['orderNo'], $orderGoodsNo);
                    }

                    throw new AlertRedirectException(__('구매확정이 정상처리 되었습니다.'), null, null, $postValue['returnUrl'], 'parent');

                    break;

                // 수취확인
                case 'deliveryCompleteRegist':
                    $order->setStatusChangeDeliveryComplete($postValue['orderNo'], $postValue['orderGoodsNo']);
                    $this->json(
                        [
                            'code'    => 200,
                            'message' => __('수취확인이 정상처리 되었습니다.'),
                        ]
                    );
                    break;

                // 환불/반품 처리 (레이어처리)
                case 'refundRegist':
                case 'backRegist':
                    // 플러스샵을 사용하는 경우에만 처리
                    if (gd_is_plus_shop(PLUSSHOP_CODE_USEREXCHANGE) === true) {
                        // 처리할 상품별 수량
                        if (empty($postValue['claimGoodsCnt']) == true && empty($postValue['orderGoodsCnt']) == false && is_array($postValue['orderGoodsNo']) == false) {
                            $goodsData[$postValue['orderGoodsNo']] = $postValue['orderGoodsCnt'];
                        } else {
                            foreach ($postValue['orderGoodsNo'] as $cKey => $cVal) {
                                $goodsData[$cVal] = $postValue['claimGoodsCnt'][$cVal];
                            }
                        }

                        $handleMode = substr($postValue['mode'], 0, 1);
                        $bundleData = [
                            'userHandleReason' => $postValue['userHandleReason'],
                            'userHandleDetailReason' => $postValue['userHandleDetailReason'],
                            'userRefundBankName' => $postValue['userRefundBankName'],
                            'userRefundAccountNumber' => $postValue['userRefundAccountNumber'],
                            'userRefundDepositor' => $postValue['userRefundDepositor'],
                            'userHandleGoodsCnt'     => $goodsData,
                        ];
                        $userHandleSno = $order->requestUserHandle($postValue['orderNo'], $postValue['orderGoodsNo'], $handleMode, $bundleData);
                        if ($handleMode == 'r') {
                            if ($userHandleSno === 'invalid_order') {
                                throw new AlertOnlyException(__('유효하지 않은 환불신청 정보입니다.'));
                            }
                            $config = ['smsAutoCodeType' => Code::REFUND];
                        } else {
                            $config = ['smsAutoCodeType' => Code::BACK];
                        }
                        $smsAutoOrder = new SmsAutoOrder($config);
                        $smsAutoOrder->setOrderNo($postValue['orderNo']);
                        $smsAutoOrder->setOrderGoodsNo($postValue['orderGoodsNo']);
                        $smsAutoOrder->autoSend();

                        if($postValue['mode'] == 'refundRegist'){
                            $result = $order->processAutoPgCancel($postValue, $userHandleSno);

                            // 이마트 보안취약점 요청사항 returnUrl 변조 방지
                            if (empty($returnUrl) || strpos($returnUrl, "order_view") === false) {
                                $postValue['returnUrl'] = Request::getReferer();
                            }
                        }
                        if ($result == 'ok') {
                            throw new AlertRedirectException(__('환불 처리가 완료되었습니다.'), null, null, $postValue['returnUrl'], 'parent');
                        } else {
                            if (empty($goodsData) === false && empty($postValue['claimGoodsCnt']) === false) {
                                throw new AlertRedirectException(($handleMode == 'r' ? __('환불') : __('반품')) . ' ' . __('신청이 완료되었습니다.'), null, null, $postValue['returnUrl'], 'parent');
                            } else if (empty($goodsData) === false && empty($postValue['orderGoodsCnt']) === false) {
                                $this->json(
                                    [
                                        'code'    => 200,
                                        'message' => ($handleMode == 'r' ? __('환불') : __('반품')) . ' ' . __('신청이 완료되었습니다.'),
                                    ]
                                );
                            } else {
                                throw new AlertRedirectException(($handleMode == 'r' ? __('환불') : __('반품')) . ' ' . __('접수가 완료되었습니다.'), null, null, $redirectUrl, 'parent');
                            }
                        }
                    }

                    break;

                // 교환 처리 (레이어처리)
                case 'exchangeRegist':
                    // 플러스샵을 사용하는 경우에만 처리
                    if (gd_is_plus_shop(PLUSSHOP_CODE_USEREXCHANGE) === true) {
                        // 처리할 상품별 수량
                        if (empty($postValue['claimGoodsCnt']) == true && empty($postValue['orderGoodsCnt']) == false && is_array($postValue['orderGoodsNo']) == false) {
                            $goodsData[$postValue['orderGoodsNo']] = $postValue['orderGoodsCnt'];
                        } else {
                            foreach ($postValue['orderGoodsNo'] as $cKey => $cVal) {
                                $goodsData[$cVal] = $postValue['claimGoodsCnt'][$cVal];
                            }
                        }

                        $handleMode = substr($postValue['mode'], 0, 1);
                        $bundleData = [
                            'userHandleReason' => $postValue['userHandleReason'],
                            'userHandleDetailReason' => $postValue['userHandleDetailReason'],
                            'userHandleGoodsCnt'     => $goodsData,
                        ];
                        $order->requestUserHandle($postValue['orderNo'], $postValue['orderGoodsNo'], $handleMode, $bundleData);
                        $config = ['smsAutoCodeType' => Code::EXCHANGE];
                        $smsAutoOrder = new SmsAutoOrder($config);
                        $smsAutoOrder->setOrderNo($postValue['orderNo']);
                        $smsAutoOrder->setOrderGoodsNo($postValue['orderGoodsNo']);
                        $smsAutoOrder->autoSend();
                        if (empty($goodsData) === false && empty($postValue['claimGoodsCnt']) === false) {
                            throw new AlertRedirectException(__('교환 신청이 완료되었습니다.'), null, null, $postValue['returnUrl'], 'parent');
                        } else if (empty($goodsData) === false && empty($postValue['orderGoodsCnt']) === false) {
                            $this->json(
                                [
                                    'code'    => 200,
                                    'message' => __('교환 신청이 완료되었습니다.'),
                                ]
                            );
                        } else {
                            throw new AlertRedirectException(__('교환 접수가 완료되었습니다.'), null, null, $redirectUrl, 'parent');
                        }
                    }

                    break;

                // 배송지 정보 수정 (레이어처리)
                case 'receiver_correct':
                    try {
                        $order->setOrderReceiverCorrect($postValue);
                        throw new AlertReloadException(__('배송정보가 수정되었습니다.'), null, null, 'parent');

                    } catch (Exception $e) {
                        throw new AlertOnlyException($e->getMessage());
                    }

                    break;

                // 현금영수증 신청
                case 'cashReceiptRequest':
                    try {
                        $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
                        $result = $cashReceipt->saveCashReceiptUser($postValue);

                        // 현금영수증 입력 정보 저장
                        if ($result['status'] === 'ok') {
                            $cashReceipt->saveMemberCashReceiptInfo();
                        }

                        $this->alert($result['msg'], null, null, null, 'parent.location.reload();');
                    } catch (Exception $e) {
                        throw new AlertOnlyException($e->getMessage());
                    }
                    break;

                // 세금계산서
                case 'taxInvoiceRequest':
                    try {
                        $postValue['memberOrder'] = true;
                        $tax = \App::load('\\Component\\Order\\Tax');

                        $result = $tax->saveTaxInvoice($postValue);

                        $this->alert(__("세금계산서 신청이 완료되었습니다."), null, null, null, 'parent.location.reload();');
                    } catch (Exception $e) {
                        throw new AlertOnlyException($e->getMessage());
                    }
                    break;
            }
            exit();

        } catch (Exception $e) {
            if (Request::isAjax()) {
                $this->json(
                    [
                        'code'    => 0,
                        'message' => $e->getMessage(),
                    ]
                );
            } else {
                throw $e;
            }
        }
    }
}
