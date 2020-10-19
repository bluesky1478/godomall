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
namespace Bundle\Controller\Admin\Scm;

use Exception;
use Globals;
use Request;

class ScmApplyProductListController extends \Controller\Admin\Controller
{
    /**
     * 공급사 상품 승인 관리
     * [관리자 모드] 공급사 상품 승인 관리리스트
     *
     * @author su
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

        $this->callMenu('scm', 'product', 'scmApplyProductList');


        // 모듈호출
        $cate = \App::load('\\Component\\Category\\CategoryAdmin');
        $brand = \App::load('\\Component\\Category\\BrandAdmin');
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        // --- 상품 리스트 데이터
        try {

            //검색 - 배송비관련

            Request::get()->set("searchDateFl","applyDt");
            $getData = $goods->getAdminListGoods();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정


            $getData['search']['applyTypeList'] = [
                'all'    => __('전체'),
                'r'   => __('상품등록'),
                'm'  => __('상품수정'),
                'd'  => __('상품삭제'),
            ];


            $getData['search']['applyFlList'] = [
                'all'    => __('전체'),
                'a'    => __('승인요청'),
                'y'   => __('승인완료'),
                'r'  => __('반려'),
                'n'  => __('철회'),
            ];


        } catch (Exception $e) {
            throw $e;
        }
        // --- 관리자 디자인 템플릿
        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);


        $this->setData('goods', $goods);
        $this->setData('cate', $cate);
        $this->setData('brand', $brand);
        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('page', $page);
        $this->setData('getIcon', $getIcon);
        $this->setData('mode', $mode);
        $this->setData('_delivery', Globals::get('gDelivery'));
    }
}
