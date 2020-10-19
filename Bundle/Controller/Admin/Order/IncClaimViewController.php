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
use Request;
use Component\Naver\NaverPay;
use Exception;

/**
 * 배송 업체 리스트 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class IncClaimViewController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // 주문 정보
            $order = App::load(\Component\Order\OrderAdmin::class);
            $data = $order->getOrderView(Request::request()->get('orderNo'));

            // 상품가공할 정보 처리
            $goods = $data['goods'];
            unset($data['goods'], $data['cnt']);

            // request 값에 따른 주문내역 처리
            switch (Request::request()->get('orderStatusMode')) {
                // 취소정보
                case 'cancel':
                    $data['handleModeStr'] = __('취소');
                    $data['statusMode'] = 'c';
                    break;

                case 'exchange':
                    $data['handleModeStr'] = __('교환');
                    $data['statusMode'] = 'e';

                    foreach ($order->getOrderStatusAdmin() as $key => $val) {
                        if (in_array(substr($key, 0, 1), $order->statusStandardCode[$data['statusMode']]) === true) {
                            if ($key == 'd1') {
                                continue;
                            }
                            $selectBoxOrderStatus[$key] = $val;
                        }
                    }
                    break;

                case 'back':
                    $data['handleModeStr'] = __('반품');
                    $data['statusMode'] = 'b';

                    foreach ($order->getOrderStatusAdmin() as $key => $val) {
                        if (in_array(substr($key, 0, 1), $order->statusStandardCode[$data['statusMode']]) === true) {
                            // 환불접수 처리
                            if ((substr($key, 0, 1) == 'r' && substr($key, 1, 1) > 1) || $key == 'd1') {
                                continue;
                            }
                            $selectBoxOrderStatus[$key] = $val;
                        }
                    }
                    break;

                case 'refund':
                    $data['handleModeStr'] = __('환불');
                    $data['statusMode'] = 'r';

                    foreach ($order->getOrderStatusAdmin() as $key => $val) {
                        if (in_array(substr($key, 0, 1), $order->statusStandardCode[$data['statusMode']]) === true) {
                            // 환불접수 처리
                            if ($key == 'd1' || (substr($key, 0, 1) == 'g' && $key != 'g1') || (substr($key, 0, 1) == 'b' && $key != 'b1') || (substr($key, 0, 1) == 'r' && $key != 'r2')) {
                                continue;
                            }
                            $selectBoxOrderStatus[$key] = $val;
                        }
                    }
                    break;

                default:
                    throw new Exception(__('호출 주문상태 파라미터가 정상적이지 않습니다.'));
                    break;
            }
            $data['incTitle'] = $data['handleModeStr'] . __('정보');
            $this->setData('orderStatusMode', Request::request()->get('orderStatusMode'));
            $this->setData('selectBoxOrderStatus', $selectBoxOrderStatus);

            // 취소사유
            if ($data['orderChannelFl'] == 'naverpay') {
                $naverPay = new NaverPay();
                $cancelReasonCode = $naverPay->getClaimReasonCode(null, 'cancel',false);
                $cancelReasonCode = array_merge(['' => '=' . __('사유선택') . '='], $cancelReasonCode);

                $backReasonCode = $naverPay->getClaimReasonCode(null, 'back',false);
                $backReasonCode = array_merge(['' => '=' . __('사유선택') . '='], $backReasonCode);
                $this->setData('backReason', gd_isset($backReasonCode));
            } else {
                $cancelReasonCode = gd_array_change_key_value(gd_code('04001'));
                $cancelReasonCode = array_merge(array('' => '=' . __('사유선택') . '='), $cancelReasonCode);
                $this->setData('backReason', gd_isset($cancelReasonCode));
            }
            $this->setData('refundReason', gd_isset($cancelReasonCode));

            // 환불수단
            $refundMethodCode = gd_array_change_key_value(gd_code('04003'));
            $refundMethodCode = array_merge(array('' => '=' . __('환불수단 선택') . '='), $refundMethodCode);
            $this->setData('refundMethod', gd_isset($refundMethodCode));

            // 환불 계좌 은행
            $bankNmCode = gd_array_change_key_value(gd_code('04002'));
            $this->setData('bankNm', gd_isset($bankNmCode));

            // 주문내역 종류별 상품 재 구성
            foreach ($goods as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    foreach ($dVal as $key => $val) {
                        if($data['statusMode'] === 'e'){
                            //개선된 교환프로세스
                            if ($val['handleSno'] > 0 && $val['handleMode'] === 'e') {
                                $exchangeData[$val['handleGroupCd']][$val['handleMode']][] = $val;
                            }
                        }
                        else {
                            if ($val['handleSno'] > 0 && $val['handleMode'] === $data['statusMode']) {
                                $data['goods'][$sKey][$dKey][] = $val;

                                // 테이블 UI 표현을 위한 변수
                                $addGoodsCnt = $val['addGoodsCnt'];
                                $data['cnt']['scm'][$sKey] += 1 + $addGoodsCnt;
                                $data['cnt']['goods']['all'] += 1 + $addGoodsCnt;
                                $data['cnt']['goods']['goods'] += 1;
                                $data['cnt']['goods']['addGoods'] += $addGoodsCnt;
                                $data['cnt']['delivery'][$dKey] += 1 + $addGoodsCnt;
                            }
                        }
                    }
                }
            }

            //교환정보 재배열처리
            if($data['statusMode'] === 'e'){
                $orderReorderCalculation = \App::load(\Component\Order\ReOrderCalculation::class);

                ksort($exchangeData);
                foreach($exchangeData as $key => $valArr){
                    ksort($exchangeData[$key]);
                    foreach($valArr as $key2 => $val2Arr){
                        $data['cnt']['row'][$key] += count($exchangeData[$key][$key2]);
                        foreach($val2Arr as $key3 => $val3){
                            if(gd_use_provider() === true){
                                if((int)DEFAULT_CODE_SCMNO !== (int)\Session::get('manager.scmNo') && (int)$val3['scmNo'] !== (int)\Session::get('manager.scmNo')){
                                    continue;
                                }
                            }

                            if((int)$val3['handleGroupCd'] > 0){
                                $exchangeData[$key][$key2][$key3]['exchangeHandle'] = $orderReorderCalculation->getOrderExchangeHandle(Request::request()->get('orderNo'), $val3['handleGroupCd'])[0];
                            }
                        }
                    }
                }

                $data['goods'] = $exchangeData;
            }

            // 주문데이터 반환
            $this->setData('data', $data);

            // 템플릿 설정
            $this->getView()->setDefine('layout', 'layout_layer.php');

            if($data['statusMode'] === 'e'){
                // 공급사와 동일한 페이지 사용
                $this->getView()->setPageName('order/inc_claim_view_exchange.php');
            }
            else {
                // 공급사와 동일한 페이지 사용
                $this->getView()->setPageName('order/inc_claim_view.php');
            }

        } catch (Exception $e) {
            throw $e;
        }
    }
}
