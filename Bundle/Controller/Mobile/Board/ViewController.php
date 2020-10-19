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
use Component\Page\Page;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\RedirectLoginException;
use Framework\Debug\Exception\RequiredLoginException;
use Request;
use View\Template;
use Component\Validator\Validator;
use Globals;
use Component\Board\BoardView;
use Component\Board\BoardList;
use Session;

class ViewController extends \Controller\Mobile\Controller
{
    public function index()
    {
        try {

            $this->addScript([
                'gd_board_common.js',
            ]);

            $req = array_merge((array)Request::get()->toArray(), (array)Request::post()->toArray());
            $boardView = new BoardView($req);
            $boardView->checkUseMobile();
            $getData = $boardView->getView();
            $bdView['cfg'] = gd_isset($boardView->cfg);
            $bdView['data'] = gd_isset($getData);
            $bdView['member'] = gd_isset($boardView->member);
            if (gd_is_login() === false) {
                // 개인 정보 수집 동의 - 이용자 동의 사항
                $tmp = gd_buyer_inform('001009');
                $private = $tmp['content'];
                if (gd_is_html($private) === false) {
                    $bdView['private'] = $private;
                }
            }
            if(gd_isset($req['noheader'],'n') != 'n') {
                $this->getView()->setDefine('header', 'outline/_share_header.html');
                $this->getView()->setDefine('footer', 'outline/_share_footer.html');
            }
            $boardView->canReadSecretReply($bdView['data']);
            $boardSecretReplyCheck = $boardView->setSecretReplyView($bdView['cfg']);

            $this->setData('secretReplyCheck', $boardSecretReplyCheck);
            $this->setData('req',gd_isset($req));
            $this->setData('bdView', $bdView);
            $this->setData('gPageName', __($bdView['cfg']['bdNm']));
            $path = 'board/skin/'.$bdView['cfg']['themeId'].'/view.html';
            $this->getView()->setDefine('view', $path);

            if ($bdView['cfg']['bdListInView'] == 'y') {
                gd_isset($req['page'], 1);
                $this->setData('req', $req);
                $boardList = new BoardList($req);
                $getData = $boardList->getList();
                $bdList['cfg'] = $boardList->cfg;
                $bdList['list'] = $getData['data'];
                $bdList['cnt'] = $getData['cnt'];
                $bdList['noticeList'] = $getData['noticeData'];
                $bdList['categoryBox'] = $boardList->getCategoryBox($req['category'], ' onChange="this.form.submit();" ');
                $bdList['pagination'] = $getData['pagination']->getPage();
                $this->setData('logoutReturnUrl', '../board/list.php?bdId=' . $req['bdId']);
                $this->setData('bdList', $bdList);
                $this->setData('inList',true);
                $path = 'board/skin/' . $bdList['cfg']['themeId'] . '/list.html';
                $this->getView()->setDefine('list', $path);
            }
        } catch (RequiredLoginException $e) {
            throw new RedirectLoginException($e->getMessage());
        } catch (\Exception $e) {
            if($req['gboard'] == 'y') {
                throw new AlertCloseException($e->getMessage());
            }
            throw new AlertRedirectException($e->getMessage(), null, null, '../board/list.php?' . Request::getQueryString());
        }
    }


}
