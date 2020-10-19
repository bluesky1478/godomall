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

namespace Bundle\Component\Scm;

use Component\Database\DBTableField;
use Component\Member\Manager;

/**
 * Class Scm
 * @package Bundle\Component\Scm
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class Scm
{
    /** @var \Framework\Database\DBTool $db */
    protected $db;
    /** @var array */
    protected $arrBind = [];
    protected $arrWhere = [];
    protected $checked = [];
    protected $search = [];


    public function __construct()
    {
        if (!\is_object($this->db)) {
            $this->db = \App::load('DB');
        }
    }

    /**
     * selectOperationScmList
     *
     * @return array
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function selectOperationScmList()
    {
        $this->db->strField = 'scmNo, companyNm, functionAuth';
        $this->db->strOrder = 'scmNo ASC';
        $this->db->strWhere = 'scmType=\'y\'';
        if (!Manager::useProvider()) {
            $this->db->strWhere .= ' AND scmNo=' . DEFAULT_CODE_SCMNO;
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_MANAGE . ' as sm ' . implode(' ', $query);

        return $this->db->query_fetch($strSQL);
    }

    /**
     * 장바구니내 공급사 정보 출력
     *
     * @param array $arrScmNo 공급사 고유 번호 배열
     *
     * @return array 공급사 정보
     */
    public function getCartScmInfo($arrScmNo)
    {
        // scmNo 정보가 없는 경우 return
        if (empty($arrScmNo) === true) {
            return false;
        }

        $arrScmNo = array_unique($arrScmNo); // scmNo 값의 배열중 중복 배열을 제거
        $arrBind = [];
        foreach ($arrScmNo as $scmNo) {
            $tmpParam[] = '?';
            $this->db->bind_param_push($arrBind, 'i', $scmNo);
        }
        $arrInclude['scm'] = [
            'scmNo',
            'companyNm',
            'scmType',
            'scmKind',
        ];
        $arrFieldScm = DBTableField::setTableField('tableScmManage', $arrInclude['scm'], null, 'sm');
        $this->db->strWhere = 'scmNo IN (' . implode(' , ', $tmpParam) . ')';

        // SCM Data
        $data = $this->getScmInfo(null, implode(', ', $arrFieldScm), $arrBind, true);

        // SCM 정보가 없는 경우 return
        if (empty($data) === true) {
            return false;
        }

        $scmData = [];
        foreach ($data as $sVal) {
            $scmData[$sVal['scmNo']] = $sVal;
        }

        return $scmData;
    }

    /**
     * 공급사 정보 출력
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param string $scmNo     공급사 고유 번호 (기본 null)
     * @param string $scmField  출력할 필드명 (기본 null)
     * @param array  $arrBind   bind 처리 배열 (기본 null)
     * @param string $dataArray return 값을 배열처리 (기본값 false)
     *
     * @return array 공급사 정보
     */
    public function getScmInfo($scmNo = null, $scmField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($scmNo) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " sm.scmNo = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " sm.scmNo = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $scmNo);
        }
        if ($scmField) {
            if ($this->db->strField) {
                $this->db->strField = $scmField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $scmField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_MANAGE . ' as sm ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }
}
