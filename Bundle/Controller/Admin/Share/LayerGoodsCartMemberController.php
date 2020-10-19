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

use Component\GoodsStatistics\GoodsStatistics;
use Exception;
use Request;

/**
 * [관리자 모드] 통계 > 상품분석 > 장바구니 분석
 * 설명 : 장바구니 분석의 담은 회원 리스트
 *
 * @package Bundle\Controller\Admin\Share
 * @author  su
 */
class LayerGoodsCartMemberController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     */
    public function index()
    {
        try {
            $searchData['searchDate'] = $cartData['cartYMD'] = Request::post()->get('searchDate');
            $searchData['mallFl'] = $cartData['mallSno'] = Request::post()->get('mallFl');
            $searchData['goodsNo'] = $cartData['goodsNo'] = Request::post()->get('goodsNo');
            $cartData['page'] = Request::post()->get('page');
//            $cartData['pageNum'] = Request::post()->get('pageNum');

            $goodsStatistics = new GoodsStatistics();

            $getData = $goodsStatistics->getGoodsCartMemberStatistics($cartData);
            $page = \App::load('\\Component\\Page\\Page');    // 페이지 재설정

            $this->getView()->setDefine('layout', 'layout_layer.php');

            $this->setData('searchData', http_build_query(gd_isset($searchData)));
            $this->setData('data', gd_isset($getData));
            $this->setData('page', $page);

        } catch (Exception $e) {
            throw $e;
        }
    }
}
