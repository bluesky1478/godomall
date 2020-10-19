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
use Component\Storage\Storage;
use Component\Board\BoardAdmin;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\LayerNotReloadException;
use Message;
use Request;

class BoardPsController extends \Controller\Admin\Controller
{

    public function index()
    {
        $mode = (Request::post()->get('mode')) ? Request::post()->get('mode') : Request::get()->get('mode');

        switch ($mode) {
            case 'regist':
                try {
                    $boardAdmin = new BoardAdmin();
                    $boardAdmin->insertBoardData(Request::post()->toArray(), Request::files()->toArray());
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.href="board_list.php";');
                } catch (\Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;
            case 'modify':
                try {
                    $boardAdmin = new BoardAdmin();
                    $boardAdmin->modifyBoardData(Request::post()->toArray());
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.href="board_list.php";');
                } catch (\Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;
            case 'delete':
                try {
                    $boardAdmin = new BoardAdmin();
                    if (empty(Request::post()->get('sno')) === false) {
                        foreach (Request::post()->get('sno') as $sno) {
                            $seoTagSno = Request::post()->get('seoTagSno')[$sno];
                            $boardAdmin->deleteBoardData($sno, $seoTagSno);
                        }
                    }
                    $this->layer(__('삭제 되었습니다.'));
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            case 'captcha':
                try {
                    $boardAdmin = new BoardAdmin();
                    $boardAdmin->modifyCaptchaColor(Request::post()->toArray());
                    $this->js('parent.$.unblockUI();');
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            case 'forbidden':
                try {
                    $tmp = explode(',', Request::post()->get('word'));
                    $tmp = array_map("trim", $tmp);
                    gd_set_policy('board.forbidden', ['word' => implode(STR_DIVISION, $tmp)]);
                    unset($tmp);
                    $this->Layer(__('저장이 완료되었습니다.'), 'top.location.reload()');
                } catch (\Exception $e) {
                    throw new AlertBackException($e->getMessage());
                }
                break;
            case 'overlapBdId':
                try {
                    $boardAdmin = new BoardAdmin();
                    $result = $boardAdmin->overlapBdId(Request::post()->get('bdId'));
                    if ($result) {
                        $this->json(['result' => 'fail', 'msg' => __('중복된 아이디입니다.')]);
                    } else {
                        $this->json(['result' => 'ok', 'msg' => __('사용가능합니다.')]);
                    }
                } catch (\Exception $e) {
                    $this->json(['result' => 'fail', 'msg' => $e->getMessage()]);
                }
                break;
            case 'getStorage' :
                $storageName = Request::get()->get('storage');
                $pathCode = Request::get()->get('pathCode');
                $savePath = Storage::disk($pathCode,$storageName)->getRealPath('');
                echo $savePath;
                break;
            case 'selectListTheme' :
                $boardTheme = new BoardTheme();

                if(\Globals::get('gGlobal.isUse')) {
                    $skin = Request::get()->get('liveSkin');
                }
                else {
                    if (Request::get()->get('mobileFl') == 'y') {
                        $skin = \Globals::get('gSkin.mobileSkinLive');
                    } else {
                        $skin = \Globals::get('gSkin.frontSkinLive');
                    }
                }

                $skinList = $boardTheme->getThemeListByKind($skin,Request::get()->get('bdKind'),Request::get()->get('mobileFl'));    //TODO:작업할것
                $mobileFl = Request::get()->get('mobileFl') == 'y' ? true : false;
                $themeData = $boardTheme->getDefaultKindTheme($skin,Request::get()->get('bdKind'),$mobileFl);

                $jsonResult = json_encode(['list'=>$skinList , 'selected'=>$themeData['sno']]);
                exit($jsonResult);
                break;
        }

    }
}
