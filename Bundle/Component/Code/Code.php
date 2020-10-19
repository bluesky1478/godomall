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
namespace Bundle\Component\Code;

use Component\Database\DBTableField;
use Component\Validator\Validator;
use Framework\Debug\Exception\Except;
use Framework\Utility\ArrayUtils;
use Logger;

/**
 * Class Code
 * @package Bundle\Component\Code
 * @author  gise, sunny, artherot
 */
class Code
{
    const GROUP_CATEGORY_CODE_MEMBER = '01';
    const GROUP_CODE_MEMBER_INTEREST = '01001';
    const GROUP_CODE_MEMBER_JOB = '01002';

    const ECT_INVALID_ARG = 'Code.ECT_INVALID_ARG';

    const TEXT_REQUIRE_VALUE = '%s은(는) 필수 항목 입니다.';
    const TEXT_NONEXISTENT_VALUE = '%s 항목이 존재하지 않습니다.';

    protected $db;
    private $arrBind = [];        // 리스트 검색관련
    private $arrWhere = [];        // 리스트 검색관련
    private $checked = [];        // 리스트 검색관련
    private $search = [];        // 리스트 검색관련
    protected $codeTableName = DB_CODE;
    protected $mallSno = DEFAULT_MALL_NUMBER;
    protected $isNotDefaultMall = false;
    protected $tableMethodName = 'tableCode';

