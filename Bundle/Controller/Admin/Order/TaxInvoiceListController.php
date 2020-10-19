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

class TaxInvoiceListController extends \Controller\Admin\Controller
{
    /**
     * 세금계산서 내역 페이지
     * [관리자 모드] 세금계산서 내력 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('order', 'taxInvoice', 'list');

        // --- 모듈 호출
        $tax = \App::load('\\Component\\Order\\Tax');
        $order = \App::load('\\Component\\Order\\OrderAdmin');


        // --- 세금계산서 데이터
        try {

            Request::get()->set('statusFl','y');
            $getData = $tax->getListTaxInvoice();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            // 세금계산서 사용여부
            $taxInfo = $tax->getTaxConf();

        } catch (Exception $e) {

        }

        // --- 관리자 디자인 템플릿
        $this->setData('data', $getData['data']);
        $this->setData('taxStats', $getData['taxStats']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $checked = $getData['checked']);
        $this->setData('page', $page);
        $this->setData('taxInfo', $taxInfo);
        $this->setData('tax', $tax);
        $this->setData('statusSearchableRange', $order->getOrderStatusAdmin());
    }
}
