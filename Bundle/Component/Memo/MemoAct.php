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

/**
 * 댓글 처리 Class
 * 저장, 수정, 삭제
 */

namespace Bundle\Component\Memo;

use Component\Board\BoardFront;
use Component\Board\BoardUtil;
use Component\Board\Board;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use Framework\Utility\Strings;
use Framework\Utility\StringUtils;

class MemoAct extends \Component\Board\BoardFront
{
    public function __construct($req)
    {
        parent::__construct($req);

        if ($this->cfg['auth']['memo'] != 'y') {
            throw new \Exception(sprintf(parent::TEXT_NOTHAVE_AUTHORITY, ''));
        }
    }

    public function getMemoDetail()
    {
        $arrBind = [];

        $session = \App::getInstance('session');
        $session->get(\Component\Member\Manager::SESSION_MANAGER_LOGIN . '.managerId');
        $memberNo = $session->get('member.memNo');

        //비회원일 경우
        if (empty($memberNo) || $memberNo == 0) {
            $query = "SELECT bm.* ,m.memId,m.memNm,m.nickNm  FROM " . DB_BOARD_MEMO . " as bm LEFT OUTER JOIN " . DB_MEMBER . " as m ON bm.memNo = m.memNo WHERE bm.bdId = ? AND bm.sno = ? ";
            $this->db->bind_param_push($arrBind, 's', $this->cfg['bdId']);
            $this->db->bind_param_push($arrBind, 'i', $this->req['sno']);
            $data = $this->db->query_fetch($query, $arrBind, false);

            //게시판 설정에서 비밀댓글 암호보안에 체크 된 경우
            if ( $this->cfg['bdPasswordMemoFl'] == 'y' ) {
                //비 회원의 비밀뎃글인경우
                if ( $data['isSecretReply'] == 'y' ) {
                    if (md5($this->req['writerPw']) == $data['writerPw']) {
                        $data['memo'] = $this->setContents($data['memo'],$data,true,true);
                    } else {
                        $data['memo'] = "";
                    }
                //비 회원의 일반뎃글인경우
                } else {
                    $data['memo'] = $this->setContents($data['memo'],$data,true,true);
                }

            //게시판 설정에서 비밀댓글 암호보안에 체크 되지 않은 경우 (네거시 보장)
            } else {
                $data['memo'] = $this->setContents($data['memo'],$data,true,true);
            }

        //회원으로 로그인 된 경우
        } else {
            $query = "SELECT bm.* ,m.memId,m.memNm,m.nickNm  FROM " . DB_BOARD_MEMO . " as bm LEFT OUTER JOIN " . DB_MEMBER . " as m ON bm.memNo = m.memNo WHERE bm.bdId = ? AND bm.sno = ? AND bm.memNo = ? ";
            $this->db->bind_param_push($arrBind, 's', $this->cfg['bdId']);
            $this->db->bind_param_push($arrBind, 'i', $this->req['sno']);
            $this->db->bind_param_push($arrBind, 'i', $memberNo);
            $data = $this->db->query_fetch($query, $arrBind, false);
            $data['memo'] = $this->setContents($data['memo'],$data,true,true);
        }

        if (empty($data['memo'])) {
            throw new \Exception(__('댓글 접근 권한이 없습니다.'));
        }

        return $data;
    }

