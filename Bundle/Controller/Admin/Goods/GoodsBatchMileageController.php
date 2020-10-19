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

use Component\Member\Group\Util;
use Exception;
use Globals;
use Request;

class GoodsBatchMileageController extends \Controller\Admin\Controller
{

    /**
     * 빠른 마일리지 수정 페이지
     * [관리자 모드] 빠른 마일리지 수정 페이지
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
        // --- 상품 데이터
        try {

            // --- 메뉴 설정
            $this->callMenu('goods', 'batch', 'mileage');

            // --- 모듈 호출
            $cate = \App::load('\\Component\\Category\\CategoryAdmin');
            $brand = \App::load('\\Component\\Category\\CategoryAdmin', 'brand');
            $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
            $memberGroup = \App::load('\\Component\\Member\\MemberGroup');

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

            $conf['mileage'] = Globals::get('gSite.member.mileageGive'); // 마일리지 지급 여부
            $conf['mileageBasic'] = Globals::get('gSite.member.mileageBasic'); // 마일리지 기본설정

            $conf['mileageBasic']['mileageText'] = '판매가';
            if ($conf['mileageBasic']['optionPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+옵션가';
            if ($conf['mileageBasic']['addGoodsPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+추가상품가';
            if ($conf['mileageBasic']['textOptionPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+텍스트옵션가';
            if ($conf['mileageBasic']['goodsDcPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+상품할인가';
            if ($conf['mileageBasic']['addGoodsPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+회원할인가';
            if ($conf['mileageBasic']['couponDcPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+쿠폰할인가';

            // 지급마일리지 설정
            gd_isset($conf['mileage']['giveType'], 'price'); // 지급 기준
            if ($conf['mileage']['giveType'] == 'price') { // 구매금액의 %
                gd_isset($conf['mileage']['goods'], 0); // %
            } else if ($conf['mileage']['giveType'] == 'priceUnit') { // 구매금액당 지급
                gd_isset($conf['mileage']['goodsPriceUnit'], 0); // 구매금액당
                gd_isset($conf['mileage']['goodsMileage'], 0); // 원 지급
            } else if ($conf['mileage']['giveType'] == 'cntUnit') { // 수량(개)당 지급
                gd_isset($conf['mileage']['cntMileage'], 0); // 원 지급
            }

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

            // 회원그룹리스트
            $groupList = $memberGroup->getGroupListSelectBox(['key'=>'sno', 'value'=>'groupNm']);

            // 할인금액 기준
            $fixedGoodsDiscount = Util::getFixedRateOptionData();
            unset($fixedGoodsDiscount['goods']);

            if(!gd_is_provider()) {
                $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
                $goodsBenefitSelect = $goodsBenefit->goodsBenefitSelect($getData['search']);
            }

            $this->setData('conf', $conf);
            $this->setData('goods', $goods);
            $this->setData('cate', $cate);
            $this->setData('brand', $brand);
            $this->setData('data', $getData['data']);
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);
            $this->setData('selected', $getData['selected']);
            $this->setData('batchAll', gd_isset($getData['batchAll']));
            $this->setData('page', $page);
            $this->setData('getIcon', $getIcon);
            $this->setData('mode', $mode);
            $this->setData('groupCnt', $groupList['cnt']);
            $this->setData('groupList', $groupList['data']);
            $this->setData('fixedGoodsDiscount', $fixedGoodsDiscount);
            $this->setData('exceptBenefit', Util::getExceptBenefitData());
            $this->setData('mileageFl', ['c'=>'통합설정', 'g'=>'개별설정']);
            $this->setData('mileageGroup', ['all'=>'전체회원', 'group'=>'특정회원등급']);
            $this->setData('goodsBenefitSelect', $goodsBenefitSelect);
            $this->setData('goodsBenefitUse', $goodsBenefit->getConfig());


            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('goods/goods_batch_mileage.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
