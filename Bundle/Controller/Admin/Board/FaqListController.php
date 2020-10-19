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
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Request;

class FaqListController extends \Controller\Admin\Controller
{

    /**
     * Description
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('board', 'board', 'faqList');

//        if (!Request::get()->get('isBest')) {
//            Request::get()->set('isBest', 'n');
//        }
//        $selected['isBest'][Request::get()->get('isBest')] = ' selected="selected"';

        // --- 페이지 데이터
        try {
            $faqAdmin = new FaqAdmin();
            $req = Request::get()->all();
            $getData = $faqAdmin->getFaqList($req);
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->setData('pageInfo',$getData['pageInfo']);
        $this->setData('data', $getData['data']);
        $this->setData('categoryBox', $getData['categoryBox']);
        $this->setData('search', gd_htmlspecialchars($getData['search']));
        $this->setData('checked', gd_htmlspecialchars($getData['checked']));
    }
}
