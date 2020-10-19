<?php
namespace Bundle\Component\Member;

use App;
use Component\Database\DBTableField;
use LogHandler;
use Request;
use Session;


/**
 * Class 운영자별 검색값 설정
 * @package Bundle\Component\Member
 * @author  cjb3333@godo.co.kr
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

class ManagerSearchConfig
{

    /**
     * @var \Framework\Database\DBTool $db
     */
    protected $db;

    /**
     * @var mixed
     */
    protected $managerSno;

    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        $this->managerSno = Session::get('manager.sno');
    }

    /**
     * setGetData
     * Request::get 재설정
     *
     */
    public function setGetData()
    {
        if (empty(Request::get()->get('searchFl'))) {
            $config = $this->getSearchConfig(gd_php_self());
            if (!empty($config)) {
                $searchName = (preg_match('/order/',gd_php_self()) && gd_isset(\Session::get('manager.isOrderSearchMultiGrid'), 'n') == 'y' ) ? 'treatDate': 'searchDate';
                foreach ($config as $key => $value) {
                    if ($key == 'searchPeriod' && is_numeric($value) && $value > -1) {
                        $sDate = date('Y-m-d', strtotime('-' . $value . ' day'));
                        $eDate = date('Y-m-d');
                        if($value == 1) {
                            $eDate = $sDate;
                        }
                        $$searchName = [
                            $sDate, $eDate
                        ];
                        Request::get()->set($searchName, $$searchName);
                    }
                    if ($key == 'periodFl' && !empty($value) && $value > -1) {
                        $treatDate = [
                            date('Y-m-d', strtotime('-' . $value . ' day')),
                            date('Y-m-d'),
                        ];
                        Request::get()->set('treatDate', $treatDate);
                    }
                    if ($key == 'searchDate' || $key == 'treatDate' || ($key == 'treatTime' && (!is_numeric($config['searchPeriod']) || $config['searchPeriod'] <= -1))) {
                        continue;
                    }

                    Request::get()->set($key, $value); //$_GET 변수 검색설정값으로 재정의
                }
            }
        }
    }

    /**
     * getSearchConfig
     * 검색설정값 출력
     *
     * @param string $applyPath
     *
     * @return array
     */
    public function getSearchConfig($applyPath) {

        $fieldType = DBTableField::getFieldTypes('tableManagerSearchConfig');
        $strSQL = 'SELECT data FROM ' . DB_MANAGER_SEARCH_CONFIG . ' WHERE managerSno = ? AND applyPath = ? AND isOrderSearchMultiGrid = ?';
        $this->db->bind_param_push($arrBind, $fieldType['managerSno'], $this->managerSno);
        $this->db->bind_param_push($arrBind, $fieldType['applyPath'], $applyPath);
        $this->db->bind_param_push($arrBind, $fieldType['isOrderSearchMultiGrid'], gd_isset(\Session::get('manager.isOrderSearchMultiGrid'), 'n'));
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        unset($arrBind);
        return json_decode($getData['data'],true);

    }

    /**
     * setSearchConfig
     * 검색설정값 등록/수정
     *
     * @param array $getData
     * @param array $exceptKey
     *
     */
    public function setSearchConfig($getData, $exceptKey)
    {
        $setData['isOrderSearchMultiGrid'] = gd_isset(\Session::get('manager.isOrderSearchMultiGrid'), 'n');

        // applypath에 파라미터가 있는 경우 제외
        $applyPath = $getData['applyPath'];
        $tmpApplyPath = explode('?', $applyPath);
        if (count($tmpApplyPath) > 1) {
            $applyPath = $tmpApplyPath[0];
        }

        $setData['managerSno'] = $this->managerSno;
        $setData['applyPath'] = $applyPath;

        unset($getData['applyPath']);

        if($setData['isOrderSearchMultiGrid'] == 'y') {
            $exceptKey = array_diff($exceptKey, ['searchPeriod']);
        }

        foreach ($getData as $key => $value) {
            if (in_array($key, $exceptKey)) {
                unset($getData[$key]);
            }
        }

        $setData['data'] = json_encode($getData, JSON_UNESCAPED_UNICODE);

        $config = $this->getSearchConfig($setData['applyPath']);

        if (!empty($config)) {//수정

            $this->db->bind_param_push($arrBind, 's', $setData['data']);
            $this->db->bind_param_push($arrBind, 'i', $setData['managerSno']);
            $this->db->bind_param_push($arrBind, 's', $setData['applyPath']);
            $this->db->bind_param_push($arrBind, 's', $setData['isOrderSearchMultiGrid']);
            $this->db->set_update_db(DB_MANAGER_SEARCH_CONFIG, 'data = ?', 'managerSno = ? AND applyPath = ? AND isOrderSearchMultiGrid = ?', $arrBind);

        } else {//등록
            $arrBind = $this->db->get_binding(DBTableField::tableManagerSearchConfig(), $setData, 'insert');
            $this->db->set_insert_db(DB_MANAGER_SEARCH_CONFIG, $arrBind['param'], $arrBind['bind'], 'y');
        }
    }

    public function setIsOrderSearchMultiGrid($getData)
    {
        $strSql = "UPDATE " . DB_MANAGER . " SET isOrderSearchMultiGrid = '".$getData['isOrderSearchMultiGrid']."' WHERE sno = ".$this->managerSno;
        $this->db->query($strSql);
        Session::set('manager.isOrderSearchMultiGrid',$getData['isOrderSearchMultiGrid']);
    }
}
