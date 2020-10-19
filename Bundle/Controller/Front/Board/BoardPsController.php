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
namespace Bundle\Controller\Front\Board;

use Component\Board\BoardWrite;
use Component\Board\BoardConfig;
use Component\Storage\Storage;
use Component\Board\BoardBuildQuery;
use Component\Board\BoardUtil;
use Component\Goods\GoodsCate;
use Component\Page\Page;
use Component\Validator\Validator;
use Component\Board\BoardList;
use Component\Board\BoardView;
use Component\Board\BoardAct;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\StaticProxy\Proxy\Logger;
use Framework\Utility\ArrayUtils;
use View\Template;
use Request;

class BoardPsController extends \Controller\Front\Controller
{

    public function index()
    {
        $req = Request::post()->toArray();
        switch ($req['mode']) {
            case 'duplicateOrderGoodsNo' :
                $cnt = BoardBuildQuery::init($req['bdId'])->selectCountByOrderGoodsNo($req['orderGoodsNo'], $req['bdSno']);
                if ($cnt > 0) {
                    exit('y');
                }
                exit('n');
                break;
            case 'validRegistOrderGoodsNo' :
                $boardWrite = new BoardWrite($req);
                $errorMsg = $boardWrite->checkReviewPossible();
                if ($errorMsg['possible'] == true) {
                    exit('n');
                }
                exit('y');
                break;
            case 'captcha' :
                $result = BoardUtil::checkCaptcha(Request::post()->get('captchaKey'));
                if ($result['code'] != '0000') {
                    exit(json_encode(false));
                }
                exit(json_encode(true));
                break;
            case 'delete':
                try {
                    $boardAct = new BoardAct($req);
                    $result = $boardAct->deleteData($req['sno']);
                    $msg = '';
                    if ($result == 'ok') {
                        $msg = __('삭제되었습니다');
                    }
                    $data = ['result' => $result, 'msg' => $msg];

                    echo $this->json($data);
                    exit;
                } catch (\Exception $e) {
                    $this->json(['result' => 'fail', 'msg' => $e->getMessage()]);
                }
                break;
            case 'modifyCheck' :
                $boardAct = new BoardAct($req);
                $result = $boardAct->checkModifyPassword($req['writerPw']);
                if ($result) {
                    echo $this->json(['result' => 'ok', 'msg' => '']);
                } else {
                    echo $this->json(['result' => 'fail', 'msg' => __('비밀번호가 틀렸습니다.')]);
                }
                exit;
                break;
            case 'modify':
            case 'write':
            case 'reply':
                $req['isMobile'] = false;
                try {
                    $boardAct = new BoardAct($req);
                    if (method_exists($boardAct, 'setHttpStorage') === true) { //HTTP Storage setting.
                        $boardAct->setHttpStorage(false);
                    }
                    $addScrpt = '';
                    $msgs[] = $boardAct->saveData();
                    if ($msgs) {
                        foreach ($msgs as $msg) {
                            if (!$msg) continue;
                            $addScrpt .= 'alert("' . $msg . '");';
                        }
                    }
                    if (gd_isset($req['gboard']) == 'y') {
                        if ($req['windowType'] == 'popup') {
                            if (\Request::isSecure()) {   //보안서버인경우 보안해제된 url로 리다이랙트해서 부모창을 제어해준다.
                                $this->js($addScrpt . 'location.href="../goods/goods_ps.php?mode=openerReload";');
                            } else {
                                $this->js($addScrpt . 'alert("' . __('저장되었습니다.') . '");opener.location.reload();self.close();');
                            }
                        } else {
                            $this->js($addScrpt . 'location.href="../goods/goods_ps.php?mode=layerReload";');
                        }
                    } else {
                        $this->js($addScrpt . 'location.href="../board/list.php?' . $req['returnUrl'] . '";');
                    }
                    exit;

                } catch (\Exception $e) {
                    if (gd_isset($req['gboard']) == 'y' && $req['windowType']!='popup') {
                        throw new AlertOnlyException($e->getMessage());
                    } else {
                        throw new AlertBackException($e->getMessage());
                    }
                }
                break;
            case 'ajaxUpload' : //ajax업로드
                try {
                    $boardAct = new BoardAct($req);
                    if (method_exists($boardAct, 'setHttpStorage') === true) { //HTTP Storage setting.
                        $boardAct->setHttpStorage(true, ['target'=>'board', 'req'=>$req, 'uploadName'=>'uploadFile', 'methodName'=>'uploadAjax']);
                    }
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
                /* $boardAct = new BoardAct($req); @TODO:간헐적으로 삭제되는 경우가있어 임시 주석
                 $boardAct->deleteUploadGarbageImage($req['deleteImage']);*/
                break;
            case 'category': // 말머리 양식글 가져오기
                try {
                    $boardAct = new BoardAct($req);
                    $result = $boardAct->getBdCategoryTemplate($req);
                    echo $result;
                    exit;
                } catch (\Exception $e) {
                    $this->json(['result' => 'fail', 'msg' => $e->getMessage()]);
                }
                break;
        }

        switch (Request::get()->get('mode')) {
            case 'searchGoods':
                try {
                    $data = Request::get()->toArray();
                    $goodsSearch = \App::load('Component\Goods\GoodsSearch');
                    $getData = $goodsSearch->getSearchedGoodsList($data);
                    $page = \App::load('Component\Page\Page', Request::get()->get('page'), 1); // 페이지 설정
                    $page->recode['total'] = $getData['cnt']['search']; // 검색 레코드 수

                    $page->set_page();
                    if (isset($getData['goodsData']) === false) {
                        $getData['goodsData'] = '';
                    }
                    $jsonData = array('goodsData' => $getData['goodsData'], 'pager' => $page->getPage('SearchGoods.search(PAGELINK)'));
                } catch (\Exception $e) {
                    $jsonData[] = 'fail';
                    $jsonData[] = alert_reform($e->getMessage());
                }

                echo 'data=' . json_encode($jsonData);
                break;
            case 'recommend' :  //추천하기
                try {
                    $boardAct = new BoardAct(['bdId' => Request::get()->get('bdId')]);
                    $recommendCount = $boardAct->recommend(Request::get()->get('sno'));
                    echo $this->json(['message' => __('추천되었습니다.'), 'recommendCount' => $recommendCount]);
                } catch (\Exception $e) {
                    echo $this->json(['message' => $e->getMessage()]);
                }
                exit;
                break;
        }

        exit;
    }
}
