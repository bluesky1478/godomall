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
namespace Bundle\Controller\Admin\Goods;

use Exception;
use Globals;
use Request;

class PurchaseListController extends \Controller\Admin\Controller
{
    /**
     * [관리자 모드] 매입처 관리 페이지
     *
     * @author atomyang
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @throws Except
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'category', 'purchaseList');

        // --- 모듈 호출
        $purchase = \App::load('\\Component\\Goods\\Purchase');

        // --- 상품 아이콘 데이터
        try {

            $getData = $purchase->getAdminListPurchase();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

        } catch (Exception $e) {
            throw $e;
        }

        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('page', $page);
    }
}
