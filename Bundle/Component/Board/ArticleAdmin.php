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

use Component\Member\Manager;
use Framework\StaticProxy\Proxy\Session;
use Framework\Utility\ArrayUtils;
use UserFilePath;

class ArticleAdmin extends \Component\Board\Board
{
    const LIST_COUNT = 10;

    protected function isAdmin()
    {
        return true;
    }

    public function __construct($reqData)
    {
        parent::__construct($reqData);
        $this->member = ['memNo' => -Session::get('manager.sno'), 'memNm' => Session::get('manager.managerNm'), 'memId' => Session::get('manager.managerId'), 'memNick' => Session::get('manager.managerNickNm')];
        $this->cfg['bdSubjectLength'] = 20;
        $this->cfg['bdIconAdmin'] = '<img src="' . PATH_ADMIN_GD_SHARE . 'img/ico_bd_admin.gif" />';
        $this->cfg['bdIconHot'] = '<img src="' . PATH_ADMIN_GD_SHARE . 'img/ico_bd_hot.gif" />';
        $this->cfg['bdIconNew'] = '<img src="' . PATH_ADMIN_GD_SHARE . 'img/ico_bd_new.gif" />';
        $this->cfg['bdIconNotice'] = '<img src="' . PATH_ADMIN_GD_SHARE . 'img/ico_bd_notice.gif" />';
        $this->cfg['bdIconSecret'] = '<img src="' . PATH_ADMIN_GD_SHARE . 'img/ico_bd_secret.gif" />';
        $this->cfg['bdIconReply'] = '<img src="' . PATH_ADMIN_GD_SHARE . 'img/ico_bd_reply.gif" />';
        $this->cfg['bdIconFile'] = '<img src="' . PATH_ADMIN_GD_SHARE . 'img/ico_bd_file.gif" />';
    }

    public function getWriterInfo($data = null, $refManagerSno = 'memNo')
    {
        $rtnData = null;
        if (!$data) {
            return $this->member['memNm'] . '(' . $this->member['memId'] . ')';
        }
        if($data['channel'] == 'naverpay') {
            return '네이버페이 구매자';
        }
        if ($data[$refManagerSno] < 0) {    //비회원일때
            $query = "SELECT scmNo,managerNickNm,dispImage,managerId,managerNm,isDelete FROM " . DB_MANAGER . " WHERE sno = " . abs($data[$refManagerSno]);
            $managerData = $this->db->query_fetch($query, null, false);
            $addInfo = '';
            if ($managerData['isDelete'] == 'y') {
                $addInfo = '<br><span class="text-red">(' . Manager::DELETE_DISPLAY . ')</span>';   //TODO:운영자표기
            }
            return $managerData['managerId'] . '(' . $managerData['managerNm'] . ')' . $addInfo;
        } else if ($data[$refManagerSno] > 0) { //회원일때
            if (!$data['writerId']) {
                $data['writerId'] = __('알수없는ID');
            }
            return $data['writerId'] . '(' . $data['writerNm'] . ')';
        } else if ($data[$refManagerSno] == 0) {//비회원일경우
            return $this->xssClean(strip_tags($data['writerNm'])) . __('(비회원)');
        } else {        //관리자
            $result = $data['writerNm'];
            if (!$result) {
                $result = __('관리자');
            }
            return $this->xssClean(strip_tags($result));
        }
    }

    public function canWrite($mode = 'w', $parentData = null)
    {
        if ($mode == 'r') {
            if ($this->cfg['bdReplyStatusFl'] == 'y') {  //QA게시판인경우
                return 'y';
            }

            if ($this->cfg['bdReplyFl'] == 'n') {
                return 'n';
            }

            if ($parentData['isDelete'] == 'y') {
                return 'n';
            }

            if ($parentData['isNotice'] == 'y') {
                return 'n';
            }

            if (Manager::isProvider()) {
                if (Session::get('manager.scmNo') != $parentData['goodsData']['scmNo'] && Session::get('manager.scmNo') != $parentData['scmNo']) {
                    return 'n';
                }
            }
        } else if ($mode == 'm') {
            if ($this->cfg['bdMemoFl'] == 'n') {
                return 'n';
            }
        } else if ($mode == 'w') {
            if ($this->channel == 'naverpay') {
                return 'y';
            }
            if (gd_is_provider() === true) {
                $canNotWriteBdId = [Board::BASIC_GOODS_QA_ID, Board::BASIC_GOODS_REIVEW_ID];
                if (in_array($this->cfg['bdId'], $canNotWriteBdId)) {
                    return 'n';
                }
            }

        } else {
            throw new \Exception(__('잘못된 인자'));
        }

        return 'y';
    }

    public function canList()
    {
        return 'y';
    }

    public function canRead($data)
    {
        return 'y';
    }

    public function canModify($data)
    {
        if(gd_is_provider() === true){
            if(abs(Session::get('manager.sno')) == abs($data['memNo'])){    //본인글만 수정가능
                return 'y';
            }
            return 'n';
        }

        return 'y';
    }

