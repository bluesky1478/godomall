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

namespace Bundle\Controller\Front\Service;

use Component\Board\Board;
use Component\Board\BoardWrite;
use Component\Board\BoardList;
use Component\Goods\GoodsCate;
use Component\Page\Page;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\RedirectLoginException;
use Framework\StaticProxy\Proxy\Session;
use Request;
use View\Template;
use Framework\Utility\Strings;

class QaController extends \Controller\Front\Controller
{
    public function index()
    {
        $boardWrite = new BoardWrite(['bdId'=>Board::BASIC_QA_ID,'mode'=>'write']);
        if($boardWrite->canList() == 'n') {
            if(gd_is_login()) {
                throw new AlertBackException(__('접근 권한이 없습니다.'));
            }
            else {
                throw new RedirectLoginException();
            }
        }

        $this->setData('bdId', Board::BASIC_QA_ID);
        $this->setData('memNo', Session::get('member.memNo'));
    }
}
