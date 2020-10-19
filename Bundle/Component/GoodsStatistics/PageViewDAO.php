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

namespace Bundle\Component\GoodsStatistics;


use Component\AbstractComponent;
use Component\Database\DBTableField;

/**
 * Class PageViewDAO
 * @package Bundle\Component\GoodsStatistics
 * @author  yjwee
 */
class PageViewDAO extends \Component\AbstractComponent
{
    public function __construct()
    {
        parent::__construct();
        $this->tableFunctionName = 'tableGoodsPageView';
        $this->tableName = DB_GOODS_PAGE_VIEW;
    }

    public function insertPageView(array $goodsPageView)
    {
        $arrBind = $this->db->get_binding(DBTableField::tableGoodsPageView(), $goodsPageView, 'insert');
        $this->db->set_insert_db(DB_GOODS_PAGE_VIEW, $arrBind['param'], $arrBind['bind'], 'y');

        return $this->db->insert_id();
    }

    public function updateStartPageView(array $pageView)
    {
        $arrBind = $this->db->get_binding(DBTableField::tableGoodsPageView(), $pageView, 'update', ['startCount']);
        $this->db->bind_param_push($arrBind['bind'], 'i', $pageView['mallSno']);
        $this->db->bind_param_push($arrBind['bind'], 's', $pageView['pageUrl']);
        $this->db->bind_param_push($arrBind['bind'], 's', $pageView['viewDate']);
        $this->db->set_update_db(DB_GOODS_PAGE_VIEW, $arrBind['param'], 'mallSno=? AND pageUrl=? AND viewDate=?', $arrBind['bind']);
    }

    public function updateEndPageView(array $pageView)
    {
        $arrBind = $this->db->get_binding(DBTableField::tableGoodsPageView(), $pageView, 'update', ['endCount']);
        $this->db->bind_param_push($arrBind['bind'], 'i', $pageView['mallSno']);
        $this->db->bind_param_push($arrBind['bind'], 's', $pageView['pageUrl']);
        $this->db->bind_param_push($arrBind['bind'], 's', $pageView['viewDate']);
        $this->db->set_update_db(DB_GOODS_PAGE_VIEW, $arrBind['param'], 'mallSno=? AND pageUrl=? AND viewDate=?', $arrBind['bind']);
    }

    public function selectPageUrl($pageUrl, $viewDate)
    {
        $arrParam = [
            'pageUrl'  => $pageUrl,
            'viewDate' => $viewDate,
        ];
        $arrBind = $arrWhere = [];
        $this->db->strField = '*';
        $this->db->bindParameter('pageUrl', $arrParam, $arrBind, $arrWhere, $this->tableFunctionName);
        $this->db->bindParameter('viewDate', $arrParam, $arrBind, $arrWhere, $this->tableFunctionName);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();

        return $this->db->query_fetch('SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_PAGE_VIEW . implode(' ', $query), $arrBind, false);
    }

    public function getTodayPageUrlCount($pageUrl)
    {
        return $this->getCount(DB_GOODS_PAGE_VIEW, '1', ' WHERE pageUrl=\'' . $pageUrl . '\' AND regDt LIKE CONCAT(\'%\', \'' . gd_date_format('Y-m-d', 'now') . '\' ,\'%\')');
    }

    public function lists(array  $requestParams, $offset = null, $limit = null)
    {
        $arrBind = $arrWhere = [];
        $this->db->bindParameterByDateRange('viewDate', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName);
        $this->db->strField = 'pageUrl, SUM(pageViewCount) as pageViewCount, SUM(pageViewSec) as pageViewSec';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = ' pageUrl';
        $this->db->strOrder = ' pageViewCount DESC';
        if (is_null($offset) === false && is_null($limit) === false) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }
        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_GOODS_PAGE_VIEW . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        unset($arrBind, $arrWhere, $arrQuery);

        return $resultSet;
    }

    public function listsByStart(array  $requestParams, $offset = null, $limit = null)
    {
        $arrBind = $arrWhere = [];
        $this->db->bindParameterByDateRange('viewDate', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName);
        $this->db->strField = 'pageUrl, SUM(startCount) as startCount';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = ' pageUrl';
        $this->db->strOrder = ' startCount DESC';
        if (is_null($offset) === false && is_null($limit) === false) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }
        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_GOODS_PAGE_VIEW . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        unset($arrBind, $arrWhere, $arrQuery);

        return $resultSet;
    }

    public function listsByEnd(array  $requestParams, $offset = null, $limit = null)
    {
        $arrBind = $arrWhere = [];
        $this->db->bindParameterByDateRange('viewDate', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName);
        $this->db->strField = 'pageUrl, SUM(endCount) as endCount';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = ' pageUrl';
        $this->db->strOrder = ' endCount DESC';
        if (is_null($offset) === false && is_null($limit) === false) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }
        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_GOODS_PAGE_VIEW . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);

        unset($arrBind, $arrWhere, $arrQuery);

        return $resultSet;
    }

    /**
     * 지정한 컬럼의 Sum 합계
     *
     * @param string $column        컬럼명
     * @param array  $requestParams 데이터
     *
     * @return array|object 컬럼 Sum 결과
     */
    public function getTotal($column, $requestParams)
    {
        $arrBind = $arrWhere = [];
        $this->db->bindParameterByDateTimeRange('viewDate', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName);
        $this->db->strField = 'SUM(' . $column . ') as total';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_GOODS_PAGE_VIEW . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind, false);

        return $resultSet['total'];
    }
}
