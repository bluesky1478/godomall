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

use Component\Faq\FaqAdmin;
use Request;

class FaqRegisterController extends \Controller\Admin\Controller
{

    /**
     * Description
     */
    public function index()
    {

        /**
         * FAQ등록/수정 폼
         *
         * @author sj
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        // --- 모듈 호출


        // --- 페이지 데이터
        $faqAdmin = new FaqAdmin();
        // 데이터
        $getData = $faqAdmin->getFaqView(Request::get()->get('sno'));

        $mallSno = \Request::get()->get('mallSno',1);
        // 모드정의
        if (empty(Request::get()->get('sno') == false)) {
            $mode = 'modify';
            $this->setData('mallSno', $getData['data']['mallSno']);
            // --- 메뉴 설정
            $this->callMenu('board', 'board', 'faqModify');
        } else {
            $mode = 'register';
            $this->setData('mallSno', $mallSno);
            // --- 메뉴 설정
            $this->callMenu('board', 'board', 'faqWrite');
        }

        $modeTxt = ($mode != 'modify' ? __('등록') : __('수정'));



        // --- 관리자 디자인 템플릿
        $this->addScript(['jquery/validation/jquery.validate.js']);



        $this->setData('mode', $mode);
        $this->setData('modeTxt', $modeTxt);
        if (empty($getData['data']) === false) {
            $this->setData('data', gd_htmlspecialchars($getData['data']));
        }
        $this->setData('checked', gd_isset($getData['checked']));
    }
}
