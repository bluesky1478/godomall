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
use Framework\Utility\ArrayUtils;
use Globals;
use Request;
class GoodsSortBrandController extends \Controller\Admin\Controller
{

    /**
     * 상품 순서 변경[카테고리] 페이지
     * [관리자 모드] 상품 순서 변경[카테고리] 페이지
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

        // --- 메뉴 설정
        $this->callMenu('goods', 'display', 'brand');

        // --- 모듈 호출
        $cate = \App::load('\\Component\\Category\\BrandAdmin');
        $display = \App::load('\\Component\\Display\\DisplayConfigAdmin');
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        // --- 상품 데이터
        try {

            $cateInfo = array();

            $getValue = Request::get()->toArray();
            $cateGoods = ArrayUtils::last(gd_isset($getValue['cateGoods']));

            if($cateGoods) {
                list($cateInfo)  = $cate->getCategoryData($cateGoods);
                $cateInfo['pcThemeInfo'] = $display->getInfoThemeConfig($cateInfo['pcThemeCd']);
                $cateInfo['mobileThemeInfo'] = $display->getInfoThemeConfig($cateInfo['mobileThemeCd']);
                $sortTypeNmArray = $display->goodsSortList;
                $sortTypeNmArray['g.regDt desc'] = '최근 등록 상품 위로';
                $cateInfo['sortTypeNm'] = $sortTypeNmArray[$cateInfo['sortType']];
                if($cateInfo['sortAutoFl'] =='y') Request::get()->set('sort',$cateInfo['sortType']);
            }
            $navi = $display->getDateNaviDisplay();
            $getData = $goods->getAdminListSort('brand', true, $navi['data']['brand']['linkUse']);

            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

        } catch (Exception $e) {
            throw $e;
        }


        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/goods_sort.php');

        $this->addCss([
            'goodsChoiceStyle.css?'.time(),
        ]);
        $this->addScript([
            'jquery/jquery.multi_select_box.js',
            'goodsChoice.js?'.time(),
        ]);

        $this->setData('cateMode', 'brand');
        $this->setData('goods', $goods);
        $this->setData('cate', $cate);
        $this->setData('cateInfo', $cateInfo);
        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('fixCount', $getData['fixCount']);
        $this->setData('page', $page);
    }
}
