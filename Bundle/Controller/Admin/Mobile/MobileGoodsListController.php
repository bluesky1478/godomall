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

namespace Bundle\Controller\Admin\Mobile;

use Exception;
use Globals;

class MobileGoodsListController extends \Controller\Admin\Controller
{
    public function index()
    {

        /**
         * 상품 리스트 페이지
         *
         * [관리자 모드] 상품 리스트 페이지
         * @author artherot
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */


        //--- 모듈 호출


        //--- 메뉴 설정
        $this->callMenu('mobile', 'goods', 'list');

        //--- 모듈 호출
        $cate = \App::load('\\Component\\Category\\CategoryAdmin');
        $brand = \App::load('\\Component\\Category\\CategoryAdmin', 'brand');
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        //--- 상품 리스트 데이터
        try {
            ob_start();

            $getData = $goods->getAdminListGoods();
            $page = \App::load('\\Component\\Page\\Page');    // 페이지 재설정

            $getIcon = $goods->getManageGoodsIconInfo();

            $mobile = gd_policy('mobile.config');    // 모바일샵 설정

            if ($out = ob_get_clean()) {
                throw new Except('ECT_LOAD_FAIL', $out);
            }
        } catch (Exception $e) {
            $e->actLog();
            //echo ($e->ectMessage);
        }

        //--- 관리자 디자인 템플릿
        $this->addScript(
            [
                'jquery/jquery.multi_select_box.js',
            ]
        );
        $this->setData('goods', $goods);
        $this->setData('cate', $cate);
        $this->setData('brand', $brand);
        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $checked = $getData['checked']);
        $this->setData('page', $page);
        $this->setData('getIcon', $getIcon);
        $this->setData('_delivery', Globals::get('gDelivery'));
        $this->setData('mobile', $mobile);


    }
}
