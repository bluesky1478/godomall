<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2018 NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Component\Delivery;

use App;
use Component\Database\DBTableField;
use Framework\Object\SingletonTrait;

/**
 * Class UnstoringDAO
 * @package Bundle\Component\Delivery
 * @author  dlwoen9
 * @method static UnstoringDAO getInstance
 */
class UnstoringDAO
{
    use SingletonTrait;

    /** @var  \Framework\Database\DBTool $db */
    protected $db;
    protected $fields;

    public function __construct(array $config = [])
    {
        if (empty($config) === true) {
            $config = [
                'db' => App::load('DB'),
            ];
        }
        $this->db = $config['db'];
        $this->fields = DBTableField::getFieldTypes('tableUnstoringInfo');
    }

    public function selectStandardUnstoring($addressFl, $mallFl, $mainFl)
    {
        $this->db->strField = '*';
        $this->db->strWhere = 'addressFl=? AND mallFl=? AND mainFl=?';
        $this->db->strOrder = "regDt desc";

        $this->db->bind_param_push($arrBind, $this->fields['addressFl'], $addressFl);
        $this->db->bind_param_push($arrBind, $this->fields['mallFl'], $mallFl);
        $this->db->bind_param_push($arrBind, $this->fields['mainFl'], $mainFl);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_UNSTORING_ADDRESS . implode(' ', $query);

        $result = $this->db->query_fetch($strSQL, $arrBind, false);

        return $result;
    }

    public function selectUnstoringListToChangeKey($mallFl)
    {
        $this->db->strField = '*';
        $this->db->strWhere = 'mallFl=?';
        $this->db->strOrder = "regDt desc";

        $this->db->bind_param_push($arrBind, $this->fields['mallFl'], $mallFl);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_UNSTORING_ADDRESS . implode(' ', $query);

        $result = $this->db->query_fetch($strSQL, $arrBind);

        return $result;
    }

    public function selectCheckedUnstoringList($sno)
    {
        $bind = '';
        $this->db->strField = '*';
        foreach ($sno as $key => $value) {
            $params[] = '?';
            $this->db->bind_param_push($bind, 's', $value);
        }
        $this->db->strWhere = "sno = " . implode('AND ', $params);
        $this->db->strOrder = "regDt desc";

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_UNSTORING_ADDRESS . implode(' ', $query);

        $result = $this->db->query_fetch($strSQL, $bind);

        return $result;
    }

    public function selectUnstoringInfoOne($sno)
    {
        $this->db->strField = '*';
        $this->db->strWhere =  "sno = ?";

        $this->db->bind_param_push($bind, $this->fields['sno'], $sno);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_UNSTORING_ADDRESS . implode(' ', $query);

        $result = $this->db->query_fetch($strSQL, $bind, false);

        return $result;
    }

    public function selectUnstoringListBy($addressFl, $mallFl, $startPage, $endPage, $sno = null)
    {
        if (empty($mallFl)) {
            $whereCol2 = null;
        } else {
            $whereCol2 = 'AND mallFl=?';
        }

        $this->db->strField = '*';
        $this->db->strWhere =  'addressFl = ? ' . $whereCol2;

        if (empty($startPage) == false) {
            $this->db->strLimit = ($startPage - 1) * $endPage . ',' . $endPage;
        }

        $this->db->bind_param_push($bind, $this->fields['addressFl'], $addressFl);

        if (isset($mallFl)) {
            $this->db->bind_param_push($bind, $this->fields['mallFl'], $mallFl);
        }

        if (empty($sno) == false) {
            foreach ($sno as $k => $value) {
                $param[] = 'sno=? desc, ';
                $this->db->bind_param_push($bind, $this->fields['sno'], $value);
            }
            $this->db->strOrder = implode(' ', $param) . "regDt desc";
        } else {
            $this->db->strOrder = 'regDt desc';
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_UNSTORING_ADDRESS . implode(' ', $query);

        $result = $this->db->query_fetch($strSQL, $bind);

        return $result;
    }

    public function insertUnstoringInfo($unstoringInfo)
    {
        $arrBind = $this->db->get_binding(DBTableField::tableUnstoringInfo(), $unstoringInfo, 'insert', array_keys($unstoringInfo));
        $this->db->set_insert_db(DB_UNSTORING_ADDRESS, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);
        return $this->db->insert_id();
    }

    public function updateMainFl($addressFl, $mainFl)
    {
        $arrBind = [];
        $strSQL = "UPDATE " . DB_UNSTORING_ADDRESS . " SET mainFl = 'n' WHERE mainFl = ? AND addressFl = ?";

        $this->db->bind_param_push($arrBind, $this->fields['mainFl'], $mainFl);
        $this->db->bind_param_push($arrBind, $this->fields['addressFl'], $addressFl);
        $this->db->bind_query($strSQL, $arrBind);
    }

    public function updateUnstoringInfo($unstoringInfo)
    {
        $arrBind = $this->db->get_binding(DBTableField::tableUnstoringInfo(), $unstoringInfo, 'update');
        $this->db->bind_param_push($arrBind['bind'], $this->fields['sno'], $unstoringInfo['sno']);
        $this->db->set_update_db(DB_UNSTORING_ADDRESS, $arrBind['param'], 'sno = ?', $arrBind['bind']);
    }

    public function deleteUnstoring($unstoringInfo)
    {
        $strWhere = array_keys($unstoringInfo)[0] . '=?';
        $this->db->bind_param_push($bind, $this->fields['sno'], $unstoringInfo['sno']);
        $this->db->set_delete_db(DB_UNSTORING_ADDRESS, $strWhere, $bind);
    }

    public function selectCountUnstoring($addressFl, $mallFl)
    {
        $strWhere = 'addressFl = ? AND mallFl = ?';
        $strSQL = 'SELECT COUNT(*) AS cnt FROM ' . DB_UNSTORING_ADDRESS . ' WHERE ' .  $strWhere;

        $this->db->bind_param_push($arrBind, $this->fields['addressFl'], $addressFl);
        $this->db->bind_param_push($arrBind, $this->fields['mallFl'], $mallFl);

        $result = $this->db->query_fetch($strSQL, $arrBind, false);

        return $result['cnt'];
    }
}
