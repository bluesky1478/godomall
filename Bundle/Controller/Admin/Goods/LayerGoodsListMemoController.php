<?php

namespace Bundle\Controller\Admin\Goods;

use App;
use Request;

/**
 * @author  <tomi@godo.co.kr>
 */
class LayerGoodsListMemoController extends \Controller\Admin\Controller
{
	public function index()
	{
		$postValue = Request::post()->toArray();
		$goodsNo = $postValue['goodsNo'];
		$goodsAdmin = \App::load('\\Component\\Goods\\GoodsAdmin');
		// 상품 관리자 메모 데이터 로드
		$goodsAdminMemoData = $goodsAdmin->getGoodsListAdminMemo($goodsNo)[0];

		$this->setData('goodsNo', $goodsNo); // 상품번호
		$this->setData('goodsAdminMemoData', $goodsAdminMemoData); // 관리자메모


		$this->getView()->setDefine('layout', 'layout_layer.php');

		// 공급사와 동일한 페이지 사용
		$this->getView()->setPageName('goods/layer_goods_list_memo.php');
	}
}
