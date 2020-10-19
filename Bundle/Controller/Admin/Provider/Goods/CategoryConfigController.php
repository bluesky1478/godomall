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
use Globals;
use Request;

class CategoryConfigController extends \Controller\Admin\Controller
{
    /**
     * 상품 카테고리 상세 수정 페이지
     * [관리자 모드] 상품 카테고리 상세 수정 페이지
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
        $postValue = Request::post()->toArray();

        // --- 카테고리 타입에 따른 설정 (상품,브랜드)
        gd_isset($getValue['cateType'], 'goods');
        if ($getValue['cateType'] == 'goods') {
            $objName = 'CategoryAdmin';
            $themeCate = 'E';
        } elseif ($getValue['cateType'] == 'brand') {
            $objName = 'BrandCategoryAdmin';
            $themeCate = 'C';
        }

        // --- 모듈 호출
        // @todo 브랜드 카테고리 클래스 분리 필요
        $cate = \App::load('\\Component\\Category\\CategoryAdmin', $getValue['cateType']);
        $goods = \App::load('\\Component\\Goods\\Goods');

        // --- 상품 카테고리 데이터
        try {
            $data = $cate->getDataCategory(gd_isset($postValue['cateCd']), $postValue['mode']);

            // --- 카테고리 타입에 따른 설정 (상품,브랜드)
            $arrCate = array('goods' => array('cateTitle' => __('카테고리'), 'cateDepth' => DEFAULT_DEPTH_CATE, 'nameLength' => DEFAULT_LENGTH_CATE_NAME), 'brand' => array('cateTitle' => '브랜드', 'cateDepth' => DEFAULT_DEPTH_BRAND, 'nameLength' => DEFAULT_LENGTH_BRAND_NAME));

            $data['info'] = $arrCate[$getValue['cateType']];
            $data['info']['cateType'] = $getValue['cateType'];

            $displayConfig = \App::load('\\Component\\Display\\DisplayConfigAdmin');
            $data['pcThemeList'] = $displayConfig->getInfoThemeConfigCate($themeCate,'n');
            $data['mobileThemeList'] = $displayConfig->getInfoThemeConfigCate($themeCate,'y');
            $data['recomPcThemeList'] = $displayConfig->getInfoThemeConfigCate('D','n');

            $data['data']['sortList'] = $displayConfig->goodsSortList;

        } catch (Exception $e) {
            throw $e;
        }




        // --- 관리자 디자인 템플릿

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->setData('cate', $cate);
        $this->setData('goods', $goods);
        $this->setData('data', gd_htmlspecialchars(gd_isset($data['data'])));
        $this->setData('checked', gd_isset($data['checked']));
        $this->setData('selected', gd_isset($data['selected']));
        $this->setData('info', $data['info']);
        $this->setData('themes', gd_isset($data['themes']));
        $this->setData('group', gd_isset($data['group']));
        $this->setData('pcThemeList', gd_isset($data['pcThemeList']));
        $this->setData('mobileThemeList', gd_isset($data['mobileThemeList']));
        $this->setData('themeCate', gd_isset($themeCate));
        $this->setData('recomPcThemeList', gd_isset($data['recomPcThemeList']));
    }
}
