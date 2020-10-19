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

class GoodsIconListController extends \Controller\Admin\Controller
{

    /**
     * 상품 아이콘 리스트 페이지
     * [관리자 모드] 상품 아이콘 리스트 페이지
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
        // --- 메뉴 설정
        $this->callMenu('goods', 'goods', 'icon_list');

        // --- 모듈 호출
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        // --- 상품 아이콘 데이터
        try {

            $getData = $goods->getAdminListGoodsIcon();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

        } catch (Exception $e) {
            throw $e;
        }


        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());


        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('page', $page);
        $this->setData('etcIcon', $goods->etcIcon);
    }
}
