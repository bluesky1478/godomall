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
namespace Bundle\Controller\Admin\Board;

use Framework\Utility\ArrayUtils;
use Globals;
use Request;

class EventRegistController extends \Controller\Admin\Controller
{

    /**
     * Description
     */
    public function index()
    {
        /**
         * 게시판등록/수정 폼
         *
         * @author sunny
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        // --- 모듈 호출

        // --- 메뉴 설정
        $this->callMenu('board', 'event', 'view');

        // 모드정의
        if (!Request::get()->get('mode')) {
            Request::get()->set('mode','regist');
        }

        $eventAdmin = \App::load('\\Component\\Event\\EventAdmin');
        $getData = $eventAdmin->getView(Request::get()->get('sno'));

        // --- 관리자 디자인 템플릿
        $this->addScript([
            'jquery/jquery.dataOverlapChk.js',
            'jquery/jquery.multi_select_box.js',
        ]);
        $this->setData('mode', Request::get()->get('mode'));
        if (Request::get()->get('mode') == 'modify') {
            $this->setData('sno', gd_isset(Request::get()->get('sno')));
            $this->setData('data', gd_htmlspecialchars(gd_isset($getData['data'])));
        }
        $this->setData('checked', gd_isset($getData['checked']));
    }
}