    /**
     * 생성자
     */
    public function __construct($mallSno = 1)
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        if (\Globals::get('gGlobal.isUse')) {
            $mallSno = $mallSno ? $mallSno : DEFAULT_MALL_NUMBER;
            $this->mallSno = $mallSno;
            if ($mallSno != DEFAULT_MALL_NUMBER) {
                $this->codeTableName = DB_CODE_GLOBAL;
                $this->tableMethodName = 'tableCodeGlobal';
                $this->isNotDefaultMall = true;
            }
        }
    }

    protected function setGlobal($mallSno)
    {

    }

    /**
     * save
     *  코드 정보변경
     *
     * @param $getData
     *
     * @throws \Exception
     */
    public function save($getData)
    {
        if (isset($getData['itemCd']) === false) {
            throw new \Exception(self::TEXT_ERROR_VALUE);
        }
        unset($getData['mode']);
        // 데이타 체크
        $groupCd = $getData['groupCd'];
        unset($getData['groupCd']);

        if ($getData['itemNmAdd']) {
            foreach ($getData['itemNm'] as $k => $v) {
                if ($getData['itemNmAdd'][$k]) $getData['itemNm'][$k] = $v . STR_DIVISION . str_replace("#", "", $getData['itemNmAdd'][$k]);
            }
            unset($getData['itemNmAdd']);
        }

        $_codeList = $this->getCodeList(['groupCd' => $groupCd]);
        $_codeList = $_codeList['data'];
        foreach ($getData['itemNm'] as $key => $val) {
            $getData['sort'][$key] = $key + 1;
        }

        foreach ($_codeList as $key => $val) {
            $dbCodeList[$val['itemCd']] = $val;
        }
        $reqData = [];
        $itemCd = [];
        foreach ($getData as $key => $val) {
            if ($key == 'itemCd') {
                foreach ($val as $_key => $_val) {
                    $itemCd[] = $_val;
                    $reqData[$_val] = [$key => $_val];
                }
            } else {
                $i = 0;
                foreach ($val as $_key => $_val) {
                    $reqData[$itemCd[$i]][$key] = $_val;
                    $i++;
                }
            }
        }

        try {
            foreach ($dbCodeList as $key => $val) {
                if (array_key_exists($key, $reqData) === false) {   //db에 존재하지 않으면 삭제
                    if ($this->isNotDefaultMall) {
                        $query = "DELETE FROM " . $this->codeTableName . " WHERE isUpdatableText = 'y' AND isUpdatableUse = 'y' AND itemCd= ? AND mallSno = " . $this->mallSno;
                    } else {
                        $query = "DELETE FROM " . $this->codeTableName . " WHERE isUpdatableText = 'y' AND isUpdatableUse = 'y' AND itemCd= ? ";
                    }

                    $this->db->bind_query(
                        $query, [
                            's',
                            $val['itemCd'],
                        ]
                    );
                }
            }
            foreach ($reqData as $key => $val) {
                if (array_key_exists($key, $dbCodeList)) {   //키가 존재하면 수정
                    if ($val['itemNm'] != $dbCodeList[$key]['itemNm'] || $val['sort'] != $dbCodeList[$key]['sort'] || $val['useFl'] != $dbCodeList[$key]['useFl']) {
                        $query = "UPDATE " . $this->codeTableName . " SET  itemNm=? ,sort=? ,useFl = ? WHERE  itemCd= ? ";
                        if ($this->isNotDefaultMall) {
                            $query .= " AND mallSno = " . $this->mallSno;
                        }
                        $this->db->bind_query(
                            $query, [
                                'siss',
                                $val['itemNm'],
                                $val['sort'],
                                $val['useFl'],
                                $val['itemCd'],
                            ]
                        );
                    }
                } else {  //존재하지 않는키면 추가
                    $groupCd = \Request::post()->get('groupCd');
                    $queryItemCd = "SELECT MAX(itemCd)+1 as newItemCd FROM " . $this->codeTableName . " WHERE 1 ";
                    if ($this->isNotDefaultMall) {
                        $queryItemCd .= " AND mallSno = " . $this->mallSno;
                    }
                    $queryItemCd .= " AND groupCd = '" . $groupCd . "'";
                    $result = $this->db->query_fetch($queryItemCd, null, false);
                    $maxItemCd = $result['newItemCd'];
                    $newItemCd = sprintf("%08d", $maxItemCd);

                    if ($this->isNotDefaultMall) {
                        $query = "INSERT INTO " . $this->codeTableName . "(mallSno , itemCd,groupCd, itemNm,sort,useFl) VALUES(" . $this->mallSno . ",?,?,?,?,?)";
                    } else {
                        $query = "INSERT INTO " . $this->codeTableName . "(itemCd,groupCd, itemNm,sort,useFl) VALUES(?,?,?,?,?)";
                    }

                    $this->db->bind_query(
                        $query, [
                            'sssis',
                            $newItemCd,
                            $groupCd,
                            $val['itemNm'],
                            $val['sort'],
                            $val['useFl'],
                        ]
                    );
                }
            }

        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * 코드 데이터 조작
     * @return bool
     * @throws Except
     * @internal param mixed $args
     *
     */
    public function codeHandle()
    {
        $argArr = func_get_args();
        switch ($argArr[0]) {
            case "setSort" :
                return $this->setSort($argArr[1]);
                break;
            case "modifyGroupCode" :
                return $this->modifyGroupCode($argArr[1], $argArr[2]);
                break;
            case "modifyCode" :
                return $this->modifyCode($argArr[1], $argArr[2]);
                break;
            case "registerCode" :
                return $this->registerCode($argArr[1], $argArr[2]);
                break;
            case "registerGroupCode" :
                return $this->registerGroupCode($argArr[1]);
                break;
            case "deleteCode" :
                return $this->deleteCode($argArr[1]);
                break;
        }
    }

    /**
     * 코드 데이터 조회
     * @return mixed
     * @internal param mixed $args
     *
     */
    public function codeFetch()
    {
        Logger::info(__METHOD__);
        $argArr = func_get_args();
        Logger::debug(__METHOD__, $argArr);
        switch ($argArr[0]) {
            case "getCodeData" :
                return $this->getCodeData($argArr[1]);
                break;
            case "getCodeList" :
                return $this->getCodeList($argArr[1]);
                break;
            case "getGroupCode" :
                return $this->getGroupCode(gd_isset($argArr[1]));
                break;
            case "getCodeCount" :
                return $this->getCodeCount($argArr[1]);
                break;
            case "categoryGroup" :
                return $this->getCategoryGroupCodeData();
                break;
        }
    }

    /**
     * 그룹코드 생성
     * @return string
     */
    private function newGroupCode()
    {
        $strSQL = 'SELECT MAX(groupCd) from ' . $this->codeTableName;
        if ($this->isNotDefaultMall) {
            $strSQL .= " WHERE mallSno = " . $this->mallSno;
        }
        list($tmp) = $this->db->fetch($strSQL, 'row');

        return sprintf('%03d', ($tmp + 1));
    }

    private function getCategoryGroupCodeData()
    {
        $strSQL = "SELECT * FROM " . $this->codeTableName . " WHERE length(itemCd) =2";
        if ($this->isNotDefaultMall) {
            $strSQL .= " AND mallSno = " . $this->mallSno;
        }
        $data = $this->db->query_fetch($strSQL, null, false);

        foreach ($data as $key => $val) {
            $result[$val['groupCd']] = $val['itemNm'];
        }

        return $result;
    }

    /**
     * getCodeItemName
     *
     * @static
     *
     * @param $itemCd
     *
     * @return mixed
     */
    public static function getCodeItemName($itemCd, $mallSno = 1)
    {
        $mallSno = $mallSno ? $mallSno : DEFAULT_MALL_NUMBER;
        if (\Globals::get('gGlobal.isUse')) {
            if ($mallSno != DEFAULT_MALL_NUMBER) {
                $codeTableName = DB_CODE_GLOBAL;
            } else {
                $codeTableName = DB_CODE;
            }
        } else {
            $codeTableName = DB_CODE;
        }

        $db = \App::load('DB');
        $strSQL = "SELECT itemNm FROM  " . $codeTableName . " WHERE 1 ";
        if ($mallSno != DEFAULT_MALL_NUMBER) {
            $strSQL .= " AND mallSno = " . $mallSno;
        }
        $strSQL .= " AND itemCd='" . $itemCd . "' ";


        list($itemNm) = $db->fetch($strSQL, 'row');

        return $itemNm;
    }


    /**
     * 코드 생성
     *
     * @param  string $groupCd
     *
     * @return string
     */
    private function newCode($groupCd)
    {
        $strSQL = 'SELECT MAX(substring(itemCd,4)) FROM ' . $this->codeTableName . ' WHERE 1 ';
        if ($this->isNotDefaultMall) {
            $strSQL .= " AND mallSno = " . $this->mallSno;
        }
        $strSQL .= ' AND groupCd=\'' . $groupCd . '\'';
        list($tmp) = $this->db->fetch($strSQL, 'row');

        return $groupCd . sprintf('%03d', ($tmp + 1));
    }

    /**
     * 그룹 코드 등록
     *
     * @param  string $itemNm
     *
     * @return bool
     * @throws Except
     */
    private function registerGroupCode($itemNm)
    {
        // 그룹명 체크
        if (Validator::required(gd_isset($itemNm)) === false) {
            throw new \Exception(__('그룹명 은(는) 필수 항목 입니다.'));
        }

        // 그룹 코드 생성
        $newGroupCd = $this->newGroupCode();

        // 기본값 설정
        if ($this->isNotDefaultMall) {
            $tmpCode = DBTableField::tableCodeGlobal();
        } else {
            $tmpCode = DBTableField::tableCode();
        }

        foreach ($tmpCode as $key => $val) {
            if ($val['typ'] == 'i') {
                $arrData[$val['val']] = (int) $val['def'];
            } else {
                $arrData[$val['val']] = $val['def'];
            }
        }

        // 그룹 저장
        $arrData['groupCd'] = $newGroupCd;
        $arrData['itemCd'] = $newGroupCd;
        $arrData['sort'] = (int) $newGroupCd;
        $arrData['itemNm'] = $itemNm;
        $arrBind = $this->db->get_binding($tmpCode, $arrData, 'insert');
        $this->db->set_insert_db($this->codeTableName, $arrBind['param'], $arrBind['bind'], 'y');
    }

    /**
     * 코드 등록
     *
     * @param  string $itemNm ,$groupCd
     *
     * @param         $groupCd
     *
     * @return bool
     * @throws Except
     */
    private function registerCode($itemNm, $groupCd)
    {
        // 코드명 체크
        if (Validator::required(gd_isset($itemNm)) === false) {
            throw new \Exception(__('코드명 은(는) 필수 항목 입니다.'));
        }

        // 그룹코드 체크
        if (Validator::required(gd_isset($groupCd)) === false) {
            throw new \Exception(__('그룹코드 은(는) 필수 항목 입니다.'));
        }

        // 코드 생성
        $newItemCd = $this->newCode($groupCd);

        // 기본값 설정
        if ($this->isNotDefaultMall) {
            $tmpCode = DBTableField::tableCodeGlobal();
        } else {
            $tmpCode = DBTableField::tableCode();
        }

        foreach ($tmpCode as $key => $val) {
            if ($val['typ'] == 'i') {
                $arrData[$val['val']] = (int) $val['def'];
            } else {
                $arrData[$val['val']] = $val['def'];
            }
        }

        // 코드 저장
        $arrData['groupCd'] = $groupCd;
        $arrData['itemCd'] = $newItemCd;
        $arrData['sort'] = (int) $newItemCd;
        $arrData['itemNm'] = $itemNm;
        $arrBind = $this->db->get_binding($tmpCode, $arrData, 'insert');
        $this->db->set_insert_db($this->codeTableName, $arrBind['param'], $arrBind['bind'], 'y');
    }

    /**
     * 그룹코드 수정
     *
     * @param  string $itemNm ,$groupCd
     *
     * @param         $groupCd
     *
     * @return bool
     * @throws Except
     */
    private function modifyGroupCode($itemNm, $groupCd)
    {
        // 코드명 체크
        if (Validator::required(gd_isset($itemNm)) === false) {
            throw new \Exception(__('그룹명 은(는) 필수 항목 입니다.'));
        }

        // 코드 체크
        if (Validator::required(gd_isset($groupCd)) === false) {
            throw new \Exception(__('그룹코드 은(는) 필수 항목 입니다.'));
        }

        // 코드 저장
        $arrData['groupCd'] = substr($groupCd, 0, 3);
        $arrData['itemNm'] = $itemNm;
        $arrInclude = ['itemNm'];
        if ($this->isNotDefaultMall) {
            $arrData['mallSno'] = $this->mallSno;
            $arrBind = $this->db->get_binding(DBTableField::tableCodeGlobal(), $arrData, 'update', $arrInclude);
        } else {
            $arrBind = $this->db->get_binding(DBTableField::tableCode(), $arrData, 'update', $arrInclude);
        }

        $this->db->bind_param_push($arrBind['bind'], 's', $arrData['groupCd']);
        if ($this->isNotDefaultMall) {
            $this->db->set_update_db($this->codeTableName, $arrBind['param'], 'groupCd = ? AND itemCd = groupCd AND mallSno = ' . $this->mallSno, $arrBind['bind']);
        } else {
            $this->db->set_update_db($this->codeTableName, $arrBind['param'], 'groupCd = ? AND itemCd = groupCd', $arrBind['bind']);
        }
    }

    /**
     * 코드 수정
     *
     * @param  string $itemNm ,$groupCd
     *
     * @param         $itemCd
     *
     * @return bool
     * @throws Except
     */
    private function modifyCode($itemNm, $itemCd)
    {
        // 코드명 체크
        if (Validator::required(gd_isset($itemNm)) === false) {
            throw new \Exception(__('코드명 은(는) 필수 항목 입니다.'));
        }

        // 코드 체크
        if (Validator::required(gd_isset($itemCd)) === false) {
            throw new \Exception(__('코드번호 은(는) 필수 항목 입니다.'));
        }

        // 코드 저장
        $arrData['itemCd'] = $itemCd;
        $arrData['itemNm'] = $itemNm;
        $arrInclude = ['itemNm'];
        if ($this->isNotDefaultMall) {
            $arrBind = $this->db->get_binding(DBTableField::tableCodeGlobal(), $arrData, 'update', $arrInclude);
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['itemCd']);
            $this->db->set_update_db($this->codeTableName, $arrBind['param'], 'itemCd = ? AND itemCd != groupCd AND mallSno = ' . $this->mallSno, $arrBind['bind']);
        } else {
            $arrBind = $this->db->get_binding(DBTableField::tableCode(), $arrData, 'update', $arrInclude);
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['itemCd']);
            $this->db->set_update_db($this->codeTableName, $arrBind['param'], 'itemCd = ? AND itemCd != groupCd', $arrBind['bind']);
        }
    }

    /**
     * 코드 삭제 (실제 삭제처리가 되지 않고 상태만 변경)
     *
     * @param  string $itemCd
     *
     * @return bool
     */
    private function deleteCode($itemCd)
    {
        // 코드 체크
        if (Validator::required(gd_isset($itemCd)) === false) {
            return false;
        }

        // 상태 변경
        $arrData['itemCd'] = $itemCd;
        $arrData['useFl'] = 'n';
        $arrInclude = ['useFl'];
        if ($this->isNotDefaultMall) {
            $arrBind = $this->db->get_binding(DBTableField::tableCodeGlobal(), $arrData, 'update', $arrInclude);
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['itemCd']);
            $this->db->set_update_db($this->codeTableName, $arrBind['param'], 'itemCd = ? AND itemCd != groupCd AND mallSno = ' . $this->mallSno, $arrBind['bind']);
        } else {
            $arrBind = $this->db->get_binding(DBTableField::tableCode(), $arrData, 'update', $arrInclude);
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['itemCd']);
            $this->db->set_update_db($this->codeTableName, $arrBind['param'], 'itemCd = ? AND itemCd != groupCd', $arrBind['bind']);
        }

        return true;
    }

    /**
     * 코드 정렬순서 수정
     *
     * @param $codeSort
     *
     * @return bool
     * @throws Except
     * @internal param mixed $chkSort
     *
     */
    private function setSort($codeSort)
    {
        // 코드명을 배열 처리
        $arrCode = ArrayUtils::removeEmpty(explode(',', $codeSort));

        // 코드명 체크
        if (empty($arrCode) || is_array($arrCode) == false) {
            throw new \Exception(__('코드 항목이 존재하지 않습니다.'));
        }
        foreach ($arrCode as $key => $val) {
            $arrData['sort'] = $key + 1;
            $arrInclude = ['sort'];
            if ($this->isNotDefaultMall) {
                $arrBind = $this->db->get_binding(DBTableField::tableCodeGlobal(), $arrData, 'update', $arrInclude);
                $this->db->bind_param_push($arrBind['bind'], 's', $val);
                $this->db->set_update_db($this->codeTableName, $arrBind['param'], 'itemCd = ? AND itemCd != groupCd AND mallSno = ' . $this->mallSno, $arrBind['bind']);
            } else {
                $arrBind = $this->db->get_binding(DBTableField::tableCode(), $arrData, 'update', $arrInclude);
                $this->db->bind_param_push($arrBind['bind'], 's', $val);
                $this->db->set_update_db($this->codeTableName, $arrBind['param'], 'itemCd = ? AND itemCd != groupCd', $arrBind['bind']);
            }
            unset($arrBind);
        }
    }

    /**
     * 코드 세부데이터 조회
     *
     * @param $arrData
     *
     * @return mixed
     * @internal param string $itemCd
     *
     */
    private function getCodeData($arrData)
    {
        if (empty($arrData['itemCd'])) {
            // 등록인 경우
            $data['mode'] = 'code_register';
            $data['mallSno'] = $this->mallSno;
            $data['groupCd'] = $arrData['groupCd'];
            $data['itemCd'] = gd_isset($arrData['itemCd'], 0);
            gd_isset($data['groupNm']);

            // 기본값 설정
            if ($this->isNotDefaultMall) {
                $tmpCode = DBTableField::tableCodeGlobal();
            } else {
                $tmpCode = DBTableField::tableCode();
            }

            foreach ($tmpCode as $key => $val) {
                if (isset($data[$val['val']]) === false) {
                    if ($val['typ'] == 'i') {
                        $data[$val['val']] = (int) $val['def'];
                    } else {
                        $data[$val['val']] = $val['def'];
                    }
                }
            }
            unset($tmpCode);
        } else {
            // 수정인 경우
            $this->arrBind = [];
            $this->arrWhere = [];
            $this->arrWhere[] = 'itemCd = ?';
            $this->db->bind_param_push($this->arrBind, 's', $arrData['itemCd']);

            $this->db->strField = implode(', ', DBTableField::setTableField($this->tableMethodName, null, null, 'c'));
            $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));

            $subQuery = ', (SELECT itemNm FROM ' . $this->codeTableName . ' sc WHERE c.groupCd = sc.groupCd AND itemCd = groupCd ';
            if ($this->isNotDefaultMall) {
                $subQuery .= " AND sc.mallSno = " . $this->mallSno;
            }
            $subQuery .= " ) as groupNm ";
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . $subQuery . ' FROM ' . $this->codeTableName . ' c ' . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $this->arrBind, false);

            $data['mode'] = 'code_modify';
        }

        $checked = [];
        $checked['stype'][$arrData['stype']] = 'checked="checked"';

        $getData['data'] = $data;
        $getData['checked'] = $checked;

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 그룹코드 데이터 조회
     *
     * @param  string $groupCd
     *
     * @return mixed
     */
    public function getGroupCode($groupCd = null)
    {
        Logger::info(__METHOD__);

        // 키워드 검색
        $this->arrBind = [];
        $this->arrWhere = [];
        if ($groupCd) {
            $this->arrWhere[] = 'groupCd = ?';
            $this->db->bind_param_push($this->arrBind, 's', $groupCd);
        }


        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
        $this->arrWhere[] = 'length(itemCd) = 5';
        $this->arrWhere[] = 'useFl = \'y\'';
        if ($this->isNotDefaultMall) {
            $this->arrWhere[] = 'mallSno = ' . $this->mallSno;
        }

        $this->db->strField = implode(', ', DBTableField::setTableField($this->tableMethodName));
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->codeTableName . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);
        //        foreach ($getData as $key => $val) {
        //            $data[$val['groupCd']] = $val['itemNm'];
        //        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 코드 리스트 데이터 조회
     *
     * @param null $getData
     *
     * @return mixed
     * @internal param string $start ,$limit,$groupCd,$rowItem,$rowMode
     *
     */
    private function getCodeList($getData = null)
    {
        //--- 검색 설정
        $this->arrBind = [];
        $this->arrWhere = [];
        $this->search = [];
        if (gd_isset($getData['categoryGroupCd']) === null) {
            $getData['categoryGroupCd'] = '01';
        }

        if (gd_isset($getData['groupCd']) === null) {
            $getData['groupCd'] = $getData['categoryGroupCd'] . '001';
        }

        $this->search['categoryGroupCd'] = $getData['categoryGroupCd'];
        $this->search['groupCd'] = $getData['groupCd'];

        // 키워드 검색
        // $this->arrWhere[] = 'itemCd != groupCd';
        // 마이앱 사용유무
        $useMyapp = gd_policy('myapp.config')['useMyapp'];
        if ($useMyapp) {
            $this->arrWhere[] = '(length(itemCd) = 8 OR itemCd = 010059996)';
        } else {
            $this->arrWhere[] = 'length(itemCd) = 8';
        }
        //  $this->arrWhere[] = 'useFl = \'y\'';
        $this->arrWhere[] = 'groupCd = ?';
        if ($this->isNotDefaultMall) {
            $this->arrWhere[] = 'mallSno = ' . $this->mallSno;
        }
        $this->db->bind_param_push($this->arrBind, 's', $this->search['groupCd']);

        // 쿼리문
        $this->db->strField = implode(', ', DBTableField::setTableField($this->tableMethodName)) . ', regDt';
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = 'sort ASC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->codeTableName . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);



        $this->db->strField = 'count(*) as cnt';
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->codeTableName . implode(' ', $query);
        $total = $this->db->query_fetch($strSQL, $this->arrBind, false)['cnt'];

        // 검색 레코드 수
        $count = $total;

        $getData['data'] = $data;
        $getData['count'] = $count;
        $getData['search'] = gd_htmlspecialchars($this->search);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 코드 갯수 조회
     *
     * @param  string $groupCd
     *
     * @return int
     */
    private function getCodeCount($groupCd)
    {
        $strSQL = "SELECT COUNT(itemCd) FROM " . $this->codeTableName . " WHERE 1 ";
        if ($this->isNotDefaultMall) {
            $strSQL .= " AND mallSno = " . $this->mallSno;
        }
        $strSQL .= " AND groupCd='" . $this->db->escape($groupCd) . "' AND itemCd != groupCd";

        list($tmp) = $this->db->fetch($strSQL, "row");

        return $tmp;
    }

    /**
     * 그룹의 아이템코드
     * @author sunny
     *
     * @param  string $groupCd 그룹코드
     *
     * @return array
     */
    public static function getGroupItems($groupCd, $mallSno = 1)
    {
        if (empty($groupCd)) {
            return false;
        }

        $mallSno = $mallSno ? $mallSno : DEFAULT_MALL_NUMBER;
        if (\Globals::get('gGlobal.isUse')) {
            if ($mallSno != DEFAULT_MALL_NUMBER) {
                $codeTableName = DB_CODE_GLOBAL;
                $tableMethodName = 'tableCodeGlobal';
            } else {
                $codeTableName = DB_CODE;
                $tableMethodName = 'tableCode';
            }
        } else {
            $codeTableName = DB_CODE;
            $tableMethodName = 'tableCode';
        }


        $db = \App::load('DB');
        $getData = [];
        $strSQL = "SELECT " . implode(', ', DBTableField::setTableField($tableMethodName)) . " FROM " . $codeTableName . " WHERE 1 ";
        if ($mallSno > DEFAULT_MALL_NUMBER) {
            $strSQL .= " AND mallSno = " . $mallSno;
        }
        $strSQL .= " AND itemCd!=groupCd AND groupCd='" . $db->escape($groupCd) . "' AND useFl='y' ";

        $strSQL .= " ORDER BY sort ASC";
        $result = $db->query($strSQL);
        while ($data = $db->fetch($result)) {
            $getData[$data['itemCd']] = $data['itemNm'];
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 존재하는 코드인지 여부를 체크
     *
     * @param $code
     *
     * @return bool
     */
    public function existsCode($code)
    {
        $db = \App::getInstance('DB');
        $query = 'SELECT COUNT(*) as cnt FROM ' . $this->codeTableName . ' WHERE itemCd=?';
        $binds = [];
        $db->bind_param_push($binds, 's', $code);
        $data = $db->query_fetch($query, $binds, false);

        return ($data['cnt'] > 0) === true;
    }
}
