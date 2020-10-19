<?php

namespace Bundle\Controller\Admin\Share;

use Globals;
use Request;

/**
 * 메인 페이지 상품 진열(팝업)
 * @author sueun <cseun555g@godo.co.kr>
 */
class PopupDisplayMainListController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        $getValue = Request::get()->toArray();

        // --- 메뉴 설정
        $this->callMenu('goods', 'display', 'mainList');

        // --- 모듈 호출
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        // --- 상품 아이콘 데이터
        try {
            $getData = $goods->getAdminListDisplayTheme();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
        } catch (\Exception $e) {
            throw $e;
        }

        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('orderBy', $getValue['orderBy']);
        $this->setData('page', $page);

        // --- 관리자 디자인 템플릿
        if (isset($getValue['popupMode']) === true) {
            $this->getView()->setDefine('layout', 'layout_blank_noiframe.php');
            $this->setData('popupMode', isset($getValue['popupMode']));
        }
    }
}
