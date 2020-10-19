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

use App;
use Component\Board\BoardTemplate;
use Component\Board\ArticleViewAdmin;
use Component\Board\BoardUtil;
use Component\Board\Board;
use Component\Board\ArticleWriteAdmin;
use Component\Board\BoardAdmin;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\UrlUtils;
use Request;
use Globals;

class ArticleWriteController extends \Controller\Admin\Controller
{
    /**
     * Description
     */
    public function index()
    {
        // --- 페이지 데이터

        try {
            $req = Request::get()->toArray();

            if(gd_is_provider() === false){

                if($req['mode'] == 'reply') {
                    $this->callMenu('board', 'board', 'boardReply');
                }
                else if($req['mode'] == 'modify'){
                    $this->callMenu('board', 'board', 'boardModify');
                }
                else {
                    $this->callMenu('board', 'board', 'boardWrite');
                }

//                $this->callMenu('board', 'board', 'manager');
            }
            else {
                if (Request::get()->get('bdId') == Board::BASIC_GOODS_REIVEW_ID) {

                    if($req['mode'] == 'reply') {
                        $this->callMenu('board', 'board', 'goodsReviewReply');
                    }
                    else if($req['mode'] == 'modify'){
                        $this->callMenu('board', 'board', 'goodsReviewModify');
                    }

                } else {
                    if($req['mode'] == 'reply') {
                        $this->callMenu('board', 'board', 'goodsQaReply');
                    }
                    else if($req['mode'] == 'modify'){
                        $this->callMenu('board', 'board', 'goodsQaModify');
                    }
                }
            }

            $articleWriteAdmin = new ArticleWriteAdmin($req);
            $getData = $articleWriteAdmin->getData();
            $bdWrite['cfg'] = $articleWriteAdmin->cfg;
            $bdWrite['code'] = gd_code('01005');
            $bdWrite['data'] = $getData['data'];

            if (isset($getData['data']['goodsData'])) {
                $bdWrite['data']['goodsData'] = $getData['data']['goodsData'];// memo에 get_entity 중복적용 처리
            }

            $bdWrite['categoryBox'] = $articleWriteAdmin->getCategoryBox($getData['data']['category'], 'class="form-control"', true);
            $bdWrite['link'] = gd_isset($req['link'], null);
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        if($req['mode'] == 'reply') {
            $mode = '답변';
        }
        else if($req['mode'] == 'modify'){
            $mode = '수정';
        }
        else {
            $mode ='등록';
        }

        $boardTemplate = new BoardTemplate();
        $templateList = $boardTemplate->getSelectData('admin');

        // --- 관리자 디자인 템플릿
        if (gd_isset($getData['pData'], null)) {
            $bdParent['categoryBox'] = $articleWriteAdmin->getCategoryBox($getData['pData']['category'], 'class="form-control"', true);
            $bdParent['data'] = ($getData['pData']);
            if (isset($getData['pData']['goodsData'])) {
                $bdParent['data']['goodsData'] = $getData['pData']['goodsData']; // memo에 get_entity 중복적용 처리
            }

            $this->setData('bdParent', $bdParent);
        }
        $boardAdmin = new BoardAdmin();
        $mileageUse = Globals::get('gSite.member.mileageUse');
        $moveBoardList = $boardAdmin->getMoveBoardList($bdWrite['cfg']);
        $boardList = $boardAdmin->getBoardList(null,false,null,false,0)['data'];
        $this->addScript(['gd_board_common.js', 'jquery/jquery.multi_select_box.js', 'jquery.number_only.js', 'jquery/jquery-ui/jquery-ui.js', 'jquery/validation/jquery.validate.js']);
        $this->setData('templateList', $templateList);
        $this->setData('listReplyStatus', Board::REPLY_STATUS_LIST);
        $this->setData('moveBoardList', $moveBoardList);
        $this->setData('boardList', $boardList);
        $this->setData('mode', $mode);

        // 일반형, 갤러리형의 최초 답변글의 답변디폴트는 답변완료
        if($bdWrite['cfg']['bdAnswerStatusFl'] == 'y' && $req['mode'] == 'reply'){
            //if(empty($getData['pData']['groupThread'])){
                $bdWrite['data']['replyStatus'] = Board::REPLY_STATUS_COMPLETE;
            //}
        }

        if($mode == '등록') {
            $bdWrite['data']['contents'] = $bdWrite['cfg']['templateContents'];
        }
        $this->setData('bdWrite', $bdWrite);
        $this->setData('req', gd_htmlspecialchars($articleWriteAdmin->req));
        $this->setData('mileageUse', $mileageUse);
        $this->setData('adminList', UrlUtils::getAdminListUrl());

        if ($bdWrite['cfg']['bdReplyStatusFl'] == 'y' && $req['mode'] == 'reply') {
            $boardView = new ArticleViewAdmin($req);
            $articleWrite = new ArticleWriteAdmin($req);
            $getData = $boardView->getView();
            $bdView['cfg'] = gd_isset($boardView->cfg);
            $bdView['data'] = gd_isset($getData);
            $bdView['member'] = gd_isset($boardView->member);
            if(!$bdView['data']['answerModDt']) {   //처음답변 시 디폴트 답변상태 는 답변완료.
                $bdView['data']['replyStatus'] = Board::REPLY_STATUS_COMPLETE;
            }
            $this->setData('writer', $articleWrite->getWrite());
            $this->setData('bdView', $bdView);
            $this->setData('replyStatusComplete',Board::REPLY_STATUS_COMPLETE);
        } else if ($bdWrite['cfg']['bdReplyStatusFl'] == 'n' && $req['mode'] == 'reply') {
            $boardView = new ArticleViewAdmin($req);
            $articleWrite = new ArticleWriteAdmin($req);
            $getData = $boardView->getView();
            $bdView['cfg'] = gd_isset($boardView->cfg);
            $bdView['cfg']['bdMemoFl'] = 'n';
            $bdView['data'] = gd_isset($getData);
            $bdView['member'] = gd_isset($boardView->member);
            $this->setData('writer', $articleWrite->getWrite());
            $this->setData('bdView', $bdView);
            $this->setData('replyStatusComplete',Board::REPLY_STATUS_COMPLETE);
        }

        // CRM고객관리에서 클릭 시 팝업모드실행
        if ($req['popupMode'] === 'yes') {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        }
        $this->getView()->setPageName('board/article_write.php');
    }
}
