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

use Request;

/**
 * 카테고리 트리용 json 데이터
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class CategoryTreeJsonController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        // get, post 정보
        $getValue = Request::get()->toArray();
        $postValue = Request::post()->toArray();

        // --- 카테고리 class
        $cate = \App::load('\\Component\\Category\\CategoryAdmin', $getValue['cateType']);

        // --- 카테고리 JSON 데이타
        echo $cate->getTreeJson($cate->getCategoryTreeData(gd_isset($postValue['cateCd'])));

        exit();
    }
}
