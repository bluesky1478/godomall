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
use Framework\Debug\Exception\AlertBackException;
use Request;
use Session;

class ScmBoardViewController extends \Controller\Admin\Controller
{
    public function index()
    {
        if (gd_is_provider()) {
            $this->callMenu('board', 'board', 'scmBoardView');
        } else {
            $this->callMenu('scm', 'scm', 'scmBoardView');
        }

        try {
            $req = Request::get()->toArray();
            $scmBoard = new ProviderArticle();
            $data = $scmBoard->getView(gd_isset($req['sno']));
            if (gd_is_provider()) {
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

        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
        $queryString = preg_replace(array('/(sno|' . urlencode('[]') . ')=[^&]*/', '/[&]+/', '/\?[&]+/', '/[&]+$/'), array('', '&', '?', ''), Request::server()->get('QUERY_STRING'));
        $this->setData('queryString', $queryString);
        $this->setData('req', $req);
        $this->setData('data', $data);
        $this->getView()->setPageName('scm/scm_board_view.php');
    }

}
