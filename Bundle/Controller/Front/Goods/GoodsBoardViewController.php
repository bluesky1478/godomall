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

namespace Bundle\Controller\Front\Goods;

use Component\Board\Board;
use Component\Board\BoardView;
use Request;

/**
 * Class GoodsBoardViewController
 *
 * @package Bundle\Controller\Front\Goods
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class GoodsBoardViewController extends \Controller\Front\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        $qryStr = preg_replace(array('/(sno|sel\[\]|sel' . urlencode('[]') . ')=[^&]*/', '/[&]+/', '/\?[&]+/', '/[&]+$/'), array('', '&', '?', ''), Request::getQueryString());

        $req = Request::request()->toArray();

        try {
            $boardView = new BoardView($req);
            $getData = $boardView->getView();
            $relationList = $boardView->getRelation($getData);
        } catch (\Exception $e) {
            echo $this->json(['result'=>'fail' , 'contents'=>$e->getMessage()]);
            exit;
        }

        $bdView['cfg'] = gd_isset($boardView->cfg);
        $bdView['isAdmin'] = gd_isset($boardView->isAdmin);
        $bdView['data'] = gd_isset($getData);
        $bdView['member'] = gd_isset($boardView->member);
        $boardView->canReadSecretReply($bdView['data']);
        $boardSecretReplyCheck = $boardView->setSecretReplyView($bdView['cfg']);

        if ($req['bdId'] == Board::BASIC_GOODS_QA_ID) {
            $pageName = 'goods_board_qa_view.html';
        } else {
            $pageName = 'goods_board_review_view.html';
        }
        if (gd_is_login() === false) {
            // 개인 정보 수집 동의 - 이용자 동의 사항
            $tmp = gd_buyer_inform('001009');
            $private = $tmp['content'];
            if (gd_is_html($private) === false) {
                $bdView['private'] = $private;
            }
        }
        $this->getView()->setData('req', gd_isset($req));
        $this->getView()->setData('bdView', $bdView);
        $this->getView()->setData('secretReplyCheck', $boardSecretReplyCheck);
        $this->getView()->setData('bdListCfg' , $bdView['cfg']);
        $this->getView()->setData('relationList', $relationList);
        $this->getView()->setData('queryString', $qryStr);
        $this->getView()->setDefine('contents', 'goods/' . $pageName);

        $contents = $this->getView()->render('contents');

        echo $this->json(['result'=>'ok' , 'contents'=> $contents  ,'bdId'=>$req['bdId'],deleteAuth=>$bdView['data']['auth']['delete'] , modifyAuth=>$bdView['data']['auth']['modify']]);
    }
}