    public function canRemove($data)
    {
        return $this->canModify($data);
    }

    /**
     * 리스트 설정 적용
     *
     * @param array &$arrData
     * @return bool
     */
    public function applyConfigList(&$arrData)
    {
        if ($this->cfg == null || ArrayUtils::isEmpty($arrData) === true) {
            return false;
        }
        foreach ($arrData as &$data) {
            $data['regDate'] = date_format(date_create($data['regDt']), "Y.m.d");
            if (date('Y.m.d') == $data['regDate']) {
                $data['regDate'] = date_format(date_create($data['regDt']), "H:i");
            }

            $data['auth']['view'] = $this->canRead($data);
            $data['auth']['modify'] = $this->canModify($data);
            $data['auth']['delete'] = $this->canRemove($data);
            $data['auth']['reply'] = $this->canWrite('r', $data);

            $data['gapReply'] = '';
            $data['isNew'] = 'n';
            $data['isHot'] = 'n';
            $data['isFile'] = 'n';

            // 이미지 설정
            $data['imgSizeW'] = $this->cfg['bdListImgWidth'];
            $data['imgSizeH'] = $this->cfg['bdListImgHeight'];

            $data['subject'] = gd_html_cut($data['subject'], 100);

            if (gd_isset($data['saveFileNm'])) {
                $data['isFile'] = 'y';
            }

            if ($data['groupThread']) {
                $data['gapReply'] = '<span style="margin-left:' . (((strlen($data['groupThread']) / 2) - 1) * 15) . 'px"></span>';
            }

            if ($data['isDelete'] == 'y') {
                $data['subject'] = '<i><u>'. __('고객이 삭제 한 글입니다.') .'</u></i>';
            }

            // 아이콘 설정
            if ($this->cfg['bdNewFl'] && (time() - strtotime($data['regDt'])) / 60 / 60 < $this->cfg['bdNewFl']) $data['isNew'] = 'y';
            if ($this->cfg['bdHotFl'] && $data['hit'] >= $this->cfg['bdHotFl']) $data['isHot'] = 'y';

            $data['writer'] = $this->getWriterInfo($data);

            // 관리자 답변시 답변 버튼 미노출을 위함
            if($data['groupThread']){
                $data['adminFl'] = $this->getAdminData($data['writerId']);
            }

            $data['replyStatusText'] = '-';
            if (($this->cfg['bdReplyStatusFl'] == 'y' && $data['replyStatus'] > 0) || ($this->cfg['bdAnswerStatusFl'] == 'y' && $data['replyStatus'] > 0)) {
                $array = Board::REPLY_STATUS_LIST;
                $data['replyStatusText'] = $array[$data['replyStatus']];
            }

            // 메인글의 답변이 관리자 답변인지 구분하기위한 플래그 추가
            //$adminReplyChk = $this->chkAdminReplyList($data);

            // 메인글이 관리자 답변이 있다면
            if($data['adminFl'] == '1'){
                $data['adminReplyStatusFl'] = 'y';
                $data['replyStatusText'] = '-';
            }

            if(!empty($data['groupThread'])){
                $userReplyStatus = $this->getAdminData($data['writerId']);
                if(empty($userReplyStatus)){
                    $array = Board::REPLY_STATUS_LIST;
                    $data['replyStatusText'] = $array[$data['replyStatus']];
                }
            }
        }
    }

