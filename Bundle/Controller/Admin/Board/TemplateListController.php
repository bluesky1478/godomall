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

use Component\Board\BoardTemplate;
use Exception;
use Framework\Application\Bootstrap\ErrorMessage;
use Framework\Debug\Exception\AlertOnlyException;
use Request;

class TemplateListController extends \Controller\Admin\Controller
{

    /**
     * Description
     * @throws Except
     */
    public function index()
    {

        /**
         * 게시물관리
         *
         * @author sj
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        // --- 모듈 호출

        // --- 메뉴 설정
        $this->callMenu('board', 'board', 'template');

        try {
            // --- 페이지 데이터
            $req = Request::get()->toArray();
            gd_isset($req['pageNum'],10);
            gd_isset($req['page'],1);
            $bdTemplate = new BoardTemplate();
            $getData = $bdTemplate->getList($req);
            $pager = \App::load('\\Component\\Page\\Page', $req['page'], $getData['totalCnt'], $getData['amountCnt'], $req['pageNum']);
            $pager->setUrl(Request::server()->get('QUERY_STRING'));
        } catch (Exception $e) {
            throw new AlertOnlyException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->setData('data', $getData);
        $this->setData('req', $req);
//        $this->setData('search', $getData['search']);
//        $this->setData('selected', $getData['selected']);
        $this->setData('pager', $pager);

        unset($getData);
    }
}
