<?php

namespace Bundle\Component\PlusShop\ExchangeRateWidget;


/**
 * 환율계산 위젯 DAO
 * @package Bundle\Component\PlusShop
 */
class ExchangeRateDao
{
    protected $db;
    protected $tableName;
    protected $data;

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::getInstance('DB');
        }
        $this->tableName = DB_APP_DATA;

        if (count($this->get()) == 0) {
            $this->init();
        }
    }

    /**
     * 초기 데이터 입력
     */
    public function init()
    {
        $jsonData = json_encode([
            'widget_display' => true,
            'base_cur_type' => 'USD',
            'exchange_cur_type' => 'KRW',
            'widget_type' => 'icon_type',
            'widget_icon_type' => 'basic_icon',
            'widget_icon_use_both' => 'true'
        ]);
        $regDt = date('Y-m-d H:i:s');

        $this->db->query(
            'INSERT INTO ' . $this->tableName .
            "(app_cd, app_space, json_data, reg_dt) VALUES " .
            "('NE45C002', 'exchange_rate_widget_free', '$jsonData', '$regDt')");
    }

    /**
     * 위젯 조회
     */
    public function get()
    {
        if ($this->data) {
            return $this->data;
        }

        $query = 'SELECT * FROM ' . $this->tableName .
            " WHERE `app_cd` = 'NE45C002' AND `app_space` = 'exchange_rate_widget_free'";
        $this->data = $this->db->query_fetch($query);

        return $this->data;
    }

    /**
     * 위젯 업데이트
     *
     * @param array $data
     */
    public function update($data)
    {
        $bind = [];

        $set[] = 'json_data=?';
        $this->db->bind_param_push($bind, 's', json_encode($data));

        $set[] = 'mod_dt=?';
        $this->db->bind_param_push($bind, 's', date('Y-m-d H:i:s'));

        $where = "`app_cd` = 'NE45C002' AND `app_space` = 'exchange_rate_widget_free'";

        $this->db->set_update_db_query($this->tableName, $set, $where, $bind, false, false);
    }

    /**
     * 위젯 설정 조회
     */
    public function getAppData()
    {
        $data = $this->get();
        if (count($data) == 0) {
            return null;
        }

        return json_decode($data[0]['json_data'], true);
    }

    /**
     * 위젯 노출 설정 조회
     */
    public function isDisplay()
    {
        $appData = $this->getAppData();
        if ($appData === null) {
            return false;
        }

        if (!isset($appData['widget_display'])) {
            return true;
        }

        return $appData['widget_display'];
    }
}
