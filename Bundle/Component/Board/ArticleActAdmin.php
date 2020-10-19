<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Smart to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
namespace Bundle\Component\Board;

use App;
use Component\Mail\MailMimeAuto;
use Framework\StaticProxy\Proxy\Globals;
use Framework\StaticProxy\Proxy\Session;
use Framework\Utility\ImageUtils;
use Logger;
use Request;

class ArticleActAdmin extends \Component\Board\ArticleAdmin
{

    /** @var \Bundle\Component\Mail\MailMimeAuto $mailMimeAuto */
    private $mailMimeAuto;

    public function __construct($req)
    {
        parent::__construct($req);

        gd_isset($this->req['sno']);
        gd_isset($this->req['mode']);

        if (in_array($this->req['mode'], ['modify', 'reply', 'del']) && (!is_numeric($this->req['sno']))) {
            throw new \Exception(__('잘못된 접근입니다.'));
        }
    }

    public function updateAnswer(&$msgs)
    {
        $query = "UPDATE " . DB_BD_ . $this->cfg['bdId'] . " SET replyStatus = ? , answerSubject = ? , answerContents = ?,answerManagerNo = ? , answerModDt = now() WHERE sno = ?";
        $this->db->bind_query($query, ['sssii', $this->req['replyStatus'], $this->req['answerSubject'], $this->req['answerContents'], (Session::get('manager.sno')) * -1, $this->req['sno']]);
        $data = $this->buildQuery->selectOneWithMember($this->req['sno']);
        // 답변완료 상태일 경우 1:1문의 답변 자동메일 발송
        if ($this->req['replyStatus'] == Board::REPLY_STATUS_COMPLETE) {

            if ($this->cfg['bdEmailFl'] == 'y') {  //모바일 정보 따로 받게돼있으면.
                $email = $data['writerEmail'];
            } else {
                if (!$data['memNo']) { //비회원이면

                } else {    //회원이면 회원정보에서 가져온다.
                    $email = $data['email'];
                }
            }
            $mailData = [
                'boardSno' => $this->cfg['sno'],
                'memNm' => $data['writerNm'],
                'memId' => $data['writerId'],
                'regDt' => $data['regDt'],
                'subject' => $data['subject'],
                'contents' => $data['contents'],
                'answerTitle' => $data['answerSubject'],
                'answerContents' => $data['answerContents'],
                'email' => $email,
            ];

            if ($mailData['email']) {
                $this->mailMimeAuto = App::load('\\Component\\Mail\\MailMimeAuto');
                $mailResult = $this->mailMimeAuto->init(MailMimeAuto::BOARD_QNA, $mailData)->autoSend();
                if ($mailResult) {
                    $msgs[] = __('메일이 발송되었습니다.');
                }
            }
            $smsResult = $this->sendSmsFromAdmin($data);
            if ($smsResult) {
                $msgs[] = __('SMS가 발송되었습니다.');
            }
        }

        return true;
    }

    public function getTemplate()
    {
        $boardTemplate = new BoardTemplate();
        $data = $boardTemplate->getData($this->req['templateSno']);
        return $data;
    }

