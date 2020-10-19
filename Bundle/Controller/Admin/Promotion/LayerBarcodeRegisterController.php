<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Promotion;
use Bundle\Component\Promotion\BarcodeAdmin;
use Exception;
use Request;

class LayerBarcodeRegisterController extends \Controller\Admin\Controller
{

    public function index()
    {
        $getValue = Request::get()->toArray();

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 20);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $barcodeAdmin               = new BarcodeAdmin();
        $page->recode['amount']     = $barcodeAdmin->getCouponList('all'); //전체 카운트
        $page->recode['total']      = $barcodeAdmin->getCouponList('total'); // 전체 레코드 수
        $page->page['list']         = $getValue['pageNum']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());
        $couponList = $barcodeAdmin->getCouponList('list', $page);
        $searchData = $barcodeAdmin->getSearchBarcode();
        $this->setData('search', $searchData['search']);
        $this->setData('checked', $searchData['checked']);
        $this->setData('couponList', $couponList);
        $this->setData('page', $page);
        $this->setData('convertOption', $barcodeAdmin->convertCouponOptionName());

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
