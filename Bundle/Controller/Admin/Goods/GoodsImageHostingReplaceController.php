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
use Component\Category\CategoryAdmin;
use Component\Category\BrandAdmin;
use Globals;
use Request;

/**
 * 상품 리스트 페이지
 */
class GoodsImageHostingReplaceController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws Except
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'batch', 'goodsImageHostingReplace');

        // 모듈호출
        $cate = \App::load('\\Component\\Category\\CategoryAdmin');
        $brand = \App::load('\\Component\\Category\\BrandAdmin');
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        // --- 상품 리스트 데이터
        try {

            /* 운영자별 검색 설정값 */
            $searchConf = \App::load('\\Component\\Member\\ManagerSearchConfig');
            $searchConf->setGetData();

            //검색 - 배송비관련
            $mode['fix'] = [
                'free' => __('배송비무료'),
                'price' => __('금액별배송'),
                'count' => __('수량별배송'),
                'weight' => __('무게별배송'),
                'fixed' => __('고정배송비'),
            ];

            //검색 - 아이콘 관련
            $getIcon = $goods->getManageGoodsIconInfo();

            // 이미지호스팅일괄전환용으로 검색전 검색일 기본값 전체 설정
            if (Request::get()->get('searchPeriod') == null) {
                Request::get()->set('searchPeriod', -1);
            }

            $getData = $goods->getAdminListGoods();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            // --- 관리자 디자인 템플릿

            $this->getView()->setDefine('goodsSearchFrm',  Request::getDirectoryUri() . '/goods_image_hosting_replace_search.php');

            $this->addScript([
                'jquery/jquery.multi_select_box.js',
            ]);

            // 이미지호스팅일괄전환 용으로 소트리스트 재설정
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
            $this->setData('sort', $getData['sort']);
            $this->setData('checked', $getData['checked']);
            $this->setData('selected', $getData['selected']);
            $this->setData('page', $page);
            $this->setData('getIcon', $getIcon);
            $this->setData('mode', $mode);
            $this->setData('_delivery', Globals::get('gDelivery'));

            $this->getView()->setPageName('goods/goods_image_hosting_replace.php');

        } catch (Exception $e) {
            throw $e;
        }

    }
}
