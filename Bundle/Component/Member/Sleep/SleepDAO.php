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

namespace Bundle\Component\Member\Sleep;


use Component\Database\DBTableField;
use Framework\Database\DBTool;
use Framework\Utility\DateTimeUtils;

/**
 * 휴면회원 DAO 클래스
 * @package Bundle\Component\Member\Sleep
 * @author  yjwee
 */
class SleepDAO extends \Component\AbstractComponent
{
    public function __construct(DBTool $db = null)
    {
        parent::__construct($db);
        $this->tableFunctionName = 'tableMemberSleep';
    }

    /**
     * 휴면회원 전체 삭제 함수
     */
    public function deleteAllSleep()
    {
        $this->db->set_delete_db(DB_MEMBER_SLEEP, '1=1');
    }

    /**
     * 휴면회원 데이터 추가
     *
     * @param $sleepInfo
     */
    public function insertSleep($sleepInfo)
    {
        $arrBind = $this->db->get_binding(DBTableField::tableMemberSleep(), $sleepInfo, 'insert', array_keys($sleepInfo));
        $this->db->set_insert_db(DB_MEMBER_SLEEP, $arrBind['param'], $arrBind['bind'], 'y');
    }

    /**
     * 휴면번호 해당 휴면정보 삭제
     *
     * @param integer $sleepNo 휴면번호
     */
    public function deleteSleepBySleepNo($sleepNo)
    {
        $fieldInfo = DBTableField::getBindField('tableMemberSleep', ['sleepNo']);
        $arrBind['where'] = 'sleepNo' . ' = ?';
        $this->db->bind_param_push($arrBind['bind'], $fieldInfo[0]['typ'], $sleepNo);
        $this->db->set_delete_db(DB_MEMBER_SLEEP, $arrBind['where'], $arrBind['bind']);
    }

    /**
     * 휴면회원 해제로 인한 회원정보 수정
     *
     * @param array $wake
     */
    public function updateMemberByWake(array $wake)
    {
        $wake['sleepFl'] = 'n';
        $wake['sleepMailFl'] = 'n';
        $wake['sleepSmsFl'] = 'n';
        $wake['lastLoginDt'] = DateTimeUtils::dateFormat('Y-m-d G:i:s', 'now');
        $wake['sleepWakeDt'] = DateTimeUtils::dateFormat('Y-m-d G:i:s', 'now');
        $arrBind = $this->db->get_binding(DBTableField::tableMember(), $wake, 'update', array_keys($wake));
        $this->db->bind_param_push($arrBind['bind'], 'i', $wake['memNo']);
        $this->db->set_update_db(DB_MEMBER, $arrBind['param'], 'memNo = ?', $arrBind['bind']);
    }

    /**
     * 휴면회원 처리로 인한 회원정보 수정
     *
     * @param array $decryptInfo
     * @param       $memberNo
     */
    public function updateMemberByDecrypt(array $decryptInfo, $memberNo)
    {
        $decryptInfo['sleepFl'] = 'n';
        $decryptInfo['sleepMailFl'] = 'n';
        $decryptInfo['sleepSmsFl'] = 'n';
        $decryptInfo['lastLoginDt'] = DateTimeUtils::dateFormat('Y-m-d G:i:s', 'now');
        $decryptInfo['sleepWakeDt'] = DateTimeUtils::dateFormat('Y-m-d G:i:s', 'now');
        $arrBind = $this->db->get_binding(DBTableField::tableMember(), $decryptInfo, 'update', array_keys($decryptInfo));
        $this->db->bind_param_push($arrBind['bind'], 'i', $memberNo);
        $this->db->set_update_db(DB_MEMBER, $arrBind['param'], 'memNo = ?', $arrBind['bind']);
    }

    /**
     * 휴면회원 정보 조회
     *
     * @param        $data
     * @param string $field
     *
     * @return array|object
     */
    public function select($data, $field = 'sleepNo')
    {
        if (is_array($data)) {
            $arrBind['where'] = $field . ' IN(' . implode(',', array_fill(0, count($data), '?')) . ')';
            foreach ($data as $no) {
                $this->db->bind_param_push($arrBind['bind'], 's', $no);
            }
        } else {
            $arrBind['where'] = $field . ' = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $data);
        }

        $this->db->strField = '*';
        $this->db->strWhere = $arrBind['where'];

        $query = $this->db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_SLEEP . ' ' . implode(' ', $query);
        $result = $this->db->query_fetch($strSQL, $arrBind['bind']);

        return $result;
    }

