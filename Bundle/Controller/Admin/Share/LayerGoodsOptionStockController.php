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

class LayerGoodsOptionStockController extends \Controller\Admin\Controller
{
    public function index()
    {

        /**
         * 레이어 상품 옵션 재고 확인 페이지
         *
         * [관리자 모드] 레이어 상품 옵션 재고 확인 페이지
         * 설명 : 상품 옵션 재고 정보가 필요한 페이지
         * @author seonghu
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */
        $getValue = Request::get()->toArray();

        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');


        $data = $goods->getDataGoods($getValue['goodsNo']);

        try {
            $this->getView()->setDefine('layout', 'layout_layer.php');

            unset($data['data']['option']['optVal']);

            // 품절 상태 코드 추가
            $request = \App::getInstance('request');
            $mallSno = $request->get()->get('mallSno', 1);
            $code = \App::load('\\Component\\Code\\Code',$mallSno);
            $stockReason = $code->getGroupItems('05002');
            $stockReasonNew['y'] = $stockReason['05002001']; //정상은 코드 변경
            $stockReasonNew['n'] = $stockReason['05002002']; //품절은 코드 변경
            unset($stockReason['05002001']);
            unset($stockReason['05002002']);
            $stockReason = array_merge($stockReasonNew, $stockReason);

            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('optionName', gd_isset($data['data']['optionName']));
            $this->setData('data', gd_isset($data['data']['option']));
            $this->setData('stockReason', gd_isset($stockReason));

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/layer_goods_option_stock.php');

        } catch (Exception $e) {
            throw $e;
        }

    }
}
