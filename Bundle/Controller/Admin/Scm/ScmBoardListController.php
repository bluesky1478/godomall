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
use Request;

class ScmBoardListController extends \Controller\Admin\Controller
{

    public function index()
    {
        if (gd_is_provider()) {
            $this->callMenu('board', 'board', 'scmBoardList');
        } else {
            $this->callMenu('scm', 'scm', 'scmBoardList');
        }
        $req = Request::get()->toArray();

        //리스트 기본 검색기간 7일 설정
        if(!$req) {
            $req['searchDate'][0] = date('Y-m-d', strtotime('-6 day'));;
            $req['searchDate'][1] = date('Y-m-d');
            $req['searchPeriod'] = 6;
        }

        $scmBoard = new ProviderArticle();
        $data = $scmBoard->getList($req);
        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);

        $this->setData('pagination', $data['pagination']);
        $this->setData('queryString', Request::getQueryString());
        $this->setData('data', $data);
        $this->setData('category', $scmBoard->getCode());
        $this->setData('search', $scmBoard->getSearchInfo($req));
        $this->getView()->setPageName('scm/scm_board_list.php');
    }
}
