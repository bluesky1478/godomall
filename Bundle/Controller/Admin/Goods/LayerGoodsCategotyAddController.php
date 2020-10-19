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

use Request;

class LayerGoodsCategotyAddController extends \Controller\Admin\Controller
{

    /**
     * 레이어 상품 카테고리 추가
     * [관리자 모드] 레이어 상품 카테고리 추가 페이지
     * 설명 : 상품 등록/수정시 카테고리 추가하는 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @param array $files
     */
    public function index()
    {

        // --- 카테고리 설정
        $cate = \App::load('\\Component\\Category\\CategoryAdmin');

        // --- 관리자 디자인 템플릿

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $this->setData('cate', $cate);
    }
}
