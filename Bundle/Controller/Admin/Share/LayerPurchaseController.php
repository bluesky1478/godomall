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

namespace Bundle\Controller\Admin\Share;

use Exception;
use Request;

class LayerPurchaseController extends \Controller\Admin\Controller
{
    public function index()
    {

        /**
         * 레이어 매입처 등록 페이지
         *
         * [관리자 모드]  레이어 매입처 등록 페이지
         * @author atomyang
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        //--- 카테고리 설정
        $purchase = \App::load('\\Component\\Goods\\Purchase');
        $getValue = Request::get()->toArray();

        try {
            $getData = $purchase->getAdminListPurchase('layer', 10);
            $page = \App::load('\\Component\\Page\\Page');    // 페이지 재설정

            //--- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('parentFormID', $getValue['parentFormID']);
            $this->setData('dataFormID', $getValue['dataFormID']);
            $this->setData('dataInputNm', $getValue['dataInputNm']);
            $this->setData('mode', gd_isset($getValue['mode'],'search'));
            $this->setData('callFunc', gd_isset($getValue['callFunc'],''));
            $this->setData('childRow',gd_isset($getValue['childRow']));

            $this->setData('data', gd_isset($getData['data']));
            $this->setData('search', gd_isset($getData['search']));
            $this->setData('checked', $getData['checked']);
            $this->setData('useMallList', gd_isset($getData['useMallList']));
            $this->setData('page', $page);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/layer_purchase.php');

        } catch (Exception $e) {
            throw $e;
        }

    }
}
