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

use Globals;
use Request;

/**
 * 메인 페이지 분류 등록
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class DisplayMainRegisterController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'display', 'mainRegister');

        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        // --- 메인 증정 데이터
        try {

            $data = $goods->getDataDisplayTheme(Request::get()->get('sno'));

            $displayConfig = \App::load('\\Component\\Display\\DisplayConfigAdmin');

            $data['data']['sortList'] = $displayConfig->goodsSortList;

            $this->addScript([
                'jquery/jquery.multi_select_box.js',
            ]);

        } catch (\Exception $e) {
            throw $e;
        }

        // --- 관리자 디자인 템플릿
        if (Request::get()->get('popupMode')) {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        }

        $this->setData('data',$data['data']);
        $this->setData('checked', $data['checked']);
        $this->setData('selected', $data['selected']);

    }
}
