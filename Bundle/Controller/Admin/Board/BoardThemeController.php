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

use Component\Board\Board;
use Component\Board\BoardTheme;
use Framework\Http\Request;
use Globals;

/**
 * 게시판 테마 리스트
 * @author Lee Namju <lnjts@godo.co.kr>
 */
class BoardThemeController extends \Controller\Admin\Controller
{

    /**
     * Description
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('board', 'board', 'theme');

        // --- 모듈 호출
        $bTheme = new BoardTheme();
        // --- 게시판 테마 데이터
        try {
            $getData = $bTheme->getList(\Request::get()->toArray());
            $applySkinList = $bTheme->getLiveSkinList(\Request::get()->get('domainFl'),\Request::get()->get('deviceType','pc'));
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
        } catch (\Exception $e) {
            $this->layer($e->getMessage());
        }
        $this->setData('applySkinList', $applySkinList);
        $this->setData('data', $getData['data']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('sort', $getData['sort']);
        $this->setData('page', $page);
        $this->setData('req', \Request::get()->toArray());
        $this->setData('theme', $getData['theme']);
    }
}
