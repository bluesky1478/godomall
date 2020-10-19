<?php

namespace Bundle\Component\PlusShop\ScreenEffect;


/**
 * 화면 효과 DAO
 *
 * @package Bundle\Component\PlusShop\ScreenEffect
 */
class ScreenEffectDao
{
    protected $db;
    protected $tableName;
    protected $count;
    private $appCd = 'NSF6DA7D';
    private $appSpace = 'screen_effect_free';

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::getInstance('DB');
        }
        $this->tableName = DB_APP_DATA;
    }

    /**
     * 화면 효과 조회
     *
     * @param int $offset
     * @param string $where
     * @param array $bind
     * @return array
     */
    public function get($offset = 0, $where = '', $bind = null)
    {
        if ($where) {
            $where = "AND $where";
        }

        $countQuery = 'SELECT COUNT(*) FROM ' . $this->tableName . " WHERE `app_cd` = '" . $this->appCd . "'" .
            " AND `app_space` = '" . $this->appSpace . "' $where";
        $query = 'SELECT * FROM ' . $this->tableName . " WHERE `app_cd` = '" . $this->appCd . "'" .
            " AND `app_space` = '" . $this->appSpace . "' $where ORDER BY `sno` DESC LIMIT $offset, 20";

        if ($bind) {
            $count = $this->db->query_fetch($countQuery, $bind);
            $this->count = $count[0]['COUNT(*)'];

            return $this->db->query_fetch($query, $bind);
        } else {
            $count = $this->db->query_fetch($countQuery);
            $this->count = $count[0]['COUNT(*)'];

            return $this->db->query_fetch($query);
        }
    }

    /**
     * Read a count
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Get a total count
     *
     * @return int
     */
    public function getTotalCount()
    {
        $query = 'SELECT COUNT(*) FROM ' . $this->tableName .
            " WHERE `app_cd` = 'NSF6DA7D' AND `app_space` = 'screen_effect_free'";

        return $this->db->query_fetch($query)[0]['COUNT(*)'];
    }

    /**
     * 리스트 조회
     *
     * @param string $effectName
     * @return array|null
     */
    public function getList($offset = 0, $effectName = null)
    {
        $where = '';
        $bind = null;
        if ($effectName) {
            $where = "JSON_EXTRACT(json_data, '$.effect_name') = ?";
            $bind = [];
            $this->db->bind_param_push($bind, 's', $effectName);
        }

        $data = $this->get($offset, $where, $bind);
        if (count($data) == 0) {
            return null;
        }

        return array_map(
            function ($row) {
                $merged = array_merge($row, json_decode($row['json_data'], true));
                unset($merged['json_data']);
                return $merged;
            },
            $data
        );
    }

    /**
     * 등록일로 검색
     *
     * @param int $offset
     * @param string $from
     * @param string $to
     * @param string $effectName
     * @return array
     */
    public function getByReg($offset = 0, $from, $to, $effectName = null)
    {
        $where = "reg_dt >= ? AND reg_dt <= ?";
        $bind = [];
        $this->db->bind_param_push($bind, 's', $from);
        $this->db->bind_param_push($bind, 's', "$to 23:59:59");

        if ($effectName) {
            $where .= " AND JSON_EXTRACT(json_data, '$.effect_name') LIKE ?";
            $this->db->bind_param_push($bind, 's', "%$effectName%");
        }

        $data = $this->get($offset, $where, $bind);

        return $this->getAppData($data);
    }

    /**
     * 수정일로 검색
     *
     * @param int $offset
     * @param string $from
     * @param string $to
     * @param string $effectName
     * @return array
     */
    public function getByMod($offset = 0, $from, $to, $effectName = null)
    {
        $where = "mod_dt >= ? AND mod_dt <= ?";
        $bind = [];
        $this->db->bind_param_push($bind, 's', $from);
        $this->db->bind_param_push($bind, 's', "$to 23:59:59");

        if ($effectName) {
            $where .= " AND JSON_EXTRACT(json_data, '$.effect_name') LIKE ?";
            $this->db->bind_param_push($bind, 's', "%$effectName%");
        }

        $data = $this->get($offset, $where, $bind);

        return $this->getAppData($data);
    }

    /**
     * 시작일로 검색
     *
     * @param int $offset
     * @param string $from
     * @param string $to
     * @param string $effectName
     * @return array
     */
    public function getByStart($offset = 0, $from, $to, $effectName = null)
    {
        $where = "
            JSON_EXTRACT(json_data, '$.effect_start_date') >= ? AND 
            JSON_EXTRACT(json_data, '$.effect_start_date') <= ? OR
            CAST(JSON_EXTRACT(json_data, '$.effect_limited') AS UNSIGNED) = 0";
        $bind = [];
        $this->db->bind_param_push($bind, 's', $from);
        $this->db->bind_param_push($bind, 's', $to);

        if ($effectName) {
            $where .= " AND JSON_EXTRACT(json_data, '$.effect_name') LIKE ?";
            $this->db->bind_param_push($bind, 's', "%$effectName%");
        }

        $data = $this->get($offset, $where, $bind);

        return $this->getAppData($data);
    }

    /**
     * 종료일로 검색
     *
     * @param int $offset
     * @param string $from
     * @param string $to
     * @param string $effectName
     * @return array
     */
    public function getByEnd($offset = 0, $from, $to, $effectName = null)
    {
        $where = "
            JSON_EXTRACT(json_data, '$.effect_end_date') >= ? AND 
            JSON_EXTRACT(json_data, '$.effect_end_date') <= ? OR
            CAST(JSON_EXTRACT(json_data, '$.effect_limited') AS UNSIGNED) = 0";
        $bind = [];
        $this->db->bind_param_push($bind, 's', $from);
        $this->db->bind_param_push($bind, 's', $to);

        if ($effectName) {
            $where .= " AND JSON_EXTRACT(json_data, '$.effect_name') LIKE ?";
            $this->db->bind_param_push($bind, 's', "%$effectName%");
        }

        $data = $this->get($offset, $where, $bind);

        return $this->getAppData($data);
    }

    public function getBySno($sno)
    {
        $bind = [];
        $this->db->bind_param_push($bind, 'i', $sno);

        $query = 'SELECT * FROM ' . $this->tableName . " WHERE `app_cd` = '" . $this->appCd . "'" .
            " AND `app_space` = '" . $this->appSpace . "' AND `sno` = ?";

        $data = $this->db->query_fetch($query, $bind);

        return json_decode($data[0]['json_data'], true);
    }

    public function getByCode($code)
    {
        $bind = [];
        $this->db->bind_param_push($bind, 's', $code);

        $query = 'SELECT * FROM ' . $this->tableName . " WHERE `app_cd` = '" . $this->appCd . "'" .
            " AND `app_space` = '" . $this->appSpace . "' AND JSON_EXTRACT(json_data, '$.effect_code') = ?";

        $data = $this->db->query_fetch($query, $bind);

        return json_decode($data[0]['json_data'], true);
    }

    public function getDuplicateCount($from, $to, $sno = null)
    {
        $where = '';
        if ($sno) {
            $where = "sno != $sno AND ";
        }

        $query = 'SELECT COUNT(*) FROM ' . $this->tableName . " WHERE `app_cd` = '" . $this->appCd . "'" .
            " AND `app_space` = '" . $this->appSpace . "' AND $where" .
            "((JSON_EXTRACT(json_data, '$.effect_start_date') <= ? AND 
            JSON_EXTRACT(json_data, '$.effect_end_date') >= ?) OR
            (JSON_EXTRACT(json_data, '$.effect_start_date') <= ? AND 
            JSON_EXTRACT(json_data, '$.effect_end_date') >= ?) OR
            CAST(JSON_EXTRACT(json_data, '$.effect_limited') AS UNSIGNED) = 0)";

        $bind = [];
        $this->db->bind_param_push($bind, 's', $from);
        $this->db->bind_param_push($bind, 's', $from);
        $this->db->bind_param_push($bind, 's', $to);
        $this->db->bind_param_push($bind, 's', $to);
        $result = $this->db->query_fetch($query, $bind);

        return $result[0]['COUNT(*)'];
    }

    /**
     * Decode all 'json_data' given array
     *
     * @param array $data
     * @return array
     */
    private function getAppData($data)
    {
        return array_map(
            function ($row) {
                $merged = array_merge($row, json_decode($row['json_data'], true));
                unset($merged['json_data']);
                return $merged;
            },
            $data
        );
    }

    /**
     * Delete by sno
     *
     * @param array $arrSno
     * @return int
     */
    public function delete($arrSno)
    {
        $bind = [];
        $this->db->bind_param_push($bind, 's', $this->appCd);
        $this->db->bind_param_push($bind, 's', $this->appSpace);

        $whereSno = [];
        foreach ($arrSno as $sno) {
            $this->db->bind_param_push($bind, 'i', $sno);
            $whereSno[] = '?';
        }
        $where = 'app_cd = ? AND app_space = ? AND sno IN (' . implode(',', $whereSno) . ')';

        return $this->db->set_delete_db($this->tableName, $where, $bind);
    }

    /**
     * 효과 종료
     *
     * @param array $arrSno
     * @return boolean
     */
    public function stopEffect($arrSno)
    {
        $resultLimited = $this->stopLimitedEffect($arrSno);
        $resultUnlimited = $this->stopUnlimitedEffect($arrSno);

        return $resultLimited || $resultUnlimited;
    }

    private function stopLimitedEffect($arrSno)
    {
        $bind = [];
        $set = [];
        $whereSno = [];

        $endDate = date('Y-m-d');
        $endTime = date('H:i', strtotime('-1 minute'));

        $set[] = "json_data = JSON_SET(json_data, '$.effect_end_date', '$endDate')";
        $set[] = "json_data = JSON_SET(json_data, '$.effect_end_time', '$endTime')";
        $set[] = "mod_dt = NOW()";

        $this->db->bind_param_push($bind, 's', $this->appCd);
        $this->db->bind_param_push($bind, 's', $this->appSpace);
        foreach ($arrSno as $sno) {
            $this->db->bind_param_push($bind, 'i', $sno);
            $whereSno[] = '?';
        }
        $where = 'app_cd = ? AND app_space = ? AND sno IN (' . implode(',', $whereSno) . ') AND ' .
            "CAST(JSON_EXTRACT(json_data, '$.effect_limited') AS UNSIGNED) = 1";

        $this->db->set_update_db_query($this->tableName, $set, $where, $bind, false, false);

        return true;
    }

    private function stopUnlimitedEffect($arrSno)
    {
        $bind = [];
        $set = [];
        $whereSno = [];

        $endDate = date('Y-m-d');
        $endTime = date('H:i', strtotime('-1 minute'));

        $set[] = "json_data = JSON_SET(json_data, '$.effect_start_date', DATE_FORMAT(reg_dt, '%Y-%m-%d'))";
        $set[] = "json_data = JSON_SET(json_data, '$.effect_start_time', DATE_FORMAT(reg_dt, '%H:%i'))";
        $set[] = "json_data = JSON_SET(json_data, '$.effect_end_date', '$endDate')";
        $set[] = "json_data = JSON_SET(json_data, '$.effect_end_time', '$endTime')";
        $set[] = "json_data = JSON_SET(json_data, '$.effect_limited', 1)";
        $set[] = "mod_dt = NOW()";

        $this->db->bind_param_push($bind, 's', $this->appCd);
        $this->db->bind_param_push($bind, 's', $this->appSpace);
        foreach ($arrSno as $sno) {
            $this->db->bind_param_push($bind, 'i', $sno);
            $whereSno[] = '?';
        }
        $where = 'app_cd = ? AND app_space = ? AND sno IN (' . implode(',', $whereSno) . ') AND ' .
            "CAST(JSON_EXTRACT(json_data, '$.effect_limited') AS UNSIGNED) = 0";

        $this->db->set_update_db_query($this->tableName, $set, $where, $bind, false, false);

        return true;
    }

    /**
     * Update json_data
     *
     * @param int $sno
     * @param array $data
     * @return int
     */
    public function updateJsonData($sno, $data)
    {
        $bind = [];
        $set = [];
        $set[] = "json_data = ?";
        $set[] = "mod_dt = NOW()";
        $this->db->bind_param_push($bind, 's', json_encode($data));

        $where = "app_cd = '" . $this->appCd . "' AND app_space = '" . $this->appSpace . "' AND sno = ?";
        $this->db->bind_param_push($bind, 'i', $sno);
        $this->db->set_update_db_query($this->tableName, $set, $where, $bind, false, false);

        return true;
    }

    /**
     * Insert
     *
     * @param array $data
     * @return int
     */
    public function insert($data)
    {
        $query = "INSERT INTO " . $this->tableName . " (app_cd, app_space, json_data, reg_dt) VALUES ('" .
            $this->appCd . "', '" . $this->appSpace . "', ?, NOW())";

        $bind = [];
        $this->db->bind_param_push($bind, 's', json_encode($data));

        return $this->db->bind_query($query, $bind);
    }
}
