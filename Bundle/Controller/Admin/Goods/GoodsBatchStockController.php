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
use Request;
use Component\Delivery\Delivery;

class GoodsBatchStockController extends \Controller\Admin\Controller
{
    /**
     * 가격/마일리지/재고 수정 페이지
     * [관리자 모드] 가격/마일리지/재고 수정 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     */
    public function index()
    {
        // --- 상품 데이터
        try {

            // --- 메뉴 설정
            $this->callMenu('goods', 'batch', 'stock');

            // --- 모듈 호출
            $cate = \App::load('\\Component\\Category\\CategoryAdmin');
            $brand = \App::load('\\Component\\Category\\CategoryAdmin', 'brand');
            $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

            /* 운영자별 검색 설정값 */
            $searchConf = \App::load('\\Component\\Member\\ManagerSearchConfig');
            $searchConf->setGetData();

            //배송비관련
            $mode['fix'] = [
                'free'   => __('배송비무료'),
                'price'  => __('금액별배송'),
                'count'  => __('수량별배송'),
                'weight' => __('무게별배송'),
                'fixed'  => __('고정배송비'),
            ];

            $getIcon = $goods->getManageGoodsIconInfo();

            $getData = $goods->getAdminListOptionBatch('image');
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            if(!gd_is_provider()) {
                $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
                $goodsBenefitSelect = $goodsBenefit->goodsBenefitSelect($getData['search']);
            }

            $this->getView()->setDefine('goodsSearchFrm',  Request::getDirectoryUri() . '/goods_list_search.php');

            $this->addScript([
                'jquery/jquery.multi_select_box.js',
            ]);

            //정렬 재정의
            $getData['search']['sortList'] = array(
                'g.goodsNo desc' => sprintf(__('등록일 %1$s'), '↓'),
                'g.goodsNo asc' => sprintf(__('등록일 %1$s'), '↑'),
                'g.goodsNm asc' => sprintf(__('상품명 %1$s'), '↓'),
                'g.goodsNm desc' => sprintf(__('상품명 %1$s'), '↑'),
                'companyNm asc' => sprintf(__('공급사 %1$s'), '↓'),
                'companyNm desc' => sprintf(__('공급사 %1$s'), '↑'),
                'g.totalStock asc' => sprintf(__('재고 %1$s'), '↓'),
                'g.totalStock desc' => sprintf(__('재고 %1$s'), '↑'),
            );

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

            // 배송 상태 코드 추가
            $deliveryReason = $code->getGroupItems('05003');
            $deliveryReasonNew['normal'] = $deliveryReason['05003001']; //정상은 코드 변경
            unset($deliveryReason['05003001']);
            $deliveryReason = array_merge($deliveryReasonNew, $deliveryReason);

            foreach($getData['data'] as $key => $value){
                if($value['optionSellFl'] == 't') $getData['data'][$key]['optionSellFl'] = $value['optionSellCode'];
                if($value['optionDeliveryFl'] == 't') $getData['data'][$key]['optionDeliveryFl'] = $value['optionDeliveryCode'];
            }

            //상품 그리드 설정
            $goodsAdminGrid = \App::load('\\Component\\Goods\\GoodsAdminGrid');
            $goodsAdminGridMode = $goodsAdminGrid->getGoodsAdminGridMode();
            //설정에 따른 항목 보임/숨김
            $optionType = gd_policy('goods.option_1903');
            $this->setData('optionType', $optionType['use']);
            $this->setData('goodsBatchStockAdminGridMode', $goodsAdminGridMode);
            unset($getData['goodsBatchStockGridConfigList']['display']);
            unset($getData['goodsBatchStockGridConfigList']['btn']);
            $this->setData('goodsBatchStockGridConfigList', $getData['goodsBatchStockGridConfigList']); // 상품 그리드 항목

            $this->setData('goods', $goods);
            $this->setData('cate', $cate);
            $this->setData('brand', $brand);
            $this->setData('data', $getData['data']);
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);
            $this->setData('batchAll', gd_isset($getData['batchAll']));
            $this->setData('page', $page);
            $this->setData('getIcon', $getIcon);
            $this->setData('mode', $mode);
            $this->setData('goodsBenefitSelect', $goodsBenefitSelect);
            $this->setData('selected', $getData['selected']);

            $this->setData('optionStockFlag', true);
            $this->setData('stockReason', $stockReason);
            $this->setData('deliveryReason', $deliveryReason);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('goods/goods_batch_stock.php');

        } catch (Exception $e) {
            throw $e;
        }


    }

    public function post()
    {
        $session = \App::getInstance('session');

        // 재고 수정 기능권한에 따른 상품 재고 비활성화
        if ($session->get('manager.functionAuth.goodsStockOverall') != 'y') {
            $returnFunctionAuth[] = '$(\'#setOptionStockFl\').prop("disabled", true);';
            $returnFunctionAuth[] = '$(\'#setOptionStockCnt\').prop("disabled", true);';
            $returnFunctionAuth[] = '$(\'select[name^="option[stockFl]"]\').prop("disabled", true);';
            $returnFunctionAuth[] = '$(\'input:text[name^="option[stockCnt]"]\').prop("disabled", true);';
            $returnFunctionAuth[] = '$(\'select[name="optionStockFl"]\').prop("disabled", true);';
            $returnFunctionAuth[] = '$(\'input[name="optionStockCnt"]\').prop("disabled", true);';
            $returnFunctionAuth[] = '$(\'input[name="stockLimit"]\').prop("disabled", true);';
            $this->setData('functionAuth', $this->getData('functionAuth') . implode("\n", $returnFunctionAuth));
            $this->getView()->setData('functionAuth', $this->getData('functionAuth'));
        }
    }
}
