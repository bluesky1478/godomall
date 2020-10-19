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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\Member\HackOut;


use Component\Database\DBTableField;
use Exception;
use Framework\Database\DBTool;
use Framework\Utility\ArrayUtils;
use Framework\Utility\StringUtils;

/**
 * 회원탈퇴 DAO 클래스
 * @package Bundle\Component\Member\HackOut
 * @author  yjwee
 */
class HackOutDAO extends \Component\AbstractComponent
{
    protected $fields = [];
    private $binders = [];
    private $wheres = [];
    private $params = [];
    private $offset = 0;
    private $limit = 20;

    public function __construct(DBTool $db = null)
    {
        parent::__construct($db);
        $this->tableFunctionName = 'tableMemberHackout';
        $this->fields = DBTableField::getFieldTypes($this->tableFunctionName);
    }

    /**
     * 회원탈퇴 리스트 조회
     *
     * @return array
     */
    public function lists()
    {
        $this->wheres = $this->binders = [];
        if ($this->params['memId'] != '') {
            $this->db->bindParameterByLike('memId', $this->params, $this->binders, $this->wheres, $this->tableFunctionName, 'mh');
        }
        if ($this->params['hackType'] != '' && $this->params['hackType'] != 'done') {
            $this->db->bindParameter('hackType', $this->params, $this->binders, $this->wheres, $this->tableFunctionName, 'mh');
        }
        if ($this->params['rejoinFl'] != '' && $this->params['rejoinFl'] != 'done') {
            $this->db->bindParameter('rejoinFl', $this->params, $this->binders, $this->wheres, $this->tableFunctionName, 'mh');
        }
        $this->db->bindParameterByDateTimeRange('hackDt', $this->params, $this->binders, $this->wheres, $this->tableFunctionName, 'mh');
        if (StringUtils::strIsSet($this->params['mallSno'], '') !== '') {
            $this->wheres[] = 'mh.mallSno=?';
            $this->db->bind_param_push($this->binders, $this->fields['mallSno'], $this->params['mallSno']);
        }
        $this->db->strField = "mh.*, ma.managerNm";
        $this->db->strJoin = 'LEFT JOIN ' . DB_MANAGER . ' AS ma ON mh.managerNo = ma.sno ';
        $this->db->strWhere = implode(" and ", $this->wheres);
        $this->db->strOrder = $this->params['sort'];
        if (is_null($this->offset) === false && is_null($this->limit) === false) {
            $this->db->strLimit = ($this->offset - 1) * $this->limit . ', ' . $this->limit;
        }
        $query = $this->db->query_complete(true, true);
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_HACKOUT . ' AS mh ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->binders);

        return $data;
    }

    /**
     * 회원 탈퇴 / 삭제 관리 리스트 검색결과 카운트 함수
     * \Component\Member\HackOut\HackOutDAO::lists 실행되어야 조건을 참조할 수 있음.
     *
     * @return int
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function foundRowsByLists()
    {
        $query = $this->db->getQueryCompleteBackup(
            [
                'field' => 'COUNT(*) AS cnt',
                'limit' => null,
                'order' => null,
            ]
        );
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_HACKOUT . ' AS mh ' . implode(' ', $query);
        $cnt = $this->db->query_fetch($strSQL, $this->binders, false)['cnt'];
        StringUtils::strIsSet($cnt, 0);

        return $cnt;
    }

    /**
     * 회원 재가입 가능 여부 변경
     */
    public function updateReJoinFlag()
    {
        $arrBind = $arrUpdate = [];
        $arrUpdate[] = 'rejoinFl= ?';
        $this->db->bind_param_push($arrBind, 's', 'y');
        $strWhere = 'sno IN(' . implode(',', array_fill(0, count($this->params), '?')) . ')';

        foreach ($this->params as $hackOut) {
            $this->db->bind_param_push($arrBind, 'i', $hackOut['sno']);
        }

        $this->db->set_update_db(DB_MEMBER_HACKOUT, $arrUpdate, $strWhere, $arrBind);
    }

