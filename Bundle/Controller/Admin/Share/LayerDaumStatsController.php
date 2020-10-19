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

use Framework\Debug\Exception\LayerException;
use Globals;
use Request;
use Exception;

/**
 * 다음 쇼핑하우 노출상품 현황 레이어
 *
 * @author haky <haky2@godo.co.kr>
 */
class LayerDaumStatsController extends \Controller\Admin\Controller
{
    public function index()
    {
        //--- 모듈 호출
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
        $cate = \App::load('\\Component\\Category\\CategoryAdmin');
        $getValue = Request::get()->toArray();

        //--- 상품 데이터
        try {
            $statsData = $goods->getDaumStats();

            // 카테고리 지정인 경우 상품 검색
            if ($getValue['categoryNoneFl'] != 'y' && (empty($getValue['cateNm']) === false || empty($getValue['cateGoods'][0]) === false)) {
                $getData = $cate->getAdminSeachCategory('layer', 10);
                $page = \App::load('Component\\Page\\Page');    // 페이지 재설정
                $goodsDaumStats = $goods->getGoodsDaumStats(array_column($getData['data'], 'cateCd'));
            }

            // 카테고리 미지정인 경우 상품 검색
            if ($getValue['categoryNoneFl'] == 'y' && empty($getValue['cateNm']) === true && empty($getValue['cateGoods'][0]) === true) {
                $goodsDaumStats = $goods->getGoodsDaumStats();
            }

            //--- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

            $this->setData('cate', $cate);
            $this->setData('search', gd_isset($getValue));
            $this->setData('statsData',gd_isset($statsData));
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('useMallList', gd_isset($getData['useMallList']));
            $this->setData('page', gd_isset($page));
            $this->setData('goodsDaumStats', gd_isset($goodsDaumStats));
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }
    }
}
