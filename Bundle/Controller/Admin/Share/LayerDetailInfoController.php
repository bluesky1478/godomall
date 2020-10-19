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

class LayerDetailInfoController extends \Controller\Admin\Controller
{

    /**
     * 상품 이용안내 선택입력 레이어
     *
     * @author cjb3333
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     */
    public function index()
    {
        // --- 모듈 호출
        $getValue = Request::get()->toArray();

        // --- 모듈 설정
        $inform = \App::load('\\Component\\Agreement\\BuyerInform');

        $getData = $inform->getGoodsInfoListLayer($getValue);
        $page = \App::load('Component\\Page\\Page');

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');


        $this->setData('layerFormID', $getValue['layerFormID']);
        $this->setData('parentFormID', $getValue['parentFormID']);
        $this->setData('dataFormID', $getValue['dataFormID']);
        $this->setData('dataInputNm', $getValue['dataInputNm']);
        $this->setData('mode',gd_isset($getValue['mode']));
        $this->setData('callFunc', gd_isset($getValue['callFunc'],''));
        $this->setData('scmFl',gd_isset($getValue['scmFl']));
        $this->setData('scmNo',gd_isset($getValue['scmNo']));
        $this->setData('groupCd',gd_isset($getValue['groupCd']));


        $this->setData('data', gd_isset($getData['data']));
        $this->setData('search', gd_isset($getData['search']));
        $this->setData('page', $page);

        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('share/layer_detail_info.php');

    }
}
