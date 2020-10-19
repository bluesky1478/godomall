<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Component\Board;


use Component\Database\DBTableField;

class PlusMemoArticleDao
{
    protected $db;
    protected $tableName;
    protected $tableField;

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::getInstance('DB');
        }
        $this->tableName = DB_PLUS_MEMO_ARTICLE;
        $this->tableField = DBTableField::setTableField('tablePlusMemoArticle', null, null, 'pma');
    }

    public function selectBySno($sno)
    {
        $arrBind = null;
        $query = "SELECT pm.boardName  , " . implode(',', $this->tableField) . "  FROM " . $this->tableName . " as pma  ";
        $query .= " INNER JOIN " . DB_PLUS_MEMO . " as pm ON pm.sno = pma.plusMemoSno";
        $query .= " WHERE pma.sno = ?  ";
        $this->db->bind_param_push($arrBind, 's', $sno);

        return $this->db->query_fetch($query, $arrBind, false);
    }

    public function selectByParentSno($parentSno)
    {
        if (!$parentSno) {
            return null;
        }

        $arrBind = null;
        $query = "SELECT " . implode(',', $this->tableField) . ",pm.boardName  FROM " . $this->tableName . " as pma ";
        $query .= " INNER JOIN " . DB_PLUS_MEMO . " as pm ON pm.plusMemoSno = pma.sno";

        $this->db->bind_param_push($arrBind, 's', $parentSno);
        return $this->db->query_fetch($query, $arrBind);
    }

    public function getQueryWhere($search = null)
    {
        $arrBind = null;
        if ($search['plusMemoSno']) {
            $where[] = "plusMemoSno = ? ";
            $this->db->bind_param_push($arrBind, 's', $search['plusMemoSno']);
        }
        if ($search['searchWord']) {
            switch ($search['searchField']) {
                case 'contents' :
                    $where[] = "contents LIKE concat('%',?,'%')";
                    $this->db->bind_param_push($arrBind, 's', $search['searchWord']);
                    break;
                case 'writerNm' :
                    $this->db->bind_param_push($arrBind, 's', $search['searchWord']);
                    $where[] = "writerNm  LIKE concat('%',?,'%') ";
                    break;
                case 'writerNick' :
                    $this->db->bind_param_push($arrBind, 's', $search['searchWord']);
                    $where[] = "writerNick  LIKE concat('%',?,'%')";
                    break;
                case 'writerId' :
                    $this->db->bind_param_push($arrBind, 's', $search['searchWord']);
                    $where[] = "writerId  LIKE concat('%',?,'%')";
                    break;
            }
        }

        //일자 검색
        if (gd_isset($search['rangDate'][0]) && gd_isset($search['rangDate'][1])) {
            $dateField = $search['searchDateFl'] == 'modDt' ? 'pma.modDt' : 'pma.regDt';
            $where[] = $dateField . " between ? and ?";
            $this->db->bind_param_push($arrBind, 's', $search['rangDate'][0]);
            $this->db->bind_param_push($arrBind, 's', $search['rangDate'][1] . ' 23:59');
        }
        $strWhere = implode(" AND ", $where);

        return [$strWhere, $arrBind];
    }

    public function select($search, $offset, $limit, $orderByField = 'groupNo')
    {
        $arrBind = null;
        $query = "SELECT pm.boardName , " . implode(',', $this->tableField) . " FROM " . $this->tableName . " as pma ";
        $query .= "   INNER JOIN " . DB_PLUS_MEMO . " as pm ON pma.plusMemoSno = pm.sno ";
        $query .= " WHERE 1 AND parentSno = 0";

        if ($search) {
            list($whereQuery, $arrBind) = $this->getQueryWhere($search);
            $whereQuery = (!$whereQuery) ? "" : " AND " . $whereQuery;
        }
        $orderByField = $orderByField ? $orderByField : ' groupNo  ';
        $orderByQuery = " ORDER BY " . $orderByField;
        if ($offset || $limit) {
            $orderByQuery .= " LIMIT {$offset},{$limit}";
        }

        $query .= $whereQuery . " " . $orderByQuery;
        $result = $this->db->query_fetch($query, $arrBind);
        return $result;
    }

    public function selectComment($search, $offset, $limit, $orderByField = 'sno desc')
    {
        $arrBind = null;
        $query = "SELECT pm.boardName , " . implode(',', $this->tableField) . " FROM " . $this->tableName . " as pma ";
        $query .= "   INNER JOIN " . DB_PLUS_MEMO . " as pm ON pma.plusMemoSno = pm.sno ";
        $query .= " WHERE 1 ";
        $query .= " AND parentSno =  ".$search['parentSno'];
        if ($search) {
            list($whereQuery, $arrBind) = $this->getQueryWhere($search);
            $whereQuery = (!$whereQuery) ? "" : " AND " . $whereQuery;
        }
        $orderByQuery = " ORDER BY " . $orderByField;
        if ($offset || $limit) {
            $orderByQuery .= " LIMIT {$offset},{$limit}";
        }

        $query .= $whereQuery . " " . $orderByQuery;
        $result = $this->db->query_fetch($query, $arrBind);
        return $result;
    }


    public function count($search)
    {
        $arrBind = null;
        if ($search) {
            list($whereQuery, $arrBind) = $this->getQueryWhere($search);
            $whereQuery = (!$whereQuery) ? "" : " AND " . $whereQuery;
        }

        $query = " SELECT count(*) AS cnt FROM " . $this->tableName . " as pma  WHERE 1 AND parentSno = 0 " . $whereQuery;
        $result = $this->db->query_fetch($query, $arrBind, false);

        return $result['cnt'];

    }

    public function insert($data)
    {
        $arrBind = $this->db->get_binding(DBTableField::tablePlusMemoArticle(), $data, 'insert', array_keys($data));
        $this->db->set_insert_db($this->tableName, $arrBind['param'], $arrBind['bind'], 'y');
        return $this->db->insert_id();
    }

    public function update($sno, $data)
    {
        $arrBind = $this->db->get_binding(DBTableField::tablePlusMemoArticle(), $data, 'update', array_keys($data));
        $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
        return $this->db->set_update_db($this->tableName, $arrBind['param'], 'sno = ?', $arrBind['bind']);
    }

    public function delete($sno)
    {
        $arrBind = [];
        if (is_array($sno)) {
            $where = 'sno in (' . implode(',', $sno) . ')';
        } else {
            $where = 'sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $sno);
        }
        return $this->db->set_delete_db($this->tableName, $where, $arrBind);
    }

    public function selectGroupNo()
    {
        $query = "SELECT MIN(groupNo) FROM " . $this->tableName;
        list($groupNo) = $this->db->fetch($query, 'row');
        if ($groupNo == null) {
            return -1;
        }

        return $groupNo - 1;
    }


    public function countComment($sno)
    {
        $arrBind = [];
        $query = "SELECT COUNT(*) as cnt FROM " . $this->tableName . " WHERE parentSno = ?";
        $this->db->bind_param_push($arrBind, 's', $sno);
        $result = $this->db->query_fetch($query, $arrBind, false);
        return $result['cnt'];
    }
}