    /**
     * 휴면회원 삭제
     *
     * @param        $data
     * @param string $field
     *
     * @return bool|int
     */
    public function deleteSleep($data, $field = 'sleepNo')
    {
        $fieldInfo = DBTableField::getBindField('tableMemberSleep', [$field]);
        if (is_array($data)) {
            $arrBind['where'] = $field . ' IN(' . implode(',', array_fill(0, count($data), '?')) . ')';
            foreach ($data as $no) {
                $this->db->bind_param_push($arrBind['bind'], $fieldInfo[0]['typ'], $no);
            }
        } else {
            $arrBind['where'] = $field . ' = ?';
            $this->db->bind_param_push($arrBind['bind'], $fieldInfo[0]['typ'], $data);
        }
        $return = $this->db->set_delete_db(DB_MEMBER_SLEEP, $arrBind['where'], $arrBind['bind']);

        return $return;
    }

    /**
     * @deprecated
     * @uses updateMemberSleepGuideFlag
     *
     * @param $memNo
     *
     * @return bool
     */
    public function updateSleepMailFlag($memNo)
    {
        $arrInclude[] = 'sleepMailFl';
        $arrData['sleepMailFl'] = 'y';
        $arrBind = $this->db->get_binding(DBTableField::tableMember(), $arrData, 'update', $arrInclude);

        if (is_array($memNo)) {
            $arrBind['where'] = 'memNo IN(' . implode(',', array_fill(0, count($memNo), '?')) . ')';
            foreach ($memNo as $no) {
                $this->db->bind_param_push($arrBind['bind'], 'i', $no);
            }
        } else {
            $arrBind['where'] = 'memNo = ?';
            $this->db->bind_param_push($arrBind['bind'], 'i', $memNo);
        }

        return $this->db->set_update_db(DB_MEMBER, $arrBind['param'], $arrBind['where'], $arrBind['bind']);
    }

    /**
     * 휴면회원정보 수정
     * @deprecated 2017-02-06 yjwee MySQL 연결시간 초과 이슈 수정을 위해 사용하지 않습니다.
     *
     * @param integer|array $memberNo     회원번호
     * @param array         $updateValues 변경할 정보
     *
     * @return bool
     */
    public function updateMemberSleepGuideFlag($memberNo, array $updateValues)
    {
        $arrBind = $this->db->get_binding(DBTableField::tableMember(), $updateValues, 'update', array_keys($updateValues));
        if (is_array($memberNo)) {
            $arrBind['where'] = 'memNo IN(' . implode(',', array_fill(0, count($memberNo), '?')) . ')';
            foreach ($memberNo as $no) {
                $this->db->bind_param_push($arrBind['bind'], 'i', $no);
            }
        } else {
            $arrBind['where'] = 'memNo = ?';
            $this->db->bind_param_push($arrBind['bind'], 'i', $memberNo);
        }

        return $this->db->set_update_db(DB_MEMBER, $arrBind['param'], $arrBind['where'], $arrBind['bind']);
    }

    /**
     * 휴면회원정보 암호화 한 뒤 기존 정보 기본 정보로 수정하는 함수
     *
     * @param $sleepInfo
     * @param $arrInclude
     */
    public function updateMemberByEncrypt($sleepInfo, $arrInclude)
    {
        $arrInclude[] = 'sleepFl';
        $arrExclude = [
            'memNo',
            'memId',
            'memPw',
            'appFl',
        ];
        $arrData = [];
        DBTableField::setDefaultData('tableMember', $arrData);
        $arrData['sleepFl'] = 'y';
        $arrBind = $this->db->get_binding(DBTableField::tableMember(), $arrData, 'update', $arrInclude, $arrExclude);

        $this->db->bind_param_push($arrBind['bind'], 'i', $sleepInfo['memNo']);
        $this->db->set_update_db(DB_MEMBER, $arrBind['param'], 'memNo = ?', $arrBind['bind']);
    }

