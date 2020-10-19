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
 * 상품 재입고 알림 신청 리스트 페이지
 */
class GoodsRestockController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws Except
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'goods', 'reStock');

        // 모듈호출
        $goods = App::load('\\Component\\Goods\\GoodsAdmin');

        // --- 상품 리스트 데이터
        try {
            $getData = $goods->getGoodsRestockList();

            $page = App::load('\\Component\\Page\\Page'); // 페이지 재설정

            $this->setData('goods', $goods);
            $this->setData('data', $getData['data']);
            $this->setData('page', $page);
            $this->setData('sort', $getData['sort']);
            $this->setData('search', $getData['search']);
            $this->setData('selected', $getData['selected']);
            $this->setData('checked', $getData['checked']);
            $this->addScript([
                'jquery/jquery.multi_select_box.js',
            ]);
            $this->getView()->setPageName('goods/goods_restock.php');
        } catch (Exception $e) {
            throw $e;
        }

    }
}
