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

namespace Bundle\Component\Member;

use App;
use Component\Board\Board;
use Component\Database\DBTableField;
use Component\Mail\MailMimeAuto;
use Framework\Database\DBTool;
use Framework\Object\SingletonTrait;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use Framework\Security\Digester;
use Framework\Utility\GodoUtils;
use Exception;

/**
 * 회원 테이블 데이터 처리 클래스
 * @package Bundle\Component\Member
 * @author  yjwee
 * @method static MemberDAO getInstance
 */
class MemberDAO
{
    use SingletonTrait;

    /** @var \Framework\Database\DBTool $db */
    protected $db;
    protected $fields;

    public function __construct(DBTool $db = null, array $config = [])
    {
        $this->db = $db ?? \App::getInstance('DB');
        if (isset($config['tableMemberFieldTypes']) && \is_array($config['tableMemberFieldTypes'])) {
            $this->fields = $config['tableMemberFieldTypes'];
        } else {
            $this->fields = DBTableField::getFieldTypes('tableMember');
        }
    }

    /**
     * 회원 정보와 등급, SNS 조회
     *
     * @param $value
     * @param $column
     *
     * @return array|object
     */
    public function selectMemberWithGroup($value, $column)
    {
        $this->db->strField = 'm.memNo, m.memId, m.memPw, m.groupSno, m.memNm, m.nickNm, m.appFl, m.sleepFl, m.maillingFl, m.smsFl, m.saleCnt, m.saleAmt, m.mallSno';
        $this->db->strField .= ', m.cellPhone, m.email, m.adultConfirmDt, m.adultFl, m.loginCnt, m.changePasswordDt, m.guidePasswordDt, m.loginLimit';
        $this->db->strField .= ', m.modDt AS mModDt, m.regDt AS mRegDt, ms.snsJoinFl, IF(ms.connectFl=\'y\', ms.snsTypeFl, \'\') AS snsTypeFl, ms.connectFl, ms.accessToken';
        $this->db->strField .= ', mg.groupNm, mg.groupSort, m.modDt AS memberModDt, m.regDt AS memberRegDt';
        $this->db->strJoin = ' LEFT JOIN ' . DB_MEMBER_GROUP . ' AS mg ON m.groupSno = mg.sno';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MEMBER_SNS . ' AS ms ON ms.memNo = m.memNo';
        $this->db->strWhere = 'm.' . $column . ' = ?';
        $this->db->bind_param_push($arrBind, $this->fields[$column], $value);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' AS m ' . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $arrBind, false);
    }

    /**
     * 회원 비밀번호 조회
     *
     * @param $memberNo
     *
     * @return array|object
     */
    public function selectPassword($memberNo)
    {
        $this->db->strField = 'memPw';
        $this->db->strWhere = 'memNo=?';
        $this->db->bind_param_push($bind, $this->fields['memNo'], $memberNo);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $bind, false);
    }

    /**
     * 내 정보 페이지에 보여질 회원 정보 조회
     *
     * @param $memberNo
     *
     * @return array|object
     */
    public function selectMyPage($memberNo)
    {
        $this->db->strField = 'm.*, ms.snsJoinFl, IF(ms.connectFl=\'y\', ms.snsTypeFl, \'\') AS snsTypeFl, ms.connectFl';
        $this->db->strJoin = ' LEFT JOIN ' . DB_MEMBER_SNS . ' AS ms ON m.memNo = ms.memNo';
        $this->db->strWhere = 'm.memNo=?';
        $this->db->bind_param_push($bind, $this->fields['memNo'], $memberNo);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' AS m ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $bind, false);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 회원 정보 수정
     *
     * @param $member
     * @param $include
     * @param $exclude
     *
     * @return int
     */
    public function updateMember($member, $include, $exclude)
    {
        //@formatter:off
        $memberInclude = array_merge(array_keys($member), $include);
        $memberExclude = array_merge(['memNo', 'memId',], $exclude);
        //@formatter:on
        $arrBind = $this->db->get_binding(DBTableField::tableMember(), $member, 'update', $memberInclude, $memberExclude);
        $this->db->bind_param_push($arrBind['bind'], 'i', $member['memNo']);
        $this->db->set_update_db(DB_MEMBER, $arrBind['param'], 'memNo = ?', $arrBind['bind'], false);

        return $this->db->affected_rows();
    }

    /**
     * 회원정보를 수정하는 함수. 조건컬럼 과 조건값 에 해당하는 회원의 정보를 수정정보를 이용하여 변경한다.
     *
     * $params = ['columnName'=>'value'];
     *
     * @param array  $params 수정정보
     * @param array  $clause 조건값
     * @param string $column 조건컬럼
     *
     * @return bool|int
     */
    public function updateInClause(array $params, array $clause, $column = 'memNo')
    {
        $bind = $update = [];
        foreach ($params as $key => $param) {
            $update[] = $key . '=?';
            $this->db->bind_param_push($bind, $this->fields[$key], $param);
        }
        $strWhere = $column . ' IN (' . implode(',', array_fill(0, count($clause), '?')) . ')';
        foreach ($clause as $item) {
            $this->db->bind_param_push($bind, $this->fields[$column], $item);
        }
        $affectedRows = $this->db->set_update_db(DB_MEMBER, $update, $strWhere, $bind);

        return $affectedRows;
    }

    /**
     * 회원 마일리지 조회
     *
     * @param $memberId
     *
     * @return array|object
     */
    public function selectMileageByMemberId($memberId)
    {
        $this->db->strField = '*';
        $this->db->strWhere = 'memId=? AND sleepFl=\'n\'';
        $this->db->bind_param_push($bind, $this->fields['memId'], $memberId);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $bind, false);
    }

    /**
     * 회원 예치금 조회
     *
     * @param $memberId
     *
     * @return array|object
     */
    public function selectDepositByMemberId($memberId)
    {
        $this->db->strField = '*';
        $this->db->strWhere = 'memId=? AND sleepFl=\'n\'';
        $this->db->bind_param_push($bind, $this->fields['memId'], $memberId);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $bind, false);
    }

    /**
     * 회원 테이블에 입력될 정보
     *
     * @param array $member
     *
     * @return int|string
     */
    public function insertMember(array $member)
    {
        // 아이디 중복여부 체크
        if (\Component\Member\Util\MemberUtil::getInstance()->overlapMemId($member['memId'])) {
            throw new Exception(sprintf(__('%s는 이미 등록된 아이디입니다'), $member['memId']));
        }

        if (isset($member['memPw']) && strlen($member['memPw']) < 17) {
            if(GodoUtils::sha256Fl()) {
                $member['memPw'] = Digester::digest($member['memPw']);
            } else {
                $member['memPw'] = App::getInstance('password')->hash($member['memPw']);
            }
        }
        // 평생회원 이벤트
        if ($member['expirationFl'] === '999') {
            $member['lifeMemberConversionDt'] = date('Y-m-d H:i:s');
        }
        $arrBind = $this->db->get_binding(DBTableField::tableMember(), $member, 'insert', array_keys($member));
        $this->db->set_insert_db(DB_MEMBER, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);

        return $this->db->insert_id();
    }

    /**
     * @deprecated
     * @uses insertMemberByThirdParty
     *
     * @param array $member
     *
     * @return int|string
     */
    public function insertMemberByPayco(array $member)
    {
        return $this->insertMemberByThirdParty($member);
    }

    /**
     * 회원 테이블에 입력될 정보, 외부 서비스 정보를 이용한 가입이기 때문에 비밀번호는 제외
     *
     * @param array $member
     *
     * @return int|string
     */
    public function insertMemberByThirdParty(array $member)
    {
        // 평생회원 이벤트
        if ($member['expirationFl'] === '999') {
            $member['lifeMemberConversionDt'] = date('Y-m-d H:i:s');
        }

        $arrBind = $this->db->get_binding(DBTableField::tableMember(), $member, 'insert', array_keys($member), ['memPw']);
        $this->db->set_insert_db(DB_MEMBER, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);

        return $this->db->insert_id();
    }

    /**
     * CRM 화면에 보여질 회원 정보 조회
     *
     * @param $memberNo
     *
     * @return array|object
     */
    public function selectMemberCrm($memberNo)
    {
        $this->db->strField = 'm.*, ms.snsJoinFl, IF(ms.connectFl=\'y\', ms.snsTypeFl, \'\') AS snsTypeFl, ms.connectFl';
        $this->db->strField .= ', COUNT(DISTINCT cc.sno) AS counselCount';
        $this->db->strField .= ', COUNT(DISTINCT bqa.sno) AS questionCount, (SELECT COUNT(1) FROM es_bd_qa WHERE memNo=' . $memberNo . ' AND isDelete=\'n\' AND replyStatus in (1,2)) as noAnswerCount';
        $this->db->strField .= ', COUNT(DISTINCT mc.memberCouponNo) AS memberCouponCount';
        $this->db->strJoin = ' LEFT JOIN ' . DB_CRM_COUNSEL . ' AS cc ON m.memNo = cc.memNo';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_BD_ . Board::BASIC_QA_ID . ' AS bqa ON (m.memNo = bqa.memNo) OR (m.memNo = bqa.parentSno)';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MEMBER_COUPON . ' AS mc ON m.memNo = mc.memNo AND (mc.memberCouponState=\'y\' AND mc.memberCouponStartDate <= NOW() AND mc.memberCouponEndDate >= NOW())';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MEMBER_SNS . ' AS ms ON m.memNo = ms.memNo';
        $this->db->strWhere = 'm.memNo=? ';
        $this->db->bind_param_push($bind, $this->fields['memNo'], $memberNo);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' AS m ' . implode(' ', $query);
        $resultSet = $this->db->query_fetch($strSQL, $bind, false);

        return $resultSet;
    }

    /**
     * @deprecated 2016-11-17 yjwee 제거될 수도 있는 함수로 사용을 권장하지 않습니다. @uses 에 안내된 함수를 사용하세요.
     * @uses       selectMemberByOne
     * 회원번호를 기준으로 회원 정보 조회
     *
     * @param $memberNo
     *
     * @return array|object
     */
    public function selectMember($memberNo)
    {
        return $this->selectMemberByOne($memberNo);
    }

    /**
     * 단일 항목 회원 조회 추가적인 바인딩된 데이터로 조건을 걸어야 하는 경우
     * 새로운 함수를 만들어서 사용하시기 바랍니다.
     *
     * @param integer|string $value      조건 값
     * @param string         $columnName 조회 기준 컬럼
     *
     * @return array
     */
    public function selectMemberByOne($value, $columnName = 'memNo')
    {
        // 해당펑션은 검색결과를가 하나만 나와야하는것을 기대하고 사용하는 펑션이기에 검색값이 없이 오면 안됨
        if ($value == '' || $value == null) {
            return null;
        }
        $this->db->strField = '*';
        $this->db->strWhere = $columnName . '=?';
        $this->db->bind_param_push($bind, $this->fields[$columnName], $value);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . implode(' ', $query);
        $resultSet = $this->db->query_fetch($strSQL, $bind, false);

        return $resultSet;
    }

    /**
     * 사용자 정보 수정 할때 본인 정보는 제외한 나머지 정보와 중복체크
     *
     * @param integer|string $value      조건 값
     * @param string         $columnName 조회 기준 컬럼
     * @param string         $memNo      본인 회원 번호
     *
     * @return array
     */
    public function selectModifyMemberByOne($value, $columnName = 'memNo', $memId)
    {
        $this->db->strField = '*';
        $this->db->strWhere = $columnName . '=? AND memId<>?';
        $this->db->bind_param_push($bind, $this->fields[$columnName], $value);
        $this->db->bind_param_push($bind, $this->fields['memId'], $memId);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . implode(' ', $query);
        $resultSet = $this->db->query_fetch($strSQL, $bind, false);

        return $resultSet;
    }

    /**
     * 다수의 회원 조회 추가적인 바인딩된 데이터로 조건을 걸어야 하는 경우
     * 새로운 함수를 만들어서 사용하시기 바랍니다.
     *
     * @param integer|string $value      조건 값
     * @param string         $columnName 조회 기준 컬럼
     *
     * @return array
     */
    public function selectByAll($value, $columnName = 'memNo')
    {
        $this->db->strField = '*';
        $this->db->strWhere = $columnName . '=?';
        $this->db->bind_param_push($bind, $this->fields[$columnName], $value);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . implode(' ', $query);
        $resultSet = $this->db->query_fetch($strSQL, $bind, true);

        return $resultSet;
    }

    /**
     * 관리자 회원리스트 조회
     *
     * @param array $params
     * @param array $arrWhere
     * @param array $arrBind
     * @param null  $offset
     * @param null  $limit
     *
     * @deprecated  2016-11-17 yjwee 현재 사용하는 곳은 없으며 소스 개선 및 테스트 중 생성된 함수 입니다.
     *                        튜닝 보장으로 인해 남겨두는 것이니 사용하지 않는 것은 권장합니다.
     * @uses        MemberDAO::selectListBySearch
     *
     * @return array
     */
    public function selectAdminMemberList(array $params, array $arrWhere, array $arrBind, $offset = null, $limit = null)
    {
        $this->db->bindParameter('connectSns', $params, $arrBind, $arrWhere, 'tableMemberSns', 'ms', 'snsTypeFl');

        //@formatter:off
        $memberFields = ['memNo', 'memId', 'groupSno', 'memNm', 'nickNm', 'appFl', 'memberFl', 'smsFl', 'mileage', 'deposit', 'maillingFl', 'saleAmt', 'entryDt', 'lastLoginDt', 'loginCnt', 'sexFl', 'email', 'cellPhone'];
        //@formatter:on
        $this->db->strField = implode(', ', DBTableField::setTableField('tableMember', $memberFields, null, 'm'));
        $this->db->strField .= ', IF(ms.connectFl=\'y\', ms.snsTypeFl, \'\') AS snsTypeFl';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = gd_isset($params['sort'], 'entryDt desc');
        $this->db->strJoin = ' LEFT JOIN ' . DB_MEMBER_SNS . ' AS ms ON m.memNo = ms.memNo';
        if ($offset !== null && $limit !== null) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }

        $arrQuery = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . ' AS m ' . implode(' ', $arrQuery);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        unset($arrWhere, $arrBind, $arrExcludeField, $memberFields, $arrQuery);

        return $data;
    }

    /**
     * 회원 검색 조회 함수
     *
     * @param array $params 조회 조건 데이터 및 offset, limit 정보가 담긴 파라미터
     *                      offset, limit 에 값이 있으면 조회 조건에 추가된다.
     *
     * @return array
     */
    public function selectListBySearch(array $params)
    {
        $this->setQuerySearch($params, $arrBind);
        if ($params['offset'] !== null && $params['limit'] !== null) {
            $this->db->strLimit = ($params['offset'] - 1) * $params['limit'] . ', ' . $params['limit'];
        }
        $arrQuery = $this->db->query_complete();
        $strSQL = 'SELECT  ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . ' AS m ' . implode(' ', $arrQuery);

        return $this->db->query_fetch($strSQL, $arrBind);
    }

    /**
     * selectListBySearch 검색 카운트
     *
     * @param array $params
     *
     * @return int
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function countListBySearch(array $params)
    {
        $arrBind = [];
        $this->setQuerySearch($params, $arrBind);
        $this->db->strField = 'COUNT(*) AS cnt';
        $this->db->strOrder = $this->db->strLimit = null;
        $arrQuery = $this->db->query_complete();
        $strSQL = 'SELECT  ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . ' AS m ' . implode(' ', $arrQuery);
        $strSQL = 'SELECT COUNT(*) AS cnt FROM (' . $strSQL . ') AS m';

        return $this->db->query_fetch($strSQL, $arrBind, false)['cnt'];
    }

    public function selectListBySearchCount(array $params)
    {

        $arrBind = [];
        $this->setQuerySearch($params, $arrBind);

        $arrQuery = $this->db->query_complete();
        array_shift($arrQuery);
        $strSQL = 'SELECT  count(*) as cnt  FROM ' . DB_MEMBER . ' AS m ' . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($strSQL, $arrBind, false);

        return \count($resultSet);
    }

    /**
     * 회원 엑셀에서 사용하는 회원 리스트 조회
     *
     * @param array $params
     *
     * @return array
     */
    public function selectExcelMemberList(array $params)
    {
        $this->setQuerySearch($params, $arrBind);
        $arrExclude = [
            'mallSno',
            'memNo',
        ];
        $this->db->strField = 'm.*, ' . implode(', ', DBTableField::setTableField('tableMemberSns', null, $arrExclude));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' AS m ' . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $arrBind);
    }

    /**
     * 마지막 수신동의 일자가 기준일 이상이면서 조회일~기준일 까지 안내 메일, SMS를 받지 않은 회원 조회
     * 수신동의 일자는 회원정보 변경이력의 수신동의 변경 일자를 기준으로하며 이력이 없는 경우 가입일을 기준으로 한다.
     * 단, 가입일이 2014년 11월 29일 이전일 경우 수신동의일자를 2014년 11월 28일로 적용(수신동의 여부의 확인에 관한 특례)
     *
     * @param null $standardDt 동의일자와 비교될 기준일 특별한 경우가 아니면 데이터를 넘기지 않도록 한다. 기본은 -2 year +1 days
     *
     * @return array|object
     */
    public function selectMembersByReceiveNotification($standardDt = null)
    {
        if ($standardDt == null) {
            $standardDt = DateTimeUtils::dateFormat('Y-m-d', '-2 year +1 days');
        }
        $nowDt = DateTimeUtils::dateFormat('Y-m-d', 'now');
        $strFieldBySmsAgreementDt = 'IF(mh.updateColumn IS NULL, IF(m.entryDt < \'2014-11-29\', \'2014-11-28 00:00:00\', m.entryDt), MAX(mh.regDt)) AS smsAgreementDt';
        $strFieldByMailAgreementDt = 'IF(mh2.updateColumn IS NULL, IF(m.entryDt < \'2014-11-29\', \'2014-11-28 00:00:00\', m.entryDt), MAX(mh2.regDt)) AS mailAgreementDt';
        $strHaving = ' HAVING ((smsAgreementDt <= \'' . $standardDt . ' 23:59:59\' AND m.smsFl=\'y\')';
        $strHaving .= ' OR (mailAgreementDt <= \'' . $standardDt . ' 23:59:59\' AND m.maillingFl=\'y\'))';
        $strHaving .= ' AND notificationDt IS NULL';

        $this->db->strField = 'm.memNo, m.memId, m.memNm, m.groupSno, m.mileage, m.deposit, m.smsFl, m.maillingFl, m.cellPhone, m.email';
        $this->db->strField .= ', mh.updateColumn, mh2.updateColumn, ' . $strFieldBySmsAgreementDt . ', ' . $strFieldByMailAgreementDt;
        $this->db->strField .= ', mnl.regDt AS notificationDt';
        $this->db->strJoin = ' LEFT JOIN ' . DB_MEMBER_NOTIFICATION_LOG . ' AS mnl ON (m.memNo=mnl.memNo AND mnl.ReasonCode=\'' . MailMimeAuto::AGREEMENT2YPERIOD . '\' AND mnl.regDt >= \'' . $standardDt . ' 00:00:00\' AND mnl.regDt <= \'' . $nowDt . ' 23:59:59\')';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MEMBER_HISTORY . ' AS mh ON (m.memNo=mh.memNo AND mh.updateColumn=\'SMS수신동의\')';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MEMBER_HISTORY . ' AS mh2 ON (m.memNo=mh2.memNo AND mh2.updateColumn=\'메일수신동의\')';
        $this->db->strWhere = '((m.smsFl=\'y\' AND m.cellPhone IS NOT NULL AND m.cellPhone != \'\')';
        $this->db->strWhere .= ' OR (m.maillingFl=\'y\' AND m.email IS NOT NULL AND m.email != \'\'))';
        $this->db->strWhere .= ' AND m.sleepFl=\'n\'';
        $this->db->strGroup = 'm.memNo' . $strHaving;
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' AS m ' . implode(' ', $query);
        $result = $this->db->query_fetch($strSQL);

        return $result;
    }

    /**
     * 회원에게 메일,SMS 안내 메시지 전송 내역을 별도로 저장하는 함수(전송을 위한 데이터 중 회원번호가 있어야 저장됨)
     *
     * @param array $log
     *
     * @return int|string
     */
    public function insertNotificationLog(array $log)
    {
        $bind = $this->db->get_binding(DBTableField::tableMemberNotificationLog(), $log, 'insert', array_keys($log));
        $this->db->set_insert_db(DB_MEMBER_NOTIFICATION_LOG, $bind['param'], $bind['bind'], 'y');

        return $this->db->insert_id();
    }

    /**
     * 회원의 마지막 수신동의 재안내 내역을 조회
     *
     * @param $memberNo
     *
     * @return array|object
     */
    public function selectLastAgreementNotificationByMember($memberNo)
    {
        $this->db->strField = 'memNo, type, MAX(regDt) AS lastNotificationDt';
        $this->db->strWhere = 'memNo=? AND type!=\'none\' AND reasonCode=\'' . MailMimeAuto::AGREEMENT2YPERIOD . '\'';
        $this->db->strGroup = 'type';
        $this->db->bind_param_push($bind, 'i', $memberNo);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_NOTIFICATION_LOG . implode(' ', $query);
        $result = $this->db->query_fetch($strSQL, $bind);

        return $result;
    }

    /**
     * 회원에게 전송된 알림 내역을 조회
     *
     * @param array $param
     *
     * @return array
     */
    public function selectMemberNotification($param = [])
    {
        $db = \App::getInstance('DB');
        StringUtils::strIsSet($param['dataArray'], true);
        $bind = $where = [];
        if (array_key_exists('memNo', $param)) {
            $where[] = 'memNo = ?';
            $db->bind_param_push($bind, 'i', $param['memNo']);
        }
        if (array_key_exists('reasonCode', $param)) {
            $where[] = 'reasonCode = ?';
            $db->bind_param_push($bind, 's', $param['reasonCode']);
        }
        if (array_key_exists('type', $param)) {
            $where[] = 'type = ?';
            $db->bind_param_push($bind, 's', $param['type']);
        }
        $db->strField = '*';
        $db->strWhere = implode(' AND ', $where);
        $db->strOrder = 'sno DESC';
        $query = $db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_NOTIFICATION_LOG . implode(' ', $query);
        $result = $db->query_fetch($strSQL, $bind, $param['dataArray']);

        return $result;
    }

    /**
     * 회원정보 삭제 함수
     *
     * @param int|string $value  삭제될 회원 기준값
     * @param string     $column 삭제 기준 컬럼명
     *
     * @return bool|int
     */
    public function deleteByOne($value, $column = 'memNo')
    {
        $strWhere = $column . '=?';
        $this->db->bind_param_push($bind, $this->fields[$column], $value);
        $affectedRows = $this->db->set_delete_db(DB_MEMBER, $strWhere, $bind);

        return $affectedRows;
    }

    /**
     * @return DBTool
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param null|string $key
     *
     * @return array|string
     */
    public function getFields($key = null)
    {
        if ($key === null) {
            return $this->fields;
        }

        return $this->fields[$key];
    }

    /**
     * setQuerySearch
     *
     * @param $params
     * @param $arrBind
     */
    protected function setQuerySearch($params, &$arrBind)
    {
        $arrBind = $arrWhere = [];
        // 체크박스를 선택하여 조회하는 경우
        if ($params['chk'] && \is_array($params['chk'])) {
            $arrWhere[] = 'm.memNo IN (' . implode(',', $params['chk']) . ')';
        }
        // 검색제한(회원 일괄 관리)
        if (StringUtils::strIsSet($params['indicate']) === false) {
            $arrWhere[] = '0';
        }
        // 검색제한(회원등급평가(수동))
        if (StringUtils::strIsSet($params['groupValidDt']) === true) {
            $arrWhere[] = 'm.groupValidDt < now()';
        }
        // 메일이나 SMS 보내기에 따른 검색 설정
        if (isset($params['sendMode']) === true) {
            if ($params['sendMode'] === 'mail') {
                $arrWhere[] = '(m.email != \'\' AND m.email IS NOT NULL)';
            }
            if ($params['sendMode'] === 'sms') {
                $arrWhere[] = '(m.cellPhone != \'\' AND m.cellPhone IS NOT NULL)';
            }
        }
        //수기주문에서의 회원검색은 승인회원만 노출
        if ($params['loadPageType'] === 'order_write') {
            $arrWhere[] = "m.appFl = 'y'";
        }
        // 통합검색 처리
        if ((StringUtils::strIsSet($params['key'], null) !== null)
            && (StringUtils::strIsSet($params['keyword'], '') !== '')) {
            $hyphenKeys = 'phone,cellPhone,fax,busiNo';
            if ($params['key'] === 'all') {
                $tmpWhere = [];
                foreach (\Component\Member\Member::COMBINE_SEARCH as $mKey => $mVal) {
                    $type = $this->fields[$mKey];
                    if ($mKey === 'all' || $type === null) {
                        continue;
                    }
                    // 2016-11-17 yjwee 하이픈 없이도 검색되게 처리
                    if ((strpos($params['keyword'], '-') === false) &&
                        \in_array($mKey, explode(',', $hyphenKeys), true)) {
                        $tmpWhere[] = '(REPLACE(m.' . $mKey . ', \'-\', \'\') LIKE concat(\'%\',?,\'%\'))';
                        $this->db->bind_param_push($arrBind, $type, $params['keyword']);
                    } else {
                        $tmpWhere[] = '(m.' . $mKey . ' LIKE concat(\'%\',?,\'%\'))';
                        $this->db->bind_param_push($arrBind, $type, $params['keyword']);
                    }
                }
                $arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            } elseif ($this->fields[$params['key']] !== null) {
                // 2016-11-17 yjwee 하이픈 없이도 검색되게 처리
                if ((strpos($params['keyword'], '-') === false) &&
                    \in_array($params['key'], explode(',', $hyphenKeys), true)) {
                    $arrWhere[] = 'REPLACE(m.' . $params['key'] . ', \'-\', \'\') LIKE concat(\'%\',?,\'%\')';
                    $this->db->bind_param_push($arrBind, $this->fields[$params['key']], $params['keyword']);
                } else {
                    $arrWhere[] = 'm.' . $params['key'] . ' LIKE concat(\'%\',?,\'%\')';
                    $this->db->bind_param_push($arrBind, $this->fields[$params['key']], $params['keyword']);
                }
            }
        }

        $this->db->bindParameter('memberFl', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameter('entryPath', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameter('appFl', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameter('groupSno', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameter('sexFl', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameter('maillingFl', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameter('smsFl', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameter('calendarFl', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameter('marriFl', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameter('connectSns', $params, $arrBind, $arrWhere, 'tableMemberSns', 'ms', 'snsTypeFl');
        $this->db->bindParameter('expirationFl', $params, $arrBind, $arrWhere, 'tableMember', 'm'); //개인정보유효기간 검색

        $this->db->bindParameterByRange('saleCnt', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameterByRange('saleAmt', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameterByRange('mileage', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameterByRange('deposit', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameterByRange('loginCnt', $params, $arrBind, $arrWhere, 'tableMember', 'm');

        $this->db->bindParameterByDateTimeRange('entryDt', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameterByDateTimeRange('lastLoginDt', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameterByDateTimeRange('marriDate', $params, $arrBind, $arrWhere, 'tableMember', 'm');
        $this->db->bindParameterByDateTimeRange('sleepWakeDt', $params, $arrBind, $arrWhere, 'tableMember', 'm');

        // 생일 검색 조건 추가
        if (StringUtils::strIsSet($params['birthFl']) === 'y') { //  특정일 검색
            if (\strlen($params['birthDt'][0]) === 5) {   //  MM-DD
                $arrWhere[] = 'substr(m.birthDt, 6, 5) = ?';
                $this->db->bind_param_push($arrBind, $this->fields['birthDt'], $params['birthDt'][0]);
            } else {    //  YYYY-MM-DD
                $arrWhere[] = 'm.birthDt = ?';
                $this->db->bind_param_push($arrBind, $this->fields['birthDt'], $params['birthDt'][0]);
            }
        } else {    //  범위 검색
            if (\strlen($params['birthDt'][0]) === 5 || \strlen($params['birthDt'][1]) === 5) {   //  날짜를 한개만 입력한 경우
                if (empty($params['birthDt'][0])) {
                    $arrWhere[] = 'substr(m.birthDt, 6, 5) <= ?';
                    $this->db->bind_param_push($arrBind, $this->fields['birthDt'], $params['birthDt'][1]);
                } elseif (empty($params['birthDt'][1])) {
                    $arrWhere[] = 'substr(m.birthDt, 6, 5) >= ?';
                    $this->db->bind_param_push($arrBind, $this->fields['birthDt'], $params['birthDt'][0]);
                } else {
                    $arrWhere[] = 'substr(m.birthDt, 6, 5) BETWEEN ? AND ?';
                    $this->db->bind_param_push($arrBind, $this->fields['birthDt'], $params['birthDt'][0]);
                    $this->db->bind_param_push($arrBind, $this->fields['birthDt'], $params['birthDt'][1]);
                }
            } else {
                $this->db->bindParameterByDateTimeRange('birthDt', $params, $arrBind, $arrWhere, 'tableMember', 'm');
            }
        }

        // 만14세 미만회원만 보기가 체크된 경우 연령층 검색은 전체로 설정된다.
        if (StringUtils::strIsSet($params['under14'], 'n') === 'y') {
            $under14Date = DateTimeUtils::getDateByUnderAge(14);
            $arrWhere[] = 'm.birthDt > ?';
            $this->db->bind_param_push($arrBind, $this->fields['birthDt'], $under14Date);
        } else {
            // 연령층
            $params['age'] = StringUtils::strIsSet($params['age']);
            if ($params['age'] > 0) {
                $ageTerms = DateTimeUtils::getDateByAge($params['age']);
                $arrWhere[] = 'm.birthDt BETWEEN ? AND ?';
                $this->db->bind_param_push($arrBind, $this->fields['birthDt'], $ageTerms[1]);
                $this->db->bind_param_push($arrBind, $this->fields['birthDt'], $ageTerms[0]);
            }
        }

        // 장기 미로그인
        $novisit = (int) $params['novisit'];
        if ($novisit >= 0 && is_numeric($params['novisit'])) {
            $arrWhere[] = 'IF(m.lastLoginDt = \'0000-00-00 00:00:00\' OR m.lastLoginDt IS NULL, DATE_FORMAT(m.entryDt,\'%Y%m%d\') <= ?, DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') <= ?)';
            $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $novisit . ' day')));
            $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $novisit . ' day')));
        }

        //휴면전환예정회원 검색.
        if ($params['dormantMemberExpected'] === 'y') {
            $expirationDay = $params['expirationDay'];// 휴면 전환 예정 7일, 30일, 60일
            $expirationFl = $params['expirationFl']; // 개인정보 유효기간 전체,1년,3년,5년 선택값

            //개인정보유효기간 전체 선택 시
            if (!$expirationFl) {
                $arrWhere[] = 'm.expirationFl != \'999\' AND CASE m.expirationFl WHEN \'1\' THEN IF(m.lastLoginDt = \'0000-00-00 00:00:00\' OR m.lastLoginDt IS NULL, DATE_FORMAT(m.entryDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.entryDt,\'%Y%m%d\') >= ?, DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') >= ?) WHEN \'3\' THEN IF(lastLoginDt = \'0000-00-00 00:00:00\' OR m.lastLoginDt IS NULL, DATE_FORMAT(m.entryDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.entryDt,\'%Y%m%d\') >= ?, DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') >= ?) WHEN \'5\' THEN IF(lastLoginDt = \'0000-00-00 00:00:00\' OR m.lastLoginDt IS NULL, DATE_FORMAT(m.entryDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.entryDt,\'%Y%m%d\') >= ?, DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') >= ?) END';
                $dormantMemberTerms = [
                    365 - $expirationDay,
                    365,
                    1095 - $expirationDay,
                    1095,
                    1825 - $expirationDay,
                    1825,
                ];
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[0] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[1] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[0] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[1] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[2] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[3] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[2] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[3] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[4] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[5] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[4] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[5] . ' day')));
            } else {
                $arrWhere[] = 'IF(lastLoginDt = \'0000-00-00 00:00:00\' OR m.lastLoginDt IS NULL, DATE_FORMAT(m.entryDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.entryDt,\'%Y%m%d\') >= ?, DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') >= ?)';
                if ((int) $expirationFl === 1) {
                    $dormantMemberTerms = [
                        365 - $expirationDay,
                        365,
                    ];
                } elseif ((int) $expirationFl === 3) {
                    $dormantMemberTerms = [
                        1095 - $expirationDay,
                        1095,
                    ];
                } elseif ((int) $expirationFl === 5) {
                    $dormantMemberTerms = [
                        1825 - $expirationDay,
                        1825,
                    ];
                }
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[0] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[1] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[0] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fields['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[1] . ' day')));
            }
        }

        // 휴면회원 여부
        $arrWhere[] = 'm.sleepFl != \'y\'';
        if (StringUtils::strIsSet($params['mallSno'], '') !== '') {
            $arrWhere[] = 'm.mallSno=?';
            $this->db->bind_param_push($arrBind, $this->fields['mallSno'], $params['mallSno']);
        }

        $this->db->strField = 'm.memNo, m.memId, m.mallSno, m.groupSno, m.memNm, m.nickNm, m.appFl';
        $this->db->strField .= ', m.memberFl, m.smsFl, m.mileage, m.deposit, m.maillingFl';
        $this->db->strField .= ', m.saleAmt, m.saleCnt, m.entryDt, m.lastLoginDt, m.loginCnt, m.sexFl, m.sleepWakeDt';
        $this->db->strField .= ', m.email, m.phone, m.cellPhone, COUNT(mc.memberCouponState) as couponCount';
        $this->db->strField .= ', m.zipcode, m.zonecode, m.address, m.addressSub';
        $this->db->strField .= ', IF(ms.connectFl=\'y\', ms.snsTypeFl, \'\') AS snsTypeFl';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = StringUtils::strIsSet($params['sort'], 'm.entryDt desc');
        $this->db->strGroup = 'm.memNo';
        $this->db->strJoin = ' LEFT JOIN ' . DB_MEMBER_COUPON . ' AS mc ON m.memNo = mc.memNo';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MEMBER_SNS . ' AS ms ON m.memNo = ms.memNo';
    }
}
