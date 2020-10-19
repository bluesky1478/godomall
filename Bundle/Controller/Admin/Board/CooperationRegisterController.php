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

use Exception;
use Framework\Utility\Strings;
use Request;

class CooperationRegisterController extends \Controller\Admin\Controller
{

    /**
     * Description
     */
    public function index()
    {

        /**
         * 광고제휴문의등록/수정 폼
         *
         * @author sunny
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        // --- 모듈 호출

        // --- 메뉴 설정
        $this->callMenu('board', 'board', 'cooperation');

        // --- 페이지 데이터
        try {
            // --- 광고제휴문의 설정
            $coop = \App::load('\\Component\\Board\\Cooperation');

            // 데이터
            $getData = array();
            $getData = $coop->getCooperationView(Request::get()->get('sno'));
            $selected = $getData['selected'];

            // 광고제휴분야코드
  //          $field['itemCd'] = gd_code('002'); //@todo:말머리데체
            $field['itemCd'] = null;
            // 메일도메인
            $field['email'] = gd_array_change_key_value(gd_code('01004'));
            $field['email'] = array_merge(['self' => __('직접입력')], $field['email']);
        } catch (Exception $e) {
            echo ($e->ectMessage);
        }

        // --- 관리자 디자인 템플릿
        $this->setData('field', $field);
        $this->setData('data', gd_htmlspecialchars($getData['data']));
        $this->setData('selected', $selected);
    }
}
