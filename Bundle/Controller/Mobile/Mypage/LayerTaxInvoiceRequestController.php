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
 * 세금계산서 신청 레이어
 *
 * @package Bundle\Controller\Mobile\Mypage
 * @author  Hakyoung Lee <haky2@godo.co.kr>
 */
class LayerTaxInvoiceRequestController extends \Controller\Mobile\Controller
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

        // 세금계산서 정보
        $receipt['taxFl'] = $postValue['receiptTaxFl'];
        $this->setData('receipt', $receipt);

        //세금계산서 정보
        $taxInfo = gd_policy('order.taxInvoice');
        $this->setData('taxInfo', $taxInfo);

        // 메일도메인
        $emailDomain = gd_array_change_key_value(gd_code('01004'));
        $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);
        $this->setData('emailDomain', $emailDomain); // 메일주소 리스팅

        // 세금계산서 입력 정보 가져오기
        if (gd_is_login() === true) {
            $tax = \App::load('\\Component\\Order\\Tax');
            $memNo = \Session::get('member.memNo');
            $memberTaxInfo = $tax->getMemberTaxInvoiceInfo($memNo);
            $memberInvoiceInfo['tax'] = $memberTaxInfo;
            $this->setData('memberInvoiceInfo', $memberInvoiceInfo);
        }
    }
}
