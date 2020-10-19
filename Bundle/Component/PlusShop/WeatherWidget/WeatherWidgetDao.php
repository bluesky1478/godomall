<?php

namespace Bundle\Component\PlusShop\WeatherWidget;


class WeatherWidgetDao
{
    private $db;
    private $data;

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::getInstance('DB');
        }

        if (count($this->get()) == 0) {
            $this->init();
        }
    }

    public function init()
    {
        $jsonData = json_encode([
            'active' => true,
            'base_location' => '서울',
            'widget_link_usable_setting' => 1,
            'widget_background_color_usable_setting' => 0,
            'widget_border_usable_setting' => 0,
            'font_color' => '#444444',
            'background_color' => '#ffffff',
            'border_color' => '#e4e4e4',
            'widget_type' => 0
        ], JSON_UNESCAPED_UNICODE);
        $regDt = date('Y-m-d H:i:s');

        $this->db->query(
            'INSERT INTO ' . DB_APP_DATA . " (app_cd, app_space, json_data, reg_dt) VALUES " .
            "('NW27A9D4', 'weather_widget', '$jsonData', '$regDt')");
    }

    public function get()
    {
        if ($this->data) {
            return $this->data;
        }

        $query = 'SELECT * FROM ' . DB_APP_DATA . " WHERE app_cd = 'NW27A9D4' AND app_space = 'weather_widget'";
        $data = $this->db->query_fetch($query);
        $this->data = $data[0];

        return $this->data;
    }

    public function getData()
    {
        $data = $this->get();

        return $this->getAppData($data);
    }

    public function update($data)
    {
        $bind = [];

        $set[] = 'json_data=?';
        $this->db->bind_param_push($bind, 's', json_encode($data));

        $set[] = 'mod_dt=?';
        $this->db->bind_param_push($bind, 's', date('Y-m-d H:i:s'));

        $where = "app_cd = 'NW27A9D4' AND app_space = 'weather_widget'";

        $this->db->set_update_db_query(DB_APP_DATA, $set, $where, $bind, false, false);
    }

    private function getAppData($data)
    {
        return json_decode($data['json_data'], true);
    }
}
