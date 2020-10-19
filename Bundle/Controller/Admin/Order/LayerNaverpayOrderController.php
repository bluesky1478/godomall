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

use Component\Delivery\Delivery;
use Component\Naver\NaverPay;
use Component\Order\Order;
use Framework\Utility\StringUtils;
use Request;
use Exception;

/**
 * 주문상세의 환불접수 리스트내 수정 레이어
 * [관리자 모드] 환불접수내용 수정
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerNaverpayOrderController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // --- 모듈 호출
            $status = \Request::get()->get('status');
            $orderNo = Request::get()->get('orderNo');
            $layerMode = \Request::get()->get('mode');
            $orderGoodsNos = Request::get()->get('orderGoodsNos');
            $naverpay = new NaverPay();
            $order = new Order();
            $config = $naverpay->getConfig();
            if ($layerMode == 'view') {
                $sno = \Request::get()->get('orderGoodsNo');
                $data = $order->getOrderGoodsData($orderNo, $sno, null, null, null, false);
                $this->setData('data', $data['naverpayStatus']);
                $this->setData('checkoutData', $data['checkoutData']);
                $mode = $data['naverpayStatus']['code'];
            } else {
                list($statusType, $channel, $mode) = explode('_', $status);
            }
            $snakeCase = StringUtils::strToSnakeCase($mode);
            $returnPrice = $config['returnPrice'];
//            switch($status){
            switch ($mode) {
//                case 'g_naverpay_DelayProductOrder' :   //발송지연
                case 'DelayProductOrder' :   //발송지연
                    $codeList = $naverpay->getClaimReasonCode(null, 'DelayProductOrder');
                    if ($layerMode != 'view') {
                        $arrOrderGoodsNo = explode(',', $orderGoodsNos);
                        foreach ($arrOrderGoodsNo as $sno) {
                            $data = $order->getOrderGoodsData($orderNo, $sno, null, null, null, false);
                            if (substr($data['orderStatus'], 0, 1) == 'p' || substr($data['orderStatus'], 0, 1) == 'g') {
                                if ($data['naverpayStatus']['code'] == 'DelayProductOrder') {
                                    exit('error|발송지연이 불가능한 상태가 포함되어 있습니다.\'');
                                }
                            } else {
                                exit('error|발송지연이 불가능한 상태가 포함되어 있습니다.');
                            }
                        }
                    }
                    break;
                case 'RejectReturn' :    //반품 거부
                    if ($layerMode == 'view') {
                        exit('error|반품거부 사유는 네이버페이센터에서 확인가능합니다.');
                    }
                    break;
                case 'WithholdReturn' :  //반품 보류

                    if ($layerMode != 'view') {
                        $arrOrderGoodsNo = explode(',', $orderGoodsNos);
                        $data = $order->getOrderGoodsData($orderNo, $arrOrderGoodsNo, null, null, null, false);
                        foreach($data  as $val) {
                            if ($val['orderStatus'] == 'b3') {
                                exit('error|이미 보류처리 되었습니다.');
                            }
                        }
                        $codeList = $naverpay->getClaimReasonCode(null, 'WithholdReturn');
                    }
                    break;
                case 'ReleaseReturnHold' :   //반품보류해제
                    //레이어처리안함
                    $arrOrderGoodsNo = explode(',', $orderGoodsNos);
                    if ($layerMode != 'view') {
                        foreach ($arrOrderGoodsNo as $sno) {
                            $data = $order->getOrderGoodsData($orderNo, $sno, null, null, null, false);
                            if ($data['naverpayStatus']['code'] != 'WithholdReturn') {    //반품거부상태가 아니면 경고창
                                exit('error|반품보류 주문은 반품보류해제 전에는 주문상태를 변경하실 수 없습니다.');
                            }
                        }
                    }
                    exit;
                    break;
                case 'ReDeliveryExchange' :  //교환 재배송 처리
                    $delivery = new Delivery();
                    $deliveryCompanyList = $delivery->getDeliveryCompany(null, true, 'naverpay');
                    $this->setData('deliveryCompanyList', $deliveryCompanyList);
                    if ($layerMode == 'view') {
                        $this->setData('deliveryCompanyNm', $data['naverpayStatus']['deliveryCompanyNm']);
                        $this->setData('invoiceNo', $data['naverpayStatus']['invoiceNo']);
                    }
                    break;
                case 'RejectExchange' ://교환거부
                    if ($layerMode == 'view') {
                        exit('error|교환거부 사유는 네이버페이센터에서 확인가능합니다.');
                    }
                    break;
                case 'WithholdExchange' :    //교환보류
                    if ($layerMode != 'view') {
                        $arrOrderGoodsNo = explode(',', $orderGoodsNos);
                        $data = $order->getOrderGoodsData($orderNo, $arrOrderGoodsNo, null, null, null, false);
                        foreach($data  as $val) {
                            if ($val['orderStatus'] == 'e4') {
                                exit('error|이미 보류처리 되었습니다.');
                            }
                        }
                        $codeList = $naverpay->getClaimReasonCode(null, 'WithholdExchange');
                    }
                    break;
                case 'ReleaseExchangeHold' :    //교환보류 해제
                    break;

                case 'ApproveCancelApplication' :

                    break;
            }
//            $this->getView()->setPageName('order/layer_naverpay_'.$snakeCase.'.php');
            $this->setData('includeFile', 'inc_layer_naverpay_' . $snakeCase . '.php');
            // 레이어 템플릿
            $this->setData('layerMode', $layerMode);
            $this->setData('orderNo', $orderNo);
            $this->setData('codeList', $codeList);
            $this->setData('returnPrice', $returnPrice);
            $this->setData('mode', $mode);
            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setPageName('order/layer_naverpay_order.php');

        } catch (Exception $e) {
            throw $e;
        }


    }
}
