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
use Globals;
use Request;

class LayerGoodsController extends \Controller\Admin\Controller
{
    public function index()
    {

        /**
         * 레이어 상품 등록 페이지
         *
         * [관리자 모드] 레이어 상품 등록 페이지
         * 설명 : 상품 정보가 필요한 페이지에서 선택할 상품의 리스트
         * @author artherot
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */
        $getValue = Request::get()->toArray();



        $cate = \App::load('\\Component\\Category\\CategoryAdmin');
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
        $brand = \App::load('\\Component\\Category\\BrandAdmin');


        try {
            Request::get()->set('applyFl','y');
            $getData = $goods->getAdminListGoods('layer', 10);
            $page = \App::load('\\Component\\Page\\Page');    // 페이지 재설정

            $this->getView()->setDefine('layout', 'layout_layer.php');


            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('parentFormID', $getValue['parentFormID']);
            $this->setData('dataFormID', $getValue['dataFormID']);
            $this->setData('dataInputNm', $getValue['dataInputNm']);
            $this->setData('mode',gd_isset($getValue['mode']));
            $this->setData('callFunc', gd_isset($getValue['callFunc'],''));
            $this->setData('scmFl',gd_isset($getValue['scmFl']));
            $this->setData('scmNo',gd_isset($getValue['scmNo']));
            $this->setData('childRow',gd_isset($getValue['childRow']));
            $this->setData('optionRegister',gd_isset($getValue['optionRegister']));

            $this->setData('goods', $goods);
            $this->setData('cate', $cate);
            $this->setData('brand', $brand);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('search', gd_isset($getData['search']));
            $this->setData('checked', $getData['checked']);
            $this->setData('page', $page);


            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/layer_goods.php');

        } catch (Exception $e) {
            throw $e;
        }

    }
}
