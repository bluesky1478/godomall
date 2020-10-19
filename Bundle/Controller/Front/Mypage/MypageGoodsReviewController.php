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

namespace Bundle\Controller\Front\Mypage;

use Component\Board\Board;
use Component\Board\BoardList;
use Component\Goods\GoodsCate;
use Component\Page\Page;
use Component\Except\Except;
use Component\Database\DBTableField;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\RedirectLoginException;
use Framework\StaticProxy\Proxy\Session;
use View\Template;
use Request;

class MypageGoodsReviewController extends \Controller\Front\Controller
{
    public function index()
    {
        $board = new BoardList(['bdId'=>Board::BASIC_GOODS_REIVEW_ID]);
        if($board->canList() == 'n') {
            if(gd_is_login()) {
                throw new AlertBackException(__('접근 권한이 없습니다.'));
            }
            else {
                throw new RedirectLoginException();
            }
        }

        $this->setData('bdId', Board::BASIC_GOODS_REIVEW_ID);
        $this->setData('memNo', Session::get('member.memNo'));
    }
}
