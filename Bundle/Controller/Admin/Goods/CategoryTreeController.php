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

use Framework\Debug\Exception\AlertBackException;
use Globals;
use Request;

class CategoryTreeController extends \Controller\Admin\Controller
{

    /**
     * 상품 카테고리 관리 페이지
     * [관리자 모드] 상품 카테고리 관리 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        $getValue = Request::get()->toArray();

        try {
            // --- 카테고리 타입에 따른 설정 (상품,브랜드)
            gd_isset($getValue['cateType'], 'goods');
            if ($getValue['cateType'] == 'goods') {
                $objName = 'CategoryAdmin';
            } elseif ($getValue['cateType'] == 'brand') {
                $objName = 'BrandCategoryAdmin';
            } else {
                throw new \Exception(__('잘못된 카테고리 타입입니다.'));
            }
        } catch (\Exception $e) {
            if (!Request::isAjax()) {
                throw new AlertBackException($e->getMessage());
            }
        }

        // --- 메뉴 설정
        $this->callMenu('goods', 'category', $getValue['cateType']);

        // --- 모듈 호출
        $cate = \App::load('\\Component\\Category\\CategoryAdmin', $getValue['cateType']);

        // --- 카테고리 타입에 따른 설정 (상품,브랜드)
        // @formatter:off
        $arrCate = array('goods' => array('cateTitle' => __('카테고리'), 'cateDepth' => DEFAULT_DEPTH_CATE, 'nameLength' => DEFAULT_LENGTH_CATE_NAME),
                                'brand' => array('cateTitle' => __('브랜드'), 'cateDepth' => DEFAULT_DEPTH_BRAND, 'nameLength' => DEFAULT_LENGTH_BRAND_NAME));
        // @formatter:on

        $data = $arrCate[$getValue['cateType']];
        $data['cateType'] = $getValue['cateType'];

        $mapping = $cate->getGoodsMappingResult();

        // @formatter:off
        $this->addScript([
            'jquery/jquery.hotkeys.js',
            'jquery/jstree/jquery.tree.js',
            'jquery/jstree/plugins/jquery.tree.contextmenu.js',
            'jquery/jquery.url.js',
            'category_tree.js?'.time(),
            'jquery/jquery.multi_select_box.js',
        ]);

        // @formatter:on
        $this->setData('data', $data);
        $this->setData('mapping', gd_isset($mapping));
    }
}
