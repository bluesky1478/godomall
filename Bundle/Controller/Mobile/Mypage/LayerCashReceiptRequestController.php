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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Mobile\Mypage;

use Request;

/**
 * 현금영수증 신청 레이어
 *
 * @package Bundle\Controller\Mobile\Mypage
 * @author  Hakyoung Lee <haky2@godo.co.kr>
 */
class LayerCashReceiptRequestController extends \Controller\Mobile\Controller
{
    public function index()
    {
        // POST 리퀘스트
        $postValue = Request::post()->toArray();

        // 모듈 설정
        $order = \App::load('\\Component\\Order\\Order');

        // 주문 리스트 정보
        $orderData = $order->getOrderView($postValue['orderNo']);
        $this->setData('orderInfo', $orderData);

        // 현금영수증 정보
        $receipt['cashFl'] = $postValue['receiptCashFl'];
        $receipt['periodFl'] = $postValue['receiptPeriodFl'];
        $this->setData('receipt', $receipt);

        // 현금영수증 입력 정보 가져오기
        if (gd_is_login() === true) {
            $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
            $memNo = \Session::get('member.memNo');
            $memberCashInfo = $cashReceipt->getMemberCashReceiptInfo($memNo);
            $memberInvoiceInfo['cash'] = $memberCashInfo;
            $this->setData('memberInvoiceInfo', $memberInvoiceInfo);
        }
    }
}