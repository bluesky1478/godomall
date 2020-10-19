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
use Globals;
use Request;
use Message;

/**
 * 에스크로 배송 등록 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class FrameEscrowDeliveryController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        // --- 모듈 호출
        $pgCodeConfig = App::getConfig('payment.pg');

        // --- 에스크로 배송 등록 데이터
        try {
            // --- PG 설정 불러오기
            $pgConf = gd_pgs();
            $pgConf['pgNm'] = Globals::get('gPg.' . $pgConf['pgName']);

            // --- 모듈 호출
            $order = \App::load('\\Component\\Order\\OrderAdmin');

            // --- 주문 데이타
            $orderData = $order->getOrderData(Request::get()->get('orderNo'));

            // 배송등록 확인일시
            if (empty($orderData['escrowDeliveryDt']) === true || $orderData['escrowDeliveryDt'] == '0000-00-00 00:00:00') {
                $orderData['escrowDeliveryDt'] = date('Y-m-d H:i:s');
            }

            // 택배사 코드
            $escrowDelivery = Request::get()->get('escrowInvoiceNo');
            if (empty($orderData['escrowDeliveryCd']) === true) {
                // Allat 이나 NHN KCP 인 경우 배송업체 처리
                if (($pgConf['pgName'] == 'allat' || $pgConf['pgName'] == 'kcp') && empty($escrowDelivery) === false) {
                    $delivery = \App::load('\\Component\\Delivery\\Delivery');
                    $tmpDelivery = $delivery->getDeliveryCompany($escrowDelivery, false);
                    $orderData['escrowDeliveryCd'] = $tmpDelivery[0]['companyName'];
                } else {
                    $orderData['escrowDeliveryCd'] = gd_isset($pgCodeConfig->getPgEscrowDelivery()[$pgConf['pgName']][gd_isset($escrowDelivery, 'no')]);
                }
            }

            // LG U+ 인 경우
            if ($pgConf['pgName'] == 'lguplus') {
                $escrowDeliveryCd = explode(STR_DIVISION, $orderData['escrowDeliveryCd']);
                if ($escrowDeliveryCd[0] == 'etc') {
                    $orderData['escrowDeliveryCd'] = $escrowDeliveryCd[0];
                    $orderData['rcvname'] = $escrowDeliveryCd[1];
                    $orderData['rcvrelation'] = $escrowDeliveryCd[2];
                }

                // 상품 코드 : FAIL : 실패 > 상품번호 오류(productid)1000000008==1000000000 라는 오류로 인해 주문시와 동일한 첫번째 배열만 처리함
                $goodsData = $order->getOrderGoodsData(Request::get()->get('orderNo'), null, null, null, 'admin', false, true);
                $orderData['goodsNo'] = $goodsData[0]['goodsNo'];
                /*
                 * foreach ($goodsData as $val) { $orderData['goodsNo']	= $val['goodsNo']; }
                 */
            }

            // Allat 인 경우 지불방법 세팅
            if ($pgConf['pgName'] == 'allat') {
                $orderData['payType'] = $pgCodeConfig->getPgSettleCode()[$pgConf['pgName']][substr($orderData['settleKind'], 1, 1)];
            }

            $selected['escrowDeliveryCd'][$orderData['escrowDeliveryCd']] = 'selected="selected"';

            // 운송장 번호
            if (empty($orderData['escrowInvoiceNo']) === true) {
                $orderData['escrowInvoiceNo'] = Request::get()->get('escrowInvoiceNo');
            }
        } catch (\Exception $e) {
            $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
            $this->layer(__('오류가 발생 하였습니다.') . $item);
        }

        // --- 관리자 디자인 템플릿

        $this->getView()->setDefine('layout', 'layout_blank.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());
        //$this->getView()->setDefine('layoutPGContent', DIR_PG . $pgConf['pgName'] . '/escrow_delivery_start.php');

        $this->setData('orderData', $orderData);
        $this->setData('pgConf', $pgConf);
        $this->setData('selected', $selected);
        $this->setData('gMall', Globals::get('gMall'));
    }
}
