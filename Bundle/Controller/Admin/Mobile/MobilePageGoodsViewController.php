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

class MobilePageGoodsViewController extends \Controller\Admin\Controller
{
    public function index()
    {

        /**
         * 모바일샵 상품 상세 페이지 설정 페이지
         *
         * [관리자 모드] 모바일샵 상품 상세 페이지 설정 페이지
         * @author    artherot
         * @version   1.0
         * @since     1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */


        //--- 모듈 호출


        //--- 메뉴 설정
        $this->callMenu('mobile', 'page', 'goodsView');

        //--- 상품 상세 페이지 설정 정보
        try {
            ob_start();

            // 페이지 코드
            $pageCode = 'goods_view';

            //--- 모바일샵 상품상세 페이지 설정 config 불러오기
            $configKey = sprintf('mobile.page_%s', $pageCode);
            $data = gd_policy($configKey);

            //--- MobilePageConfig 정의
            $config = \App::load('\\Component\\Mobile\\MobilePageConfig');
            $config->setConfig($pageCode, $data);

            if ($out = ob_get_clean()) {
                throw new Except('ECT_LOAD_FAIL', $out);
            }
        } catch (Exception $e) {
            $e->actLog();
            //echo ($e->ectMessage);
        }

        //--- 관리자 디자인 템플릿
        $this->setData('pageCode', $pageCode);
        $this->setData('data', $config->arrData);
        $this->setData('checked', $checked = $config->arrChecked);
        $this->setData('arrFields', $config->arrFields);


    }
}
