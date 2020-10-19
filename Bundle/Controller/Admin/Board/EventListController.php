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
use Request;

class EventListController extends \Controller\Admin\Controller
{

    /**
     * Description
     */
    public function index()
    {

        /**
         * 게시판관리
         *
         * @author sunny
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        // --- 모듈 호출

        // --- 메뉴 설정
        $this->callMenu('board', 'event', 'list');

        // --- 페이지 데이터
        try {
            $eventAdmin = \App::load('\\Component\\Event\\EventAdmin');
            $data = Request::get()->toArray();
            $getData = $eventAdmin->getList($data);
            $selected = $getData['selected'];
            $search = $getData['search'];
            $pager = \App::load('\\Component\\Page\\Page', Request::get()->get('page'), $getData['srchCnt'], $getData['totalCnt'], Request::get()->get('perPage'));
        } catch (Exception $e) {
            echo ($e->ectMessage);
        }

        // --- 관리자 디자인 템플릿
        $this->setData('data', $getData['data']);
        $this->setData('search', gd_htmlspecialchars(gd_isset($getData['search'])));
        $this->setData('pager', $pager);
        $this->setData('selected', $selected);
    }
}
