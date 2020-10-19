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

use App;
use Exception;

/**
 * 상품 재입고 알림 신청 내역
 */
class GoodsRestockViewController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws Except
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'goods', 'reStockView');

        // 모듈호출
        $goods = App::load('\\Component\\Goods\\GoodsAdmin');
        $memberService = App::getInstance('Member');

        // --- 상품 리스트 데이터
        try {
            $this->addScript(
                [
                    'sms.js?ts='.time(),
                ]
            );
            $smsTarget = [
                '' => '=발송범위 선택=',
                'all' => '전체 신청자',
                'search' => '검색된 신청자',
                'select' => '선택된 신청자',
            ];
            $getDataView = $goods->getGoodsRestockView();
            $getData = $goods->getGoodsRestockViewList();

            //상품 상태변화에 따른 색상
            list($getDataView['status'], $color) = $goods->getGoodsRestockStatus($getDataView);
            if(trim($color) !== ''){
                $getDataView['trBackground'] = "style='background-color:".$color.";'";
            }

            $page = App::load('\\Component\\Page\\Page'); // 페이지 재설정

            $this->setData('memberService', $memberService);
            $this->setData('getDataView', $getDataView);
            $this->setData('goods', $goods);
            $this->setData('data', $getData['data']);
            $this->setData('page', $page);
            $this->setData('sort', $getData['sort']);
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);
            $this->setData('smsTarget', $smsTarget);

            $this->getView()->setPageName('goods/goods_restock_view.php');
        } catch (Exception $e) {
            throw $e;
        }

    }
}
