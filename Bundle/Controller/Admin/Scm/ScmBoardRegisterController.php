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
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\LayerException;
use Request;
use Session;

/**
 * Class ScmBoardRegisterController
 * @package Bundle\Controller\Admin\Scm
 * @author nam-ju Lee <lnjts@godo.co.kr>
 */
class ScmBoardRegisterController extends \Controller\Admin\Controller
{

    public function index()
    {
        $req = Request::get()->toArray();
        if (gd_is_provider()) {

            if($req['mode'] == 'reply') {
                $this->callMenu('board', 'board', 'scmBoardReply');
            }
            else if($req['mode'] == 'modify'){
                $this->callMenu('board', 'board', 'scmBoardModify');
            }
            else {
                $this->callMenu('board', 'board', 'scmBoardWrite');
            }
        } else {

            if($req['mode'] == 'reply') {
                $this->callMenu('scm', 'scm', 'scmBoardReply');
            }
            else if($req['mode'] == 'modify'){
                $this->callMenu('scm', 'scm', 'scmBoardModify');
            }
            else {
                $this->callMenu('scm', 'scm', 'scmBoardWrite');
            }
        }

        try {
            $scmBoard = new ProviderArticle();
            $data = $scmBoard->getFormData(gd_isset($req['mode']), gd_isset($req['sno']));

            if (gd_is_provider() && $req['mode'] == 'modify') {
                if ($data['scmNo'] != Session::get('manager.scmNo') && $data['scmFl'] != 'all') {
                    $auth = false;
                    foreach ($data['scmBoardGroup'] as $row) {
                        if (Session::get('manager.scmNo') == $row['scmNo']) {
                            $auth = true;
                            break;
                        }
                    }
                    if ($auth === false) {
                        throw new \Exception(__('잘못된 경로로 접근하셨습니다.'));
                    }
                }
            }

        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        $this->setData('req', $req);
        $this->setData('mode', $mode);
        $this->setData('category', $scmBoard->getCode());
        $this->setData('data', $data);
        $this->setData('maxUploadSize', $scmBoard->getMaxUploadSize());
        $this->setData('maxUploadCount', $scmBoard::MAX_UPLOAD_COUNT);
    }
}
