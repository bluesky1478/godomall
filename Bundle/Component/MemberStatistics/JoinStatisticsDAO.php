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

/**
 * Class JoinStatisticsDAO
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class JoinStatisticsDAO extends \Component\AbstractComponent
{
    public function countByEntryDateTimeWeek(array $params)
    {
        $arrBind = $arrWhere = [];

        $this->db->bindParameterByDateRange('entryDt', $params, $arrBind, $arrWhere, 'tableMember');

        $fields = [];
        $fields[] = 'entryDt, COUNT(1) AS count';

        $this->db->strField = join('', $fields);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = 'WEEKDAY(entryDt)';
        $this->db->strOrder = 'entryDt ASC';

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        return $resultSet;
    }

    public function listsWeek(array $params)
    {
        $arrBind = $arrWhere = [];

        $this->db->bindParameterByDateRange('entryDt', $params, $arrBind, $arrWhere, 'tableMember');

        $fields = [];
        $fields[] = 'WEEKDAY(entryDt) AS weekNo, COUNT(1) AS totalCount';
        $fields[] = ', COUNT(IF(entryPath=\'pc\', 1, IF(entryPath=\'\', 1, NULL))) AS pcCount';
        $fields[] = ', COUNT(IF(entryPath=\'mobile\', 1, NULL)) AS mobileCount';

        $this->db->strField = join('', $fields);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = 'WEEKDAY(entryDt)';
        $this->db->strOrder = 'weekNo ASC';

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        return $resultSet;
    }

    public function countByEntryDateTimeMonth(array $params)
    {
        $arrBind = $arrWhere = [];

        $this->db->bindParameterByDateRange('entryDt', $params, $arrBind, $arrWhere, 'tableMember');

        $fields = [];
        $fields[] = 'entryDt, COUNT(1) AS count';

        $this->db->strField = join('', $fields);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = 'MONTH(entryDt)';
        $this->db->strOrder = 'entryDt ASC';

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        return $resultSet;
    }

    public function listsMonth(array $params)
    {
        $arrBind = $arrWhere = [];

        $this->db->bindParameterByDateRange('entryDt', $params, $arrBind, $arrWhere, 'tableMember');

        $fields = [];
        $fields[] = 'DATE_FORMAT(entryDt, \'%Y%m\') AS entryDate, COUNT(1) AS totalCount';
        $fields[] = ', COUNT(IF(entryPath=\'pc\', 1, IF(entryPath=\'\', 1, NULL))) AS pcCount';
        $fields[] = ', COUNT(IF(entryPath=\'mobile\', 1, NULL)) AS mobileCount';

        $this->db->strField = join('', $fields);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = 'DATE_FORMAT(entryDt, \'%Y%m\')';
        $this->db->strOrder = 'entryDate ASC';

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        return $resultSet;
    }

    public function countByEntryDateTimeHour(array $params)
    {
        $arrBind = $arrWhere = [];

        $this->db->bindParameterByDateRange('entryDt', $params, $arrBind, $arrWhere, 'tableMember');

        $fields = [];
        $fields[] = 'entryDt, COUNT(1) AS count';

        $this->db->strField = join('', $fields);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = 'DAY(entryDt), HOUR(entryDt)';
        $this->db->strOrder = 'entryDt ASC';

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        return $resultSet;
    }

    public function listsHour(array $params)
    {
        $arrBind = $arrWhere = [];

        $this->db->bindParameterByDateRange('entryDt', $params, $arrBind, $arrWhere, 'tableMember');

        $fields = [];
        $fields[] = 'HOUR(entryDt) AS hour, COUNT(1) AS totalCount';
        $fields[] = ', COUNT(IF(entryPath=\'pc\', 1, IF(entryPath=\'\', 1, NULL))) AS pcCount';
        $fields[] = ', COUNT(IF(entryPath=\'mobile\', 1, NULL)) AS mobileCount';

        $this->db->strField = join('', $fields);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = 'HOUR(entryDt)';
        $this->db->strOrder = 'hour ASC';

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        return $resultSet;
    }

    public function listsDay(array $params)
    {
        $arrBind = $arrWhere = [];

        $this->db->bindParameterByDateRange('entryDt', $params, $arrBind, $arrWhere, 'tableMember');

        $fields = [];
        $fields[] = 'DATE_FORMAT(entryDt, \'%Y%m%d\') AS entryDate, COUNT(1) AS totalCount';
        $fields[] = ', COUNT(IF(entryPath=\'pc\', 1, IF(entryPath=\'\', 1, NULL))) AS pcCount';
        $fields[] = ', COUNT(IF(entryPath=\'mobile\', 1, NULL)) AS mobileCount';

        $this->db->strField = join('', $fields);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = 'DATE_FORMAT(entryDt, \'%Y%m%d\')';
        $this->db->strOrder = 'entryDate ASC';

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        return $resultSet;
    }

    public function countByEntryDateTimeDay(array $params)
    {
        $arrBind = $arrWhere = [];

        $this->db->bindParameterByDateRange('entryDt', $params, $arrBind, $arrWhere, 'tableMember');

        $fields = [];
        $fields[] = 'entryDt, COUNT(1) AS count';

        $this->db->strField = join('', $fields);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = 'DAY(entryDt)';
        $this->db->strOrder = 'entryDt ASC';

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        return $resultSet;
    }
}