    /**
     * applyConfigView
     *
     * @param $data
     * @return bool
     */
    public function applyConfigView(&$data)
    {
        if ($this->cfg == null || empty($data) === true) {
            return false;
        }

        $data['extraData'] = $this->getExtraData($this->req['sno']);

        $data['regDate'] = date_format(date_create($data['regDate']), "Y.m.d H:i:s");
        if ($this->cfg['bdEventFl'] == 'y') {
            $data['eventStart'] = date_format(date_create($data['eventStart']), "Y.m.d");
            $data['eventEnd'] = date_format(date_create($data['eventEnd']), "Y.m.d");
        }
        $data['subject'] = $this->xssClean($data['subject']);
        $data['replyStatusText'] = '-';
        $data['replyComplete'] = false;
        if ($this->cfg['bdReplyStatusFl'] == 'y' || $this->cfg['bdAnswerStatusFl'] == 'y') {
            $replyStatusList = Board::REPLY_STATUS_LIST;
            if ($data['replyStatus']) {
                $data['replyStatusText'] = $replyStatusList[$data['replyStatus']];
            }
            $data['workedAnswerContents'] = $this->setContents($data['answerContents'],$data,false,$data['isMobile'] == 'y' ? true : false,true);
            if ($data['answerManagerNo']) {
                $data['answerManagerNm'] = $this->getWriterInfo($data, 'answerManagerNo');
            }

            $data['replyComplete'] = ($data['replyStatus'] == Board::REPLY_STATUS_COMPLETE);
        }

        gd_isset($data['recommend'],0);
        $data['gapReply'] = '';
        $data['isNew'] = 'n';
        $data['isHot'] = 'n';
        $data['isAdmin'] = 'n';
        $data['isFile'] = 'n';
        if ($this->cfg['bdGoodsFl'] == 'y') {
            $data['goodsData'] = $this->setGoodsDataPool($data['goodsNo']);
        }
        // 이미지 설정
        $data['imgSizeW'] = $this->cfg['bdListImgWidth'];
        $data['imgSizeH'] = $this->cfg['bdListImgHeight'];

        if ($data['groupThread'] != '') {
            $data['gapReply'] = '<span style="margin-left:' . (((strlen($data['groupThread']) / 2) - 1) * 15) . 'px"></span>';
        }

        $data['isFile'] = 'n';
        if (gd_isset($data['saveFileNm'])) {
            $data['isFile'] = 'y';
        }

        //권한설정
        $directoryUri = \Request::getDirectoryUri();
        $data['auth']['view']  = $this->canRead($data);
        $data['auth']['modify'] = $this->canModify($data);
        $data['auth']['delete'] = $this->canRemove($data);
        $data['auth']['reply'] = $this->canWrite('r',$data);
        // 관리자앱 게시글 권한 설정
        if (stripos($directoryUri, 'mobileapp') !== false) {
            $data['auth']['authView'] = $this->getArticleAuth('view');
            $data['auth']['authReply'] = $this->getArticleAuth('reply');
        }
        // 아이콘 설정
        if ($this->cfg['bdNewFl'] && (time() - strtotime($data['regDt'])) / 60 / 60 < $this->cfg['bdNewFl']) $data['isNew'] = 'y';
        if ($this->cfg['bdHotFl'] && $data['hit'] >= $this->cfg['bdHotFl']) $data['isHot'] = 'y';

        $data['writer'] = $this->getWriterInfo($data);
        $data['answerWriter'] = $this->getWriterInfo($data,'answerManagerNo');
        $data['hit'] = number_format(($data['hit']));
        $data['workedContents'] = $this->setContents($data['contents'],$data,false,$data['isMobile'] == 'y' ? true : false);
        $data['memoList'] = $this->getMemo($data['sno']);
        $data['uploadImg'] = $this->getUploadedImage($data['uploadFileNm'], $data['saveFileNm'], $data['bdUploadStorage'], $data['bdUploadPath']);

        if ($data['saveFileNm']) {
            $data['uploadedFile'] = $this->getFilelist(array('uploadFileNm' => $data['uploadFileNm'], 'saveFileNm' => $data['saveFileNm']), $data['bdUploadStorage'], $data['bdUploadPath']);
        }
    }

    public function checkReviewPossible(){
        return ['possible' =>true];
    }

    /**
     * 관리자 체크
     * @return $cnt
     */
    public function getAdminData($id)
    {
        $db = \App::load('DB');
        $arrBind = [];
        $query = "SELECT COUNT(*)as cnt FROM " . DB_MANAGER . " WHERE managerId = ?";
        $db->bind_param_push($arrBind,'s',$id);
        $result = $db->query_fetch($query, $arrBind, false);
        unset($arrBind);

        return $result['cnt'];
    }

    /**
     * 게시판 메인글 관리자 답변 체크
     * @return $result
     */
    /*public function chkAdminReplyList($data)
    {
        $db = \App::load('DB');
        $arrBind = [];
        $query = "SELECT bd.sno, bd.groupNo FROM " . DB_BD_.$this->cfg['bdId']. " AS bd LEFT JOIN ".DB_MANAGER." AS m on bd.writerId = m.managerId WHERE bd.groupNo = ? AND m.managerId = ? AND bd.groupThread = ? AND bd.parentSno != 0";
        $db->bind_param_push($arrBind,'i',$data['groupNo']);
        $db->bind_param_push($arrBind,'s',$data['writerId']);
        $db->bind_param_push($arrBind,'s',$data['groupThread']);
        $result = $db->query_fetch($query, $arrBind, false);
        unset($arrBind);

        return $result;
    }*/

    /**
     * 게시판 비밀댓글 설정에 따른 비밀댓글 체크박스 변화
     * @return array
     */
    public function setSecretReplyView($type)
    {
        $result = array();
        if(gd_isset($type)) {
            switch ($type) {
                case '1' :
                    $result['checkbox'] = ' checked="checked"';
                    break;
                case '2' :
                    $result['checkbox'] = ' disabled';
                    break;
                case '3' :
                    $result['checkbox'] = ' checked="checked" disabled';
                    $result['hiddenCheckboxInModify'] = '<input type="hidden" name="isSecretReplyInModify" value="y">';
                    $result['hiddenCheckboxInReply'] = '<input type="hidden" name="isSecretReplyInReply" value="y">';
                    $result['hiddenCheckboxInWrite'] = '<input type="hidden" name="isSecretReplyInWrite" value="y">';
                    break;
                default:
                    $result['checkbox'] = '';
                break;
            }
        }

        return $result;
    }
}