    /**
     * 휴면회원안내 SMS 발송 대상자 조회
     *
     * @param int $expirationFl
     *
     * @return array|null|object
     */
    public function selectSleepSmsReceiver($expirationFl = 1)
    {
        $period = DateTimeUtils::dateFormat('Y-m-d', '-' . ((SleepService::SLEEP_PERIOD * $expirationFl) - 30) . ' day');
        $where = 'IF(lastLoginDt = \'0000-00-00 00:00:00\' OR lastLoginDt IS NULL, DATE_FORMAT(entryDt, \'%Y-%m-%d\') <= \'' . $period . '\' , DATE_FORMAT(lastLoginDt, \'%Y-%m-%d\') <= \'' . $period . '\')';
        $where .= ' AND sleepFl=\'n\' AND sleepSmsFl=\'n\' AND expirationFl=\'' . $expirationFl . '\' AND (cellPhone!=\'\' AND cellPhone IS NOT NULL)';

        return $this->getDataByTable(DB_MEMBER, null, $where, '*', true);
    }

    /**
     * 휴면회원안내 메일 발송 대상자 조회
     *
     * @param int $expirationFl
     *
     * @return array|null|object
     *
     * ex) where = IF(lastLoginDt = '0000-00-00 00:00:00' OR lastLoginDt IS NULL, DATE_FORMAT(entryDt, '%Y-%m-%d') <= '2012-03-06' ,
     *             DATE_FORMAT(lastLoginDt, '%Y-%m-%d') <= '2012-03-06') AND sleepFl='n' AND sleepMailFl='n' AND expirationFl='5'
     */
    public function selectSleepMailReceiver($expirationFl = 1)
    {
        $period = DateTimeUtils::dateFormat('Y-m-d', '-' . ((SleepService::SLEEP_PERIOD * $expirationFl) - 50) . ' day');
        $period .= ' 23:59:59';
        $where[] = 'IF(lastLoginDt = \'0000-00-00 00:00:00\' OR lastLoginDt IS NULL, entryDt <= \'' . $period . '\' , lastLoginDt <= \'' . $period . '\')';
        $where[] = 'sleepFl=\'n\' AND sleepMailFl=\'n\' AND expirationFl=\'' . $expirationFl . '\' AND (email!=\'\' AND email IS NOT NULL)';
        $this->db->strField = '*';
        $this->db->strWhere = implode(' AND ', $where);
        $this->db->strOrder = 'lastLoginDt ASC';
        $this->db->strLimit = '2000';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' ' . implode(' ', $query);
        return $this->db->query_fetch($strSQL, false);
    }

    /**
     * 휴면회원 조회
     *
     * @param int $expirationFl 개인정보제공기간
     *
     * @return array|null|object
     */
    public function selectSleepMember($expirationFl = 1)
    {
        $period = DateTimeUtils::dateFormat('Y-m-d', '-' . (SleepService::SLEEP_PERIOD * $expirationFl) . ' day');
        $where = 'IF(lastLoginDt = \'0000-00-00 00:00:00\' OR lastLoginDt IS NULL, DATE_FORMAT(entryDt, \'%Y-%m-%d\') <= \'' . $period . '\' , DATE_FORMAT(lastLoginDt, \'%Y-%m-%d\') <= \'' . $period . '\')';
        $where .= ' AND sleepFl=\'n\' AND expirationFl=\'' . $expirationFl . '\'';

        return $this->getDataByTable(DB_MEMBER, null, $where, '*', true);
    }

