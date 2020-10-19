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

use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\AlertRedirectException;
use Message;
use Request;

/**
 * 세금계산서 처리 페이지
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class TaxInvoicePsController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        $postValue = Request::post()->xss()->toArray();

        // --- PG class
        $tax = \App::load('\\Component\\Order\\Tax');
        $order = \App::load('\\Component\\Order\\OrderAdmin');

        // 각 모드에 따른 처리
        switch ($postValue['mode']) {
            // --- 일반 세금계산서 설정 저장
            case 'tax_invoice_config':
                try {
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $tax = \App::load('\\Component\\Order\\Tax');

                    if ($postValue['eTaxInvoiceFl'] =='y'  && $tax->setCheckConnection($postValue['godobillSiteId'], $postValue['godobillApiKey']) === false) {
                        $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . ' - ' . __('고도빌 회원 ID와 고도빌 API KEY를 다시 확인해주세요'));
                        exit();
                    }

                    $policy->saveTaxInvoice($postValue);

                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // --- 세금계산서 발급 요청 저장
            case 'tax_invoice_register':
                try {
                    // --- 세금계산서 저장
                    $result = $tax->saveTaxInvoice($postValue);
                    if ($postValue['saveTaxInfoFl'] == 'y') {
                        $memberInvoiceInfo = $tax->setOrderTaxInfoConvert($postValue);
                        $tax->saveMemberTaxInvoiceInfo($memberInvoiceInfo);
                    }

                    // 주문 정보 수정
                    $order->setOrderReceiptRequest($postValue['orderNo'], 't');

                    // 에러 처리
                    if ($result[0] === false) {
                        echo '고도빌 전송 오류(' . $result[1] . ')';
                    }

                    if($postValue['taxMode'] =='modify') throw new LayerException(__("세금계산서가 수정되었습니다."));
                    else throw new LayerException(__("세금계산서가 신청되었습니다."));

                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;

            // 세금계산서 삭제
            case 'tax_invoice_delete':

                foreach($postValue['orderNo'] as $k => $v) {
                    $tax->setTaxInvoiceDelete($v);
                }

                throw new LayerException(__('삭제 되었습니다.'));

                exit();
                break;

            // 세금계산서 수정
            case 'tax_invoice_modify':

                try {
                    $postValue['orderNo'] = array_reverse($postValue['orderNo']);

                    foreach($postValue['orderNo'] as $k => $v) {
                        // --- 세금계산서 저장
                        $result = $tax->saveTaxInvoice($postValue['taxInvoiceData'][$v]);

                        if ($postValue['saveTaxInfoFl'] == 'y') {
                            $memberInvoiceInfo = $tax->setOrderTaxInfoConvert($postValue['taxInvoiceData'][$v]);
                            $tax->saveMemberTaxInvoiceInfo($memberInvoiceInfo);
                        }

                        // 주문 정보 수정
                        $order->setOrderReceiptRequest($v, 't');

                        // 에러 처리
                        if ($result[0] === false) {
                            echo '고도빌 전송 오류(' . $result[1] . ')';
                        }
                    }

                    throw new LayerException(__('세금계산서가 수정되었습니다.'));
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }

                exit();
                break;
            // 리스트에서 세금계산서 발행
            case 'send_invoice':
                try {
                    $postValue['orderNo'] = array_reverse($postValue['orderNo']);

                    // --- 세금계산서 저장
                    $result = $tax->setSendTaxInvoice($postValue);

                    if($postValue['godobillSend'] === 'y'){
                        $this->js("parent.BootstrapDialog.show({title: '정보', size: parent.get_layer_size('wide'), message: '<div>처리되었습니다.</div><div style=\"margin-top: 15px;\">고도빌로 전송된 세금계산서는 <strong>고도빌에서 전송처리를 진행하셔야만 발급처리가 완료</strong>되므로</div><div>고도빌>전자세금계산서 발급/관리>전자세금계산서 관리에서‘전송’처리를 해주시기 바랍니다.</div>', closable: false, buttons: [{label: '확인',cssClass: 'btn-black',action: function (dialog) {parent.location.href=\"../order/tax_invoice_list.php\";dialog.close();} }], });");
                    }
                    else {
                        throw new AlertRedirectException("처리되었습니다.",null,null,'../order/tax_invoice_list.php','parent');
                    }
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            // 세금계산서 재발행
            case 'resend_godobill':
                try {
                    $postValue['orderNo'] = array_reverse($postValue['orderNo']);

                    // --- 세금계산서 저장
                    $result = $tax->setSendTaxInvoice($postValue);

                    echo json_encode(gd_htmlspecialchars_stripslashes($result),JSON_FORCE_OBJECT);
                    exit;

                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;

        }

        exit();
    }
}
