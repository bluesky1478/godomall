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
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\Strings;
use Message;
use Globals;
use Request;

/**
 * 게시판 테마 등록
 * @author Lee Namju <lnjts@godo.co.kr>
 */
class BoardThemeRegisterController extends \Controller\Admin\Controller
{
    /**
     * Description
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        if ((Request::get()->get('sno'))) {
            $this->callMenu('board', 'board', 'themeModify');
        } else {
            $this->callMenu('board', 'board', 'themeWrite');
        }

        // --- 모듈 설정
        $bTheme = \App::load('\\Component\\Board\\BoardTheme');

        $storageBox = [];
        // --- 게시판 테마 데이터
        try {
            $data = $bTheme->getView(Request::get()->get('sno'));
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->setData('iconTypeList', BoardTheme::ICON_TYPE_LIST);

        $this->addScript([
            'jquery/jquery.dataOverlapChk.js',
        ]);

        $this->setData('data', gd_htmlspecialchars($data['data']));
        $this->setData('checked', $data['checked']);
        $this->setData('bdKind', Board::KIND_LIST);
        $this->setData('storageBox', $storageBox);
    }
}
