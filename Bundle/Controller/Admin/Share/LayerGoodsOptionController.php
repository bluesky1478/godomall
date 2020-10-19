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

class LayerGoodsOptionController   extends \Controller\Admin\Controller
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

        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');


        try {
            //$goodsView = $goods->getGoodsView($postValue['goodsNo']);
            $goodsView = $goods->getGoodsView($getValue['goodsNo']);

            $scmAdmin = \App::load('\\Component\\Scm\\ScmAdmin');
            $tmpData = $scmAdmin->getScmInfo($goodsView['scmNo'], 'companyNm');
            $goodsView['scmNm'] = $tmpData['companyNm'];

            // default 구매 최소 수량
            $goodsView['defaultGoodsCnt'] = 1;
            if($goodsView['fixedOrderCnt'] == 'option') {
                $goodsView['defaultGoodsCnt'] = $goodsView['minOrderCnt'];
            }
            if($goodsView['fixedSales'] != 'goods' && ($goodsView['salesUnit'] > $goodsView['defaultGoodsCnt'])) {
                $goodsView['defaultGoodsCnt'] = $goodsView['salesUnit'];
            }

            $this->getView()->setDefine('layout', 'layout_layer.php');

            $this->setData('mode',gd_isset($getValue['mode']));
            $this->setData('goodsView', $goodsView);
            $this->setData('currency', Globals::get('gCurrency'));

            $mileage = $goodsView['mileageConf']['info'];
            $this->setData('mileageData', gd_isset($mileage));

            //상품 품절 설정 코드 불러오기
            $code = \App::load('\\Component\\Code\\Code',$mallSno);
            $optionSoldOutCode = $code->getGroupItems('05002');
            $optionSoldOutCode['n'] = $optionSoldOutCode['05002002'];
            $this->setData('optionSoldOutCode', $optionSoldOutCode);

            //상품 배송지연 설정 코드 불러오기
            $code = \App::load('\\Component\\Code\\Code',$mallSno);
            $optionDeliveryDelayCode = $code->getGroupItems('05003');
            $this->setData('optionDeliveryDelayCode', $optionDeliveryDelayCode);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/layer_goods_option.php');

        } catch (Exception $e) {
            throw $e;
        }

    }
}
