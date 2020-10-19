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

use Framework\Utility\StringUtils;
use Message;
use Globals;
use Request;

/**
 * 세금계산서 발급 요청 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class FrameTaxInvoiceRegisterController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 세금계산서 데이터
        try {
            // --- 모듈 호출
            $tax = \App::load('\\Component\\Order\\Tax');
            $order = \App::load('\\Component\\Order\\OrderAdmin');

            // 등록인 경우
            if (Request::get()->get('mode')  == 'register') {
                // --- 주문 데이타
                $orderData = $order->getOrderViewForReceipt(Request::get()->get('orderNo'));
                $orderData['taxInvoiceInfo'] = $tax->setTaxInvoicePrice(Request::get()->get('orderNo'), $orderData['taxPolicy']);
                $orderData['taxEmail'] = $orderData['orderEmail'];
                $orderData['requestNm'] =  \Session::get('manager.managerNm');

                // --- 회원 데이터
                if (empty($orderData['memNo']) == false) {
                    $memberTaxInfo = $tax->getMemberTaxInvoiceInfo($orderData['memNo']);
                    $orderData['taxCompany'] = $memberTaxInfo['company'];
                    $orderData['taxService'] = $memberTaxInfo['service'];
                    $orderData['taxItem'] = $memberTaxInfo['item'];
                    $orderData['taxBusiNo'] = $memberTaxInfo['taxBusiNo'];
                    $orderData['taxCeoNm'] = $memberTaxInfo['ceo'];
                    $orderData['taxZipcode'] = $memberTaxInfo['comZipcode'];
                    $orderData['taxZonecode'] = $memberTaxInfo['comZonecode'];
                    $orderData['taxAddress'] = $memberTaxInfo['comAddress'];
                    $orderData['taxAddressSub'] = $memberTaxInfo['comAddressSub'];
                    $orderData['taxEmail'] = $memberTaxInfo['email'];

                    $member = \App::load('\\Component\\Member\\Member');
                    $memberData = $member->getMemberInfo($orderData['memNo']);
                    $memberData['comZipcode'] = $memberData['comZipcode'] ? $memberData['comZipcode'] : $memberTaxInfo['comZipcode'];
                    $memberData['comZonecode'] = $memberData['comZonecode'] ? $memberData['comZonecode'] : $memberTaxInfo['comZonecode'];
                    $memberData['comAddress'] = $memberData['comAddress'] ? $memberData['comAddress'] : $memberTaxInfo['comAddress'];
                    $memberData['comAddressSub'] = $memberData['comAddressSub'] ? $memberData['comAddressSub'] : $memberTaxInfo['comAddressSub'];
                    $this->setData('memberData', $memberData);
                }
            }
            // 수정인 경우
            elseif (Request::get()->get('mode') == 'modify') {
                $orderData = $tax->getOrderTaxInvoice(Request::get()->get('orderNo'));
                $orderData['orderNo'] = Request::get()->get('orderNo');
                $orderData['checkStatus'] = $tax->setCheckTaxStatus($orderData['orderStatus']);
                $orderData['requestGoodsNm'] = StringUtils::removeTag($orderData['requestGoodsNm']);
                $checked['statusFl'][$orderData['statusFl']] = 'checked="checked"';
                if($orderData['taxEmail'] =='' && $orderData['orderEmail'])  $orderData['taxEmail'] = $orderData['orderEmail'];
            }
            // mode 값이 없다면 오류
            else {
                throw new \Exception(__('오류가 발생 하였습니다.'));
            }


            // mode 설정
            $orderData['mode'] = Request::get()->get('mode');

            // 세금계산서 사용여부
            $orderData['tax'] = $tax->getTaxConf();
        } catch (\Exception $e) {
            $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
            $this->layer(__('오류가 발생 하였습니다.') . $item);
        }

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_blank.php');

        // 공급사와 템플릿 공유 사용
        $this->getView()->setPageName('order/frame_tax_invoice_register.php');

        $this->setData('orderData', $orderData);
        $this->setData('statusStandardNm', $order->statusStandardNm);
        $this->setData('checked', gd_isset($checked));
    }
}
