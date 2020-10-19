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

use Framework\Utility\ImageUtils;
use Globals;

/**
 * 추가상품 그룹 리스트
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class AddGoodsGroupListController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'addGoods', 'addGoodsGroupList');

        // --- 모듈 호출
        $addGoods = \App::load('\\Component\\Goods\\AddGoodsAdmin');

        // --- 상품 아이콘 데이터
        try {
            $getData = $addGoods->getAdminListAddGoodsGroup();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            $confImage = gd_policy('goods.image');
            ImageUtils::sortImageConf($confImage, ['detail', 'magnify']);
            foreach ($confImage as $key => $val) {
                $useImage[$key] = $val['text'];
            }

            $this->addScript(['jquery/jquery.multi_select_box.js']);
            $this->setData('data', $getData['data']);
            $this->setData('search', $getData['search']);
            $this->setData('sort', $getData['sort']);
            $this->setData('checked', $getData['checked']);
            $this->setData('page', $page);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('goods/add_goods_group_list.php');

        } catch (\Exception $e) {
            throw $e;
        }

    }
}
