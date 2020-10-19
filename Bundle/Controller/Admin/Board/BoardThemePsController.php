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

use Component\Board\BoardTheme;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertOnlyException;
use Message;
use Globals;
use Request;
use Framework\Debug\Exception\LayerException;
use Framework\Utility\Strings;

class BoardThemePsController extends \Controller\Admin\Controller
{

    /**
     * Description
     *
     * @throws Except
     */
    public function index()
    {

        /**
         * 게시판 테마 처리 페이지
         * [관리자 모드] 게시판 테마 처리
         *
         * @author sunny
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        // --- 게시판 class
        $bTheme = new BoardTheme();
        $mode = (Request::post()->get('mode')) ? Request::post()->get('mode') : Request::get()->get('mode');
        try {
            if (empty($mode) === true) {
                throw new \Exception(__('잘못된 경로로 접근하였습니다.'));
            }
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        switch ($mode) {
            // 게시판 테마
            case 'theme_register':
            case 'theme_modify':
                try {
                    $dataSno = $bTheme->saveData(Request::post()->toArray(), Request::files()->all());
                    $this->layer(__('저장이 완료되었습니다.'), null, null, null, 'parent.location.replace("../board/board_theme_register.php?sno=' . $dataSno . '")');
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
//                    throw $e;
                }
                break;

            // 게시판 테마 삭제
            case 'theme_delete':
                try {
                    // 게시판테마삭제
                    $bTheme->deleteData(Request::post()->get('sno'));
                    $this->layer(__('삭제 되었습니다.'));
                } catch (\Exception $e) {
                    throw new AlertBackException($e->getMessage());
                }
                break;
            case 'overlapThemeId':
                try {
                    $result = $bTheme->overlapThemeId(Request::post()->get('themeId'), Request::post()->get('isMobile'),Request::post()->get('liveSkin'));
                    //echo $result;
                    if ($result) {
                        $this->json(['result' => 'fail', 'msg' => __('사용중인 스킨코드입니다.')]);
                    } else {
                        $this->json(['result' => 'ok', 'msg' => __('사용가능합니다.')]);
                    }

                } catch (\Exception $e) {
                    $this->json(['result' => 'fail', 'msg' => $e->getMessage()]);
                }
                break;
            case 'deleteIcon' :
                $bTheme->deleteIcon(Request::get()->get('themeId'), Request::get()->get('iconType'), Request::get()->get('device'));
                $this->alert('아이콘이 삭제되었습니다.', null, null, null, 'parent.location.reload()');
                break;
            case 'getApplySkinList':
                try {
                    $applySkinList = $bTheme->getLiveSkinList(\Request::post()->get('domainFl'),\Request::post()->get('deviceType'));
                    $this->json(['result'=>'ok','list' => $applySkinList]);
                } catch (\Exception $e) {
                    $this->json(['result' => 'fail', 'msg' => $e->getMessage()]);
                }
                break;
        }
    }
}
