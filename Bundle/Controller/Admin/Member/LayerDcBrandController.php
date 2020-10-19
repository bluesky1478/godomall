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
namespace Bundle\Controller\Admin\Member;

use Component\Category\CategoryAdmin;
use Request;

/**
 * 회원등급 브랜드별 추가할인
 *
 * @author agni <agni@godo.co.kr>
 */
class LayerDcBrandController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        $memberGroup = \App::load('\\Component\\Member\\MemberGroup');
        $groupData = $memberGroup->getGroupViewToArray(Request::get()->get('sno'));

        //--- 카테고리 설정
        $brand = new CategoryAdmin('brand');
        $getBrandData = $brand->getCategoryListSelectBox('y');

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setDefine('layoutContent', \Request::getDirectoryUri() . '/' . \Request::getFileUri());

        $this->setData('groupData', $groupData);
        $this->setData('getBrandData', $getBrandData);
    }
}
