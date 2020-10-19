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

use Request;
use Exception;

/**
 *
 * 카테고리 일괄선택
 *
 * @package Bundle\Controller\Admin\Share
 * @author  Hakyoung Lee <haky2@godo.co.kr>
 */
class LayerCategoryBatchController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        //--- 카테고리 설정
        $cate = \App::load('\\Component\\Category\\CategoryAdmin');
        $getValue = Request::get()->toArray();

        //--- 상품 데이터
        try {

            $getData = $cate->getAdminSeachCategory('layer');
            $page = \App::load('\\Component\\Page\\Page');    // 페이지 재설정

            //--- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('cate', $cate);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('search', gd_isset($getData['search']));
            $this->setData('useMallList', gd_isset($getData['useMallList']));
            $this->setData('page', $page);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/layer_category_batch.php');

        } catch (Exception $e) {
            throw $e;
        }

    }
}
