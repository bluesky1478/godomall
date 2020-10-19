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

namespace Bundle\Component\Mileage;

use Component\Database\DBTableField;
use Framework\Object\SingletonTrait;
use Framework\Utility\StringUtils;

/**
 * 마일리지 데이터베이스 담당 클래스
 * @package Bundle\Component\Mileage
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class MileageDAO extends \Component\AbstractComponent
{
    use SingletonTrait;

    /** @var array $bindParams 쿼리 바인딩 파라미터 배열 */
    protected $bindParams = [];
    /** @var array $fieldTypes 마일리지 테이블 필드 타입 */
    private $fieldTypes;

    public function __construct()
    {
        parent::__construct();
        $this->fieldTypes = DBTableField::getFieldTypes('tableMemberMileage');
        $this->fieldTypes['sno'] = 'i'; // DBTableField 에 sno 가 없기때문에 추가
    }

    /**
     * 마일리지 소멸 대상 조회
     *
     * @param $expireDate
     *
     * @return array|object
     */
    public function getListsByExpireDate($expireDate)
    {
        $this->db->strField = 'mm.sno, mm.memNo, SUM(mm.mileage) AS mileage, mm.deleteFl, DATE(mm.deleteScheduleDt) AS deleteScheduleDt, mm.useHistory, m.email, m.cellPhone, m.maillingFl, m.smsFl, m.mileage AS totalMileage, m.memId, m.memNm, m.sleepFl';
        $this->db->strWhere = 'm.sleepFl=\'n\' AND mm.mileage > 0 AND mm.deleteFl IN (\'n\', \'use\') AND mm.deleteScheduleDt LIKE \'' . $expireDate . '%\'';
        $this->db->strGroup = 'mm.memNo';
        $this->db->strJoin = 'LEFT JOIN ' . DB_MEMBER . ' AS m ON mm.memNo = m.memNo';

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER_MILEAGE . ' AS mm ' . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query);

        return $resultSet;
    }

    /**
     * 마일리지 소멸 안내 대상 조회
     *
     * @param $expireDate
     *
     * @return array|object
     */
    public function getListsByExpireGuideDate($expireDate)
    {
        $this->db->strField = 'mm.sno, mm.memNo, SUM(mm.mileage) AS mileage, mm.deleteFl, DATE(mm.deleteScheduleDt) AS deleteScheduleDt, mm.useHistory, m.email, m.cellPhone, m.maillingFl, m.smsFl, m.deposit, m.mileage AS totalMileage, m.groupSno, m.memId, m.memNm';
        $this->db->strWhere = 'mm.deleteScheduleDt LIKE \'' . $expireDate . '%\' AND mm.deleteFl IN (\'n\', \'use\') AND mm.mileage > 0';
        $this->db->strWhere .= ' AND ((m.maillingFl=\'y\' AND m.email!=\'\') OR (m.cellPhone!=\'\'))';
        $this->db->strWhere .= ' AND m.sleepFl=\'n\'';
        $this->db->strGroup = 'mm.memNo';
        $this->db->strJoin = 'LEFT JOIN ' . DB_MEMBER . ' AS m ON mm.memNo = m.memNo';

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER_MILEAGE . ' AS mm ' . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query);

        return $resultSet;
    }

    /**
     * 회원의 사용가능한 마일리지 조회
     *
     * @param $memberNo
     *
     * @return array|null|object
     */
    public function getUsableMemberMileage($memberNo)
    {
        $this->db->strOrder = 'regDt ASC';

        return $this->db->getData(DB_MEMBER_MILEAGE, null, 'memNo=\'' . $memberNo . '\' AND mileage > 0 AND (deleteFl=\'n\' OR deleteFl=\'use\')', 'sno,memNo,beforeMileage,afterMileage,mileage,useHistory,deleteFl', true);
    }


    /**
     * 마일리지 사용내역 및 삭제 상태 변경
     *
     * @param MileageDomain $domain
     */
    public function updateUseHistoryWithDeleteFlag(MileageDomain $domain)
    {
        $arrBind = $arrUpdate = [];

        $arrUpdate[] = 'useHistory=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['useHistory'], $domain->getUseHistory());

        $arrUpdate[] = 'deleteFl=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['deleteFl'], $domain->getDeleteFl());

        $this->db->strWhere = 'sno = ?';
        $this->db->bind_param_push($arrBind, 'i', $domain->getSno());

        $this->db->set_update_db(DB_MEMBER_MILEAGE, $arrUpdate, $this->db->strWhere, $arrBind);
        $this->db->query_reset();
    }

    /**
     * 마일리지 추가
     *
     * @param MileageDomain $domain
     *
     * @return int|string
     */
    public function insertMileage(MileageDomain $domain)
    {
        $arrBind = $this->db->get_binding(DBTableField::tableMemberMileage(), $domain->toArray(), 'insert');
        $this->db->set_insert_db(DB_MEMBER_MILEAGE, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);

        return $this->db->insert_id();
    }

    /**
     * 마일리지 일괄처리 리스트 조회
     *
     * @param array $params
     * @param       $start
     * @param       $limit
     *
     * @return array|object
     * @throws \Exception
     */
    public function selectMemberBatchMileageList(array $params, $start, $limit)
    {
        $this->bindParams = $arrWhere = [];

        //@formatter:off
        $this->db->bindKeywordByTables(Mileage::COMBINE_SEARCH, $params, $this->bindParams, $arrWhere, ['tableMemberMileage', 'tableMember'], ['mm', 'mb']);
        //@formatter:on
        $this->db->bindParameter('handleMode', $params, $this->bindParams, $arrWhere, 'tableMemberMileage', 'mm');
        $this->db->bindParameter('handleCd', $params, $this->bindParams, $arrWhere, 'tableMemberMileage', 'mm');
        $this->db->bindParameter('handleNo', $params, $this->bindParams, $arrWhere, 'tableMemberMileage', 'mm');
        $this->db->bindParameter('memNo', $params, $this->bindParams, $arrWhere, 'tableMemberMileage', 'mm');
        $this->db->bindParameter('deleteFl', $params, $this->bindParams, $arrWhere, 'tableMemberMileage', 'mm');
        $this->db->bindParameterByRange('mileage', $params, $this->bindParams, $arrWhere, 'tableMemberMileage', 'mm');
        $this->db->bindParameterByDateTimeRange('regDt', $params, $this->bindParams, $arrWhere, 'tableMemberMileage', 'mm');
        if ($params['reasonCd'] == Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ETC && $params['contents'] != '') {
            $this->db->bindParameterByLike('contents', $params, $this->bindParams, $arrWhere, 'tableMemberMileage', 'mm');
        }
        $this->db->bindParameter('reasonCd', $params, $this->bindParams, $arrWhere, 'tableMemberMileage', 'mm');
        $this->db->bindParameter('groupSno', $params, $this->bindParams, $arrWhere, 'tableMember', 'mb');

        //@formatter:off
        $memberFields = DBTableField::setTableField(
            'tableMember', ['memNo', 'memId', 'groupSno', 'memNm', 'nickNm', ], null, 'mb'
        );
        $mileageFields = DBTableField::setTableField(
            'tableMemberMileage', ['memNo', 'managerId', 'managerNo', 'mileage', 'afterMileage', 'contents', 'handleMode', 'handleCd', 'handleNo', 'reasonCd', 'deleteFl', 'deleteScheduleDt', 'deleteDt', ], null, 'mm'
        );
        //@formatter:on
        $memberField = implode(', ', $memberFields);
        $mileageField = implode(', ', $mileageFields);

        $this->db->strField = $memberField . ', ' . $mileageField . ', mm.sno, mm.regDt,ma.isDelete';
        $this->db->strJoin = ' LEFT JOIN ' . DB_MEMBER . ' as mb ON mm.memNo = mb.memNo';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MANAGER . ' as ma ON ma.sno = mm.managerNo';
        if ($params['mode'] == 'add') {
            $arrWhere[] = 'mm.mileage >= 0';
        } else if ($params['mode'] == 'remove') {
            $arrWhere[] = 'mm.mileage <= 0';
        }
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = gd_isset($params['sort'], 'entryDt desc');
        $this->db->strLimit = '?,?';
        $this->db->bind_param_push($this->bindParams, 'i', $start);
        $this->db->bind_param_push($this->bindParams, 'i', $limit);

        $query = $this->db->query_complete(true, true);
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_MILEAGE . ' as mm ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->bindParams);

        return $data;
    }

    /**
     * 마일리지 지급/차감 내역 조회 총 검색 건수
     * 사전조건 : 리스트 조회 시 쿼리 조건을 금지
     *            전역변수 $bindParams 에 바인딩된 정보가 존재
     *
     * @return int
     */
    public function countMemberBatchMileageList(): int
    {
        $query = $this->db->getQueryCompleteBackup();
        unset($query['field'], $query['limit'], $query['order']);
        $strSQL = 'SELECT COUNT(mm.sno) AS cnt FROM ' . DB_MEMBER_MILEAGE . ' AS mm ' . implode(' ', $query);
        $bindCount = substr_count($strSQL, '?');
        $bindParams = [substr($this->bindParams[0], 0, $bindCount)];
        for ($i = 1; $i <= $bindCount; $i++) {
            $bindParams[] = $this->bindParams[$i];
        }
        $resultSet = $this->db->query_fetch($strSQL, $bindParams, false);
        StringUtils::strIsSet($resultSet['cnt'], 0);

        return $resultSet['cnt'];
    }

    /**
     * selectByOne
     *
     * @param        $value
     * @param string $columnName
     *
     * @return array
     */
    public function selectByOne($value, $columnName = 'sno')
    {
        $this->db->strField = '*';
        $this->db->strWhere = $columnName . '=?';
        $this->db->bind_param_push($bind, $this->fieldTypes[$columnName], $value);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_MILEAGE . implode(' ', $query);
        $resultSet = $this->db->query_fetch($strSQL, $bind, false);

        return $resultSet;
    }

    /**
     * selectExpireMileageByMemberNo
     *
     * @param string $memNo
     * @param string $expireDate Y-m-d H:i:s
     *
     * @return array
     */
    public function selectExpireMileageByMemberNo($memNo, $expireDate): array
    {
        $this->db->strField = 'mm.sno, mm.memNo, SUM(mm.mileage) AS mileage, mm.deleteFl, DATE(mm.deleteScheduleDt) AS deleteScheduleDt, mm.useHistory';
        $this->db->strWhere = 'mm.memNo = ? AND mm.mileage > 0 AND mm.deleteFl IN (\'n\', \'use\')';
        $this->db->strWhere .= ' AND (mm.deleteScheduleDt != \'0000-00-00 00:00:00\' AND mm.deleteScheduleDt IS NOT NULL AND mm.deleteScheduleDt <= ?)';
        $this->db->bind_param_push($bind, $this->fieldTypes['memNo'], $memNo);
        $this->db->bind_param_push($bind, $this->fieldTypes['deleteScheduleDt'], $expireDate);
        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER_MILEAGE . ' AS mm ' . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $bind, false);

        return $resultSet;
    }
}
