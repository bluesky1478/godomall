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
use Component\Cart\CartAdmin;
use Component\Excel\ExcelVisitStatisticsConvert;
use Component\Godo\NaverPayAPI;
use Component\Order\OrderAdmin;
use Component\Sms\Code;
use Component\Sms\SmsAutoOrder;
use Component\Order\Order;
use Component\Member\Manager;
use Component\Bankda\BankdaOrder;
use DB;
use Exception;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\LayerNotReloadException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Utility\StringUtils;
use Message;
use Request;
use Cookie;

/**
 * 주문 상세 처리 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderPsController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws LayerException
     */
    public function index()
    {
        // --- 모듈 호출
        $order = new OrderAdmin();
        $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
        $delivery = \App::load('\\Component\\Delivery\\Delivery');
        $paycoConfig = $dbUrl->getConfig('payco', 'config');

        switch (Request::request()->get('mode')) {
            // 송장일괄등록 샘플 다운로드
            case 'invoice_download':
                try {
                    if (Manager::isProvider()) {
                        $downloadPath = App::getBasePath() . '/data/excel/order_scmOrderInvoiceSample.xls';
                    } else {
                        $downloadPath = App::getBasePath() . '/data/excel/order_orderInvoiceSample.xls';
                    }
                    $this->download($downloadPath, 'invoice_excel_sample.xls');
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // --- 주문 수정
            case 'modify':
                try {
                    DB::transaction(
                        function () use ($order, $paycoConfig) {
                            $order->updateOrder(Request::post()->toArray());

                            if ($paycoConfig['paycoFl'] == 'y') {
                                // 페이코쇼핑 결제데이터 전달
                                $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
                                $payco->paycoShoppingRequest(Request::post()->get('orderNo'));
                            }
                        }
                    );
                    $this->layer(__('저장이 완료되었습니다.'), null, 2000);
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new LayerException($e->getMessage(), null, null, null, 7000);
                    }
                }
                break;

            // 주문 상세페이지 - 주문상태, 송장번호 변경
            case 'modifyOrderStatusDelivery':
                try {
                    $orderChannelFl = Request::post()->get('orderChannelFl');

                    if($orderChannelFl === 'etc') {
                        // 기타 외부채널 주문건은 어떤한 처리도 없이 주문상태값만 변경하여 준다.
                        $externalOrder = App::load('\\Component\\Order\\ExternalOrder');

                        DB::transaction(
                            function () use ($externalOrder) {
                                $externalOrder->updateOrderStatusDelivery(Request::post()->toArray());
                            }
                        );
                    }
                    else {
                        $orderStatusUpdateFl = Request::post()->get('orderStatusUpdateFl');
                        //배송상태 변경이고 변경 주문건이 모두 방문수령일 경우 SMS발송 제한
                        if ($orderStatusUpdateFl == 'y' && substr(Request::post()->get('changeStatus'), 0, 1) == 'd') {
                            $tmpDelivery = $delivery->getDeliveryCompany(null, true);
                            $visitSno = '';
                            $deliverySno = [];
                            if (empty($tmpDelivery) === false) {
                                foreach ($tmpDelivery as $key => $val) {
                                    if ($val['companyKey'] == 'visit') {
                                        $visitSno = $val['sno'];
                                        break;
                                    }
                                }
                                unset($tmpDelivery);
                            }
                            foreach (Request::post()->get('invoiceCompanySno') as $status => $goodsSno) {
                                foreach ($goodsSno as $key => $companySno) {
                                    if (empty(Request::post()->get('invoiceNo')[$status][$key]) === true) {
                                        if (empty(Request::post()->get('defaultInvoiceCompanySno')[$status][$key]) === false) {
                                            $deliverySno[] = Request::post()->get('defaultInvoiceCompanySno')[$status][$key];
                                        } else {
                                            if (Request::post()->get('deliveryMethodFl')[$status][$key] == 'visit') {
                                                $deliverySno[] = $visitSno;
                                            }
                                        }
                                    } else {
                                        $deliverySno[] = $companySno;
                                    }
                                }
                            }
                            if (empty($deliverySno) === false && !array_diff($deliverySno, [$visitSno])) {
                                $orderStatusUpdateFl = 'n';
                                Request::post()->set('useVisit', 'y');
                            }
                        }
                        if($orderStatusUpdateFl === 'y'){
                            $smsAuto = \App::load('Component\\Sms\\SmsAuto');
                            $smsAuto->setUseObserver(true);
                            $order->setChannel(Request::post()->get('orderChannelFl'));
                        }

                        // 안심번호 재요청
                        if (substr(Request::post()->get('changeStatus'), 0, 1) == 'g') {
                            $tmpCheckData = Request::post()->get('statusCheck');
                            if (isset($tmpCheckData['p']) && count($tmpCheckData['p']) > 0) {
                                $tmpArr = explode(INT_DIVISION, $tmpCheckData['p'][0]);
                                $orderNo = $tmpArr[0];
                                $safeNumber = \App::load('Component\\Service\\SafeNumber');
                                $safeNumber->resetSafeNumberByOrderNo($orderNo);
                            }
                        }

                        if(\Request::post()->get('orderChannelFl') == 'naverpay') { //네이버페이는 트랜젹선 안태움.(실시간 DB업데이트가 아닌 api통신)
                            $order->updateOrderStatusDelivery(Request::post()->toArray());
                        }
                        else {
                            DB::transaction(
                                function () use ($order) {
                                    $order->updateOrderStatusDelivery(Request::post()->toArray());
                                }
                            );
                        }

                        if($orderStatusUpdateFl === 'y'){
                            $smsAuto->notify();
                        }
                    }

                    $this->layer(__('저장이 완료되었습니다.'), null, 2000);
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new LayerException($e->getMessage(), null, null, null, 7000);
                    }
                }
                break;

            // 주문 상세페이지 - 주문자 정보 수정
            case 'modifyOrderInfo':
                try {
                    DB::transaction(
                        function () use ($order) {
                            $order->updateOrderOrderInfo(Request::post()->toArray());
                        }
                    );
                    $this->layer(__('저장이 완료되었습니다.'), null, 2000);
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new LayerException($e->getMessage(), null, null, null, 7000);
                    }
                }
                break;

            // 주문 상세페이지 - 수령자 정보 수정
            case 'modifyReceiverInfo':
                try {
                    DB::transaction(
                        function () use ($order) {
                            $order->updateOrderReceiverInfo(Request::post()->toArray());
                        }
                    );
                    $this->layer(__('저장이 완료되었습니다.'), null, 2000);
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new LayerException($e->getMessage(), null, null, null, 7000);
                    }
                }
                break;

            // 주문 상세페이지 - 요청사항 / 고객상담메모 수정
            case 'modifyConsultMemo':
                try {
                    DB::transaction(
                        function () use ($order) {
                            $order->updateOrderConsultMemo(Request::post()->toArray());
                        }
                    );
                    $this->layer(__('저장이 완료되었습니다.'), null, 2000);
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new LayerException($e->getMessage(), null, null, null, 7000);
                    }
                }
                break;

            // 주문 상세페이지 - 관리자메모 수정
            case 'modifyAdminMemo':
                try {
                    DB::transaction(
                        function () use ($order) {
                            $order->updateOrderAdminMemo(Request::post()->toArray());
                        }
                    );
                    $this->layer(__('저장이 완료되었습니다.'), null, 2000);
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new LayerException($e->getMessage(), null, null, null, 7000);
                    }
                }
                break;

            // 주문 상세페이지 - 입금계좌 수정
            case 'modifyBankInfo':
                try {
                    DB::transaction(
                        function () use ($order) {
                            $order->updateBankInfo(Request::post()->toArray());
                        }
                    );
                    $this->layer(__('저장이 완료되었습니다.'), null, 2000);
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new LayerException($e->getMessage(), null, null, null, 7000);
                    }
                }
                break;

            // 주문컨설팅 삭제
            case 'delete_consult':
                try {
                    if ($order->deleteOrderConsult(Request::post()->get('sno')) !== false) {
                        throw new LayerException(__('요청사항/상담메모가 정상 삭제되었습니다.'), 0);
                    } else {
                        throw new LayerException(__('요청사항/상담메모 삭제처리를 실패하였습니다.'), 1);
                    }
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => $e->getCode(),
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // --- 입금 확인 처리 - 주문리스트에서
            case 'status_payment':
                try {
                    $orderNo = Request::post()->get('orderNo');
                    $orderData = $order->getOrderData($orderNo);

                    if($orderData['orderChannelFl'] === 'etc'){
                        $externalOrder = \App::load('\\Component\\Order\\ExternalOrder');
                        $externalOrder->setStatusChangePayment($orderNo);
                    }
                    else {
                        $order->setStatusChangePayment($orderNo);
                    }

                    if ($paycoConfig['paycoFl'] == 'y') {
                        // 페이코쇼핑 결제데이터 전달
                        $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
                        $payco->paycoShoppingRequest(Request::post()->get('orderNo'));
                    }
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                exit();
                break;

            // --- 주문 접수 처리 - 주문상세에서
            case 'status_order':
                try {
                    $order->setStatusChangeOrder(Request::post()->get('orderNo'));

                    if ($paycoConfig['paycoFl'] == 'y') {
                        // 페이코쇼핑 결제데이터 전달
                        $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
                        $payco->paycoShoppingRequest(Request::post()->get('orderNo'));
                    }
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                exit();
                break;

            // --- 주문 일괄취소 처리 - 입금대기리스트에서
            case 'combine_status_cancel':
                try {
                    $postValueEtc = $postValue = [];
                    $tmpPostValue = Request::post()->toArray();
                    $postValue = $tmpPostValue;
                    unset($postValue['statusCheck'], $postValue['orderStatus'], $postValue['escrowCheck']);

                    // 외부채널 주문건과 나머지 채널 주문건의 처리를 다르게 하기위해 post 값 재구성
                    if(count($tmpPostValue) > 0){
                        foreach($tmpPostValue['statusCheck'] as $statusCode => $valueArray){
                            $checkBoxCdArray = array_keys($tmpPostValue['orderChannelFl'][$statusCode]);
                            if(count($valueArray) > 0){
                                foreach($valueArray as $index => $checkBoxCd){
                                    $reverseCheckBoxCdArray = array_flip($checkBoxCdArray);
                                    $realIndex = $reverseCheckBoxCdArray[$checkBoxCd];
                                    if($tmpPostValue['orderChannelFl'][$statusCode][$checkBoxCd] === 'etc'){
                                        $postValueEtc['statusCheck'][$statusCode][] = $checkBoxCd;
                                        $postValueEtc['orderStatus'][$statusCode][] = $tmpPostValue['orderStatus'][$statusCode][$realIndex];
                                        $postValueEtc['escrowCheck'][$statusCode][] = $tmpPostValue['escrowCheck'][$statusCode][$realIndex];
                                    }
                                    else {
                                        $postValue['statusCheck'][$statusCode][] = $checkBoxCd;
                                        $postValue['orderStatus'][$statusCode][] = $tmpPostValue['orderStatus'][$statusCode][$realIndex];
                                        $postValue['escrowCheck'][$statusCode][] = $tmpPostValue['escrowCheck'][$statusCode][$realIndex];
                                    }
                                }
                            }
                        }
                    }

                    // --- 외부채널 주문건 상태변경
                    if(count($postValueEtc) > 0){
                        $postValueEtc['mode'] = $tmpPostValue['mode'];
                        $postValueEtc['changeStatus'] = 'c3';
                        $postValueEtc['orderStatusBottom'] = $tmpPostValue['orderStatusBottom'];
                        $externalOrder = \App::load('\\Component\\Order\\ExternalOrder');
                        $externalOrder->setCombineStatusCancelList($postValueEtc);
                    }

                    // --- 일반채널 주문건 상태변경
                    if(count($postValue) > 0){
                        $postValue['mode'] = $tmpPostValue['mode'];
                        $postValue['changeStatus'] = $tmpPostValue['changeStatus'];
                        $postValue['orderStatusBottom'] = $tmpPostValue['orderStatusBottom'];
                        $order->setCombineStatusCancelList($postValue);
                    }

                    throw new LayerException(__('주문취소 처리가 완료되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // --- 주문 상태 변경 (주문통합리스트)
            case 'combine_status_change':
                try {
                    $postValueEtc = $postValue = [];
                    $tmpPostValue = Request::post()->toArray();
                    $postValue = $tmpPostValue;
                    unset($postValue['statusCheck'], $postValue['orderStatus'], $postValue['escrowCheck'], $postValue['invoiceCompanySno'], $postValue['deliveryMethodFl']);

                    // 외부채널 주문건과 나머지 채널 주문건의 처리를 다르게 하기위해 post 값 재구성
                    if(count($tmpPostValue) > 0){
                        foreach($tmpPostValue['statusCheck'] as $statusCode => $valueArray){
                            $checkBoxCdArray = array_keys($tmpPostValue['orderChannelFl'][$statusCode]);
                            if(count($valueArray) > 0){
                                foreach($valueArray as $index => $checkBoxCd){
                                    $reverseCheckBoxCdArray = array_flip($checkBoxCdArray);
                                    $realIndex = $reverseCheckBoxCdArray[$checkBoxCd];
                                    if($tmpPostValue['orderChannelFl'][$statusCode][$checkBoxCd] === 'etc'){
                                        $postValueEtc['statusCheck'][$statusCode][] = $checkBoxCd;
                                        $postValueEtc['orderStatus'][$statusCode][] = $tmpPostValue['orderStatus'][$statusCode][$realIndex];
                                        $postValueEtc['escrowCheck'][$statusCode][] = $tmpPostValue['escrowCheck'][$statusCode][$realIndex];
                                    }
                                    else {
                                        $postValue['statusCheck'][$statusCode][] = $checkBoxCd;
                                        $postValue['orderStatus'][$statusCode][] = $tmpPostValue['orderStatus'][$statusCode][$realIndex];
                                        $postValue['escrowCheck'][$statusCode][] = $tmpPostValue['escrowCheck'][$statusCode][$realIndex];
                                        $postValue['invoiceCompanySno'][$statusCode][] = $tmpPostValue['invoiceCompanySno'][$statusCode][$realIndex];
                                        $postValue['deliveryMethodFl'][$statusCode][] = $tmpPostValue['deliveryMethodFl'][$statusCode][$realIndex];
                                    }
                                }
                            }
                        }
                    }

                    // --- 외부채널 주문건 상태변경
                    if(count($postValueEtc) > 0){
                        $postValueEtc['mode'] = $tmpPostValue['mode'];
                        $postValueEtc['changeStatus'] = $tmpPostValue['changeStatus'];
                        $postValueEtc['orderStatusBottom'] = $tmpPostValue['orderStatusBottom'];
                        $postValueEtc['orderStatusUpdateFl'] = 'y';

                        $externalOrder = \App::load('\\Component\\Order\\ExternalOrder');
                        DB::transaction(
                            function () use ($externalOrder, $postValueEtc) {
                                $externalOrder->updateOrderStatusDelivery($postValueEtc);
                            }
                        );
                    }

                    // --- 일반채널 주문건 상태변경
                    if(count($postValue) > 0){
                        $orderStatusUpdateFl = 'y';
                        //배송상태 변경이고 변경 주문건이 모두 방문수령일 경우 SMS발송 제한
                        if ($orderStatusUpdateFl == 'y' && substr($postValue['changeStatus'], 0, 1) == 'd') {
                            $tmpDelivery = $delivery->getDeliveryCompany(null, true);
                            $visitSno = '';
                            $deliverySno = [];
                            if (empty($tmpDelivery) === false) {
                                foreach ($tmpDelivery as $key => $val) {
                                    if ($val['companyKey'] == 'visit') {
                                        $visitSno = $val['sno'];
                                        break;
                                    }
                                }
                                unset($tmpDelivery);
                            }
                            foreach ($postValue['invoiceCompanySno'] as $goodsSno) {
                                foreach ($goodsSno as $companySno) {
                                    if (empty($companySno) === false) {
                                        $deliverySno[] = $companySno;
                                    } else {
                                        if ($postValue['deliveryMethodFl'][$goodsSno] == 'visit') {
                                            $deliverySno[] = $visitSno;
                                        }
                                    }
                                }
                            }
                            if (empty($deliverySno) === false && !array_diff($deliverySno, [$visitSno])) {
                                $postValue['useVisit'] = $orderStatusUpdateFl = 'n';
                                Request::post()->set('useVisit', 'y');
                            }
                        }
                        if($orderStatusUpdateFl === 'y') {
                            $smsAuto = \App::load('Component\\Sms\\SmsAuto');
                            $smsAuto->setUseObserver(true);
                        }

                        // 안심번호 재요청
                        if (substr($postValue['changeStatus'], 0, 1) == 'g') {
                            $tmpCheckData = $postValue['statusCheck'];
                            if (isset($tmpCheckData['p']) && count($tmpCheckData['p']) > 0) {
                                $safeNumber = \App::load('Component\\Service\\SafeNumber');
                                $beforeOrderNo = null;
                                foreach ($tmpCheckData['p'] as $tKey => $tVal) {
                                    $tmpArr = explode(INT_DIVISION, $tVal);
                                    if ($beforeOrderNo != $tmpArr[0]) {
                                        $beforeOrderNo = $tmpArr[0];
                                        $safeNumber->resetSafeNumberByOrderNo($beforeOrderNo);
                                    }
                                }
                            }
                        }

                        if($postValue['fromPageMode'] === 'order_list_goods' && $postValue['searchView'] === 'orderGoodsSimple'){
                            //상품준비중 리스트 - 주문별
                            DB::transaction(
                                function () use ($order, $postValue) {
                                    $order->requestStatusChangeListOrderG($postValue);
                                }
                            );
                        }
                        else {
                            DB::transaction(
                                function () use ($order, $postValue) {
                                    $order->requestStatusChangeList(Request::post()->toArray());
                                }
                            );
                        }
                        if($orderStatusUpdateFl === 'y') {
                            $smsAuto->notify();
                        }
                    }

                    $this->layer(__('주문상태 변경 처리가 완료되었습니다.'), null, 2000);
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new LayerException($e->getMessage(), null, null, null, 7000);
                    }
                }
                break;

            // --- 주문 상태 변경 (주문상세)
            case 'status_change':
                try {
                    $smsAuto = \App::load('Component\\Sms\\SmsAuto');
                    $smsAuto->setUseObserver(true);
                    $order->setChannel(Request::post()->get('orderChannelFl'));
                    DB::transaction(
                        function () use ($order) {
                            $order->requestStatusChange(Request::post()->toArray());
                        }
                    );
                    $smsAuto->notify();
                    $this->layer(__('주문상태 변경 처리가 완료되었습니다.'), null, 2000);
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new LayerException($e->getMessage(), null, null, null, 7000);
                    }
                }
                break;

            // 주문 일괄 삭제 (취소/실패 리스트)
            case 'combine_order_delete':
                try {
                    $order->deleteOrderList(Request::post()->toArray());
                    throw new LayerException(__('삭제 되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new LayerException($e->getMessage());
                    }
                }
                break;

            // 주문 일괄 송장번호 저장 (상품준비중 리스트에서)
            case 'combine_invoice_change':
                try {
                    $postValue = Request::post()->toArray();

                    if($postValue['fromPageMode'] === 'order_list_goods' && $postValue['searchView'] === 'orderGoodsSimple'){
                        //상품준비중 리스트 주문번호별 처리
                        $order->saveDeliveryOrderInvoice($postValue);
                    }
                    else {
                        $order->saveDeliveryInvoice($postValue);
                    }
                    throw new LayerException(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // 주문 삭제
            case 'order_delete':
                try {
                    $order->deleteOrder(Request::post()->get('orderNo'));
                    throw new LayerException(__('삭제 되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // --- 환불 완료 처리
            case 'refund_complete':
                try {
                    if (Request::get()->get('channel') == 'naverpay') {
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
                        DB::transaction(
                            function () use ($order, $paycoConfig) {
                                $order->setRefundComplete(Request::post()->toArray());

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

            // --- 환불 단계 복구 처리
            case 'refund_rollback':
                try {
                    $smsAuto = \App::load('Component\\Sms\\SmsAuto');
                    $smsAuto->setUseObserver(true);
                    DB::transaction(
                        function () use ($order, $paycoConfig) {
                            $order->setHandleRollback(Request::post()->toArray());

                            if ($paycoConfig['paycoFl'] == 'y') {
                                // 페이코쇼핑 결제데이터 전달
                                $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
                                $payco->paycoShoppingRequest(Request::post()->get('orderNo'));
                            }
                        }
                    );
                    $smsAuto->notify();
                    throw new LayerException(__('환불 복구 일괄 처리가 완료 되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // 고객 반품/교환/환불신청 승인 처리
            case 'user_handle_accept':
                try {
                    if(Request::post()->get('statusMode') === 'e'){
                        $orderReorderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');
                        $aOrderGoodsSno = $orderReorderCalculation->setUserSameExchangeOrderGoods(Request::post()->toArray());
                    }
                    else {
                        $aOrderGoodsSno = $order->approveUserHandle(Request::post()->toArray(), 'y'); // 주문 부분환불시
                    }
                    $statusCheck = Request::post()->get('statusCheck', []);
                    $config = ['smsAutoCodeType' => Code::ADMIN_APPROVAL];
                    $smsAutoOrder = new SmsAutoOrder($config);
                    foreach ($statusCheck as $index => $item) {
                        $tmp = explode(INT_DIVISION, $item);
                        $orderNo = $tmp[0];
                        $orderGoodsSno = $aOrderGoodsSno[$index];
                        $smsAutoOrder->setOrderNo($orderNo);
                        $smsAutoOrder->setOrderGoodsNo($orderGoodsSno);
                        $smsAutoOrder->autoSend();
                    }
                    throw new LayerException(__('승인 처리가 완료 되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // 고객 반품/교환/환불신청 거절 처리
            case 'user_handle_reject':
                try {
                    $aOrderGoodsSno = $order->approveUserHandle(Request::post()->toArray(), 'n');
                    $statusCheck = Request::post()->get('statusCheck', []);
                    $config = ['smsAutoCodeType' => Code::ADMIN_REJECT];
                    $smsAutoOrder = new SmsAutoOrder($config);
                    foreach ($statusCheck as $index => $item) {
                        $tmp = explode(INT_DIVISION, $item);
                        $orderNo = $tmp[0];
                        $orderGoodsSno = $aOrderGoodsSno[$index];
                        $smsAutoOrder->setOrderNo($orderNo);
                        $smsAutoOrder->setOrderGoodsNo($orderGoodsSno);
                        $smsAutoOrder->autoSend();
                    }
                    throw new LayerException(__('거절 처리가 완료 되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // 고객 반품/교환/환불신청 메모내용 변경
            case 'user_handle_update':
                try {
                    if ($order->updateUserHandle(Request::post()->toArray()) != false) {
                        throw new LayerException(__('관리자 메모 수정이 완료 되었습니다.'));
                    }
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // --- 반품접수 단계 복구 처리
            case 'back_rollback':
                try {
                    $order->setHandleRollback(Request::post()->toArray());

                    if ($paycoConfig['paycoFl'] == 'y') {
                        // 페이코쇼핑 결제데이터 전달
                        $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
                        $payco->paycoShoppingRequest(Request::post()->get('orderNo'));
                    }
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                exit();
                break;

            // --- 환불 수수료 설정
            case 'config_refund_charge':
                try {
                    // 모듈 호출
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveConfigRefundCharge(Request::post()->toArray());
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                exit();
                break;

            // 엑셀 양식 다운로드
            case 'downloadForm':
                try {
                    switch (Request::post()->get('submode')) {
                        case 'save':
                            $order->setDownloadForm(Request::post()->toArray());
                            break;
                        case 'remove':
                            $order->removeDownloadForm(Request::post()->get('formSno'));
                            break;
                    }

                    switch (Request::post()->get('submode')) {
                        case 'save':
                            throw new LayerException();
                            break;
                        case 'remove':
                            throw new LayerException(__('삭제 되었습니다.'));
                            break;
                    }
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // 송장일괄등록 엑셀 양식 업로드
            case 'uploadForm':
                try {
                    $result = $order->updateOrderInvoiceExcel(Request::files()->toArray());
                    $this->json($result);
                    exit;
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // 승장등록 상세보기의 리스트 엑셀다운로드 처리
            case 'invoiceDetailExcelDownload':
                try {
                    if (!Request::post()->has('excel_name')) {
                        throw new Exception(__('요청을 찾을 수 없습니다.'));
                        break;
                    }

                    $this->streamedDownload(Request::post()->get('excel_name') . '.xls');
                    $excel = new ExcelVisitStatisticsConvert();
                    $excel->setExcelDownByJoinData(urldecode(Request::post()->get('data')));
                    exit();
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // 송장등록 엑셀 업로드
            case 'checkUploadForm':
                try {
                    $result = $order->checkOrderInvoiceExcel(Request::files()->toArray());
                    $this->json($result);
                    exit;
                } catch (Exception $e) {
                    throw $e;
                }
                break;

            // --- 주문 리스트 설정
            case 'config_order_list':
                try {
                    // 모듈 호출
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveConfigOrderList(Request::post()->toArray());
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                exit();
                break;

            // 수기주문등록에서 회원 정보가져 오기 (Ajax)
            case 'member_info':
                try {
                    /** @var \Bundle\Component\Member\Member $member */
                    $member = App::load('Component\\Member\\Member');
                    // 회원 여부 체크
                    $memCheck = $member->getMember(Request::request()->get('memNo'), 'memNo', 'memId, memNm, email, phone, cellPhone, zipcode, zonecode, address, addressSub');
                    $this->json($memCheck);

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

            // 수기주문등록에서 상품 등록
            case 'order_write_goods':
                try {
                    $postValue = Request::post()->toArray();

                    $cart = new CartAdmin($postValue['memNo']);
                    $cart->saveInfoCartAdmin($postValue);

                    throw new LayerException(__('상품이 선택되었습니다.'), null, null, null, 2000);

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

            //회원구분 변경, 회원 변경에 따른 주문상품 memNo 변경
            case 'order_write_change_target' :
                $postValue = Request::post()->toArray();

                $cart = new CartAdmin($postValue['memNo']);

                $cart->updateCartWriteMemNo();
                break;

            //옵션변경
            case 'order_write_change_option' :
                $postValue = Request::post()->toArray();

                $cart = new CartAdmin($postValue['memNo']);

                $cart->changeSelfOrderWriteOption($postValue);
                break;

            //회원 장바구니 상품추가 적용
            case 'order_write_member_cart' :
                $postValue = Request::post()->toArray();

                try {
                    //회원의 장바구니 로드
                    $cart = new CartAdmin($postValue['memNo'], true);
                    $cartSaveInfo = array();
                    foreach($postValue['cartSno'] as $key => $cartIdx){
                        $cartInfo = array();
                        $cartInfo = $cart->getCartGoodsData($cartIdx);
                        if(count($cartInfo) > 0){
                            foreach($cartInfo as $key => $value){
                                foreach($value as $key2 => $value2){
                                    $cartSaveInfo[] = $value2[0];
                                }
                            }
                        }
                    }

                    //order write cart 에 저장
                    $cart = null;
                    unset($cart);
                    if(count($cartSaveInfo) > 0){
                        $cart = new CartAdmin($postValue['memNo']);
                        $resultCartSno = $cart->saveInfoMemberCartAdmin($cartSaveInfo);
                    }

                    if($resultCartSno){
                        $this->json($resultCartSno);
                    }
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }
                break;

            // 쿠폰상태 확인 및 상태값 변경
            case 'checkCouponType':
                $getValue = Request::post()->toArray();
                // 모듈 호출
                $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
                $return = $couponAdmin->checkCouponTypeArr($getValue['couponNo']);
                $this->json(array('isSuccess'=>$return));
                break;

            //수량변경
            case 'order_write_count_change' :
                try {
                    $postValue = Request::post()->toArray();
                    $cartData = array(
                        'cartSno' => $postValue['cartSno'],
                        'goodsNo' => $postValue['goodsNo'],
                        'goodsCnt' => $postValue['goodsCnt'],
                        'addGoodsNo' => $postValue['addGoodsNo'],
                        'addGoodsCnt' => $postValue['addGoodsCnt'],
                        'useBundleGoods' => 1,
                    );
                    $cart = new CartAdmin($postValue['memNo']);
                    $cart->setCartCnt($cartData);
                }
                catch (Exception $e) {
                    throw new AlertOnlyException($e->getMessage());
                }

                break;

            //회원정보 가져오기 (보유한 마일리지, 예치금 가져오기)
            case 'order_write_set_member_info' :
                $memberData = [];

                $memNo = Request::post()->get('memNo');

                $cart = new CartAdmin($memNo);

                //설정되어 있는 주문상품이 있다면 memNo 삽입
                $cart->updateCartWriteMemNo();

                //해당 회원이 가지고 있는 상품쿠폰의 가사용flag 값 초기화
                $cart->resetMemberCouponOrderWrite();

                // 지역별 배송비로 인해 주소 처리
                $address = Request::request()->has('address') ? Request::request()->get('address') : null;
                $cartInfo = $cart->getCartGoodsData(null, $address);

                // 회원 정보
                $memberService = \App::load('\\Component\\Member\\Member');
                $memberData = $memberService->getMemberDataOrderWrite($memNo);

                // 마일리지 정책
                // '마일리지 정책에 따른 주문시 사용가능한 범위 제한' 에 사용되는 기준금액 셋팅
                $mileagePrice = $cart->setMileageUseLimitPrice();
                // 마일리지 정책에 따른 주문시 사용가능한 범위 제한
                $mileageUse = $cart->getMileageUseLimit(gd_isset($memberData['mileage'], 0), $mileagePrice);

                $this->json([
                    'memberData' => $memberData,
                    'mileageUse' => $mileageUse,
                    'mileage' => $cart->mileageGiveInfo['info'], // 마일리지 지급 정보
                ]);
                break;

            // 수기주문등록에서 장바구니 상품 가져옴
            case 'order_write_search_goods':
                try {
                    $memNo = Request::request()->get('memNo');

                    $cart = new CartAdmin($memNo);
                    $order = new Order();

                    // 지역별 배송비로 인해 주소 처리
                    $address = Request::request()->has('address') ? Request::request()->get('address') : null;
                    $cartInfo = $cart->getCartGoodsData(null, $address);

                    // 쿠폰 설정값 정보
                    $couponConfig = gd_policy('coupon.config');
                    $couponUse = gd_isset($couponConfig['couponUseType'], 'n'); // 쿠폰 사용여부

                    // 회원 정보
                    $memberData = array();
                    $groupSno = 0;
                    if($memNo > 0){
                        $memberService = \App::load('\\Component\\Member\\Member');
                        $memberData = $memberService->getMemberDataOrderWrite($memNo);
                        $groupSno = $memberData['groupSno'];
                    }

                    // 마일리지 정책
                    // '마일리지 정책에 따른 주문시 사용가능한 범위 제한' 에 사용되는 기준금액 셋팅
                    $mileagePrice = $cart->setMileageUseLimitPrice();
                    // 마일리지 정책에 따른 주문시 사용가능한 범위 제한
                    $mileageUse = $cart->getMileageUseLimit(gd_isset($memberData['mileage'], 0), $mileagePrice);
                    // 예치금 정책
                    $depositUse = gd_policy('member.depositConfig');

                    //쇼핑몰 이용설정 정책
                    $memberAccess = gd_policy('member.access');

                    // 마일리지-쿠폰 동시사용 설정
                    if ($couponConfig['couponUseType'] == 'y' && $mileageUse['payUsableFl'] == 'y') { // 마일리지와 쿠폰이 모두 사용상태일때만 동시사용설정 체크
                        $chooseMileageCoupon = gd_isset($memberAccess['chooseMileageCoupon'], 'n');
                    } else {
                        $chooseMileageCoupon = 'n';
                    }

                    // 사은품 증정 정책
                    $giftConf = gd_policy('goods.gift');
                    if (gd_is_plus_shop(PLUSSHOP_CODE_GIFT) === true && $giftConf['giftFl'] == 'y') {
                        // 사은품리스트
                        $gift = \App::load('\\Component\\Gift\\Gift');
                        $giftInfo = $gift->getGiftPresentOrder($cart->giftForData, $groupSno, true);
                    }

                    // 추가 정보
                    $addFieldInfo = $order->getOrderAddFieldUseList($cartInfo);

                    $cartPrice['totalSumMemberDcPrice'] = $cart->totalSumMemberDcPrice; // 회원 할인 총 금액
                    $cartPrice['totalGoodsDcPrice'] = $cart->totalGoodsDcPrice; // 상품 할인 총 가격
                    $cartPrice['totalGoodsPrice'] = $cart->totalGoodsPrice; //상품 총 가격
                    $cartPrice['totalDeliveryCharge'] = $cart->totalDeliveryCharge; //상품별 총 배송 금액
                    $cartPrice['totalGoodsDeliveryPolicyCharge'] = array_sum($cart->totalGoodsDeliveryPolicyCharge); //상품 배송정책별 총 배송 금액
                    $cartPrice['totalDeliveryAreaCharge'] = array_sum($cart->totalGoodsDeliveryAreaPrice); //상품 배송정책별 총 지역별 배송 금액
                    $cartPrice['totalSettlePrice'] = $cart->totalSettlePrice; // 총 결제 금액 (예정)
                    $cartPrice['totalCouponGoodsDcPrice'] = $cart->totalCouponGoodsDcPrice; // 상품 총 쿠폰 금액
                    $cartPrice['totalMileage'] = $cart->totalMileage; // 총 적립 마일리지 (예정)
                    $cartPrice['totalGoodsMileage'] = $cart->totalGoodsMileage; // 상품별 총 상품 마일리지
                    $cartPrice['totalMemberMileage'] = $cart->totalMemberMileage; // 회원 그룹 총 마일리지
                    $cartPrice['totalCouponGoodsMileage'] = $cart->totalCouponGoodsMileage; // 상품 총 쿠폰 마일리지


                    // 결제가능한 수단이 무통장인경우
                    if (count($cart->payLimit) == 1 && $cart->payLimit[0] == 'gb') {
                        $onlyBankFl = 'y';
                    } else {
                        $onlyBankFl = 'n';
                    }
                    // 쿠폰무통장일때는 결제제한으로 무통장만넘어오기때문에 마일리지 사용가능처리
                    /*if ($mileageUse['payUsableFl'] == 'y' && $onlyBankFl == 'y') {
                        $cart->payLimit[] = 'gm';
                    }
                    // 쿠폰무통장일때는 결제제한으로 무통장만넘어오기때문에 예치금 사용가능처리
                    if ($depositUse['payUsableFl'] == 'y' && $onlyBankFl == 'y') {
                        $cart->payLimit[] = 'gd';
                    }*/

                    //사용가능한 결제수단 정보 정의
                    $payLimitData = array(
                        'orderAble' => 'y',
                        'orderBankAble' => 'y',
                        'orderPgAble' => 'y',
                        'orderMileageAble' => 'y',
                        'orderDepositAble' => 'y',
                    );
                    if(empty($cart->payLimit) === false){
                        if(in_array('false', $cart->payLimit)){
                            $payLimitData['orderAble'] = 'n';
                        }
                        else {
                            if(in_array('gb', $cart->payLimit) === false){
                                $payLimitData['orderBankAble'] = 'n';
                            }
                            if(in_array('pg', $cart->payLimit) === false){
                                $payLimitData['orderPgAble'] = 'n';
                            }
                            if(in_array('gm', $cart->payLimit) === false){
                                $payLimitData['orderMileageAble'] = 'n';
                            }
                            if(in_array('gd', $cart->payLimit) === false){
                                $payLimitData['orderDepositAble'] = 'n';
                            }
                        }
                    }

                    $cookieData = array(
                        'owMemberCartSnoData' => Cookie::get('owMemberCartSnoData'),
                        'owMemberRealCartSnoData' => Cookie::get('owMemberRealCartSnoData'),
                        'owMemberCartCouponNoData' => Cookie::get('owMemberCartCouponNoData'),
                    );

                    $this->json([
                        'cartInfo' => $cartInfo,
                        'couponConfig' => $couponConfig,
                        'couponUse' => $couponUse,
                        'mileage' => $cart->mileageGiveInfo['info'], // 마일리지 지급 정보
                        'cartPrice' => $cartPrice,
                        'cartScmInfo' => $cart->cartScmInfo,
                        'setDeliveryInfo' => $cart->setDeliveryInfo,
                        'cartCnt' => $cart->cartCnt,
                        'orderPossible' => $cart->orderPossible,
                        'orderPossibleMessage' => $cart->orderPossibleMessage,
                        'payLimitData' => $payLimitData,
                        'mileageUse' => $mileageUse,
                        'giftConf' => $giftConf,
                        'giftInfo' => $giftInfo,
                        'addFieldInfo' => $addFieldInfo,
                        'receipt' => $receipt,
                        'cookieData' => $cookieData,
                        'chooseMileageCoupon' => $chooseMileageCoupon,
                        'mileageGiveExclude' => $cart->mileageGiveExclude, // 주문시 마일리지 사용하는 경우 적립마일리지 지급 여부
                    ]);


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

            // 수기주문등록에서 장바구니 상품 삭제
            case 'order_write_delete_goods':
                try {
                    $postValue = Request::request()->toArray();
                    $cart = new CartAdmin($postValue['memNo']);
                    $cart->setCartDelete(explode(INT_DIVISION, $postValue['cartSno']));

                    $this->json(__("선택한 상품이 삭제하였습니다."));

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

            //상품쿠폰 적용
            case 'order_write_goods_coupon_apply' :
                try {
                    $postValue = Request::request()->toArray();

                    $cart = new CartAdmin($postValue['memNo']);

                    $memberCouponNo = $cart->setMemberCouponApplyOrderWrite($postValue['cart']['cartSno'], $postValue['cart']['couponApplyNo'], $postValue['memberCartAddTypeCouponNo']);
                    echo json_encode($memberCouponNo);
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

            //상품쿠폰 삭제
            case 'order_write_goods_coupon_cancel' :
                $postValue = Request::request()->toArray();

                if($postValue['selfOrderMemberCartFl'] === 'y'){
                    $cart = new CartAdmin($postValue['memNo'], true);
                }
                else {
                    $cart = new CartAdmin($postValue['memNo']);
                }

                $cart->setMemberCouponDelete($postValue['cartSno']);
                break;

            // 장바구니 사용 상태의 쿠폰 적용 취소처리
            case 'UserCartCouponDel':
                $postValue = Request::request()->toArray();
                $cart = ($postValue['cartUseType'] == 'cart') ? new CartAdmin($postValue['memNo'], true) : new CartAdmin($postValue['memNo']);
                $cartInfo = $cart->getCartGoodsData();
                if($cartInfo > 0) {
                    foreach ($cartInfo as $key => $value) {
                        foreach ($value as $key1 => $value1) {
                            foreach ($value1 as $key2 => $value2) {
                                // 장바구니 쿠폰 재정의
                                $memberCouponNoArr = explode(INT_DIVISION, $value2['memberCouponNo']);
                                $chkMemberCouponNoArr = explode(INT_DIVISION, $postValue['memberCouponNo']);
                                if ($value2['memberCouponNo']) {
                                    $newMemberCouponNoArr[$value2['sno']] = implode(INT_DIVISION, array_diff($memberCouponNoArr, $chkMemberCouponNoArr));
                                }
                            }
                        }
                    }
                }

                // 기존 장바구니인 경우
                if ($postValue['cartUseType'] === 'cart') {
                    // 기존에 쿠폰이 적용된 장바구니 쿠폰 재정의
                    if ($newMemberCouponNoArr) {
                        foreach ($newMemberCouponNoArr as $cartSno => $memberCouponNo) {
                            $cart->setMemberCouponApply($cartSno, $memberCouponNo);
                        }
                    }
                }

                // 수기 장바구니인 경우
                if ($postValue['cartUseType'] === 'write') {
                    // 기존에 쿠폰이 적용된 장바구니 쿠폰 재정의
                    if ($newMemberCouponNoArr) {
                        foreach ($newMemberCouponNoArr as $cartSno => $memberCouponNo) {
                            $cart->setMemberCouponApplyOrderWrite($cartSno, $memberCouponNo);
                        }
                    }
                }
                break;

            // 장바구니 선택 상품의 총 결제금액 계산
            case 'order_write_cart_select_calculation':
                try {
                    $postValue = Request::request()->toArray();

                    $cart = new CartAdmin($postValue['memNo'], true);

                    if ($postValue['cartSno']) {
                        $cart->getCartGoodsData($postValue['cartSno']);
                        $setData = [
                            'cartCnt' => $cart->cartCnt,
                            'totalGoodsPrice' => $cart->totalGoodsPrice,
                            'totalGoodsDcPrice' => $cart->totalGoodsDcPrice,
                            'totalGoodsMileage' => $cart->totalGoodsMileage,
                            'totalMemberDcPrice' => $cart->totalMemberDcPrice,
                            'totalMemberOverlapDcPrice' => $cart->totalMemberOverlapDcPrice,
                            'totalMemberMileage' => $cart->totalMemberMileage,
                            'totalCouponGoodsDcPrice' => $cart->totalCouponGoodsDcPrice,
                            'totalCouponGoodsMileage' => $cart->totalCouponGoodsMileage,
                            'totalDeliveryCharge' => $cart->totalDeliveryCharge,
                            'totalSettlePrice' => $cart->totalSettlePrice,
                            'totalMileage' => $cart->totalMileage,
                        ];
                    } else {
                        $setData = [
                            'cartCnt' => 0,
                            'totalGoodsPrice' => 0,
                            'totalGoodsDcPrice' => 0,
                            'totalGoodsMileage' => 0,
                            'totalMemberDcPrice' => 0,
                            'totalMemberOverlapDcPrice' => 0,
                            'totalMemberMileage' => 0,
                            'totalCouponGoodsDcPrice' => 0,
                            'totalCouponGoodsMileage' => 0,
                            'totalDeliveryCharge' => 0,
                            'totalSettlePrice' => 0,
                            'totalMileage' => 0,
                        ];
                    }

                    $this->json($setData);
                    exit;
                } catch (Exception $e) {
                    $this->json($e->getMessage());
                    exit;
                }
                break;

            // 수기주문등록에서 혜택관련
            case 'order_write_benefit_goods':
                try {
                    $postValue = Request::request()->toArray();
                    $cart = new CartAdmin();
                    $setData = $cart->goodsViewBenefitOrder($postValue);
                    echo json_encode($setData);

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

            // 자주쓰는 주소관리 등록/수정
            case 'register_frequency':
                try {
                    $postValue = StringUtils::xssArrayClean(Request::request()->toArray());
                    $order->registerFrequencyAddress($postValue);
                    throw new LayerException();

                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // 자주쓰는 주소관리 삭제
            case 'deleteFrequency':
                try {
                    $postValue = Request::request()->toArray();
                    foreach ($postValue['sno'] as $sno) {
                        $order->deleteFrequencyAddress($sno);
                    }
                    throw new Exception(__('삭제처리가 완료되었습니다.'));

                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new LayerException($e->getMessage());
                    }
                }
                break;

            // 클래임 정보 수정
            case 'modify_claim':
                try {
                    $postValue = Request::request()->toArray();
                    if ($order->updateHandle($postValue)) {
                        throw new LayerException(__('저장이 완료되었습니다.'));
                    }

                } catch (Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;

            // id-상품별 구매 수량 체크
            case 'check_memberOrderGoodsCount':
                try {
                    $postValue = Request::request()->toArray();
                    $aMemberOrderGoodsCountData = $order->getMemberOrderGoodsCountData($postValue['memNo'], $postValue['goodsNo']);

                    if ($aMemberOrderGoodsCountData) {
                        $this->json([
                            'count' => $aMemberOrderGoodsCountData['orderCount'],
                        ]);
                    } else {
                        $this->json([
                            'count' => 0,
                        ]);
                    }
                } catch (Exception $e) {
                    $this->json([
                        'error' => 1,
                        'message' => $e->getMessage(),
                    ]);
                }

                break;

            // 수기 주문 저장하기
            case 'save_write_order':
                try {
                    $orderMultiShipping = App::load('\\Component\\Order\\OrderMultiShipping');

                    // 요청 데이터
                    $postValue = Request::post()->toArray();
                    $postValue['memberOrder'] = true;

                    foreach ($postValue['receiverInfo'] as $key => $val) {
                        if ($val['deliveryVisit'] != 'y') continue;

                        $postValue['receiverInfo'][$key]['receiverName'] = gd_isset($val['receiverName'], $val['visitName']);
                        $postValue['receiverInfo'][$key]['receiverCellPhone'] = gd_isset($val['receiverCellPhone'], $val['visitPhone']);
                        $postValue['receiverInfo'][$key]['receiverAddress'] = gd_isset($val['receiverAddress'], $val['visitAddress']);
                    }

                    if ($orderMultiShipping->isUseMultiShipping() === true && $postValue['multiShippingFl'] == 'y') {
                        $postValue = $orderMultiShipping->setOrderWritePostData($postValue);
                        $checkReceiverInfoSame = $orderMultiShipping->checkReceiverInfoSame($postValue);
                        if($checkReceiverInfoSame === false){
                            throw new LayerException('동일 배송지가 존재합니다.');
                        }
                    }
                    else {
                        foreach($postValue['receiverInfo'] as $value) {
                            $postValue = array_merge((array)$postValue, (array)$value);
                        }
                    }

                    // 주문서 정보 체크
                    $postValue = $order->setOrderDataValidation($postValue, false);

                    // 장바구니 모듈
                    $cart = new CartAdmin($postValue['memNo']);

                    // 장바구니내 지역별 배송비 처리를 위한 주소 값
                    $address = str_replace(' ', '', $postValue['receiverAddress'] . $postValue['receiverAddressSub']);

                    $cart->totalCouponOrderDcPrice = $postValue['totalCouponOrderDcPrice'];
                    $cart->totalUseMileage = $postValue['useMileage'];
                    $cart->deliveryFree = gd_isset($postValue['deliveryFree'], 'n');
                    $cart->couponApplyOrderNo = $postValue['couponApplyOrderNo'];


                    if ($orderMultiShipping->isUseMultiShipping() === true && $postValue['multiShippingFl'] == 'y') {
                        $resetCart = $orderMultiShipping->resetCart($postValue, true);
                        $postValue['cartSno'] = $resetCart['setCartSno'];
                        $postValue['orderInfoCdData'] = $resetCart['orderInfoCd'];
                        $postValue['orderInfoCdBySno'] = $resetCart['orderInfoCdBySno'];
                        $cart->goodsCouponInfo = $resetCart['goodscouponInfo'];
                    }

                    // 장바구니 정보 (해당 프로퍼티를 가장 먼저 실행해야 계산된 금액 사용 가능)
                    $cartInfo = $cart->getCartGoodsData(null, $address, null, true, true, $postValue);
                    $postValue['multiShippingOrderInfo'] = $cart->multiShippingOrderInfo;

                    $goodsEachSaleCountAbleFl = true;
                    $goodsEachSaleCheckArr = null;

                    // 주문불가한 경우 진행 중지
                    if (!$cart->orderPossible) {
                        $orderPossibleGoods = array();
                        $indexKey = 1;
                        if($cartInfo > 0){
                            foreach($cartInfo as $key => $value){
                                foreach($value as $key1 => $value1){
                                    foreach($value1 as $key2 => $value2){
                                        if($value2['orderPossible'] === 'n'){
                                            if($value2['goodsType'] === 'addGoods'){
                                                continue;
                                            }
                                            else {
                                                $orderPossibleGoods[$indexKey] = $value2['goodsNm'];

                                                $indexKey++;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $resultOrderPossibleGoods = '';
                        if(count($orderPossibleGoods) > 0){
                            $orderPossibleGoods = array_values(array_unique($orderPossibleGoods));
                            foreach($orderPossibleGoods as $opgKey => $opgValue){
                                $resultOrderPossibleGoods .= "<br />" . ($opgKey+1) . ". " . addslashes($opgValue);
                            }
                        }
                        throw new LayerException(__('구매 불가 상품이 포함되어 있으니 주문상품에서 확인 후 다시 주문해주세요.' . $resultOrderPossibleGoods), 2);
                    }

                    // ID기준 구매수량 카운트 체크
                    if($cartInfo > 0) {
                        foreach ($cartInfo as $key => $value) {
                            foreach ($value as $key1 => $value1) {
                                foreach ($value1 as $key2 => $value2) {
                                    if ($value2['goodsType'] === 'addGoods') {
                                        continue;
                                    } else {
                                        // 상품별 수량체크 한번 더
                                        if ($value2['minOrderCnt'] > 1 || $value2['maxOrderCnt'] > '0') {
                                            if ($value2['fixedOrderCnt'] == 'option' ) {
                                                if ($value2['goodsCnt'] < $value2['minOrderCnt']) {
                                                    $goodsEachSaleCountAbleFl = false;
                                                }
                                                if ($value2['goodsCnt'] > $value2['maxOrderCnt'] && $value2['maxOrderCnt'] > 0) {
                                                    $goodsEachSaleCountAbleFl = false;
                                                }
                                            }

                                            if ($value2['fixedOrderCnt'] == 'goods' || $value2['fixedOrderCnt'] == 'id') {
                                                if ($value2['fixedOrderCnt'] == 'id' && $postValue['memNo'] !== null) {
                                                    $goodsEachSaleCheckArr[$value2['goodsNo']]['fixedOrderCnt'] = 'id';
                                                } else {
                                                    $goodsEachSaleCheckArr[$value2['goodsNo']]['fixedOrderCnt'] = 'goods';
                                                }
                                                $goodsEachSaleCheckArr[$value2['goodsNo']]['count'] += $value2['goodsCnt'];
                                                $goodsEachSaleCheckArr[$value2['goodsNo']]['max'] = $value2['maxOrderCnt'];
                                                $goodsEachSaleCheckArr[$value2['goodsNo']]['min'] = $value2['minOrderCnt'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (is_array($goodsEachSaleCheckArr)) {
                        foreach ($goodsEachSaleCheckArr as $k => $v) {
                            if ($v['fixedOrderCnt'] == 'id' && $postValue['memNo'] !== null) {
                                $aMemberOrderGoodsCountData = $order->getMemberOrderGoodsCountData($postValue['memNo'], $k);
                                $thisGoodsCount = gd_isset($aMemberOrderGoodsCountData['orderCount'], 0) + $v['count'];
                                if ($thisGoodsCount < $v['min'] || ($thisGoodsCount > $v['max'] && $v['max'] > 0)) {
                                    $goodsEachSaleCountAbleFl = false;
                                }
                            } else {
                                if (($v['count'] > $v['max'] && $v['max'] > 0) || $v['count'] < $v['min']) {
                                    $goodsEachSaleCountAbleFl = false;
                                }
                            }
                        }
                    }
                    if (!$goodsEachSaleCountAbleFl) {
                        throw new LayerException(__('구매 불가 상품이 포함되어 있으니 주문상품에서 확인 후 다시 주문해주세요.'), 2);
                    }

                    // EMS 배송불가
                    if (!$cart->emsDeliveryPossible) {
                        throw new LayerException(__('무게가 %sg 이상의 상품은 구매할 수 없습니다. (배송범위 제한)', '30k'));
                    }
                    // 개별결제수단이 설정되어 있는데 모두 다른경우 결제 불가
                    if (empty($cart->payLimit) === false && in_array('false', $cart->payLimit)) {
                        throw new LayerException(__('주문하시는 상품의 결제 수단이 상이 하여 결제가 불가능합니다.'));
                    }
                    // 설정 변경등으로 쿠폰 할인가등이 변경된경우
                    if (!$cart->changePrice) {
                        throw new LayerException(__('할인/적립 금액이 변경되었습니다. 상품 결제 금액을 확인해 주세요!'));
                    }

                    // 주문서 작성시 발생되는 금액을 장바구니 프로퍼티로 설정하고 최종 settlePrice를 산출 (사용 마일리지/예치금/주문쿠폰)
                    $orderPrice = $cart->setOrderSettleCalculation($postValue);

                    // 설정 변경등으로 쿠폰 할인가등이 변경된경우 - 주문쿠폰체크
                    if (!$cart->changePrice) {
                        throw new LayerException(__('할인/적립 금액이 변경되었습니다. 상품 결제 금액을 확인해 주세요!'));
                    }

                    // 마일리지/예치금 전용 구매상품인 경우 찾아내기
                    if (empty($cart->payLimit) === false) {
                        $isOnlyMileage = true;
                        foreach ($cart->payLimit as $val) {
                            if (!in_array($val, [Order::SETTLE_KIND_MILEAGE, Order::SETTLE_KIND_DEPOSIT])) {
                                $isOnlyMileage = false;
                            }
                        }

                        // 마일리지/예치금 결제 전용인 경우
                        if ($isOnlyMileage) {
                            // 예치금/마일리지 복합결제 구매상품인 경우 결제금액이 0원이 아닌 경우
                            if (in_array(Order::SETTLE_KIND_DEPOSIT, $cart->payLimit) && in_array(Order::SETTLE_KIND_MILEAGE, $cart->payLimit) && $orderPrice['settlePrice'] != 0) {
                                throw new LayerException(__('결제금액보다 예치금/마일리지 사용 금액이 부족합니다.'));
                            }

                            // 예치금 전용 구매상품이면서 결제금액이 0원이 아닌 경우
                            if (in_array(Order::SETTLE_KIND_DEPOSIT, $cart->payLimit) && $orderPrice['settlePrice'] != 0) {
                                throw new LayerException(__('결제금액보다 예치금이 부족합니다.'));
                            }

                            // 마일리지 전용 구매상품이면서 결제금액이 0원이 아닌 경우
                            if (in_array(Order::SETTLE_KIND_MILEAGE, $cart->payLimit) && $orderPrice['settlePrice'] != 0) {
                                throw new LayerException(__('결제금액보다 마일리지가 부족합니다.'));
                            }
                        }
                    }

                    // 결제금액이 0원인 경우 전액할인 수단으로 강제 변경
                    if ($orderPrice['settlePrice'] == 0) {
                        $postValue['settleKind'] = Order::SETTLE_KIND_ZERO;
                    }

                    // 주문 저장하기 (트랜젝션)
                    $result = \DB::transaction(function () use ($order, $cartInfo, $postValue, $orderPrice, $cart) {
                        // 장바구니에서 계산된 전체 과세 비율 필요하면 추후 사용 -> $cart->totalVatRate
                        return $order->saveOrderInfo($cartInfo, $postValue, $orderPrice);
                    });

                    // 주문 저장 후 처리
                    if ($result) {
                        // 장바구니 비우기
                        $cart->setCartRemove();

                        /*
                         * 회원 장바구니 상품추가로 추가된 상품의 경우 쿠폰이 적용되어 있다면 주문시
                         * 원래 적용되어 있던 실제 장바구니의 memberCouponNo를 update 처리 한다.
                         */
                        if(count($postValue['realCartSno']) > 0){
                            // 장바구니 모듈
                            $cart = null;
                            unset($cart);
                            $cart = new CartAdmin($postValue['memNo'], true);

                            foreach($postValue['realCartSno'] as $key => $realCartSno){
                                //$postValue['realCartCouponNo'][$key] => 사용한 쿠폰리스트 변수. 기존 쿠폰리스트 - 사용된 쿠폰리스트가 UPDATE 대상
                                $cart->updateOrderWriteRealCouponData($realCartSno, $postValue['realCartCouponNo'][$key]);
                            }
                        }

                        // 무통장 주문 송신
                        $bk = new BankdaOrder('send', $order->orderNo);

                        // 결제 완료 페이지 이동
                        throw new LayerException(__('주문이 정상적으로 처리되었습니다.'), 1);
                    }
                } catch (Exception $e) {
                    if ($e->ectName == 'EXCEPT_COUPON') {
                        $this->js("parent.layer_close();parent.set_goods('y');parent.BootstrapDialog.show({title: '정보', size: parent.get_layer_size('wide'), message: '" . addslashes($e->ectMessage) . "', closable: true, buttons: [{label: '확인',cssClass: 'btn-black',action: function (dialog) {dialog.close();} }], });");
                    } else {
                        if ($e->getCode() == 1) {
                            $eMessage = preg_replace('/\r\n|\r|\n/', '<br />', $e->getMessage());
                            throw new LayerException($eMessage);
                        }
                        if ($e->getCode() == 2) {
                            $this->js("parent.layer_close();parent.BootstrapDialog.show({title: '정보', size: parent.get_layer_size('wide'), message: '" . addslashes($e->getMessage()) . "', closable: true, buttons: [{label: '확인',cssClass: 'btn-black',action: function (dialog) {dialog.close();} }], });");
                        } else {
                            $eMessage = preg_replace('/\r\n|\r|\n/', '<br />', $e->getMessage());
                            throw new LayerNotReloadException($eMessage);
                        }
                    }
                }
                break;
            case 'set_member_coupon_apply':
                $postValue = Request::request()->toArray();
                $cart = new CartAdmin($postValue['memNo']);
                $cartInfo = $cart->getCartGoodsData();

                // 장바구니에 사용된 쿠폰의 유효성 체크 및 재적용
                if($cartInfo > 0) {
                    $coupon = \App::load('\\Component\\Coupon\\CouponAdmin');
                    $result = $coupon->setRealMemberCouponApplyOrderWrite($cartInfo, $postValue['realCartSno'], $postValue['memNo']);
                    $this->json($result);
                }
                break;

            // 클래임 정보 수정
            case 'super_admin_memo':
                try {
                    $postValue = Request::request()->toArray();
                    if ($order->updateSuperAdminMemo($postValue)) {
                        throw new Exception(__('저장이 완료되었습니다.'));
                    }

                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;
            case 'collectNaverpayOrder' :
                $postValue = Request::request()->toArray();
                $naverPayApi = new NaverPayApi();
                $result = $naverPayApi->collectNaverPayOrderByOrderNo($postValue['orderNo'],$postValue['naverpayOrderNo']);
                $this->json(['result'=>$result,'msg'=>$naverPayApi->getError()]);
                break;

            // 주문쿠폰 사용시 회원추가/중복 할인 금액 재조정
            case 'set_recalculation':
                try {
                    $postValue = Request::request()->all();
                    $memNo = $postValue['memNo'];

                    $cart = new CartAdmin($memNo);

                    $cart->totalCouponOrderDcPrice = $postValue['totalCouponOrderDcPrice'];
                    $cart->totalUseMileage = $postValue['useMileage'];
                    $cart->deliveryFree = gd_isset($postValue['deliveryFree'], 'n');

                    $cart->getCartGoodsData(null, $postValue['address']);

                    // 회원 정보
                    $memberData = array();
                    if($memNo > 0){
                        $memberService = \App::load('\\Component\\Member\\Member');
                        $memberData = $memberService->getMemberDataOrderWrite($memNo);
                    }

                    // 마일리지 정책
                    // '마일리지 정책에 따른 주문시 사용가능한 범위 제한' 에 사용되는 기준금액 셋팅
                    $setMileagePriceArr = [
                        'totalGoodsDeliveryAreaPrice' => $postValue['totalDeliveryAreaCharge'],
                        'totalDeliveryCharge' => $postValue['totalDeliveryCharge'],
                    ];
                    $mileagePrice = $cart->setMileageUseLimitPrice($setMileagePriceArr);
                    // 마일리지 정책에 따른 주문시 사용가능한 범위 제한
                    $mileageUse = $cart->getMileageUseLimit(gd_isset($memberData['mileage'], 0), $mileagePrice);

                    $cartPrice = [
                        'totalSumMemberDcPrice' => $cart->totalSumMemberDcPrice, // 회원 할인 총 금액
                        'totalGoodsDcPrice' => $cart->totalGoodsDcPrice, // 상품 할인 총 가격
                        'totalGoodsPrice' => $cart->totalGoodsPrice, // 상품 총 가격
                        'totalDeliveryCharge' => $cart->totalDeliveryCharge, //총 배송 금액
                        'totalGoodsDeliveryPolicyCharge' => array_sum($cart->totalGoodsDeliveryPolicyCharge), //상품 배송정책별 총 배송 금액
                        'totalDeliveryAreaCharge' => array_sum($cart->totalGoodsDeliveryAreaPrice), // 상품 배송정책별 총 지역별 배송 금액
                        'totalSettlePrice' => $cart->totalSettlePrice, // 총 결제 금액 (예정)
                        'totalCouponGoodsDcPrice' => $cart->totalCouponGoodsDcPrice, // 상품 총 쿠폰 금액
                        'totalMileage' => $cart->totalMileage, // 총 적립 마일리지 (예정)
                        'totalGoodsMileage' => $cart->totalGoodsMileage, // 상품별 총 상품 마일리지
                        'totalMemberMileage' => $cart->totalMemberMileage, // 회원 그룹 총 마일리지
                        'totalCouponGoodsMileage' => $cart->totalCouponGoodsMileage, // 상품 총 쿠폰 마일리지
                    ];

                    $this->json([
                        'cartPrice' => $cartPrice,
                        'mileageUse' => $mileageUse,
                    ]);
                    exit;
                } catch (Exception $e) {
                    $this->json([
                        'error' => 1,
                        'message' => $e->getMessage(),
                    ]);
                }

                break;

            //주문리스트 조회항목 가져오기
            case 'get_order_admin_grid_list' :
                $postValue = Request::post()->toArray();

                $orderAdminGrid = \App::load('\\Component\\Order\\OrderAdminGrid');
                $listGridConfig = $orderAdminGrid->getOrderGridConfigList($postValue['orderGridMode']);

                echo json_encode($listGridConfig, JSON_UNESCAPED_UNICODE);
                break;

            //주문리스트 조회항목 저장하기
            case 'save_order_admin_grid_list' :
                $postValue = Request::post()->toArray();

                $orderAdminGrid = \App::load('\\Component\\Order\\OrderAdminGrid');
                $orderAdminGrid->setOrderGridConfigList($postValue);

                throw new LayerException(__('설정값이 저장 되었습니다.'), null, null, null, 1000, true);
                break;

            //주문리스트 전체 조회항목 정렬순서에 따라 가져오기
            case 'get_grid_all_list_sort' :
                $postValue = Request::post()->toArray();

                $orderAdminGrid = \App::load('\\Component\\Order\\OrderAdminGrid');
                $listGridConfig = $orderAdminGrid->getOrderGridConfigAllSortList($postValue['orderGridMode'], $postValue['gridSort']);

                echo json_encode($listGridConfig, JSON_UNESCAPED_UNICODE);
                break;

            // 상품상세 교환팝업페이지 - 다른상품교환 상품가져오기
            // 주문상세 상품추가팝업 페이지
            case 'order_view_exchange_get_goods':
                try {
                    $cart = new CartAdmin();
                    $cart->orderGoodsChange = true;
                    $cartInfo = $cart->getCartGoodsData();

                    foreach ($cartInfo as $sKey => $sVal) {
                        foreach ($sVal as $dKey => $dVal) {
                            foreach ($dVal as $gKey => $gVal) {
                                $cartInfo[$sKey][$dKey][$gKey]['goodsNm'] = gd_html_cut($gVal['goodsNm'], 36, '..');
                                list($totalStock ,$stockText) = gd_is_goods_state($gVal['stockFl'],$gVal['totalStock'],$gVal['soldOutFl']);
                                $cartInfo[$sKey][$dKey][$gKey]['stockText'] = $totalStock;
                            }
                        }
                    }

                    $this->json($cartInfo);

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

            // 상품상세 교환팝업페이지 - 다른상품교환 선택상품 삭제
            case 'order_view_exchange_select_delete':
                try {
                    $postValue = Request::request()->toArray();
                    $cart = new CartAdmin();
                    $cart->setCartDelete(explode(INT_DIVISION, $postValue['cartSno']));

                    $cartInfo = $cart->getCartGoodsData();

                    $this->json($cartInfo);

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

            // 상품상세 교환사유 수정
            case 'update_order_handle_reason' :
                try {
                    $postValue = Request::request()->toArray();

                    $orderReorderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');
                    $result = $orderReorderCalculation->updateOrderHandleReason($postValue);

                    echo $result;
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

            // 수기주문등록 - 복수배송지 배송비계산
            case 'multi_shipping_delivery':
                try {
                    $postValue = Request::request()->toArray();
                    $cart = new CartAdmin($postValue['memNo']);

                    $selectGoods = json_decode($postValue['selectGoods'], true);

                    $cartIdx = $setGoodsCnt = $setAddGoodsCnt = $setDeliveryMethodFl = $setDeliveryCollectFl = [];
                    foreach ($selectGoods as $key => $val) {
                        if ($val['goodsCnt'] > 0) {
                            $cartIdx[] = $val['sno'];
                            $setGoodsCnt[$val['sno']]['goodsCnt'] = $val['goodsCnt'];
                            if (empty($val['deliveryMethodFl']) === false) $setDeliveryMethodFl[$val['sno']]['deliveryMethodFl'] = $val['deliveryMethodFl'];
                            if (empty($val['deliveryCollectFl']) === false) $setDeliveryCollectFl[$val['sno']]['deliveryCollectFl'] = $val['deliveryCollectFl'];
                        }
                        if (empty($val['addGoodsNo']) === false) {
                            foreach ($val['addGoodsNo'] as $aKey => $aVal) {
                                $setAddGoodsCnt[$val['sno']][$aVal] = $val['addGoodsCnt'][$aKey];
                            }
                        }
                    }

                    $data = $cart->getCartGoodsData($cartIdx, $postValue['address'], null, false, true, [], $setGoodsCnt, $setAddGoodsCnt, $setDeliveryMethodFl, $setDeliveryCollectFl);
                    $deliverInfo = [];
                    $parentCartSno = '';
                    if ($postValue['useDeliveryInfo'] == 'y') {
                        $deliveryCollect = ['pre' => __('선결제'), 'later' => __('착불'),];
                        $setDeliveryFl = [];

                        foreach ($data as $scmNo => $sVal) {
                            foreach($sVal as $deliverySno => $dVal) {
                                foreach ($dVal as $key => $val) {
                                    if ($val['goodsDeliveryFl'] == 'y' || ($val['goodsDeliveryFl'] != 'y' && $val['sameGoodsDeliveryFl'] == 'y')) {
                                        if ($val['goodsDeliveryFl'] == 'y') {
                                            $deliveryPrice = $val['goodsDeliveryCollectFl'] == 'pre' ? $cart->setDeliveryInfo[$deliverySno]['goodsDeliveryPrice'] : $cart->setDeliveryInfo[$deliverySno]['goodsDeliveryCollectPrice'];

                                            if (empty($setDeliveryFl[$deliverySno]) === false) {
                                                $val['parentCartSno'] = $setDeliveryFl[$deliverySno];
                                            } else {
                                                $setDeliveryFl[$deliverySno] = $val['parentCartSno'];
                                            }
                                        } else {
                                            $deliveryPrice = $val['goodsDeliveryCollectFl'] == 'pre' ? $cart->setDeliveryInfo[$deliverySno][$val['goodsNo']]['goodsDeliveryPrice'] + $cart->setDeliveryInfo[$deliverySno][$val['goodsNo']]['goodsDeliveryAreaPrice'] : $cart->setDeliveryInfo[$deliverySno][$val['goodsNo']]['goodsDeliveryCollectPrice'];
                                        }

                                        if ($parentCartSno != $val['parentCartSno']) $parentCartSno = $val['parentCartSno'];
                                        $deliverInfo[$parentCartSno]['rowspan'] += (1 + array_sum($setAddGoodsCnt[$val['sno']]));
                                        $deliverInfo[$parentCartSno]['goodsDeliveryMethod'] = $val['goodsDeliveryMethod'];
                                        $deliverInfo[$parentCartSno]['deliveryPrice'] = $deliveryPrice;
                                        $deliverInfo[$parentCartSno]['deliveryMethodFl'] = $val['goodsDeliveryMethodFlText'];
                                        $deliverInfo[$parentCartSno]['goodsDeliveryCollectFl'] = $deliveryCollect[$val['goodsDeliveryCollectFl']];
                                    } else {
                                        $deliverInfo[$val['sno']] = [
                                            'rowspan' => 1 + array_sum($setAddGoodsCnt[$val['sno']]),
                                            'goodsDeliveryMethod' => $val['goodsDeliveryMethod'],
                                            'deliveryPrice' => $val['goodsDeliveryCollectFl'] == 'pre' ? $val['price']['goodsDeliveryPrice'] + $val['price']['goodsDeliveryAreaPrice'] : $val['price']['goodsDeliveryCollectPrice'],
                                            'deliveryMethodFl' => $val['goodsDeliveryMethodFlText'],
                                            'goodsDeliveryCollectFl' => $deliveryCollect[$val['goodsDeliveryCollectFl']],
                                        ];
                                    }
                                }
                            }
                        }
                    }

                    $this->json([
                        'totalDeliveryCharge' => gd_isset($cart->totalDeliveryCharge, 0), //총 배송 금액
                        'totalGoodsDeliveryPolicyCharge' => gd_isset(array_sum($cart->totalGoodsDeliveryPolicyCharge), 0), //상품 배송정책별 총 배송 금액
                        'totalDeliveryAreaCharge' => gd_isset(array_sum($cart->totalGoodsDeliveryAreaPrice), 0), //상품 배송정책별 총 지역별 배송 금액
                        'deliveryInfo' => $deliverInfo
                    ]);

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

            // 주문 상세페이지 - 안심번호 사용해지
            case 'cancel_safe_number':
                try {
                    $safeNumber = \App::load('Component\\Service\\SafeNumber');
                    $result = $safeNumber->cancelSafeNumber(Request::post()->toArray());
                    if ($result['result'] == 'success') {
                        $this->json(__('안심번호가 정상적으로 해지되었습니다.'));
                    } else {
                        $this->json(__('일시적으로 안심번호 해지를 할 수 없습니다. 잠시 후 다시 시도해주세요.'));
                    }
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => $e->getCode(),
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // 주문 상세페이지 - 안심번호 가져오기 (휴대폰번호 수정)
            case 'get_reciever_safe_number':
                try {
                    $safeNumber = \App::load('Component\\Service\\SafeNumber');
                    $result = $safeNumber->modifySafeNumber(Request::post()->toArray());

                    $this->json($result);

                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => $e->getCode(),
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // 주문 상세페이지 - 안심번호 수동발급 요청
            case 'reset_safe_number':
                try {
                    $safeNumber = \App::load('Component\\Service\\SafeNumber');
                    $result = $safeNumber->resetSafeNumberByOrderInfo(Request::post()->toArray());

                    if ($result['result'] == 'success') {
                        $this->json('안심번호가 발급 되었습니다.');
                    } else if ($result['result'] == 'fail') {
                        $this->json('일시적으로 안심번호 통신에 오류가 발생하였습니다. 잠시후 다시 시도해주세요.');
                    } else {
                        $this->json('안심번호 서비스가 사용중이 아니거나 수동발급할 데이터가 없습니다.');
                    }
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => $e->getCode(),
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // 수기주문등록 - 복수배송지 사용시 마일리지 정보 재셋팅
            case 'order_write_set_multi_shipping_mileage' :
                $memNo = Request::request()->get('memNo');
                $totalDeliveryCharge = Request::request()->get('totalDeliveryCharge');
                $totalDeliveryAreaCharge = Request::request()->get('totalDeliveryAreaCharge');
                $totalCouponOrderDcPrice = Request::request()->get('totalCouponOrderDcPrice');

                $cart = new CartAdmin($memNo);
                $cart->getCartGoodsData(null);

                // 회원 정보
                $memberData = array();
                if($memNo > 0){
                    $memberService = \App::load('\\Component\\Member\\Member');
                    $memberData = $memberService->getMemberDataOrderWrite($memNo);
                }

                // 마일리지 정책
                // '마일리지 정책에 따른 주문시 사용가능한 범위 제한' 에 사용되는 기준금액 셋팅
                $setMileagePriceArr = [
                    'totalCouponOrderDcPrice' => gd_isset($totalCouponOrderDcPrice, 0),
                    'totalDeliveryCharge' => gd_isset($totalDeliveryCharge, 0),
                    'totalGoodsDeliveryAreaPrice' => gd_isset($totalDeliveryAreaCharge, 0),
                ];
                $mileagePrice = $cart->setMileageUseLimitPrice($setMileagePriceArr);
                // 마일리지 정책에 따른 주문시 사용가능한 범위 제한
                $mileageUse = $cart->getMileageUseLimit(gd_isset($memberData['mileage'], 0), $mileagePrice);

                $this->json([
                    'mileageUse' => $mileageUse,
                ]);
                break;

            // 상품º주문번호별 메모 등록
            case 'admin_order_goods_memo_register':
                try {
                    $postValue = Request::request()->toArray();
                    $postValue['adminOrderGoodsMemo'] = preg_replace("!<script(.*?)<\/script>!is", "", $postValue['adminOrderGoodsMemo']);
                    $order->insertAdminOrderGoodsMemo($postValue);
                    $this->layer(__('저장이 완료되었습니다.'), 'parent.location.reload()');
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            // 주문 상세페이지(환불상세) - 상품º주문번호별 메모
            case 'adminOrdGoodsMemo':
                try {
                    Request::post()->set('adminOrderGoodsMemo', preg_replace("!<script(.*?)<\/script>!is", "", Request::post()->get('adminOrderGoodsMemo')));
                    $order->insertAdminOrderGoodsMemo(Request::post()->toArray());
                    $this->layer(__('저장이 완료되었습니다.'), 'parent.location.reload()');
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw new LayerException($e->getMessage(), null, null, null, 7000);
                    }
                }
                break;

            // 상품º주문번호별 메모 수정
            case 'admin_order_goods_memo_modify':
                try {
                    $postValue = Request::request()->toArray();
                    $postValue['adminOrderGoodsMemo'] = preg_replace("!<script(.*?)<\/script>!is", "", $postValue['adminOrderGoodsMemo']);
                    $order->updateAdminOrderGoodsMemo($postValue);
                    $this->layer(__('수정되었습니다.'), 'parent.location.reload()');
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            // 상품º주문번호별 메모 삭제
            case 'admin_order_goods_memo_delete':
                try {
                    $postValue = Request::request()->toArray();
                    $order->deleteAdminOrderGoodsMemo($postValue);
                    $this->layer(__('삭제되었습니다.'), 'parent.location.reload()');
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            // 외부채널 주문 일괄등록
            case 'externalOrderExcelRegist':
                try {
                    $externalOrder = \App::load('\\Component\\Order\\ExternalOrder');
                    $returnData = $externalOrder->updateExternalOrderExcel(Request::files()->toArray());

                    if($returnData['returnCode'] === 'success'){
                        $this->streamedDownload('주문엑셀업로드결과.xls');
                        $excel = App::load('\\Component\\Excel\\ExcelDataConvert');
                        $excel->setExternalOrderResult($returnData['returnData']);
                    }
                    else {
                        throw new Exception($returnData['returnData']);
                    }
                    exit;
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            // 외부채널 주문 일괄등록 샘플 다운로드
            case 'external_order_download':
                try {
                    $downloadPath = App::getBasePath() . '/data/excel/order_externalOrderSample.xls';

                    $this->download($downloadPath, 'external_order_excel_sample.xls');
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;
            // 옵션이 같은 경우 재고 체크
            case 'cartSelectStock':
                $postValue = Request::request()->toArray();
                $cart = new CartAdmin($postValue['memNo']);
                $stock = $cart->cartSelectStock($postValue['sno']);
                echo $stock;
                break;

            //취소/교환/반품/환불 관리 운영방식 설정
            case 'orderListUserExchangeConfig':
                try {
                    // 모듈 호출
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveConfiguserExchangeConfig(Request::post()->toArray());
                    throw new LayerException(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                exit();
                break;
        }
    }
}