    public function deleteData($sno)
    {
        // 운영자 기능권한의 게시글 삭제 권한 없음 - 관리자페이지에서만
        $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerRootDirectory();
        if ($thisCallController == 'admin' && Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.boardDelete') != 'y') {
            throw new \Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
        }
        $data = $this->buildQuery->selectOne($sno);

        // 관리자 답변 삭제시 부모글(메인글)상태값 접수로 변경처리
        $boardAdminChk = $this->deleteAdminCheck($data['writerId']);
        if(!empty($boardAdminChk)){
            $getMainBoardInfo = $this->selectMainBoard($data['groupNo']);
            $this->updateMainBoardStatus($getMainBoardInfo['sno']);
        }

        if($data['goodsNo'] > 0) {
            $goods = \App::load('\\Component\\Goods\\Goods');
            $goods->setRevicwCount($data['goodsNo'], true);
        }
        if (empty($data)) {
            return;
        }

        switch ($this->canRemove($data)) {
            case 'y':
                break;
            default:
                throw new \Exception(__('권한이 없습니다.'));
                break;
        }

        if (!$data['parentSno']) {   //부모글이면 자식글 삭제
            if ($this->cfg['bdReplyDelFl'] == 'reply') {
                $childData = $this->buildQuery->selectListByGroupNo($data['groupNo']);
                if ($childData) {
                    foreach ($childData as $row) {
                        ImageUtils::deleteEditorImg($row['contents']);
                        $this->buildQuery->delete($row['sno']);
                    }
                }
            }
        } else {
            // 답변글이 있는 게시글 삭제시, 답변글도 함께 삭제
            if ($this->cfg['bdReplyDelFl'] == 'reply') {
                $childData = $this->buildQuery->selectListByGroupNo($data['groupNo'], $data['groupThread']);
                if ($childData) {
                    foreach ($childData as $row) {
                        ImageUtils::deleteEditorImg($row['contents']);
                        $this->buildQuery->delete($row['sno']);
                    }
                }
            }
        }

        ImageUtils::deleteEditorImg($data['contents']);
        $this->buildQuery->delete($sno);
        $this->buildQuery->deleteMemoByBdSno($sno);

        if ($data['saveFileNm']) {
            $saveFileNm = explode(STR_DIVISION, $data['saveFileNm']);
            $saveFileCnt = count($saveFileNm);
            for ($i = 0; $i < $saveFileCnt; $i++) {
                $this->storage->delete($this->cfg['bdUploadPath'] . $saveFileNm[$i]);
                $this->storage->delete($this->cfg['bdUploadThumbPath'] . $saveFileNm[$i]);
            }
        }
    }

    protected function handleBeforeModify($data)
    {
        if (gd_isset($this->req['isNotice']) == 'y') {
            if ($data['groupThread'] != '') {
                throw new \Exception('답변은 공지사항으로 변경이 불가능합니다.');
            }
        }
    }

    protected function handleAfterWrite($data)
    {
        //상품테이블 상품평 카운팅
        if ($this->cfg['bdId'] == Board::BASIC_GOODS_REIVEW_ID && $data['goodsNo']>0) {
            $goods = \App::load('\\Component\\Goods\\Goods');
            $goods->setRevicwCount($data['goodsNo']);
        }
    }

    /**
     * handleAfterReply
     *
     * @param $data
     * @param $msgs
     * @return bool
     */
    protected function handleAfterReply($parentData, $replyData, &$msgs)
    {
        if ($parentData['memNo'] < 0) {
            return true;
        }

        if ($this->cfg['bdEmailFl'] == 'y') {  //모바일 정보 따로 받게돼있으면.
            $email = $parentData['writerEmail'];
        } else {
            if (!$parentData['memNo']) { //비회원이면

            } else {    //회원이면 회원정보에서 가져온다.
                $email = $parentData['email'];
            }
        }
        $mailData = [
            'boardSno' => $this->cfg['sno'],
            'memNm' => gd_isset($parentData['writerNm'], $parentData['memNm']),
            'memId' => $parentData['writerId'],
            'regDt' => $parentData['regDt'],
            'subject' => $parentData['subject'],
            'contents' => $parentData['contents'],
            'answerTitle' => $replyData['subject'],
            'answerContents' => $replyData['contents'],
            'email' => $email,
        ];
        $this->mailMimeAuto = App::load('\\Component\\Mail\\MailMimeAuto');
        $mailResult = $this->mailMimeAuto->init(MailMimeAuto::BOARD_QNA, $mailData)->autoSend();
        if ($mailResult) {
            $msgs[] = __('메일이 발송되었습니다.');
        }
        $smsResult = $this->sendSmsFromAdmin($parentData);
        if ($smsResult) {
            $msgs[] = __('SMS가 발송되었습니다.');
        }

        return true;
    }

