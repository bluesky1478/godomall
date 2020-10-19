<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2017, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Order;

use Component\Order\OrderAdmin;
use App;
use Exception;
use Request;
use Framework\Debug\Exception\AlertCloseException;
use Component\Member\Manager;

/**
 * Class PopupOrderPacketController
 *
 * @package Bundle\Controller\Admin\Order
 * @author by
 */
class PopupOrderPacketController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            $orderNoArr = $orderData = $addressData = [];
            $packetAbleFl = false; //묶음배송 가능 여부
            $checkPacket = true;
            $checkScmPacket = true;
            $ckeckMultiDeliveryPacket = true;
            $checkPacketOrderChannel = ture;
            $getValue = Request::get()->toArray();
            $scmNo = \Session::get('manager.scmNo');
            $order = App::load(\Component\Order\OrderAdmin::class);

            $orderNoArr = array_values(array_unique(array_filter(explode(INT_DIVISION, $getValue['orderNoStr']))));

            if(count($orderNoArr) > 0){
                foreach($orderNoArr as $key => $orderNo){
                    $tmpOrderData = [];
                    $tmpOrderData = $order->getOrderData($orderNo);
                    if($tmpOrderData['multiShippingFl'] === 'y'){
                        $ckeckMultiDeliveryPacket = false;
                        break;
                    }

                    $orderGoodsData = $order->getOrderGoodsData($orderNo, null, null, null, 'admin', true, true, null, null);
                    if (count($orderGoodsData) > 0) {
                        foreach ($orderGoodsData as $sKey => $dataVal) {
                            foreach ($dataVal as $goodsData) {
                                if(Manager::isProvider()){
                                    //공급사로그인시 다른공급사의 상품이 있는지 확인 후 묶음배송 불가처리
                                    if((int)$scmNo !== (int)DEFAULT_CODE_SCMNO && (int)$scmNo !== (int)$goodsData['scmNo']){
                                        $checkScmPacket = false;
                                    }
                                }
                                $tmpOrderData['orderGoodsNoArr'][] = $goodsData['sno'];
                            }
                        }
                    }

                    $diffReceiverData = [
                        trim(implode('', $tmpOrderData['receiverPhone'])),
                        trim(implode('', $tmpOrderData['receiverCellPhone'])),
                        preg_replace("/\s/", "", $tmpOrderData['receiverName']),
                        preg_replace("/\s/", "", $tmpOrderData['receiverZonecode']),
                        preg_replace("/\s/", "", $tmpOrderData['receiverAddress']),
                        preg_replace("/\s/", "", $tmpOrderData['receiverAddressSub']),
                    ];

                    $addressData[] = implode("", $diffReceiverData);

                    if($checkScmPacket === false){
                        break;
                    }
                    if(trim($tmpOrderData['packetCode'])){
                        $checkPacket = false;
                        break;
                    }
                    if($tmpOrderData['orderChannelFl'] && $tmpOrderData['orderChannelFl'] !== 'shop'){
                        $checkPacketOrderChannel = false;
                        break;
                    }
                    $orderData[] = $tmpOrderData;
                }
            }


            if($ckeckMultiDeliveryPacket === false){
                throw new Exception(__('복수배송지 주문건이 존재하여 묶음배송 처리를 할 수 없습니다.'));
            }
            if($checkScmPacket === false){
                throw new Exception(__('다른 공급사 주문상품이 존재하여 묶음배송 처리를 할 수 없습니다.'));
            }
            if($checkPacket === false){
                throw new Exception(__('이미 묶음배송에 포함되어 있는 주문건이 존재합니다.'));
            }
            if($checkPacketOrderChannel === false){
                throw new Exception(__('네이버페이, 페이코, 기타채널 주문건은 묶음배송 처리를 할 수 없습니다.'));
            }

            if(count(array_unique($addressData)) === 1){
                //묶음배송 가능 여부
                $packetAbleFl = true;
            }

            $this->setData('orderData', $orderData);
            $this->setData('packetAbleFl', $packetAbleFl);
            $this->setData('orderNoStr', $getValue['orderNoStr']);

            $this->getView()->setDefine('layout', 'layout_blank.php');
            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('./order/popup_order_packet.php');
        } catch (Exception $e) {
            throw new AlertCloseException($e->getMessage());
        }
    }
}
