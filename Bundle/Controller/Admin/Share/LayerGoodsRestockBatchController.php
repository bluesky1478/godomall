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

use Framework\Debug\Exception\LayerException;
use Request;
use Exception;

/**
 *
 * 재입고 알림 상품 일괄 관리
 *
 * @package Bundle\Controller\Admin\Share
 * @author  Hakyoung Lee <haky2@godo.co.kr>
 */
class LayerGoodsRestockBatchController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'goods', 'reStock');

        $getValue = Request::get()->toArray();
        $goods = \App::load('Component\\Goods\\GoodsAdmin');
        $cate = \App::load('Component\\Category\\CategoryAdmin');
        $brand = \App::load('Component\\Category\\BrandAdmin');

        try {
            $getData = $goods->getAdminListBatch('restock');
            $page = \App::load('Component\\Page\\Page');

            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('parentFormID', $getValue['parentFormID']);
            $this->setData('dataFormID', $getValue['dataFormID']);
            $this->setData('scmFl',gd_isset($getValue['scmFl']));
            $this->setData('scmNo',gd_isset($getValue['scmNo']));

            $this->setData('cate', $cate);
            $this->setData('brand', $brand);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('search', gd_isset($getData['search']));
            $this->setData('checked', $getData['checked']);
            $this->setData('batchAll', gd_isset($getData['batchAll']));
            $this->setData('page', $page);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/layer_goods_restock_batch.php');
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }
    }
}
