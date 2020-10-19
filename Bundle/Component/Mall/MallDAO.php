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

namespace Bundle\Component\Mall;

use App;
use Component\Database\DBTableField;
use Framework\Object\SingletonTrait;

/**
 * Class MallDAO
 * @package Bundle\Component\Mall
 * @author  yjwee
 * @method static MallDAO getInstance
 */
class MallDAO
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
        $this->fields = DBTableField::getFieldTypes('tableMall');
    }

    /**
     * 상점 등록
     *
     * @param $mallData
     *
     * @return int|string
     */
    public function insertMall($mallData)
    {
        $bind = $this->db->get_binding(DBTableField::tableMall(), $mallData, 'insert');
        $this->db->set_insert_db(DB_MALL, $bind['param'], $bind['bind'], 'y');

        return $this->db->insert_id();
    }

    /**
     * 상점 수정
     *
     * @param $mallData
     */
    public function updateMall($mallData)
    {
        //@formatter:off
        $exclude = ['sno', 'domainFl', 'globalCurrencyNo', 'regDt', 'mallName'];
        //@formatter:on
        $bind = $this->db->get_binding(DBTableField::tableMall(), $mallData, 'update', null, $exclude);
        $this->db->bind_param_push($bind['bind'], 'i', $mallData['sno']);
        $this->db->set_update_db(DB_MALL, $bind['param'], 'sno = ?', $bind['bind']);
    }

    /**
     * 상점 삭제 플래그 수정
     *
     * @param $sno
     * @param $flag
     */
    public function updateDeleteFlag($sno, $flag)
    {
        //@formatter:off
        $include = ['deleteFl'];
        //@formatter:on
        $bind = $this->db->get_binding(DBTableField::tableMall(), ['deleteFl' => $flag], 'update', $include);
        $this->db->bind_param_push($bind['bind'], 'i', $sno);
        $this->db->set_update_db(DB_MALL, $bind['param'], 'sno = ?', $bind['bind']);
    }

    /**
     * 상점 조회
     *
     * @param string|integer $value 상점정보
     * @param string         $key   컬럼명
     *
     * @return array|object
     */
    public function selectMall($value, $key = 'sno')
    {
        $this->db->strField = '*';
        $this->db->strWhere = $key . ' = ?';
        $this->db->bind_param_push($bind, $this->fields[$key], $value);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MALL . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $bind, false);
    }

    /**
     * 상점 리스트 조회
     *
     * @param array $params
     *
     * @return array|object
     */
    public function selectMallList(array $params = [])
    {
        $bind = [];
        foreach ($params as $key => $value) {
            $this->db->bind_param_push($bind, $this->fields[$key], $value);
        }
        $this->db->strField = '*';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MALL . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $bind);
    }

    /**
     * 현재 사용 중인 상점 리스트 조회
     *
     * @return array|object
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function selectUsableMallList()
    {
        $this->db->strField = '*';
        $this->db->strWhere = 'useFl=\'y\'';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MALL . implode(' ', $query);

        return $this->db->query_fetch($strSQL);
    }

    /**
     * 상점 수 조회
     *
     * @param null $value
     * @param null $key
     *
     * @return int
     */
    public function selectCount($value = null, $key = null)
    {
        $bind = [];
        $this->db->strField = 'count(*) AS cnt';
        if (empty($value) === false && empty($key) === false) {
            $this->db->strWhere = $key . ' = ?';
            $this->db->bind_param_push($bind, $this->fields[$key], $value);
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MALL . implode(' ', $query);
        $resultSet = $this->db->query_fetch($strSQL, $bind, false);

        return intval($resultSet['cnt']);
    }

    /**
     * 연결된 도메인일 경우 상점 정보를 반환하는 함수
     *
     * @param $domain
     *
     * @return array|object
     */
    public function selectConnectDomain($domain)
    {
        $this->db->strField = ' * ';
        $this->db->strWhere = 'JSON_SEARCH(connectDomain, \'all\', ?, null, \'$.connect[*]\') IS NOT NULL';
        $this->db->bind_param_push($bind, $this->fields['connectDomain'], $domain);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MALL . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $bind, false);
    }

    /**
     * 기본몰&사용했던 멀티상점 정보 반환
     *
     * @param bool $count
     * @param bool $arrFl
     *
     * @return array|object
     */
    public function useDomainList($count = false, $arrFl = true)
    {
        if ($count === true) {
            $this->db->strField = 'COUNT(*) as cnt';
        } else {
            $this->db->strField = '*';
        }
        $this->db->strWhere = 'sno = ? OR modDt IS NOT NULL';
        $this->db->bind_param_push($bind, 'i', 1);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MALL . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $bind, $arrFl);
    }

    /**
     * 표준 국가 데이터 가져오기
     *
     * @param string $code
     *
     * @param bool $korAscending
     * @return array|object
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function selectCountries($code = null, $korAscending = true)
    {
        if ($code !== null) {
            $this->db->strWhere = 'code = ?';
            $this->db->bind_param_push($bind, 's', $code);
        }
        $this->db->strField = 'code, countryNameKor, countryName, callPrefix, isoNo, emsAreaCode';
        if ($korAscending === true) {
            $this->db->strOrder = 'countryNameKor ASC';
        } else {
            $this->db->strOrder = 'countryName ASC';
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COUNTRIES . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $bind, false);
    }

    /**
     * 표준 통화 데이터 가져오기
     *
     * @param string $code
     *
     * @return array|object
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function selectCurrencies($code = null)
    {
        if ($code !== null) {
            $this->db->strWhere = 'code = ?';
            $this->db->bind_param_push($bind, 's', $code);
        }
        $this->db->strField = implode(',', DBTableField::getFieldTypes('tableCurrencies'));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_CURRENCIES . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $bind, false);
    }
}
