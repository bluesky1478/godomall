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

use Exception;

/**
 * 세금계산서 설정 페이지 (일반 & 고도빌)
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class TaxInvoiceConfigController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('order', 'taxInvoice', 'config');

        // --- 장바구니 설정 정보
        try {

            $data = gd_policy('order.taxInvoice');
            $info = gd_policy('order.taxInvoiceInfo');

            gd_isset($data['taxInvoiceUseFl'], 'y');
            gd_isset($data['gTaxInvoiceFl'], 'y');
            gd_isset($data['taxInvoiceLimitFl'], 'y');
            gd_isset($data['taxInvoiceLimitDate'], '5');
            gd_isset($data['taxStepFl'], 'p');
            gd_isset($data['taxInvoiceOrderUseFl'], 'y');
            gd_isset($data['taxinvoiceInfoUseFl'], 'n');
            gd_isset($data['taxinvoiceDeadlineUseFl'], 'n');
            gd_isset($data['taxinvoiceInfo'], $info['taxinvoiceInfo']);
            gd_isset($data['taxinvoiceDeadline'], $info['taxinvoiceDeadline']);

            $checked = [];
            $checked['taxInvoiceUseFl'][$data['taxInvoiceUseFl']] =
            $checked['gTaxInvoiceFl'][$data['gTaxInvoiceFl']] =
            $checked['eTaxInvoiceFl'][$data['eTaxInvoiceFl']] =
            $checked['taxInvoiceLimitFl'][$data['taxInvoiceLimitFl']] =
            $checked['taxDeliveryFl'][$data['taxDeliveryFl']] =
            $checked['TaxMileageFl'][$data['TaxMileageFl']] =
            $checked['taxDepositFl'][$data['taxDepositFl']] =
            $checked['taxStepFl'][$data['taxStepFl']] =
            $checked['taxInvoiceOrderUseFl'][$data['taxInvoiceOrderUseFl']] =
            $checked['taxinvoiceInfoUseFl'][$data['taxinvoiceInfoUseFl']] =
            $checked['taxinvoiceDeadlineUseFl'][$data['taxinvoiceDeadlineUseFl']] =
            $checked['taxDeliveryCompleteFl'][$data['taxDeliveryCompleteFl']] = 'checked="checked"';

            $selected = [];
            $selected['taxInvoiceLimitDate'][$data['taxInvoiceLimitDate']]= 'selected="selected"';

            $disabled = [];
            $disabled['taxDeliveryCompleteFl'][$data['taxStepFl']] = 'disabled="disabled"';

        } catch (Exception $e) {
            //echo $e->getMessage();
        }

        // --- 관리자 디자인 템플릿
        $this->setData('data', $data);
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
        $this->setData('disabled', $disabled);
    }
}
