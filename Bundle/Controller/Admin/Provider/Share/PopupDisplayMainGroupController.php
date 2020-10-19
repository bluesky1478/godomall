<?php

namespace Bundle\Controller\Admin\Provider\Share;

use Exception;
use Globals;
use Request;
use Session;

/**
 * 분류 관리(팝업)
 * @author sueun <cseun555g@godo.co.kr>
 */
class PopupDisplayMainGroupController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'goods', 'list');

        $getValue = Request::get()->toArray();

        // --- 모듈 호출
        $cate = \App::load('\\Component\\Category\\CategoryAdmin');

        $this->addScript(
            [
                'jquery/jquery.multi_select_box.js',
            ]
        );

        $this->setData('cate', $cate);
        $this->setData('data', $getData['data']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('page', $page);

        // --- 관리자 디자인 템플릿
        if (isset($getValue['popupMode']) === true) {
            $this->getView()->setDefine('layout', 'layout_blank_noiframe.php');
            $this->setData('popupMode', isset($getValue['popupMode']));
        }
    }
}
