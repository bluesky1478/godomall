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
use Session;

class LayerGoodsBenefitController extends \Controller\Admin\Controller
{

    /**
     * 상품 혜택 선택 레이어
     *
     * @author cjb3333

     */
    public function index()
    {
        // --- 모듈 호출
        $getValue = Request::get()->toArray();
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');

        $getData = $goodsBenefit->getAdminListGoodsBenefit(gd_isset($getValue['mode']));
        $page = \App::load('\\Component\\Page\\Page');    // 페이지 재설정

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');

        $this->setData('layerFormID', $getValue['layerFormID']);
        $this->setData('parentFormID', $getValue['parentFormID']);
        $this->setData('dataFormID', $getValue['dataFormID']);
        $this->setData('dataInputNm', $getValue['dataInputNm']);
        $this->setData('mode',gd_isset($getValue['mode']));
        $this->setData('data', gd_isset($getData['data']));
        $this->setData('search', gd_isset($getData['search']));
        $this->setData('checked', gd_isset($getData['checked']));
        $this->setData('page', gd_isset($page));

    }
}
