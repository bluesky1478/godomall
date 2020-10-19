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
use Framework\Debug\Exception\LayerNotReloadException;
use Globals;
use Request;

class LayerGoodsListController extends \Controller\Admin\Controller
{

    /**
     * 레이어 상품 리스트 페이지
     * [관리자 모드] 레이어 상품 리스트 페이지
     * 설명 : 상품 정보 수정 페이지에서 상단에 상품 리스트로 나오는 부분
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
        $getValue = Request::get()->toArray();

        // --- 모듈 호출
        $cate = \App::load('\\Component\\Category\\CategoryAdmin');
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        // --- 상품 데이터
        try {

            $getData = $goods->getAdminListGoods('layer');
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $this->setData('goodsNo', gd_isset($getValue['goodsNo']));
        $this->setData('popupMode', gd_isset($getValue['popupMode']));
        $this->setData('goods', $goods);
        $this->setData('cate', $cate);
        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('page', $page);
    }
}
