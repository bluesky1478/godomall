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

use Component\Godo\NaverPayAPI;
use Component\Naver\NaverPay;
use Component\Order\Order;
use Component\Order\OrderAdmin;
use Framework\Debug\Exception\AlertOnlyException;
use Exception;

/**
 * 주문상세의 네이버페이 상태변경 액션처리
 *
 * @package Controller\Admin\Order
 * @author  Jong-tae Ahn <lnjts@godo.co.kr>
 */
class OrderNaverpayPsController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            $req = \Request::post()->toArray();
            $mode = $req['mode'];
            $orderNo = $req['orderNo'];
            $orderAdmin = new OrderAdmin();
            if (empty($req['orderGoodsNos'])) {
                throw new Exception(__('일괄 처리할 상품을 선택하세요.'));
            }
            $orderGoodsNos = explode(',', $req['orderGoodsNos']);
            $naverpayApi = new NaverPayAPI();
            $data = $orderAdmin->getOrderGoodsData($orderNo, $orderGoodsNos, null, null, null, false);

            switch ($mode) {
                case 'DelayProductOrder' :   //발송지연
                    foreach ($data as $val) {
                        $params = [
                            'ProductOrderID' => $val['apiOrderGoodsNo'],
                            'DispatchDueDate' => $req['date'],
                            'DispatchDelayReasonCode' => $req['naverCode'],
                            'DispatchDelayDetailReason' => $req['contents'],
                        ];
                        $naverpayApi->request($mode, $params);
                        if ($naverpayApi->getError()) {
                            throw new Exception($naverpayApi->getError());
                        }

                        $naverpay = new NaverPay();
                        $reasonText = $naverpay->getClaimReasonCode($req['naverCode'], 'DelayProductOrder');
                        $orderAdmin->orderLog($orderNo, $val['sno'], $orderAdmin->getOrderStatusAdmin($val['orderStatus']) . '(' . $val['orderStatus'] . ')', '발송지연', sprintf('사유 :  %s <br>상세사유 : %s <br>발송기한 : %s', $reasonText, nl2br($req['contents']), $req['date']));
                    }

                    $this->js('top.location.reload()');

                    break;
                case 'RejectReturn' :    //반품 거부
                    foreach ($data as $val) {
                        $params = [
                            'ProductOrderID' => $val['apiOrderGoodsNo'],
                            'RejectDetailContent' => $req['contents'],
                        ];
                        $naverpayApi->request($mode, $params);
                        if ($naverpayApi->getError()) {
                            throw new Exception(NaverPay::convertErrorMsg($naverpayApi->getError()));
                        }
                    }

                    $this->js('top.location.reload()');
                    break;
                case 'WithholdReturn' :  //반품보류
                    foreach ($data as $val) {
                        $naverpayApi->request('GetProductOrderInfoList', ['ProductOrderIDList' => $val['apiOrderGoodsNo']]);   // 갱신
                        $reloadData = $orderAdmin->getOrderGoodsData($req['orderNo'], $val['sno'], null, null, null, false);
                        if ($reloadData['orderStatus'] == 'b3') { //반품보류상태면 스킵;
                            continue;
                        }
                        $params = [
                            'ProductOrderID' => $val['apiOrderGoodsNo'],
                            'ReturnHoldCode' => $req['naverCode'],
                            'ReturnHoldDetailContent' => $req['contents'],
                        ];
                        if (empty($req['extraData']) === false && $req['extraData'] > 0) {
                            $params['EtcFeeDemandAmount'] = $req['extraData'];
                        }

                        $naverpayApi->request($mode, $params);
                        if ($naverpayApi->getError()) {
                            throw new Exception(NaverPay::convertErrorMsg($naverpayApi->getError()));
                        }
                    }
                    break;
                case 'ReleaseReturnHold' ://반품보류해제
                    $holdReason = [];
                    foreach ($data as $val) {
                        $naverpayApi->request('GetProductOrderInfoList', ['ProductOrderIDList' => $val['apiOrderGoodsNo']]);   //해제전에 갱신
                        $reloadData = $orderAdmin->getOrderGoodsData($req['orderNo'], $val['sno'], null, null, null, false);
                        $claimDeliveryFeePayMethod = $reloadData['checkoutData']['returnData']['ClaimDeliveryFeePayMethod'];
                        $holdReason[] = $reloadData['naverpayStatus']['reason'];
                        if ($claimDeliveryFeePayMethod == '환불금에서 차감') {
                            $this->json(['result' => 'fail', 'msg' => '결제방식이 환불금 차감인 건은 반품보류해제를 할 수 없으며, 반품회수완료 처리만 가능합니다.']);
                        }
                        try {
                            $params = [
                                'ProductOrderID' => $val['apiOrderGoodsNo'],
                            ];

                            $naverpayApi->request($mode, $params);
                            if ($naverpayApi->getError()) {
                                throw new Exception(NaverPay::convertErrorMsg($naverpayApi->getError()));
                            }
                        } catch (\Exception $e) {
                            $naverpayApi->request('GetProductOrderInfoList', ['ProductOrderIDList' => $val['apiOrderGoodsNo']]);
                        }
                    }

                    $orderData = $orderAdmin->getOrderGoodsData($req['orderNo'], null, null, null,null, false);
                    foreach($orderData as $val){
                        if($val['naverpayStatus']['code'] == 'WithholdReturn' && in_array($val['naverpayStatus']['reason'],$holdReason)) {    //반품보류사유가같으면
                            $naverpayApi->request('GetProductOrderInfoList', ['ProductOrderIDList' => $val['apiOrderGoodsNo']]);
                        }
                    }


                    $this->json(['result' => 'success', 'msg' => '']);
                    //히든처리
                    break;
                case 'ReDeliveryExchange' : //교환 재배송 처리
                    foreach ($data as $val) {

                        $params = [
                            'ProductOrderID' => $val['apiOrderGoodsNo'],
                            'ReDeliveryMethodCode' => 'DELIVERY',
                            'ReDeliveryCompanyCode' => $req['deliveryCompanyCode'],
                            'ReDeliveryTrackingNumber' => $req['invoiceNo'],
                        ];

                        $naverpayApi->request($mode, $params);
                        if ($naverpayApi->getError()) {
                            throw new Exception(NaverPay::convertErrorMsg($naverpayApi->getError()));
                        }
                    }

                    break;
                case 'RejectExchange' : //교환거부
                    foreach ($data as $val) {
                        $params = [
                            'ProductOrderID' => $val['apiOrderGoodsNo'],
                            'RejectDetailContent' => $req['contents'],
                        ];
                        if (empty($req['extraData']) === false && $req['extraData'] > 0) {
                            $params['EtcFeeDemandAmount'] = $req['extraData'];
                        }
                        $naverpayApi->request($mode, $params);
                        if ($naverpayApi->getError()) {
                            throw new Exception(NaverPay::convertErrorMsg($naverpayApi->getError()));
                        }
                    }
                    break;
                case 'WithholdExchange' :   //교환보류
                    foreach ($data as $val) {
                        $naverpayApi->request('GetProductOrderInfoList', ['ProductOrderIDList' => $val['apiOrderGoodsNo']]);   // 갱신
                        $reloadData = $orderAdmin->getOrderGoodsData($req['orderNo'], $val['sno'], null, null, null, false);
                        if ($reloadData['orderStatus'] == 'e4') { //교환보류상태면 스킵;
                            continue;
                        }

                        $params = [
                            'ProductOrderID' => $val['apiOrderGoodsNo'],
                            'ExchangeHoldCode' => $req['naverCode'],
                            'ExchangeHoldDetailContent' => $req['contents'],
                        ];
                        if (empty($req['extraData']) === false && $req['extraData'] > 0) {
                            $params['EtcFeeDemandAmount'] = $req['extraData'];
                        }
                        $naverpayApi->request($mode, $params);
                        if ($naverpayApi->getError()) {
                            throw new Exception(NaverPay::convertErrorMsg($naverpayApi->getError()));
                        }
                    }

                    break;
                case 'ReleaseExchangeHold' :    //교환보류 해제
                    $holdReason = [];
                    foreach ($data as $val) {
                        try {
                            $naverpayApi->request('GetProductOrderInfoList', ['ProductOrderIDList' => $val['apiOrderGoodsNo']]);   //해제전에 갱신
                            $reloadData = $orderAdmin->getOrderGoodsData($req['orderNo'], $val['sno'], null, null, null, false);
                            $holdReason[] = $reloadData['naverpayStatus']['reason'];
                            $claimDeliveryFeePayMethod = $reloadData['checkoutData']['exchangeData']['ClaimDeliveryFeePayMethod'];
                            if ($claimDeliveryFeePayMethod == '환불금에서 차감') {
                                $this->json(['result' => 'fail', 'msg' => '결제방식이 환불금 차감인 건은 교환보류해제를 할 수 없습니다.']);
                            }

                            $params = [
                                'ProductOrderID' => $val['apiOrderGoodsNo'],
                            ];

                            $naverpayApi->request($mode, $params);
                            if ($naverpayApi->getError()) {
                                throw new Exception(NaverPay::convertErrorMsg($naverpayApi->getError()));
                            }
                        } catch (\Exception $e) {
                            $naverpayApi->request('GetProductOrderInfoList', ['ProductOrderIDList' => $val['apiOrderGoodsNo']]);
                        }
                    }

                    $orderData = $orderAdmin->getOrderGoodsData($req['orderNo'], null, null, null,null, false);
                    foreach($orderData as $val){
                        if($val['naverpayStatus']['code'] == 'WithholdExchange' && in_array($val['naverpayStatus']['reason'],$holdReason)) {    //교환보류사유가같으면
                            $naverpayApi->request('GetProductOrderInfoList', ['ProductOrderIDList' => $val['apiOrderGoodsNo']]);
                        }
                    }

                    $this->json(['result' => 'success', 'msg' => '']);
                    break;

                case 'ApproveCancelApplication' :
                    $resultSno = [];
                    foreach ($data as $val) {
                        $params = [
                            'ProductOrderID' => $val['apiOrderGoodsNo'],
                            'EtcFeeDemandAmount'=>0,
                        ];
                        $naverpayApi->request($mode, $params);
                        if ($naverpayApi->getError()) {
                            throw new Exception(NaverPay::convertErrorMsg($naverpayApi->getError()));
                        }
                        $resultSno[] = $val['sno'];
                    }

                    $reloadList = $orderAdmin->getOrderGoodsData($orderNo,null,null,null,null,false,false,'r');
                    foreach($reloadList as $val){
                        if(!in_array($val['sno'],$resultSno)){
                            $naverpayApi->request('GetProductOrderInfoList', ['ProductOrderIDList' => $val['apiOrderGoodsNo']]);
                        }
                    }

                    break;
            }
            $this->js('top.location.reload()');
        } catch (Exception $e) {
            if (\Request::isAjax()) {
                $this->json(['result' => 'fail', 'msg' => $e->getMessage()]);
            } else {
                throw new AlertOnlyException(strip_tags($e->getMessage()));
            }
        }


    }
}
