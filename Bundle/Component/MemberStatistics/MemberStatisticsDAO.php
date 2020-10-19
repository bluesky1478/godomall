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
 * Class MemberStatisticsDAO
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class MemberStatisticsDAO extends \Component\AbstractComponent
{
    public function listsGender($date = null)
    {
        $arrBind = $arrWhere = [];

        $fields = [];
        $fields[] = 'COUNT(1) AS total';
        $fields[] = ', COUNT(IF(sexFl=\'m\', 1, NULL)) AS male';
        $fields[] = ', COUNT(IF(sexFl=\'w\', 1, NULL)) AS female';
        $fields[] = ', COUNT(IF(sexFl IS NULL OR sexFl=\'\', 1, NULL)) AS genderOther';

        $this->db->strField = join('', $fields);
        $this->db->strWhere = 'entryDt < \'' . $date . '\'';
        if ($date === null) {
            $date = new DateTime();
            $this->db->strWhere = 'entryDt < \'' . $date->modify('-1 day')->format('Y-m-d') . '\'';
        }

        $this->db->strWhere .= ' AND sleepFl=\'n\' AND appFl=\'y\'';

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind, false);

        return $resultSet;
    }

    public function listsAge($date = null)
    {
        $fields = [];
        $fields[] = 'COUNT(1) AS ageCount';
        $fields[] = ', CASE WHEN age >=10 AND age <=19 THEN \'age10\'';
        $fields[] = ' WHEN age >=20 AND age <=29 THEN \'age20\'';
        $fields[] = ' WHEN age >=30 AND age <=39 THEN \'age30\'';
        $fields[] = ' WHEN age >=40 AND age <=49 THEN \'age40\'';
        $fields[] = ' WHEN age >=50 AND age <=59 THEN \'age50\'';
        $fields[] = ' WHEN age >=60 AND age <=69 THEN \'age60\'';
        $fields[] = ' WHEN age >=70 AND age <=79 THEN \'age70\'';
        $fields[] = ' ELSE \'ageOther\'';
        $fields[] = ' END AS ageBand';

        $this->db->strField = join('', $fields);
        $this->db->strWhere = 'entryDt < \'' . $date . '\'';
        $this->db->strGroup = 'ageBand';

        if ($date === null) {
            $date = new DateTime();
            $this->db->strWhere = 'entryDt < \'' . $date->modify('-1 day')->format('Y-m-d') . '\'';
        }

        $this->db->strWhere .= ' AND sleepFl=\'n\' AND appFl=\'y\'';

        $subQuery = '(SELECT appFl, sleepFl, entryDt, DATE_FORMAT(NOW(), \'%Y\') - DATE_FORMAT(birthDt, \'%Y\') - ( DATE_FORMAT(NOW(), \'00-%m%d\') < DATE_FORMAT(birthDt, \'00-%m%d\') ) AS age FROM ' . DB_MEMBER . ') AS m';

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . $subQuery . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query);

        return $resultSet;
    }

    public function listsArea($date = null)
    {
        $fields = [];
        $fields[] = 'COUNT(1) AS areaCount';
        $fields[] = ', CASE WHEN INSTR(address, \'서울\') > 0 THEN \'seoul\'';
        $fields[] = ' WHEN INSTR(address, \'부산\') > 0 THEN \'busan\'';
        $fields[] = ' WHEN INSTR(address, \'대구\') > 0 THEN \'daegu\'';
        $fields[] = ' WHEN INSTR(address, \'인천\') > 0 THEN \'incheon\'';
        $fields[] = ' WHEN INSTR(address, \'광주\') > 0 THEN \'gwangju\'';
        $fields[] = ' WHEN INSTR(address, \'대전\') > 0 THEN \'daejeon\'';
        $fields[] = ' WHEN INSTR(address, \'울산\') > 0 THEN \'ulsan\'';
        $fields[] = ' WHEN INSTR(address, \'세종\') > 0 THEN \'sejong\'';
        $fields[] = ' WHEN INSTR(address, \'경기\') > 0 THEN \'gyeonggi\'';
        $fields[] = ' WHEN INSTR(address, \'강원\') > 0 THEN \'gangwon\'';
        $fields[] = ' WHEN INSTR(address, \'충청북도\') > 0 THEN \'chungbuk\'';
        $fields[] = ' WHEN INSTR(address, \'충청남도\') > 0 THEN \'chungnam\'';
        $fields[] = ' WHEN INSTR(address, \'전라북도\') > 0 THEN \'jeonbuk\'';
        $fields[] = ' WHEN INSTR(address, \'전라남도\') > 0 THEN \'jeonnam\'';
        $fields[] = ' WHEN INSTR(address, \'경상북도\') > 0 THEN \'gyeongbuk\'';
        $fields[] = ' WHEN INSTR(address, \'경상남도\') > 0 THEN \'gyeongnam\'';
        $fields[] = 'WHEN INSTR(address, \'제주\') > 0 THEN \'jeju\'';
        $fields[] = ' ELSE \'areaOther\'';
        $fields[] = ' END AS area';

        $this->db->strField = join('', $fields);
        $this->db->strWhere = 'entryDt < \'' . $date . '\'';
        $this->db->strGroup = 'area';

        if ($date === null) {
            $date = new DateTime();
            $this->db->strWhere = 'entryDt < \'' . $date->modify('-1 day')->format('Y-m-d') . '\'';
        }

        $this->db->strWhere .= ' AND sleepFl=\'n\' AND appFl=\'y\'';

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query);

        return $resultSet;
    }

    public function insertStatistics(array $params)
    {
        if (!isset($params['statisticsDt'])) {
            $params['statisticsDt'] = gd_date_format('Y-m-d', '-1days');
        }

        $arrBind = $this->db->get_binding(DBTableField::tableMemberStatistics(), $params, 'insert');
        $this->db->set_insert_db(DB_MEMBER_STATISTICS, $arrBind['param'], $arrBind['bind'], 'y');

        return $this->db->insert_id();
    }

    public function listsByStatisticsGender(array $params)
    {
        $arrBind = $arrWhere = [];

        $this->db->bindParameterByDateRange('statisticsDt', $params, $arrBind, $arrWhere, 'tableMemberStatistics');

        $this->db->strField = 'statisticsDt, total, male, female, genderOther';
        $this->db->strWhere = join(' AND ', $arrWhere);

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER_STATISTICS . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        return $resultSet;
    }

    public function listsByStatisticsAge(array $params)
    {
        $arrBind = $arrWhere = [];

        $this->db->bindParameterByDateRange('statisticsDt', $params, $arrBind, $arrWhere, 'tableMemberStatistics');

        $this->db->strField = 'statisticsDt, total, age10, age20, age30, age40, age50, age60, age70, ageOther';
        $this->db->strWhere = join(' AND ', $arrWhere);

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER_STATISTICS . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        return $resultSet;
    }

    public function listsByStatisticsArea(array $params)
    {
        $arrBind = $arrWhere = [];

        $this->db->bindParameterByDateRange('statisticsDt', $params, $arrBind, $arrWhere, 'tableMemberStatistics');

        $this->db->strField = 'statisticsDt, total, seoul ,busan ,daegu ,incheon ,gwangju ,daejeon ,ulsan ,sejong ,gyeonggi ,gangwon ,chungbuk ,chungnam ,jeonbuk ,jeonnam ,gyeongbuk ,gyeongnam ,jeju ,areaOther';
        $this->db->strWhere = join(' AND ', $arrWhere);

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER_STATISTICS . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        return $resultSet;
    }
}
