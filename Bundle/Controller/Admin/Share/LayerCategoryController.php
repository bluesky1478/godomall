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

class LayerCategoryController extends \Controller\Admin\Controller
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


        //--- 카테고리 설정
        $cate = \App::load('\\Component\\Category\\CategoryAdmin');
        $getValue = Request::get()->toArray();

        //--- 상품 데이터
        try {

            $getData = $cate->getAdminSeachCategory('layer', 10);
            $page = \App::load('\\Component\\Page\\Page');    // 페이지 재설정

            //--- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('parentFormID', $getValue['parentFormID']);
            $this->setData('dataFormID', $getValue['dataFormID']);
            $this->setData('dataInputNm', $getValue['dataInputNm']);
            $this->setData('mode', gd_isset($getValue['mode'],'search'));
            $this->setData('callFunc', gd_isset($getValue['callFunc'],''));
            $this->setData('childRow',gd_isset($getValue['childRow']));

            $this->setData('cate', $cate);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('search', gd_isset($getData['search']));
            $this->setData('useMallList', gd_isset($getData['useMallList']));
            $this->setData('page', $page);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/layer_category.php');

        } catch (Exception $e) {
            throw $e;
        }

    }
}
