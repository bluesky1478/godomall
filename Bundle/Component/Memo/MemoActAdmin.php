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

namespace Bundle\Component\Memo;

use Component\Board\BoardUtil;
use Component\Board\ArticleAdmin;
use Component\Database\DBTableField;
use Component\Validator\Validator;

class MemoActAdmin extends \Component\Board\ArticleAdmin
{
    public function __construct($req)
    {
        parent::__construct($req);
        $this->bdSno = gd_isset($this->req['bdSno']);
        $this->req['mode'] = gd_isset($this->req['mode']);
    }

    /**
     * 메모저장/수정하기
     */
    public function saveData()
    {

        $writerNm = $this->member['memNm'];
        $writerId = $this->member['memId'];
        $writerNick = $this->member['memNick'];

        $memo = $this->req['memo'];
        if(iconv_strlen($memo,'UTF-8')>1000){
            throw new \Exception(sprintf(__('%s 자 이내로 입력해주시기 바랍니다.'),1000));
        }
        switch ($this->req['mode']) {
            case 'reply' :
                # Anti-Spam 검증

                $arrBind = [];
                $query = " SELECT * FROM " . DB_BOARD_MEMO . " WHERE sno= ? ";
                $this->db->bind_param_push($arrBind, 'i', $this->req['sno']);
                $reply = gd_htmlspecialchars_stripslashes($this->db->query_fetch($query, $arrBind, false));
                $groupNo = $reply['groupNo'];
                $groupThread = BoardUtil::createMemoGroupThread($this->cfg['bdId'],$this->req['sno'],$groupNo, $reply['groupThread']);
                $arrData['bdId'] = $this->cfg['bdId'];
                $arrData['bdSno'] = $this->req['bdSno']; // 답변을 코멘트로 달 경우에만 sno 사용.
                $arrData['writerNm'] = $writerNm;
                $arrData['writerNick'] = $writerNick;
                $arrData['writerId'] = $writerId;
                $arrData['memo'] = $memo;
                $arrData['memNo'] = $this->member['memNo'];
                $arrData['groupNo'] = $groupNo;
                $arrData['groupThread'] = $groupThread;
                $arrData['isSecretReply'] = 'n';
                if(gd_isset($this->req['isSecretReplyInReply'])) {
                    $arrData['isSecretReply'] = $this->req['isSecretReplyInReply'];
                }

                $arrBind = $this->db->get_binding(DBTableField::tableBdMemo(), $arrData, 'insert');
                $this->db->set_insert_db(DB_BOARD_MEMO, $arrBind['param'], $arrBind['bind'], 'y');
                unset($arrBind);
                $this->db->bind_param_push($arrBind, 'i', $this->req['bdSno']);
                $this->db->set_update_db(DB_BD_ . $this->cfg['bdId'], 'memoCnt=memoCnt+1', 'sno=?', $arrBind, false);

                break;
            case "write": {
                $arrData['bdId'] = $this->cfg['bdId'];
                $arrData['bdSno'] = $this->req['bdSno']; // 답변을 코멘트로 달 경우에만 sno 사용.
                $arrData['writerNm'] = $writerNm;
                $arrData['writerNick'] = $writerNick;
                $arrData['writerId'] = $writerId;
                $arrData['memo'] = $this->req['memo'];
                $arrData['memNo'] = $this->member['memNo'];
                $arrData['groupNo'] = BoardUtil::createMemoGroupNo($this->cfg['bdId'],$this->req['bdSno']);
                $arrData['isSecretReply'] = 'n';
                if(gd_isset($this->req['isSecretReplyInWrite'])) {
                    $arrData['isSecretReply'] = $this->req['isSecretReplyInWrite'];
                }

                // Validation
                $validator = new Validator();
                $validator->add('bdId', '', true); // 게시판아이디
                $validator->add('bdSno', 'number', true); // 게시글의 Sno
                $validator->add('groupNo', '', true); // 게시글의 Sno
                $validator->add('writerNm', '', true); // 글쓴이
                $validator->add('writerNick', '', false); // 닉네임
                $validator->add('writerId', '', false); // 아이디
                $validator->add('memo', '', true); // 내용
                $validator->add('memNo', '', true); // 글쓴이No
                $validator->add('mileage', 'number'); // 마일리지
                $validator->add('mileageReason', ''); // 마일리지지급이유
                $validator->add('isSecretReply', 'yn', true); // 비밀댓글 유무

                if ($validator->act($arrData, true) === false) {
                    throw new \Exception(implode("\n", $validator->errors));
                }

                BoardUtil::checkForbiddenWord($arrData['memo']);

                $arrBind = $this->db->get_binding(DBTableField::tableBdMemo(), $arrData, 'insert');
                $this->db->set_insert_db(DB_BOARD_MEMO, $arrBind['param'], $arrBind['bind'], 'y');
                unset($arrBind);
                $arrBind = [];
                $memoCount = $this->buildQuery->getMemoCount($arrData['bdSno']);
                $this->db->bind_param_push($arrBind, 'i', $memoCount);
                $this->db->bind_param_push($arrBind, $this->fieldTypes['memo']['bdSno'], $arrData['bdSno']);
                $this->db->set_update_db(DB_BD_ . $this->cfg['bdId'], 'memoCnt = ? ', 'sno=?', $arrBind, false);

                unset($arrData);
                unset($arrBind);
                break;
            }
            case "modify" : {
                if (!(gd_isset($this->req['sno']) && gd_isset($this->req['memo']))) {
                    throw new \Exception(sprintf(parent::ECT_INSUFFICIENT_INPUTDATA, 'MemoAct'), parent::TEXT_INSUFFICIENT_INPUTDATA);
                }

                BoardUtil::checkForbiddenWord($this->req['memo']);

                $isSecretReply = 'n';
                if(gd_isset($this->req['isSecretReplyInModify'])) {
                    $isSecretReply = $this->req['isSecretReplyInModify'];
                }

                $this->db->bind_param_push($arrBind, $this->fieldTypes['memo']['memo'], $this->req['memo']);
                $this->db->bind_param_push($arrBind, 's', $isSecretReply);
                $this->db->bind_param_push($arrBind, $this->fieldTypes['memo']['bdId'], $this->cfg['bdId']);
                $this->db->bind_param_push($arrBind, 'i', $this->req['sno']);

                $this->db->set_update_db(DB_BOARD_MEMO, 'memo=?, isSecretReply=?', 'bdId=? AND sno=?', $arrBind, false);
                //debug($arrBind);
                unset($arrBind);
                break;
            }
        }
    }

    /**
     * 메모삭제하기
     */
    public function deleteData()
    {
        $sno = gd_isset($this->req['sno']);
        if (!$sno) {
            throw new \Exception(sprintf(parent::ECT_INSUFFICIENT_INPUTDATA, 'MemoAct'), parent::TEXT_INSUFFICIENT_INPUTDATA);
        }
        $arrBind = [];
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memo']['bdId'], $this->cfg['bdId']);
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $this->db->set_delete_db(DB_BOARD_MEMO, 'bdId=? AND sno=?', $arrBind);
        unset($arrBind);

        $arrBind = [];
        $memoCount = $this->buildQuery->getMemoCount($this->req['bdSno']);
        $this->db->bind_param_push($arrBind, 'i', $memoCount);
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memo']['bdSno'], $this->req['bdSno']);
        $this->db->set_update_db(DB_BD_ . $this->cfg['bdId'], 'memoCnt= ? ', 'sno=?', $arrBind, false);

        unset($arrBind);
    }
}