    /**
     * 로그인 기간, 회원번호 를 이용한 휴면회원 대상 조회
     *
     * @param integer $memberNo 회원번호
     * @param integer $day      로그인 안한 기간
     *
     * @return array|object
     */
    public function selectSleepMemberByMemberNo($memberNo, $day)
    {
        $arrBind['where'] = [];
        array_push($arrBind['where'], 'memNo = ?');
        $this->db->bind_param_push($arrBind['bind'], 'i', $memberNo);
        array_push($arrBind['where'], 'DATE_FORMAT(lastLoginDt, \'%Y-%m-%d\') <= ?');
        array_push($arrBind['where'], 'sleepFl != \'y\'');

        $this->db->strField = implode(',', DBTableField::setTableField('tableMember'));
        $this->db->strWhere = implode(' AND ', $arrBind['where']);
        $this->db->bind_param_push($arrBind['bind'], 's', DateTimeUtils::dateFormat('Y-m-d', '-' . $day . ' day'));
        $query = $this->db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' ' . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $arrBind['bind']);
    }

    /**
     * 일별 휴면회원 수 조회
     *
     * @param array $search
     *
     * @return array|object
     */
    public function selectSleepMemberCountByDay(array $search)
    {
        $bind = [];
        $this->db->strField = 'DATE_FORMAT(sleepDt, \'%Y-%m-%d\') AS sleepDate, COUNT(*) AS sleepCount';
        $this->db->strWhere = '(? <= sleepDt AND ? >= sleepDt)';
        $this->db->strGroup = 'sleepDate';
        $this->db->strOrder = 'sleepDate ASC';
        $this->db->bind_param_push($bind, 's', $search[0] . ' 00:00:00');
        $this->db->bind_param_push($bind, 's', $search[1] . ' 23:59:59');
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_SLEEP . implode(' ', $query);
        $resultSet = $this->db->query_fetch($strSQL, $bind);

        return $resultSet;
    }

    /**
     * 관리자 휴면회원 조회
     *
     * @param array $params
     *
     * @return array|object
     */
    public function selectListBySearch(array $params)
    {
        $arrBind = $arrWhere = [];
        $this->db->bindParameterByKeyword(\Component\Member\MemberSleep::COMBINE_SEARCH, $params, $arrBind, $arrWhere, 'tableMember', 'ms');

        $this->db->bindParameterByDateTimeRange('sleepDt', $params, $arrBind, $arrWhere, 'tableMemberSleep', 'ms');
        if ($params['mallSno']) {
            $arrWhere[] = 'm.mallSno = ?';
            $this->db->bind_param_push($arrBind, 'i', $params['mallSno']);
        }
        $arrJoin[] = ' LEFT JOIN ' . DB_MEMBER . ' AS m ON ms.memNo = m.memNo ';
        $arrJoin[] = ' LEFT JOIN ' . DB_MEMBER_COUPON . ' AS mc ON ms.memNo = mc.memNo';
        $arrJoin[] = ' AND memberCouponState = "y"';
        $arrJoin[] = ' AND memberCouponStartDate <= now()';
        $arrJoin[] = ' AND memberCouponEndDate >= now()';
        $this->db->strField =  implode(',', DBTableField::setTableField('tableMemberSleep', null, ['encryptData'], 'ms')) . ', m.mallSno, sum(case when mc.memNo is null then 0 else 1 end) as cnt';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = 'ms.sleepNo, ms.sleepDt, ms.memNo, ms.memId, ms.memNm, ms.mileage, ms.deposit, ms.groupSno, ms.email, ms.cellPhone, ms.phone, ms.entryDt, m.mallSno';
        $this->db->strOrder = 'sleepDt DESC, sleepNo DESC';
        $this->db->strJoin = implode('', $arrJoin);
        if (isset($params['offset']) && isset($params['limit'])) {
            $this->db->strLimit = ($params['offset'] - 1) * $params['limit'] . ', ' . $params['limit'];
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_SLEEP . ' AS ms ' . implode(' ', $query);
        $resultSet = $this->db->query_fetch($strSQL, $arrBind);

        return $resultSet;
    }

    /**
     * 관리자 휴면회원 조회 검색개수
     *
     * @param array $params
     *
     * @return mixed
     */
    public function selectCountListBySearch(array $params)
    {
        $arrBind = $arrWhere = [];
        $this->db->bindParameterByKeyword(\Component\Member\MemberSleep::COMBINE_SEARCH, $params, $arrBind, $arrWhere, 'tableMember', 'ms');
        $this->db->bindParameterByDateTimeRange('sleepDt', $params, $arrBind, $arrWhere, 'tableMemberSleep', 'ms');
        if ($params['mallSno'] !== '') {
            $arrWhere[] = 'm.mallSno = ?';
            $this->db->bind_param_push($arrBind, 'i', $params['mallSno']);
        }
        $this->db->strField =  'count(*) as cnt ';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'sleepDt DESC, sleepNo DESC';
        $this->db->strJoin = ' LEFT JOIN ' . DB_MEMBER . ' AS m ON ms.memNo = m.memNo ';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_SLEEP . ' AS ms ' . implode(' ', $query);
        $resultSet = $this->db->query_fetch($strSQL, $arrBind,false);

        return $resultSet['cnt'];
    }
}
