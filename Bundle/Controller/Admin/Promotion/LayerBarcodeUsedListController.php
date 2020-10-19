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


class LayerBarcodeUsedListController extends \Controller\Admin\Controller
{

    public function index()
    {
        $getValue = Request::get()->toArray();
        $postValue = Request::post()->toArray();

        $barcodeNo = $postValue['bno'];

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 20);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);

        //사용 리스트 출력
        $barcodeAdmin = new BarcodeAdmin();
        $page->recode['amount'] = $page->recode['total'] = $barcodeAdmin->setBarcodeNo($barcodeNo)
                                            ->getUsedBarcodeCouponList(true); // 전체 레코드 수
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $usedList = $barcodeAdmin->setBarcodeNo($barcodeNo)->getUsedBarcodeCouponList(false, $page);

        $this->setData('usedList', $usedList);
        $this->setData('page', $page);
        $this->setData('bno', $barcodeNo);

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
