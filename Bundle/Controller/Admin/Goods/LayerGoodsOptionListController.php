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

class LayerGoodsOptionListController extends \Controller\Admin\Controller
{

    /**
     * 자주쓰는 상품 옵션 레이어 리스트 페이지
     * [관리자 모드] 자주쓰는 상품 옵션 레이어 리스트 페이지
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
        // --- 모듈 호출
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        $scmNo = Request::get()->get('scmNo');
        gd_isset($scmNo, DEFAULT_CODE_SCMNO);

        // --- 자주쓰는 상품 옵션 데이터
        try {

            $getData = $goods->getAdminListOption('layer');
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            // --- 관리자 디자인 템플릿


            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

            $this->setData('data', $getData['data']);
            $this->setData('search', $getData['search']);
            $this->setData('sort', $getData['sort']);
            $this->setData('checked', $getData['checked']);
            $this->setData('page', $page);
            $this->setData('scmNo', $scmNo);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('goods/layer_goods_option_list.php');



        } catch (Exception $e) {
            throw $e;
        }

    }
}
