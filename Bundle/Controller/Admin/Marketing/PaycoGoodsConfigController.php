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
namespace Bundle\Controller\Admin\Marketing;

use Exception;
use Request;

class PaycoGoodsConfigController  extends \Controller\Admin\Controller
{

    /**
     * 페이코쇼핑 상품 설정
     * [관리자 모드] 페이코쇼핑 상품 설정
     *
     * @author atomyang
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
            $this->callMenu('marketing','paycoShopping','goodsConfig');

            // --- 모듈 호출
            $cate = \App::load('\\Component\\Category\\CategoryAdmin');
            $brand = \App::load('\\Component\\Category\\CategoryAdmin', 'brand');
            $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

            /* 운영자별 검색 설정값 */
            $searchConf = \App::load('\\Component\\Member\\ManagerSearchConfig');
            $searchConf->setGetData();

            $getData = $goods->getAdminListBatch('image');
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            $this->getView()->setDefine('goodsSearchFrm',  Request::getDirectoryUri() . '/goods_list_search.php');

            $this->addScript([
                'jquery/jquery.multi_select_box.js',
            ]);

            //정렬 재정의
            $getData['search']['sortList'] = array(
                'g.goodsNo desc' => sprintf(__('등록일 %1$s'), '↓'),
                'g.goodsNo asc' => sprintf(__('등록일 %1$s'), '↑'),
                'goodsNm asc' => sprintf(__('상품명 %1$s'), '↓'),
                'goodsNm desc' => sprintf(__('상품명 %1$s'), '↑'),
                'companyNm asc' => sprintf(__('공급사 %1$s'), '↓'),
                'companyNm desc' => sprintf(__('공급사 %1$s'), '↑'),
                'goodsPrice asc' => sprintf(__('판매가 %1$s'), '↓'),
                'goodsPrice desc' => sprintf(__('판매가 %1$s'), '↑'),
            );


            $this->setData('goods', $goods);
            $this->setData('cate', $cate);
            $this->setData('brand', $brand);
            $this->setData('data', $getData['data']);
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);
            $this->setData('batchAll', gd_isset($getData['batchAll']));
            $this->setData('page', $page);
            $this->setData('getIcon', $getIcon);


        } catch (Exception $e) {
            throw $e;
        }
    }
}
;
