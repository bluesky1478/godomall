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
namespace Bundle\Controller\Admin\Policy;

use Component\Mall\Mall;

class GoodsDetailInfoController extends \Controller\Admin\Controller
{

    /**
     *
     * [관리자 모드] 상품 이용안내 선택입력 레이어
     *
     * @author cjb3333
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @throws Except
     */
    public function index()
    {
        $mall = new Mall();

        // --- 메뉴 설정
        $this->callMenu('policy', 'goods', 'info');

        // --- 모듈 설정
        $inform = \App::load('\\Component\\Agreement\\BuyerInform');

        // --- 페이지 데이터
        try {

            $data = $inform->getGoodsInfoList();
            $page = \App::load('Component\\Page\\Page');

        } catch (\Exception $e) {
            $e->actLog();
        }
        // 배송안내, AS안내, 환불안내, 교환안내

        // --- 관리자 디자인 템플릿
        $this->setData('page', gd_isset($page));
        $this->setData('pageNum', gd_isset($pageNum));
        $this->setData('data', $data['data']);
        $this->setData('count', $data['count']);
        $this->setData('search', $data['search']);
        $this->setData('sort', $data['sort']);
        $this->setData('checked', $data['checked']);
        $this->setData('isUsableMall', $mall->isUsableMall());

        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('policy/goods_detail_info.php');
    }
}
