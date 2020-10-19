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
 * @link http://www.godo.co.kr
 */

namespace Bundle\Component\MemberStatistics;

use Component\AbstractComponent;
use Component\Database\DBTableField;
use DateTime;

/**
 * Class Member
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class Member extends \Component\AbstractComponent
{
    /**
     * @inheritDoc
     */
    public function __construct(array $array = null)
    {
        parent::__construct();
        if (is_null($array) === false) {
            $this->tableName = $array['tableName'];
            $this->tableFunctionName = $array['tableFunctionName'];
        }
    }


    /**
     * save
     *
     * @param array  $list
     * @param string $column
     */
    public function save(array $list, $column = 'joinYM')
    {
        foreach ($list as $index => $item) {
            $count = $this->db->getCount($this->tableName, '1', 'WHERE ' . $column . '=' . $index);
            $item[$column] = $index;

            if ($count > 0) {
                $this->update($item, $column);
            } else {
                $this->insert($item);
            }
        }
        unset($list);
    }

    /**
     * insert
     *
     * @param array $list
     */
    public function insert(array $list)
    {
        $arrBind = $this->db->get_binding(DBTableField::getBindField($this->tableFunctionName), $list, 'insert', array_keys($list));
        foreach ($arrBind['param'] as $key => &$value) {
            $value = '`' . $value . '`';
        }
        $this->db->set_insert_db($this->tableName, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);
    }

    /**
     * update
     *
     * @param array $list
     * @param       $column
     */
    public function update(array $list, $column = 'joinYM')
    {
        $excludeField = $column;
        $arrBind = $this->db->updateBinding(DBTableField::getBindField($this->tableFunctionName), $list, array_keys($list), explode(',', $excludeField));
        $this->db->bind_param_push($arrBind['bind'], 'i', $list[$column]);
        $this->db->set_update_db($this->tableName, $arrBind['param'], $column . '=?', $arrBind['bind']);
        unset($arrBind);
    }

    /**
     * lists
     *
     * @param array $requestParams
     * @param       $offset
     * @param       $limit
     *
     * @return array|object
     */
    public function lists(array $requestParams, $offset, $limit)
    {
        $arrBind = $arrWhere = [];

        /**
         * 검색기간의 년월 값을 설정
         * @var DateTime[] $arrSearchDt
         */
        $arrSearchDt = $requestParams['searchDt'];
        $requestParams['joinYM'] = [
            $arrSearchDt[0]->format('Ym'),
            $arrSearchDt[1]->format('Ym'),
        ];
        $this->db->bindParameterByRange('joinYM', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName);

        $this->db->strField = '*';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        if (is_null($offset) === false && is_null($limit) === false) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . $this->tableName . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);
        unset($arrBind, $arrWhere, $arrQuery);

        return $resultSet;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param mixed $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @return mixed
     */
    public function getTableFunctionName()
    {
        return $this->tableFunctionName;
    }

    /**
     * @param mixed $tableFunctionName
     */
    public function setTableFunctionName($tableFunctionName)
    {
        $this->tableFunctionName = $tableFunctionName;
    }
}
