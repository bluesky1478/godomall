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
use Request;

/**
 * 세금계산서 요청 페이지
 * [관리자 모드] 세금계산서 신청 리스트 페이지
 *
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class TaxInvoiceRequestController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('order', 'taxInvoice', 'request');

        // --- 모듈 호출
        $tax = \App::load('\\Component\\Order\\Tax');
        $order = \App::load('\\Component\\Order\\OrderAdmin');


        // --- 세금계산서 데이터
        try {
            $searchDateFl = gd_isset(Request::get()->get('searchDateFl'), 'regDt');
            Request::get()->set('searchDateFl',$searchDateFl);
            Request::get()->set('statusFl','r');
            $getData = $tax->getListTaxInvoice();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            // 세금계산서 사용여부
            $taxInfo = $tax->getTaxConf();

        } catch (Exception $e) {

        }

        // --- 관리자 디자인 템플릿
        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $checked = $getData['checked']);
        $this->setData('page', $page);
        $this->setData('taxInfo', $taxInfo);
        $this->setData('tax', $tax);
        $this->setData('statusSearchableRange', $order->getOrderStatusAdmin());
    }
}
