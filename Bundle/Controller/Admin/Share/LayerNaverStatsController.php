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

namespace Bundle\Controller\Admin\Share;

use Globals;
use Request;
use Exception;

class LayerNaverStatsController extends \Controller\Admin\Controller
{
    public function index()
    {

        /**
         * 레이어 브랜드 등록 페이지
         *
         * [관리자 모드]  레이어 브랜드 등록 페이지
         * @author artherot
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */


        //--- 모듈 호출
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
        $cate = \App::load('\\Component\\Category\\CategoryAdmin');
        $_defineMarketing = \App::load('Component\\Marketing\\DefineMarketing');
        $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');

        $getValue = Request::get()->toArray();

        //--- 상품 데이터
        try {
            //네어버 설정 정보
            $navsrConfigData = $dbUrl->getConfig('naver', 'config');

            $statsData = $goods->getNaverStats();

            if($getValue['categoryNoneFl'] !='y' && (empty($getValue['cateNm']) === false || empty($getValue['cateGoods'][0]) === false)) {
                $getData = $cate->getAdminSeachCategory('layer', 10);
                $page = \App::load('\\Component\\Page\\Page');    // 페이지 재설정

                $this->setData('data', gd_isset($getData['data']));
                $this->setData('useMallList', gd_isset($getData['useMallList']));
                $this->setData('page', $page);

                $goodsNaverStats = $goods->getGoodsNaverStats(array_column($getData['data'], 'cateCd'));
                $this->setData('goodsNaverStats', $goodsNaverStats);
            }

            //카테고리 미지정인 경우 상품 검색
            if(empty($getValue['cateNm']) === true && empty($getValue['cateGoods'][0]) === true && $getValue['categoryNoneFl'] =='y') {
                $goodsNaverStats = $goods->getGoodsNaverStats();
                $this->setData('goodsNaverStats', $goodsNaverStats);
            }

            //네이버 등급 정보
            $naverGradeName = $_defineMarketing->getNaverGrade();
            $naverMaxCount = $_defineMarketing->getNaverGradeMaxCount();
            $naverSafetyCount = $_defineMarketing->getNaverGradeSafetyCount();

            //설정이 없는 경우 초기값 부여
            if (empty($navsrConfigData[naverGrade]) === true) {
                $navsrConfigData[naverGrade] = '1';
            }

            $this->setData('search', gd_isset($getValue));

            //--- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('parentFormID', $getValue['parentFormID']);
            $this->setData('dataFormID', $getValue['dataFormID']);
            $this->setData('dataInputNm', $getValue['dataInputNm']);
            $this->setData('mode', gd_isset($getValue['mode'],'search'));
            $this->setData('callFunc', gd_isset($getValue['callFunc'],''));
            $this->setData('childRow',gd_isset($getValue['childRow']));
            $this->setData('statsData',gd_isset($statsData));
            $this->setData('naverGradeName',$naverGradeName[$navsrConfigData[naverGrade]]);
            $this->setData('naverGradeMaxCount',$naverMaxCount[$navsrConfigData[naverGrade]]-$naverSafetyCount[$navsrConfigData[naverGrade]]);

            $this->setData('cate', $cate);


        } catch (Exception $e) {
            throw $e;
        }

    }
}
