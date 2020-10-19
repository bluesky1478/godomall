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

use Component\Mall\Mall;
use Component\Order\Order;
use Exception;

/**
 * 주문 상태 설정 관리 페이지
 * [관리자 모드] 주문 상태 설정 관리 페이지
 *
 * @package Bundle\Controller\Admin\Policy
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderStatusController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            // 메뉴 설정
            $this->callMenu('policy', 'order', 'status');

            // 모듈 호출
            $policy = gd_policy('order.status');
            gd_isset($policy['autoCancel'], 10);
            gd_isset($policy['settle']['mplus'], 'y');
            gd_isset($policy['settle']['cplus'], 'y');
            gd_isset($policy['delivery']['sminus'], 'y');

            // 주문 상태 단계
            // @formatter:off
            $orderStep[Order::ORDER_STATUS_ORDER] = [
                'code'    => 'o',
                'add'     => 'y',
                'title'   => __('주문'),
                'correct' => 'y',
                'mplus'    => 'n',
                'cplus'    => 'n',
                'mminus'   => 'y',
                'cminus'   => 'y',
                'sminus'   => 'y',
                'sub'     =>
                    [
                        'o1' => ['title'         => __('입금대기'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('주문 후 입금 전 상태로 무통장, 가상계좌, 기타가 해당됩니다.'),
                        ],
                    ],
            ];

            $orderStep[Order::ORDER_STATUS_PAYMENT] = [
                'code'    => 'p',
                'add'     => 'y',
                'title'   => __('입금'),
                'correct' => 'y',
                'mplus'    => 'y',
                'cplus'    => 'y',
                'mminus'   => 'n',
                'cminus'   => 'n',
                'sminus'   => 'y',
                'sub'     =>
                    [
                        'p1' => ['title'         => __('결제완료'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('입금확인된 상태, 또는 결제완료된 상태입니다.'),
                        ],
                    ],
            ];

            $orderStep[Order::ORDER_STATUS_GOODS] = [
                'code'    => 'g',
                'add'     => 'y',
                'title'   => __('상품'),
                'correct' => 'n',
                'mplus'    => 'n',
                'cplus'    => 'n',
                'mminus'   => 'n',
                'cminus'   => 'n',
                'sminus'   => 'n',
                'sub'     =>
                    [
                        'g1' => ['title'         => __('상품준비중'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('배송준비 단계로 상품여부를 확인하는 단계입니다.'),
                        ],
                        'g2' => ['title'         => __('구매발주'),
                                 'indispensable' => 'n',
                                 'mode'          => 'oi',
                                 'desc'          => __('배송준비 단계로 상품부족시 발주하는 단계입니다.'),
                        ],
                        'g3' => ['title'         => __('상품입고'),
                                 'indispensable' => 'n',
                                 'mode'          => 'oi',
                                 'desc'          => __('배송준비 단계로 부족상품이 입고된 상태입니다.'),
                        ],
                        'g4' => ['title'         => __('상품출고'),
                                 'indispensable' => 'n',
                                 'mode'          => 'oi',
                                 'desc'          => __('배송준비 완료 단계입니다.'),
                        ],
                    ],
            ];

            $orderStep[Order::ORDER_STATUS_DELIVERY] = [
                'code'    => 'd',
                'add'     => 'y',
                'title'   => __('배송'),
                'correct' => 'n',
                'mplus'    => 'y',
                'cplus'    => 'y',
                'mminus'   => 'n',
                'cminus'   => 'n',
                'sminus'   => 'y',
                'sub'     =>
                    [
                        'd1' => ['title'         => __('배송중'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('상품이 출고되서 배송중인 상태입니다.'),
                        ],
                        'd2' => ['title'         => __('배송완료'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('배송 완료된 상태입니다. (고객이 수취확인하거나, 관리자가 변경한 상태'),
                        ],
                    ],
            ];

            $orderStep[Order::ORDER_STATUS_SETTLE] = [
                'code'    => 's',
                'add'     => 'y',
                'title'   => __('구매확정'),
                'correct' => 'n',
                'mplus'    => 'y',
                'cplus'    => 'y',
                'mminus'   => 'n',
                'cminus'   => 'n',
                'sminus'   => 'n',
                'sub'     =>
                    [
                        's1' => ['title'         => __('구매확정'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('구매확정된 상태입니다. (고객이 구매확정하거나, 관리자가 상태변경)'),
                        ],
                    ],
            ];

            // --- 취소 상태 단계
            $cancelStep[Order::ORDER_STATUS_CANCEL] = [
                'code'    => 'c',
                'add'     => 'y',
                'title'   => __('취소'),
                'mrestore'    => 'y',
                'crestore'   => 'y',
                'srestore'   => 'y',
                'sub'     =>
                    [
                        'c1' => ['title'         => __('자동취소'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('주문접수 후 오랜동안 미입금 되거나, 가상계좌 만료된 상태입니다.'),
                        ],
                        'c2' => ['title'         => __('품절취소'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('주문접수 후 상품재고가 없어 취소된 상태입니다.'),
                        ],
                        'c3' => ['title'         => __('관리자취소'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('주문접수 후 관리자가 여러 원인에의해 임의 취소한 상태입니다.'),
                        ],
                        'c4' => ['title'         => __('고객취소요청'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('주문접수 단계에서 고객이 취소요청을 한 상태입니다.'),
                        ],
                    ],
            ];

            $cancelStep[Order::ORDER_STATUS_FAIL] = [
                'code'    => 'f',
                'add'     => 'n',
                'title'   => __('실패'),
                'mrestore'    => 'n',
                'crestore'   => 'n',
                'srestore'   => 'n',
                'sub'     =>
                    [
                        'f1' => ['title'         => __('결제시도'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('고객결제 후 PG사에서 결과값을 받지 못한 상태입니다.'),
                        ],
                        'f2' => ['title'         => __('고객결제중단'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('고객이 결제완료 전 PG사 결제창을 닫거나 다른 페이지로 이동한 상태입니다.'),
                        ],
                        'f3' => ['title'         => __('결제실패'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('고객결제 후 PG사에서 결제실패 결과값을 받은 상태입니다.'),
                        ],
                        'f4' => ['title'         => __('PG확인요망'),
                            'indispensable' => 'y',
                            'mode'          => 'oi',
                            'desc'          => __('고객결제 후 PG사에서 결과값을 받지 못하여 PG사에서 확인이 필요한 상태입니다.'),
                        ],
                    ],
            ];

            $cancelStep[Order::ORDER_STATUS_BACK] = [
                'code'    => 'b',
                'add'     => 'n',
                'title'   => __('반품'),
                'mrestore'    => 'n',
                'crestore'   => 'n',
                'srestore'   => 'y',
                'sub'     =>
                    [
                        'b1' => ['title'         => __('반품접수'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('배송후 환불/교환 목적으로 반품을 접수하는 단계입니다.'),
                        ],
                        'b2' => ['title'         => __('반송중'),
                                 'indispensable' => 'n',
                                 'mode'          => 'oi',
                                 'desc'          => __('고객이 반품한 상품을 쇼핑몰에서 다시 고객에게 반송하는 단계입니다.'),
                        ],
                        'b3' => ['title'         => __('반품보류'),
                                 'indispensable' => 'n',
                                 'mode'          => 'oi',
                                 'desc'          => __('고객이 접수한 반품요청을 보류처리한 상태입니다.'),
                        ],
                        'b4' => ['title'         => __('반품회수완료'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('고객이 반품한 상품이 쇼핑몰에 회수완료된 상태입니다.'),
                        ],
                    ],
            ];

            $cancelStep[Order::ORDER_STATUS_EXCHANGE] = [
                'code'    => 'e',
                'add'     => 'n',
                'title'   => __('교환 취소'),
                'mrestore'    => 'n',
                'crestore'   => 'n',
                'srestore'   => 'y',
                'sub'     =>
                    [
                        'e1' => ['title'         => __('교환접수'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('반품 접수이후 상품 교환 접수 단계입니다.'),
                        ],
                        'e2' => ['title'         => __('반송중'),
                                 'indispensable' => 'n',
                                 'mode'          => 'oi',
                                 'desc'          => __('고객이 상품교환을 위해 받은 상품을 다시 반송하는 단계입니다.'),
                        ],
                        'e3' => ['title'         => __('재배송중'),
                                 'indispensable' => 'n',
                                 'mode'          => 'oi',
                                 'desc'          => __('반송된 상품을 확인하고 교환상품을 재발송하는 단계입니다.'),
                        ],
                        'e4' => ['title'         => __('교환보류'),
                                 'indispensable' => 'n',
                                 'mode'          => 'oi',
                                 'desc'          => __('고객이 접수한 반품요청을 보류처리한 상태입니다.'),
                        ],
                        'e5' => ['title'         => __('교환완료'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('교환상품이 배송완료된 상태입니다.'),
                        ],
                    ],
            ];

            $cancelStep[Order::ORDER_STATUS_REFUND] = [
                'code'    => 'r',
                'add'     => 'n',
                'title'   => __('환불'),
                'mrestore'    => 'n',
                'crestore'   => 'n',
                'srestore'   => 'y',
                'sub'     =>
                    [
                        'r1' => ['title'         => __('환불접수'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('입금확인 또는 반품요청 이후 관리자가 환불 처리하는 단계입니다.'),
                        ],
                        'r2' => ['title'         => __('환불보류'),
                                 'indispensable' => 'n',
                                 'mode'          => 'oi',
                                 'desc'          => __('고객이 접수한 환불요청을 보류처리한 상태입니다.'),
                        ],
                        'r3' => ['title'         => __('환불완료'),
                                 'indispensable' => 'y',
                                 'mode'          => 'oi',
                                 'desc'          => __('환불이 완료된 상태로, 해당주문상품이 취소완료된 상태입니다.'),
                        ],
                    ],
            ];
            // @formatter:on

            $checked['correct']['y'] = $checked['useFl']['y'] = $checked['mplus']['y'] = $checked['cplus']['y'] = $checked['mminus']['y'] = $checked['cminus']['y'] = $checked['sminus']['y'] = $checked['mrestore']['y'] = $checked['crestore']['y'] = $checked['srestore']['y'] = 'checked="checked"';

            $mall = new Mall();

            // --- 관리자 디자인 템플릿
            $this->setData('policy', $policy);
            $this->setData('orderStep', $orderStep);
            $this->setData('cancelStep', $cancelStep);
            $this->setData('step', array_merge($orderStep, $cancelStep));
            $this->setData('checked', $checked);
            $this->setData('isUsableMall', $mall->isUsableMall());

        } catch (Exception $e) {
            throw $e;
        }
    }
}