    /**
     * 탈퇴 상세정보 조회
     *
     * @param $sno
     *
     * @return array|object
     */
    public function getHackOutBySno($sno)
    {
        $this->binders = [];
        $this->wheres = [];

        $this->db->strField = "h.*, mng.managerNm,mng.isDelete";
        $this->db->strWhere = "h.sno=?";
        $this->db->strJoin = 'LEFT JOIN ' . DB_MANAGER . ' AS mng ON h.managerNo = mng.sno';
        $this->db->bind_param_push($this->binders, 'i', $sno);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_HACKOUT . ' as h ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->binders, false);

        return $data;
    }

    /**
     * 탈퇴 상세정보 조회
     *
     * @param $memberId
     *
     * @return array|object
     */
    public function getHackOutByMemberId($memberId)
    {
        $this->binders = [];
        $this->wheres = [];

        $this->db->strField = "h.*";
        $this->db->strWhere = "h.memId=?";
        $this->db->strOrder = 'h.hackDt DESC';
        $this->db->strLimit = '0, 1';
        $this->db->bind_param_push($this->binders, 's', $memberId);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_HACKOUT . ' as h ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->binders, false);

        return $data;
    }

    /**
     * 탈퇴 상세정보 조회
     *
     * @return array|null|object
     */
    public function getHackOutByParams()
    {
        $arrWhere = [
            'memNo',
            'memId',
            'dupeinfo',
        ];
        $arrData = [
            $this->params['memNo'],
            $this->params['memId'],
            $this->params['dupeinfo'],
        ];
        $hackout = $this->getDataByTable(DB_MEMBER_HACKOUT, $arrData, $arrWhere);

        return $hackout;
    }

    /**
     * 회원정보 조회
     *
     * @param $params
     *
     * @return array|null|object
     */
    public function getMember($params)
    {
        return $this->getDataByTable(DB_MEMBER, array_values($params), array_keys($params));
    }

    /**
     * 탈퇴정보 삭제
     *
     * @param $sno
     */
    public function deleteHackOutBySno($sno)
    {
        $arrBind = [
            'i',
            $sno,
        ];
        $this->db->set_delete_db(DB_MEMBER_HACKOUT, 'sno = ?', $arrBind);
    }

    /**
     * 탈퇴정보 추가
     *
     */
    public function insertHackOut()
    {
        $arrData['memNo'] = $this->params['memNo'];
        $arrData['memId'] = $this->params['memId'];
        $arrData['memNm'] = $this->params['memNm'];
        $arrData['dupeinfo'] = $this->params['dupeinfo'];


        $arrBind = $this->db->get_binding(DBTableField::tableMemberHackout(), $this->params, 'insert', array_keys($this->params));
        $this->db->set_insert_db(DB_MEMBER_HACKOUT, $arrBind['param'], $arrBind['bind'], 'y');
    }

    /**
     * 탈퇴정보 추가
     *
     */
    public function insertHackOutByParams()
    {
        $arrBind = $this->db->get_binding(DBTableField::tableMemberHackout(), $this->params, 'insert', array_keys($this->params));
        $this->db->set_insert_db(DB_MEMBER_HACKOUT, $arrBind['param'], $arrBind['bind'], 'y');
    }

    /**
     * 탈퇴정보 추가 후 탈퇴한 아이디를 추천한 회원의 추천인 아이디 삭제처리 및 회원정보 삭제
     *
     * @throws Exception
     */
    public function insertHackOutWithDeleteMemberByParams()
    {
        $arrBind = $this->db->get_binding(DBTableField::tableMemberHackout(), $this->params, 'insert', array_keys($this->params));
        $this->db->set_insert_db(DB_MEMBER_HACKOUT, $arrBind['param'], $arrBind['bind'], 'y');

        $memberInfo = $this->getDataByTable(DB_MEMBER, $this->params['memId'], 'recommId', 'memNo', true);
        $arrMemNo = ArrayUtils::getSubArrayByKey($memberInfo, 'memNo');
        /** @var \Bundle\Component\Member\Member $member */
        $member = \App::load('Component\\Member\\Member');
        if (count($arrMemNo) > 0) {
            // 탈퇴한 아이디를 추천한 회원의 추천인 아이디 삭제
            $member->updateMemberByMembersNo($arrMemNo, ['recommId'], ['']);
        }
        $member->delete(intval($this->params['memNo']));
    }

