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

namespace Bundle\Controller\Mobile\Board;

use Component\Goods\GoodsCate;
use Component\Memo\MemoAct;
use Framework\Debug\Exception\AlertBackException;
use Request;
use View\Template;
use Component\Validator\Validator;
use Globals;
use Component\Board\BoardView;
use Message;

class MemoPsController extends \Controller\Mobile\Controller
{
    public function index()
    {
        $req = Request::post()->toArray();
        switch ($req['mode']) {
            case 'reply' :
            case 'write':
            case 'modify': {
                try {
                    $memoAct = new MemoAct($req);
                    $memoAct->saveData();
                    echo $this->json(['result' => 'ok', 'msg' => __('저장이 완료되었습니다.')]);
                } catch (\Exception $e) {
                    echo $this->json(['result' => 'fail', 'msg' => $e->getMessage()]);
                }
                exit;
                break;
            }
            case 'delete': {
                try {
                    $memoAct = new MemoAct($req);
                    $memoAct->deleteData();
                    echo $this->json(['result' => 'ok', 'msg' => __('삭제 되었습니다.')]);
                } catch (\Exception $e) {
                    echo $this->json(['result' => 'fail', 'msg' => $e->getMessage()]);
                }
                exit;
                break;
            }
            case 'getMemo' : {
                try {
                    $memoAct = new MemoAct($req);
                    $data = $memoAct->getMemoDetail();
                    echo $this->json(['result' => 'ok', 'writerNm' => $data['writerNm'], 'memo' => $data['memo']]);
                } catch (\Exception $e) {
                    echo $this->json(['result' => 'fail', 'msg' => $e->getMessage()]);
                }
                exit;
            }
            case 'getSecretMemo' : {
                try {
                    $memoAct = new MemoAct($req);
                    $data = $memoAct->getSecretMemo();
                    echo $this->json(['result'=>'ok','memo'=>gd_htmlspecialchars_stripslashes($data['memo'])]);
                } catch (\Exception $e) {
                    echo $this->json(['result' => 'fail', 'msg' => $e->getMessage()]);
                }
                exit;
                break;
            }
            case 'checkPassWord' : {
                try {
                    $memoAct = new MemoAct($req);
                    $data = $memoAct->getSecretMemo();
                    echo $this->json(['result' => $data['checkPassword']]);
                } catch (\Exception $e) {
                    echo $this->json(['result' => 'fail', 'msg' => $e->getMessage()]);
                }
                exit;
            }
        }

    }

}
