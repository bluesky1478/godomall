<?php

namespace Bundle\Controller\Admin\Goods;

use App;
use Request;

/**
 * @author  <tomi@godo.co.kr>
 */
class LayerGoodsListOptionSimpleController extends \Controller\Admin\Controller
{
	public function index()
	{
		$postValue = Request::post()->toArray();
		$goodsNo = $postValue['goodsNo'];
		$goodsAdmin = \App::load('\\Component\\Goods\\GoodsAdmin');
		// 옵션명 로드
		$getGoodsOptionName = $goodsAdmin->getGoodsInfo($goodsNo)['optionName'];
		// 옵션 정보 로드
		$goodsOptionInfo = $goodsAdmin->getGoodsOption($goodsNo);
		foreach($goodsOptionInfo as $k => $v){
            if($goodsOptionInfo[$k]['optionSellFl'] == 't'){
                $goodsOptionInfo[$k]['optionSellFl'] = $goodsOptionInfo[$k]['optionSellCode'];
            }
        }
		unset($goodsOptionInfo['optVal']);

        $request = \App::getInstance('request');
        $mallSno = $request->get()->get('mallSno', 1);
        $code = \App::load('\\Component\\Code\\Code',$mallSno);
        $stockReason = $code->getGroupItems('05002');
        $stockReasonNew['y'] = $stockReason['05002001']; //정상은 코드 변경
        $stockReasonNew['n'] = $stockReason['05002002']; //품절은 코드 변경
        unset($stockReason['05002001']);
        unset($stockReason['05002002']);
        $stockReason = array_merge($stockReasonNew, $stockReason);

		$this->setData('getGoodsOptionName', explode(STR_DIVISION, $getGoodsOptionName));
		$this->setData('goodsOptionInfo', $goodsOptionInfo);
        $this->setData('stockReason', $stockReason);

		$this->getView()->setDefine('layout', 'layout_layer.php');

		// 공급사와 동일한 페이지 사용
		$this->getView()->setPageName('goods/layer_goods_list_option_simple.php');
	}
}
