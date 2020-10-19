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

use Component\Payment\CashReceipt;
use Message;
use Request;

/**
 * 현금영수증 처리 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class CashReceiptPsController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // _POST 데이터
        $postValue = Request::post()->toArray();

        // --- 모듈 호출
        $cashReceipt  = new CashReceipt();

        // 각 모드에 따른 처리
        switch ($postValue['mode']) {
            // --- 현금영수증 개별 발급 주문번호 유효성 체크
            case 'cash_receipt_order_check':
                // 개별 발급 요청 저장
                $result = $cashReceipt->checkCashReceiptOrderNo($postValue['orderNo']);
                echo $result;

                break;

            // --- 현금영수증 개별 발급 요청 저장
            case 'cash_receipt_register':
                try {
                    // 개별 발급 요청 저장
                    $cashReceipt->saveCashReceiptEach($postValue);

                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // --- 현금영수증 발급 요청 저장
            case 'cash_receipt_register_order':
                try {
                    // 현금영수증 발급 요청 저장
                    $cashReceipt->saveCashReceiptEach($postValue);

                    // 주문서의 현금영수증 발급 처리 업데이트
                    $order = \App::load('\\Component\\Order\\OrderAdmin');
                    $order->setOrderReceiptRequest($postValue['orderNo'], 'r');

                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // 현금영수증 거절
            case 'cash_receipt_deny':
                // 현금영수증 재발행기능때문에 주문번호 중복이슈가 있을 수 있어 인자값 일련번호로 변경
                //$result = $cashReceipt->setCashReceiptDeny($postValue['orderNo']);
                $result = $cashReceipt->setCashReceiptDeny($postValue['sno']);
                if ($result === true) {
                    echo 'SUCCESS';
                } else {
                    echo 'FAIL';
                }
                break;

            // 현금영수증 재요청
            case 'cash_receipt_request':
                // 현금영수증 재발행기능때문에 주문번호 중복이슈가 있을 수 있어 인자값 일련번호로 변경
                //$result = $cashReceipt->setCashReceiptRequest($postValue['orderNo']);
                $result = $cashReceipt->setCashReceiptRequest($postValue['sno']);
                if ($result === true) {
                    echo 'SUCCESS';
                } else {
                    echo 'FAIL';
                }
                break;

            // 현금영수증 삭제
            case 'cash_receipt_delete':
                // 현금영수증 재발행기능떄문에 주문번호 중복이슈가 있을 수 있어 인자값 일련번호 추가
                $result = $cashReceipt->setCashReceiptDelete($postValue['orderNo'], $postValue['sno']);
                if ($result === true) {
                    echo 'SUCCESS';
                } else {
                    echo 'FAIL';
                }
                break;

            // 현금영수증 PG 발급 요청
            case 'pg_approval':
                try {
                    // 현금영수증 PG 발급 요청
                    if (empty($postValue['modeType']) === true) {
                        $postValue['modeType'] = 'each';
                    }
                    $result = $cashReceipt->sendPgCashReceipt($postValue['orderNo'], $postValue['modeType'], 'approval','','','',$postValue['sno']);
                    if ($postValue['modeType'] === 'each') {
                        if ($result === true) {
                            $this->layer(__('현금영수증 발급 되었습니다.'));
                        } else {
                            $this->layer(__('현금영수증 발급에 실패 하였습니다.'));
                        }
                    } else {
                        if ($result === true) {
                            echo 'SUCCESS';
                        } else {
                            echo 'FAIL';
                        }
                    }
                } catch (\Exception $e) {
                    if ($postValue['modeType'] === 'each') {
                        $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                        $this->layer(__('현금영수증 발급에 실패 하였습니다.') . $item);
                    } else {
                        echo 'FAIL';
                    }
                }
                break;

            // 현금영수증 PG 발급 요청
            case 'pg_approval_selected':
                try {
                    // 현금영수증 PG 발급 요청
                    $result = $cashReceipt->sendPgCashReceipt($postValue['orderNo'], 'selected', 'approval','','','',$postValue['sno']);
                    if ($result === true) {
                        $this->layer(__('현금영수증 발급 되었습니다.'));
                    } else {
                        $this->layer(__('현금영수증 발급에 실패 하였습니다.'));
                    }
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('현금영수증 발급에 실패 하였습니다.') . $item);
                }
                break;

            // 현금영수증 PG 발급 취소
            case 'pg_cancel':
                try {
                    // 현금영수증 PG 발급pg_cancel 요청
                    if (empty($postValue['modeType']) === true) {
                        $postValue['modeType'] = 'each';
                    }
                    $result = $cashReceipt->sendPgCashReceipt($postValue['orderNo'], $postValue['modeType'], 'cancel', $postValue['cancelReason'], '', $postValue['adminChk'], $postValue['sno']);

                    if ($postValue['modeType'] === 'each') {
                        if ($result === true) {
                            $this->layer(__('현금영수증 발급 취소 되었습니다.'));
                        } else {
                            $this->layer(_('현금영수증 발급 취소에 실패 하였습니다.'));
                        }
                    } else {
                        if ($result === true) {
                            echo 'SUCCESS';
                        } else {
                            echo 'FAIL';
                        }
                    }
                } catch (\Exception $e) {
                    if ($postValue['modeType'] === 'each') {
                        $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                        $this->layer(__('현금영수증 발급 취소에 실패 하였습니다.') . $item);
                    } else {
                        echo 'FAIL';
                    }
                }
                break;

            // 현금영수증 재발행(발급신청)
            case 'cash_receipt_reissue_request':
            case 'cash_receipt_reissue_immediately':
                try {
                    $res = $cashReceipt->saveCashReceiptReissueRequest($postValue);
                    if(empty($res['mode']) === true){
                        $this->layer(__('현금영수증 발급이 완료되었습니다. 현금영수증 발급/조회 메뉴에서 영수증 조회가 가능합니다.'));
                    }else{
                        $this->layer(__('현금영수증 발급을 신청하였습니다. 현금영수증 발급처리를 해주세요.'));
                    }
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;
        }
        exit();
    }
}