    /**
     * sendSmsFromAdmin
     *
     * @param $data
     * @return bool|string
     */
    protected function sendSmsFromAdmin($data)
    {
        if ($this->cfg['bdMobileFl'] == 'y') {  //모바일 정보 따로 받게돼있으면.
            $cellPhone = $data['writerMobile'];
        } else {
            if (!$data['memNo']) { //비회원이면
                return false;
            } else {    //회원이면 회원정보에서 가져온다.
                $cellPhone = $data['cellPhone'];
            }
        }
        $aBasicInfo = gd_policy('basic.info');
        $data = ['scmNo' => Session::get('manager.scmNo'), 'memNo' => $data['memNo'], 'writerNm' => $data['writerNm'], 'cellPhone' => $cellPhone];
        $result = BoardUtil::sendSms($this->cfg['bdId'], $data, ['rc_mallNm' => $data['writerNm'], 'shopName' => Globals::get('gMall.mallNm'), 'wriNm' => Globals::get('gMall.mallNm'), 'shopUrl' => $aBasicInfo['mallDomain']], 'member', $data['smsFl']);
        if ($result) {
            foreach ($result as $val) {
                if ($val['success']) {
                    return true;
                }
            }
        }

        return false;
    }

    public function openApiBoardImages($postValue) {
        return parent::multiUpload(['uploadFileNm' => gd_isset($postValue['uploadFileNm']), 'saveFileNm' => gd_isset($postValue['saveFileNm'])]);
    }

    public function openAPIBoardDeleteImages($arrSaveFileName) {
        foreach($arrSaveFileName as $saveFileName) {
            $this->storage->delete($this->cfg['bdUploadPath'] . $saveFileName);
            $this->storage->delete($this->cfg['bdUploadThumbPath'] . $saveFileName);
        }
    }

    public function syncApiData($channel)
    {
        $this->channel = $channel;
        if(empty($this->req['apiExtraData'])){
            return null;
        }

        if($this->req['apiOrderGoodsNo']) {
            $arrBind = [];
            $query="SELECT sno FROM ".DB_ORDER_GOODS." WHERE apiOrderGoodsNo = ? LIMIT 1";
            $this->db->bind_param_push($arrBind, 's', $this->req['apiOrderGoodsNo']);
            $result = $this->db->query_fetch($query, $arrBind)[0];
            $orderGoodsNo= $result['sno'];
            $this->req['orderGoodsNo'][] = $orderGoodsNo;
        }
        $arrBind = null;
        $query = "SELECT sno,apiExtraData   FROM es_bd_goodsreview WHERE goodsNo = ? AND  apiExtraData = ? limit 1";

        $this->db->bind_param_push($arrBind, 'i', $this->req['goodsNo']);
        $this->db->bind_param_push($arrBind, 's', $this->req['apiExtraData'] );
        $result = $this->db->query_fetch($query, $arrBind, false);
        $sno = $result['sno'];
        if ($sno) {
            $this->req['mode'] = 'modify';
            $this->req['sno'] = $sno;
        } else {
            $this->req['mode'] = 'write';
        }


        return parent::saveData();
    }

    public function deleteAdminCheck($managerId)
    {
        $arrBind = [];
        $query = "SELECT sno, managerId FROM " . DB_MANAGER . " WHERE managerId = ?";
        $this->db->bind_param_push($arrBind,'s',$managerId);
        $result = $this->db->query_fetch($query, $arrBind, false);
        unset($arrBind);

        return $result;
    }

    public function selectMainBoard($groupNo)
    {
        $arrBind = [];
        $query = "SELECT * FROM " . DB_BD_ . $this->cfg['bdId']. " WHERE groupNo = ? AND groupThread = ''";
        $this->db->bind_param_push($arrBind, 's', $groupNo);
        $result = $this->db->query_fetch($query, $arrBind, false);
        unset($arrBind);

        return $result;
    }

    public function updateMainBoardStatus($sno)
    {
        $arrBind = [];// 스토리지 , groupCode DB업데이트
        $this->db->bind_param_push($arrBind, 's', '1');
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $this->db->set_update_db(DB_BD_ . $this->cfg['bdId'], "replyStatus=?", 'sno=?', $arrBind, false);
    }
}
