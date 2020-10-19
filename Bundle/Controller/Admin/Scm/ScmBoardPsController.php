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
namespace Bundle\Controller\Admin\Scm;

use Component\Scm\ProviderArticle;
use Component\Storage\Storage;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Globals;
use Request;
use Message;

class ScmBoardPsController extends \Controller\Admin\Controller
{

    public function index()
    {
        $req = Request::post()->toArray();
        $mode = Request::request()->get('mode');
        $files = Request::files()->all();
        $data = array_merge($req, $files);

        try {
            $scmBoard = new ProviderArticle();
            switch ($mode) {
                case 'modify' :
                    $scmBoard->setData($data);
                    $sno = $scmBoard->modify();
                    $this->layer(__('저장이 완료되었습니다.'),'top.location.href="scm_board_list.php";');
                    break;
                case 'delete' :
                    $reqSno = Request::get()->get('sno');
                    if(is_array($reqSno)) {
                        foreach($reqSno as $sno) {
                            $scmBoard->remove($sno);
                        }
                    }
                    else {
                        $scmBoard->remove(Request::get()->get('sno'));
                    }
                    $this->layer(__('삭제 되었습니다.'),'top.location.href="scm_board_list.php";');
                    break;
                case 'download' :
                    $saveFileName = Request::get()->get('saveFileName');
                    $uploadFileName = Request::get()->get('uploadFileName');
                    $downloadPath = Storage::disk(Storage::PATH_CODE_SCM)->getDownLoadPath('upload/' . $saveFileName);
                    $this->download($downloadPath, $uploadFileName);
                    exit;
                    break;
                default :   //글등록
                    $scmBoard->setData($data);
                    $scmBoard->add($mode == 'reply');
                    $this->layer(__('저장이 완료되었습니다.'),'top.location.href="scm_board_list.php";');
            }
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
        exit;
    }
}