    /**
     * 탈퇴정보 수정
     *
     */
    public function updateHackOut()
    {
        $arrBind = $this->db->get_binding(DBTableField::tableMemberHackout(), $this->params, 'update', array_keys($this->params));
        $this->db->bind_param_push($arrBind['bind'], 'i', $this->params['sno']);
        $this->db->set_update_db(DB_MEMBER_HACKOUT, $arrBind['param'], 'sno=?', $arrBind['bind'], false);
    }

    /**
     * @deprecated DAO 에서는 트랜잭션 사용을 안하도록 할 예정
     */
    public function deleteMember()
    {
        try {
            $this->db->begin_tran();
            $memberInfo = $this->getDataByTable(DB_MEMBER, $this->params['memId'], 'recommId', 'memNo', true);
            $arrMemNo = ArrayUtils::getSubArrayByKey($memberInfo, 'memNo');
            /** @var \Bundle\Component\Member\Member $member */
            $member = \App::load('Component\\Member\\Member');
            if (count($arrMemNo) > 0) {
                // 탈퇴한 아이디를 추천한 회원의 추천인 아이디 삭제
                $member->updateMemberByMembersNo($arrMemNo, ['recommId'], ['']);
            }

            $member->delete(intval($this->params['memNo']));
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
        }
    }

    /**
     * 재가입 대상 조회
     *
     * @param null $date
     *
     * @return array|null|object
     */
    public function getReJoin($date = null)
    {
        if ($date) {
            $sqlDate = $date;
        } else {
            $sqlDate = 'DATE(NOW())';
        }
        $where = 'rejoinFl=\'n\'';
        if ($this->params['rejoinFl'] == 'y') {
            $where .= ' AND hackDt < (' . $sqlDate . ' - INTERVAL ' . $this->params['rejoin'] . ' DAY)';
        }

        return $this->db->getData(DB_MEMBER_HACKOUT, null, $where, '*', true);
    }

    /**
     * 일별 탈퇴회원 수 조회
     *
     * @param array $search
     *
     * @return array|object
     */
    public function selectHackOutMemberCountByDay(array $search)
    {
        $bind = [];
        $this->db->strField = 'DATE_FORMAT(hackDt, \'%Y-%m-%d\') AS hackOutDate, COUNT(*) AS hackOutCount';
        $this->db->strWhere = '(? <= hackDt AND ? >= hackDt)';
        $this->db->strGroup = 'hackOutDate';
        $this->db->strOrder = 'hackOutDate ASC';
        $this->db->bind_param_push($bind, 's', $search[0] . ' 00:00:00');
        $this->db->bind_param_push($bind, 's', $search[1] . ' 23:59:59');
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_HACKOUT . implode(' ', $query);
        $resultSet = $this->db->query_fetch($strSQL, $bind);

        return $resultSet;
    }

    /**
     * @param array $params
     *
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * 회원탈퇴 리스트 조회 동적 조건 설정
     * @deprecated 2016-12-20 yjwee 삭제될 함수입니다.
     */
    private function _bindParameterByLists()
    {
        $this->binders = [];
        $this->wheres = [];
        if ($this->params['memId'] != '') {
            $this->db->bindParameterByLike('memId', $this->params, $this->binders, $this->wheres, $this->tableFunctionName, 'mh');
        }

        if ($this->params['hackType'] != '' && $this->params['hackType'] != 'done') {
            $this->db->bindParameter('hackType', $this->params, $this->binders, $this->wheres, $this->tableFunctionName, 'mh');
        }

        if ($this->params['rejoinFl'] != '' && $this->params['rejoinFl'] != 'done') {
            $this->db->bindParameter('rejoinFl', $this->params, $this->binders, $this->wheres, $this->tableFunctionName, 'mh');
        }

        $this->db->bindParameterByDateTimeRange('hackDt', $this->params, $this->binders, $this->wheres, $this->tableFunctionName, 'mh');
    }

    /**
     * 쿼리 바인딩 배열, 조건절 배열 초기화
     * @deprecated 2016-12-20 yjwee 삭제될 함수입니다.
     */
    private function _initBindParameter()
    {
        $this->binders = [];
        $this->wheres = [];
    }
}
