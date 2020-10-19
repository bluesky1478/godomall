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

namespace Bundle\Component\Deposit;


use Component\Database\DBTableField;
use Framework\Object\SingletonTrait;

/**
 * Class DepositDAO
 * @package Bundle\Component\Deposit
 * @author  yjwee
 */
class DepositDAO extends \Component\AbstractComponent
{
    use SingletonTrait;

    /** @var array $fieldTypes 마일리지 테이블 필드 타입 */
    private $fieldTypes;

    public function __construct()
    {
        parent::__construct();
        $this->fieldTypes = DBTableField::getFieldTypes('tableMemberDeposit');
        $this->fieldTypes['sno'] = 'i'; // DBTableField 에 sno 가 없기때문에 추가
    }

    public function insertDeposit(array $arrData)
    {
        $arrBind = $this->db->get_binding(DBTableField::tableMemberDeposit(), $arrData, 'insert');
        $this->db->set_insert_db(DB_MEMBER_DEPOSIT, $arrBind['param'], $arrBind['bind'], 'y');

        return $this->db->insert_id();
    }

    public function updateMemberDeposit($memNo, $afterDeposit)
    {
        $arrData['deposit'] = $afterDeposit;
        $compareField = array_keys($arrData);
        $arrBind = $this->db->get_binding(DBTableField::tableMember(), $arrData, 'update', $compareField);
        $this->db->bind_param_push($arrBind['bind'], 'i', $memNo);
        $this->db->set_update_db(DB_MEMBER, $arrBind['param'], 'memNo = ?', $arrBind['bind']);
    }

    public function getMember($memNo)
    {
        $strSQL = 'SELECT deposit FROM ' . DB_MEMBER . ' WHERE memNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        return $data;
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
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_DEPOSIT . implode(' ', $query);
        $resultSet = $this->db->query_fetch($strSQL, $bind, false);

        return $resultSet;
    }
}
