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

class CooperationListController extends \Controller\Admin\Controller
{

    /**
     * Description
     */
    public function index()
    {

        /**
         * 광고제휴문의 리스트 페이지
         * [관리자 모드] 광고제휴문의 리스트 페이지
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
            $getData = $coop->getCooperationList();
            $checked = $getData['checked'];
            $selected = $getData['selected'];
            $page = \App::load(\Component\Page\Page::class);
        } catch (Exception $e) {
            echo ($e->ectMessage);
        }

        // --- 관리자 디자인 템플릿
        $this->setData('page', gd_isset($page));
        $this->setData('data', gd_isset($getData['data']));
        $this->setData('search', gd_isset($getData['search']));
        $this->setData('itemCds', gd_isset($getData['itemCds']));
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
    }
}