    /**
     * 댓글저장/수정하기
     */
    public function saveData()
    {
        if (gd_is_login()) {
            $this->req['writerNm'] = $this->member['memNm'];
        }
        $writerId = $this->member['memId'];
        $writerNick = $this->member['memNick'];

        $memo = $this->req['memo'];
        $writerPw = md5($this->req['writerPw']);
        $sno = $this->req['sno'];
        $isSecretReply = $this->req['isSecret'];

        if(iconv_strlen($memo,'UTF-8')>1000){
            throw new \Exception(sprintf(__('%s 자 이내로 입력해주시기 바랍니다.'),1000));
        }

        BoardUtil::checkForbiddenWord($memo);
        switch ($this->req['mode']) {
            case 'reply' :
                $arrBind = [];
                $query = " SELECT * FROM " . DB_BOARD_MEMO . " WHERE sno= ? ";
                $this->db->bind_param_push($arrBind, 'i', $this->req['sno']);
                $reply = gd_htmlspecialchars_stripslashes($this->db->query_fetch($query, $arrBind, false));
                $groupNo = $reply['groupNo'];
                $groupThread = BoardUtil::createMemoGroupThread($this->cfg['bdId'],$reply['bdSno'], $groupNo, $reply['groupThread']);
                $arrData['bdId'] = $this->cfg['bdId'];
                $arrData['bdSno'] = $this->req['bdSno']; // 답변을 코멘트로 달 경우에만 sno 사용.
                $arrData['writerNm'] = $this->req['writerNm'];
                $arrData['writerNick'] = $writerNick;
                $arrData['writerId'] = $writerId;
                $arrData['memo'] = $memo;
                $arrData['isSecretReply'] = $isSecretReply;
                $arrData['writerPw'] = $writerPw;
                $arrData['memNo'] = $this->member['memNo'];
                $arrData['groupNo'] = $groupNo;
                $arrData['groupThread'] = $groupThread;
                $arrBind = $this->db->get_binding(DBTableField::tableBdMemo(), $arrData, 'insert');
                $this->db->set_insert_db(DB_BOARD_MEMO, $arrBind['param'], $arrBind['bind'], 'y');
                unset($arrBind);
                $this->db->bind_param_push($arrBind, 'i', $this->req['bdSno']);
                $this->db->set_update_db(DB_BD_ . $this->cfg['bdId'], 'memoCnt=memoCnt+1', 'sno=?', $arrBind, false);

                break;
            case "write": {
                $this->checkAntiSpam(true);

                $arrData['bdId'] = $this->cfg['bdId'];
                $arrData['groupNo'] = BoardUtil::createMemoGroupNo($this->cfg['bdId'], $this->req['bdSno']);
                $arrData['bdSno'] = $this->req['bdSno']; // 답변을 코멘트로 달 경우에만 sno 사용.
                $arrData['writerNm'] = $this->req['writerNm'];
                $arrData['writerNick'] = $writerNick;
                $arrData['writerId'] = $writerId;
                $arrData['memo'] = $memo;
                $arrData['writerPw'] = $writerPw;
                $arrData['memNo'] = $this->member['memNo'];
                $arrData['isSecretReply'] = $isSecretReply;

                // Validation
                $validator = new Validator();
                $validator->add('bdId', '', true); // 게시판아이디
                $validator->add('bdSno', 'number', true); // 게시글의 Sno
                $validator->add('groupNo', '', true); // 게시글의 Sno
                $validator->add('writerNm', '', true); // 글쓴이
                $validator->add('writerNick', '', false); // 닉네임
                $validator->add('writerId', '', false); // 아이디
                $validator->add('memo', '', true); // 내용
                $validator->add('isSecretReply', 'yn', true); // 비밀댓글 여부

                if (!$arrData['memNo']) {
                    $validator->add('writerPw', '', true); // 내용
                }
                $validator->add('memNo', '', true); // 글쓴이No
                if ($validator->act($arrData, true) === false) {
                    throw new \Exception(implode("\n", $validator->errors));
                }
                $arrBind = $this->db->get_binding(DBTableField::tableBdMemo(), $arrData, 'insert');
                $this->db->set_insert_db(DB_BOARD_MEMO, $arrBind['param'], $arrBind['bind'], 'y');
                unset($arrBind);
                unset($arrData);
                $arrBind = [];
                $memoCount = $this->buildQuery->getMemoCount($this->req['bdSno']);
                $this->db->bind_param_push($arrBind, 'i', $memoCount);
                $this->db->bind_param_push($arrBind, $this->fieldTypes['memo']['bdSno'], $this->req['bdSno']);
                $this->db->set_update_db(DB_BD_ . $this->cfg['bdId'], 'memoCnt = ?', 'sno=?', $arrBind, false);

                break;
            }
            case "modify" : {
                if(is_numeric($sno) === false){
                    throw new \Exception( parent::TEXT_INSUFFICIENT_INPUTDATA);
                    return;
                }

                $arrBind = null;
                $query = "SELECT memNo, writerPw FROM " . DB_BOARD_MEMO . " WHERE bdId=? AND sno=?";
                $this->db->bind_param_push($arrBind, $this->fieldTypes['memo']['bdId'], $this->cfg['bdId']);
                $this->db->bind_param_push($arrBind, 'i', $sno);
                $data = gd_htmlspecialchars_stripslashes($this->db->query_fetch($query, $arrBind, false));
                unset($arrBind);

                if($this->member['memNo'] < 1) {     //비회원
                    if($data['memNo']>0){   //비회원인데 회원글보려고 하면
                        throw new \Exception( parent::TEXT_INSUFFICIENT_INPUTDATA);
                    }
                    else {
                        if($writerPw != $data['writerPw']){
                            throw new \Exception(parent::TEXT_NOTMATCH_PASSWORD);
                        }
                    }
                }
                else {  //회원
                    if($this->member['memNo'] != $data['memNo']) {
                        throw new \Exception(sprintf(parent::TEXT_NOTHAVE_AUTHORITY, ''));
                    }
                }
                $arrBind = null;
                $this->db->bind_param_push($arrBind, $this->fieldTypes['memo']['memo'], $memo);
                $this->db->bind_param_push($arrBind, 's', $isSecretReply);
                $this->db->bind_param_push($arrBind, $this->fieldTypes['memo']['bdId'], $this->cfg['bdId']);
                $this->db->bind_param_push($arrBind, 'i', $sno);
                $this->db->set_update_db(DB_BOARD_MEMO, 'memo=? , isSecretReply=?', 'bdId=? AND sno=?', $arrBind, false);
                unset($arrBind);
                break;
            }
        }
    }

