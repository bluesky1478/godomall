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
use Globals;
class GoodsBatchLinkController extends \Controller\Admin\Controller
{

    /**
     * 가격/마일리지/재고 수정 페이지
     * [관리자 모드] 가격/마일리지/재고 수정 페이지
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
        // --- 상품 데이터
        try {
            // --- 메뉴 설정
            $this->callMenu('goods', 'batch', 'link');

            // --- 모듈 호출
            $cate = \App::load('\\Component\\Category\\CategoryAdmin');
            $brand = \App::load('\\Component\\Category\\BrandAdmin');
            $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

            /* 운영자별 검색 설정값 */
            $searchConf = \App::load('\\Component\\Member\\ManagerSearchConfig');
            $searchConf->setGetData();

            //배송비관련
            $mode['fix'] = [
                'free'   => __('배송비무료'),
                'price'  => __('금액별배송'),
                'count'  => __('수량별배송'),
                'weight' => __('무게별배송'),
                'fixed'  => __('고정배송비'),
            ];

            $getIcon = $goods->getManageGoodsIconInfo();

            $getData = $goods->getAdminListBatch('image');
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            // 상품 혜택관리 추가
            if(!gd_is_provider()) {
                $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
                $goodsBenefitSelect = $goodsBenefit->goodsBenefitSelect($getData['search']);
            }

            $this->getView()->setDefine('goodsSearchFrm',  Request::getDirectoryUri() . '/goods_list_search.php');

            $this->addScript([
                'jquery/jquery.multi_select_box.js',
            ]);

            $conf['mileage'] = Globals::get('gSite.member.mileageGive'); // 마일리지 지급 여부
            $conf['mileageBasic'] = Globals::get('gSite.member.mileageBasic'); // 마일리지 기본설정

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


            $this->setData('conf', $conf);
            $this->setData('goods', $goods);
            $this->setData('cate', $cate);
            $this->setData('brand', $brand);
            $this->setData('data', $getData['data']);
            $this->setData('search', $getData['search']);
            $this->setData('sort', $getData['sort']);
            $this->setData('checked', $getData['checked']);
            $this->setData('batchAll', gd_isset($getData['batchAll']));
            $this->setData('page', $page);
            $this->setData('getIcon', $getIcon);
            $this->setData('mode', $mode);
            $this->setData('goodsBenefitSelect', $goodsBenefitSelect);
            $this->setData('selected', $getData['selected']);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('goods/goods_batch_link.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
