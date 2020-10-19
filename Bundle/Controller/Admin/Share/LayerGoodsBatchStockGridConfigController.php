<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2017, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
namespace Bundle\Controller\Admin\Share;

class LayerGoodsBatchStockGridConfigController extends \Controller\Admin\Controller
{

	/**
	 * {@inheritdoc}
	 */
	public function index()
	{
		$this->addScript([
			'bootstrap/bootstrap-table.js',
			'jquery/jquery.tablednd.js',
			'bootstrap/bootstrap-table-reorder-rows.js',
		]);

		$getValue = \Request::get()->toArray();

		$this->setData('goodsGridBatchStockMode', $getValue['goodsBatchStockGridMode']);

		// --- 관리자 디자인 템플릿
		$this->getView()->setDefine('layout', 'layout_layer.php');

		// 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('share/layer_goods_batch_stock_grid_config.php');
	}
}
