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
use Component\Board\BoardView;
use Component\Board\ArticleWriteAdmin;
use Component\Board\ArticleActAdmin;
use Framework\Debug\Exception\AlertBackException;
use Component\Memo\MemoActAdmin;
use Framework\Debug\Exception\LayerException;
use Message;
use Request;
use Framework\Debug\Exception\Framework\Debug\Exception;
use Framework\Utility\StringUtils;

class ArticlePsController extends \Controller\Admin\Controller
{

    /**
     * Description
     *
     * @throws Except
     */
    public function index()
    {
        $req = Request::post()->toArray();
        switch ($req['mode']) {
            case 'replyQa' :
                try {
                    /** @var \Bundle\Component\Board\ArticleActAdmin $articleActAdmin */
                    $req['answerContents'] = StringUtils::xssClean($req['answerContents']);
                    $articleActAdmin = new ArticleActAdmin($req);
                    $articleActAdmin->updateAnswer($msgs);
                    $addScrpt = '';
                    if ($msgs) {
                        $msg = implode('\n', $msgs);
                        $addScrpt .= 'alert("' . $msg . '");';
                    }
                    if($req['queryString'] == 'popupMode=yes') { // CRM 팝업모드일 경우
                        $this->layer(__('저장이 완료되었습니다.'), "parent.opener.location.reload();parent.window.close();");
                    } else {
                        $this->layer(__('저장이 완료되었습니다.'), $addScrpt . "top.location.href='article_write.php?bdId=" . $req['bdId'] . "&sno=" . $req['sno'] . "&mode=reply&" . $req['queryString'] . "'");
                    }
                } catch (\Exception $e) {
                    throw $e;
                    //    throw new AlertBackException($e->getMessage());
                }
                break;
            case 'reply':
            case 'write':
            case 'modify':
                try {
                    $articleActAdmin = new ArticleActAdmin($req);
                    $msgs = $articleActAdmin->saveData();
                    $addScrpt = '';
                    if ($msgs) {
                        $msg = implode('\n', $msgs);
                        $addScrpt .= 'alert("' . $msg . '");';
                    }
                    if ($req['mode'] == 'reply') {
                        if($req['queryString'] == 'popupMode=yes') { // CRM 팝업모드일 경우
                            $this->layer(__('저장이 완료되었습니다.'), "parent.opener.location.reload();parent.window.close();");
                        } else {
                            $this->layer(__('저장이 완료되었습니다.'), $addScrpt . "top.location.href='article_list.php?bdId=" . $req['bdId'] . "&" . $req['queryString'] . "'");
                        }
                    } else {
                        if($req['queryString'] == 'popupMode=yes') { // CRM 팝업모드일 경우
                            $this->layer(__('저장이 완료되었습니다.'), "parent.opener.location.reload();parent.window.close();");
                        } else {
                            $this->layer(__('저장이 완료되었습니다.'), $addScrpt . 'top.location.href="article_list.php?bdId=' . $req['bdId'] . '";');
                        }
                    }
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            case 'delete':
                try {
                    $articleActAdmin = new ArticleActAdmin($req);
                    if (is_array($req['sno'])) {
                        foreach ($req['sno'] as $sno) {
                            $articleActAdmin->deleteData($sno);
                        }
                    } else {
                        $articleActAdmin->deleteData($req['sno']);
                    }
                    if($req['popupMode'] == 'yes') { // CRM 팝업모드일 경우
                        $this->layer(__('삭제 되었습니다.'), "parent.opener.location.reload();parent.window.close();");
                    } else {
                        $this->layer(__('삭제 되었습니다.'));
                    }
                } catch (\Exception $e) {
                    throw new AlertBackException($e->getMessage());
                }
                break;
            case 'getTemplate' :
                $articleActAdmin = new ArticleActAdmin($req);
                $data = $articleActAdmin->getTemplate();
                $result = [
                    'result' => 'ok',
                    'subject' => $data['subject'],
                    'contents' => $data['contents'],
                ];
                echo $this->json($result);
                exit;
                break;
            case 'ajaxUpload' : //ajax업로드
                try {
                    $boardAct = new ArticleActAdmin($req);
                    $fileData = Request::files()->get('uploadFile');
                    if (!$fileData) {
                        $this->json(['result' => 'cancel']);
                    }
                    $result = $boardAct->uploadAjax($fileData);
                    if ($result['result'] == false) {
                        throw new \Exception(__('업로드에 실패하였습니다.'));
                    }
                    $this->json(['result' => 'ok', 'uploadFileNm' => $result['uploadFileNm'], 'saveFileNm' => $result['saveFileNm']]);
                } catch (\Exception $e) {
                    $this->json(['result' => 'fail', 'errorMsg' => $e->getMessage()]);
                }
                break;
            case  'deleteGarbageImage' :    //ajax업로드 시 가비지이미지 삭제
                $boardAct = new ArticleActAdmin($req);
                $boardAct->deleteUploadGarbageImage($req['deleteImage']);
                break;

            // 게시판 리스트에서 사용자화면 눌렀을 경우 플래그 추가
            case 'userBoardChk' :
                try {
                    $board = new BoardAdmin();
                    $board->userBoardChk($req['fl']);
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
        }
    }
}
