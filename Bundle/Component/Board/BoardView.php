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
 * 게시판 글보기 Class
 */
namespace Bundle\Component\Board;

use Component\Member\Util\MemberUtil;
use Component\Validator\Validator;
use Request;
use Component\Database\DBTableField;

class BoardView extends \Component\Board\BoardFront
{

    public function __construct($req)
    {
        parent::__construct($req);
    }

    /**
     * 관련글 가져오기;
     *
     * @param array $data 글정보
     * @return array $relation
     */
    public function getRelation($data)
    {
        if ($data['isNotice'] == 'y') {
            return null;
        }
        // 답변관리 기능 사용 게시판의 경우 관리자의 답변글 답변상태가 답변완료인 글만 노출되도록 수정
        if($this->cfg['bdAnswerStatusFl'] == 'y'){
            $arrWhere = "(( memNo < 0 AND replyStatus = '3' ) OR ( memNo >= 0 ))";
        }

        $relationData = $this->buildQuery->selectList(null, [" groupNo = " . $data['groupNo'] , "isDelete = 'n'" , empty($arrWhere) ? null : $arrWhere]);
        //$relationData = $this->buildQuery->selectList(null, [" groupNo = " . $data['groupNo'] , "isDelete = 'n' "]);
        $relation['reply'] = null;
        if (count($relationData) > 1) {
            $boardList = new BoardList($this->req);
            $boardList->applyConfigList($relationData);
            $relation['reply'] = $relationData;
        }
        return $relation;
    }

    public function getView(){
        $this->increaseBoardHit($this->req['sno']); //조회수 증가

        return parent::getView();
    }

    /**
     * increaseBoardHit
     *
     * @param $sno
     * @return bool
     * @internal param int $hit
     */
    protected function increaseBoardHit($sno)
    {
        if (!$increseHit = $this->cfg['bdHitPerCnt']) {
            $increseHit = 1;
        }

        $ip = Request::getRemoteAddress();
        $bdid = $this->cfg['bdId'];

        if($this->cfg['bdHitIPCheck'] == 'y') { //IP 중복 체크시

            //지난 HIT 로그 삭제
            $arrBind = [];
            $where = 'bdId = ? AND ip = ? AND regDt < ?';
            $this->db->bind_param_push($arrBind, 's', $bdid);
            $this->db->bind_param_push($arrBind, 's', $ip);
            $this->db->bind_param_push($arrBind, 's', date("Y-m-d") . ' 00:00:00');
            $this->db->set_delete_db(DB_BOARD_HIT_IP, $where, $arrBind);

            //HIT 로그 체크
            $query = " SELECT COUNT(*) AS cnt FROM " . DB_BOARD_HIT_IP . " WHERE bdId = ? AND bdSno = ? AND ip = ? ";
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $bdid);
            $this->db->bind_param_push($arrBind, 'i', $sno);
            $this->db->bind_param_push($arrBind, 's', $ip);
            $result = $this->db->query_fetch($query, $arrBind, false);

            if($result['cnt'] > 0){
                return true; //조회수 증가 안시킴
            }else{
                $arrInsertData = array();
                $arrInsertData['bdId'] = $bdid;
                $arrInsertData['bdSno'] = $sno;
                $arrInsertData['ip'] = $ip;
                $arrBind = $this->db->get_binding(DBTableField::tableBoardHitIp(), $arrInsertData, 'insert');
                $this->db->set_insert_db(DB_BOARD_HIT_IP, $arrBind['param'], $arrBind['bind'], 'y');
            }

        }

        $arrBind = [];
        $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
        $result =  $this->db->set_update_db(DB_BD_ . $this->cfg['bdId'], 'hit=hit+ '.$increseHit, 'sno = ?',$arrBind['bind']);
        return $result;
    }
}