    /**
     * 댓글삭제하기
     */
    public function deleteData()
    {
        $writerPw = md5($this->req['writerPw']);

        if (!($this->req['sno'] && ($this->member['memNo'] || $writerPw))) {
            throw new \Exception(sprintf(parent::ECT_INSUFFICIENT_INPUTDATA, 'MemoAct'), parent::TEXT_INSUFFICIENT_INPUTDATA);
            return;
        }

        $data = $this->buildQuery->selectMemoOne($this->req['sno']);
        switch ($this->canRemove($data)) {
            case 'n': {
                throw new \Exception(sprintf(parent::ECT_NOTHAVE_AUTHORITY, 'MemoAct'), sprintf(parent::TEXT_NOTHAVE_AUTHORITY, '삭제'));
                break;
            }
            case 'c': {
                if ($writerPw != $data['writerPw']) {
                    throw new \Exception(parent::TEXT_NOTMATCH_PASSWORD);
                }
                break;
            }
        }

        $this->buildQuery->deleteMemo($this->req['sno']);
        $arrBind = [];
        $memoCount = $this->buildQuery->getMemoCount($this->req['bdSno']);
        $this->db->bind_param_push($arrBind, 'i', $memoCount);
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memo']['bdSno'], $this->req['bdSno']);
        $this->db->set_update_db(DB_BD_ . $this->cfg['bdId'], 'memoCnt = ?', 'sno = ?', $arrBind, false);
        unset($arrBind);
    }

    /**
     * 댓글 가져오기 및 권한 체크하기
     */
    public function getSecretMemo()
    {
        $writerPw = md5($this->req['writerPw']);
        $sno = $this->req['sno'];
        $result = '';

        if(is_numeric($sno) === false) {
            throw new \Exception( parent::TEXT_INSUFFICIENT_INPUTDATA);
            return;
        }

        $arrBind = null;
        $query = "SELECT memNo, writerPw, memo FROM " . DB_BOARD_MEMO . " WHERE bdId=? AND sno=?";
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memo']['bdId'], $this->cfg['bdId']);
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $data = gd_htmlspecialchars_stripslashes($this->db->query_fetch($query, $arrBind, false));
        unset($arrBind);

        if($this->member['memNo'] < 1) { //비회원
            if($writerPw != $data['writerPw']) {
                throw new \Exception(parent::TEXT_NOTMATCH_PASSWORD);
            } else {
                $result['checkPassword'] = 'ok'; //패스워드 체크 통과
                $result['memo'] = $this->setContents($data['memo'],$data,true,true); //비밀댓글 내용 리턴
            }
        }

        return $result;
    }
}

