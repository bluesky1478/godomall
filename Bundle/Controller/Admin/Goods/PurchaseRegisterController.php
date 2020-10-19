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

class PurchaseRegisterController extends \Controller\Admin\Controller
{
    /**
     * [관리자 모드] 매입처 등록 페이지
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

        if (Request::get()->get('purchaseNo') > 0) {
            $this->callMenu('goods', 'category', 'purchaseModify');
        } else {
            $this->callMenu('goods', 'category', 'purchaseRegister');
        }

        // --- 모듈 설정
        $purchase = \App::load('\\Component\\Goods\\Purchase');
        $data = $purchase->getDataPurchase(Request::get()->get('purchaseNo'));

        // --- 관리자 디자인 템플릿
        if (Request::get()->get('popupMode')) {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        }

        $this->setData('data', $data['data']);
        $this->setData('checked', $data['checked']);

        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('goods/purchase_register.php');
    }
}
