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
namespace Bundle\Component\Delivery;

use Component\Naver\NaverPay;
use Component\Member\Manager;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Utility\GodoUtils;
use Vendor\Spreadsheet\Excel\Reader as SpreadsheetExcelReader;
use Exception;
use Request;
use Session;
use Globals;

/**
 * 배송비조건 관리 및 지역별 배송비 class
 *
 * @package Bundle\Component\Delivery
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class Delivery
{
    const ECT_INVALID_ARG = 'Delivery.ECT_INVALID_ARG';
    // __('%s은(는) 필수 항목 입니다.')
    // __('조건에 대해 처리중 오류가 발생했습니다.')
    const TEXT_REQUIRE_VALUE = '%s은(는) 필수 항목 입니다.';
    const TEXT_ERROR_VALUE = '조건에 대해 처리중 오류가 발생했습니다.';

    /**
     * @var null|object
     */
    protected $db;

    /**
     * @var array 배송방식 리스트, 리스트명
     */
    public $deliveryMethodList = [
        'list' => [
            'delivery',
            'packet',
            'cargo',
            'visit',
            'quick',
            'etc',
        ],
        'name' => [
            'delivery' => "택배",
            'packet' => "등기, 소포",
            'cargo' => "화물배송",
            'visit' => "방문수령",
            'quick' => "퀵배송",
            'etc' => "기타",
        ],
        'sno' => [],
    ];

    /**
     * @var array 방문수령 배송비 부과 여부
     */
    public $deliveryVisitPayFl = [
        'y' => '배송비 부과',
        'n' => '배송비 무료',
    ];

    public $firstCharge = [];

    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
    }

    /**
     * SCM 관리 기본 정보 출력
     *
     * @param string  $sno     Deliver 테이블 sno
     * @param boolean $debugFl exception 출력 여부
     *
     * @return array 해당 SCM 정보
     * @throws AlertBackException
     */
    public function getDataSnoDelivery($sno, $debugFl = true)
    {
        // 배송지조건 sno 체크 (수정시)
        if (Validator::required(gd_isset($sno)) !== false) {
            $getData['basic'] = $this->getSnoDeliveryBasic($sno);
            $getData['charge'] = $this->getSnoDeliveryCharge($sno, $getData['basic']['fixFl']);
            $getData['multiCharge'] = $this->getSnoDeliveryMultiCharge($getData['charge'], $getData['basic']['fixFl'], $getData['basic']['deliveryConfigType']);
            $getData['firstCharge'] = $this->firstCharge;
            $getData['add'] = $this->getSnoDeliveryAreaGroup($getData['basic']['areaGroupNo']);
            // 공급사 자신의 데이터인지 확인 @todo 사용자 화면에서 겹침
            /*
            if (Manager::isProvider() && $getData['basic']['scmNo'] != Session::get('manager.scmNo')) {
                throw new Exception(__('타 공급사의 자료는 열람하실 수 없습니다.'));
            } */

            // 설정여부에 따른 공급사 번호 가져오기
            $strSQL = 'SELECT scmNo, companyNm, zipcode, zonecode, address, addressSub FROM ' . DB_SCM_MANAGE . ' WHERE scmNo = ? ORDER BY scmNo ASC';
            $arrBind = ['s', $getData['basic']['scmNo']];
            $getData['manage'] = $this->db->query_fetch($strSQL, $arrBind, false);
            unset($arrBind);
            if (!isset($getData['manage']['scmNo'])) {
                // SCM번호로 모든 배송정책 삭제
                $this->deleteWholeScmDelivery($getData['basic']['scmNo']);
                if($debugFl) {
                    throw new AlertBackException(__('공급사 코드가 존재하지 않습니다.'));
                } else {
                    return false;
                }

            }
        }

        // 수정 및 등록에서 사용되는 scmNO
        $scmNo = isset($getData['basic']['scmNo']) ? $getData['basic']['scmNo'] : (Manager::isProvider() ? Session::get('manager.scmNo') : DEFAULT_CODE_SCMNO);
        $getData['count'] = $this->getCountBasicDelivery($scmNo);
        $getData['areaList'] = $this->getNameAreaGroupDelivery($scmNo);

        return $getData;
    }

    public function getDataBySno($sno)
    {
        // 배송지조건 sno 체크 (수정시)
        if (Validator::required(gd_isset($sno)) !== false) {
            // 설정여부에 따른 공급사 번호 가져오기
            $strSQL = 'SELECT *  FROM ' . DB_ORDER_DELIVERY . ' WHERE sno = ? ';
            $arrBind = ['s', $sno];
            $getData = $this->db->query_fetch($strSQL, $arrBind, false);
            unset($arrBind);
            if (!isset($getData)) {
                throw new AlertBackException(__('배송정보가 존재하지 않습니다.'));
            }
        }

        return $getData;
    }

    /**
     * 공급사와 관련된 배송비 모두 삭제
     * 반드시 공급사가 삭제된 경우에만 실행해야 한다.
     *
     * @param integer $scmNo 공급사 번호
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function deleteWholeScmDelivery($scmNo)
    {
        $arrBind = ['s', $scmNo];
        $this->db->set_delete_db(DB_SCM_DELIVERY_BASIC, 'scmNo = ?', $arrBind);
        $this->db->set_delete_db(DB_SCM_DELIVERY_CHARGE, 'scmNo = ?', $arrBind);
        $this->db->set_delete_db(DB_SCM_DELIVERY_AREA, 'scmNo = ?', $arrBind);
        $this->db->set_delete_db(DB_SCM_DELIVERY_AREA_GROUP, 'scmNo = ?', $arrBind);
        unset($arrBind);
    }

    /**
     * SCM기준 기본 배송 정책
     *
     * @param string $scmNo SCM ID
     * @param integer $sno 배송비조건 sno
     *
     * @return array 해당 SCM 정보
     */
    public function getScmDeliveryBasic($scmNo, $sno)
    {
        // SCM ID 체크
        if (Validator::required(gd_isset($scmNo)) !== false) {

            $arrWhere[] = 'scmNo = ?';
            $this->db->bind_param_push($arrBind, 's', $scmNo);

            if ($sno) {
                $arrWhere[] = 'sno = ?';
                $this->db->bind_param_push($arrBind, 's', $sno);
            } else {
                $arrWhere[] = 'defaultFl = ?';
                $this->db->bind_param_push($arrBind, 's', 'y');
            }

            $strWhere = ' WHERE ' . implode(' AND ', $arrWhere);

            $strSQL = 'SELECT sno, method, deliveryMethodFl  FROM ' . DB_SCM_DELIVERY_BASIC . $strWhere;
            $getData = $this->db->query_fetch($strSQL, $arrBind, false);

            //배송비명에 배송방식 포함하여 노출
            if(trim($getData['method']) !== ''){
                $deliveryMethodName = gd_get_delivery_method_display($getData['deliveryMethodFl']);
                if(trim($deliveryMethodName) !== ''){
                    $getData['method'] .= ' ['.$deliveryMethodName.']';
                }
            }
        }

        return $getData;
    }

    /**
     * 기본배송정책의 개수
     *
     * @param mixed $scmNo 공급사 번호
     * @return mixed
     */
    public function getCountBasicDelivery($scmNo = null)
    {
        if ($scmNo !== null) {
            $strWhere = ' WHERE scmNo = \'' . $scmNo . '\'';
        }
        $strSQL = 'SELECT sno FROM ' . DB_SCM_DELIVERY_BASIC . $strWhere;
        $this->db->query_fetch($strSQL);

        return $this->db->affected_rows();
    }

    /**
     * 추가배송비 그룹의 개수
     *
     * @param mixed $scmNo 공급사 번호
     * @return mixed
     */
    public function getCountAreaGroupDelivery($scmNo = null)
    {
        if ($scmNo !== null) {
            $strWhere = ' WHERE scmNo = \'' . $scmNo . '\' OR scmNo = \'0\'';
        }
        $strSQL = 'SELECT sno FROM ' . DB_SCM_DELIVERY_AREA_GROUP . $strWhere;
        $this->db->query_fetch($strSQL);

        return $this->db->affected_rows();
    }

    /**
     * 지역별 배송비 그룹의 이름 (셀렉트 박스 처리용)
     *
     * @param mixed $scmNo 공급사 번호
     * @return mixed
     */
    public function getNameAreaGroupDelivery($scmNo = null)
    {
        if ($scmNo !== null) {
            $strWhere = ' WHERE scmNo = \'' . $scmNo . '\' OR scmNo = \'0\'';
        }
        $strSQL = 'SELECT sno, method, scmNo, defaultFl FROM ' . DB_SCM_DELIVERY_AREA_GROUP . $strWhere;
        $getData = $this->db->query_fetch($strSQL);

        return gd_htmlspecialchars_stripslashes(gd_isset($getData, []));
    }

    /**
     * SCM별 기본 배송 정책 정보 출력 및
     * 상품에서 해당 배송지 조건을 사용하고 있는지에 대한 체크가 가능하다.
     *
     * @param string $sno 배송비조건 sno
     * @param boolean $useGoods 상품사용 여부 체크
     *
     * @return array 해당 SCM 정보
     * @throws Exception 값 체크
     */
    public function getSnoDeliveryBasic($sno, $useGoods = false)
    {
        // SCM ID 체크
        if (Validator::required(gd_isset($sno)) === false) {
            throw new Exception(self::ECT_INVALID_ARG . sprintf(self::TEXT_REQUIRE_VALUE, 'sno'));
        }

        $arrField = DBTableField::setTableField('tableScmDeliveryBasic', null, null, 'd');

        $join = '';
        if ($useGoods !== false) {
            $arrField[] = ' COUNT(g.goodsNo) AS goodsCount ';
            $join = 'INNER JOIN ' . DB_GOODS . ' g ON g.deliverySno = d.sno';
        }

        $strSQL = 'SELECT d.sno, ' . implode(', ', $arrField) . ' FROM ' . DB_SCM_DELIVERY_BASIC . ' d ' . $join . ' WHERE d.sno = ? ORDER BY d.sno ASC';
        $arrBind = ['s', $sno];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $getData = gd_htmlspecialchars_stripslashes($getData);

        if (count($getData) > 0) {
            return $getData[0];
        } else {
            return false;
        }
    }

    /**
     * SCM별 배송비 정보 출력
     * 레이어에서 배송비설정 내역을 보여줄 때 사용하며 데이터는 모두 만들어져서 제공
     *
     * @param integer $sno 배송번호
     * @param string $fixFl 배송유형
     *
     * @return array 해당 SCM 정보
     * @throws Exception
     * @internal param string $scmNo SCM ID
     */
    public function getSnoDeliveryCharge($sno, $fixFl = 'price')
    {
        // SCM ID 체크
        if (Validator::required(gd_isset($sno)) === false) {
            throw new Exception(self::ECT_INVALID_ARG, sprintf(self::TEXT_REQUIRE_VALUE, 'sno'));
        }

        $arrField = DBTableField::setTableField('tableScmDeliveryCharge', null, ['sno']);
        $strSQL = 'SELECT sno, ' . implode(', ', $arrField) . ' FROM ' . DB_SCM_DELIVERY_CHARGE . ' WHERE basicKey = ? ORDER BY basicKey ASC, sno ASC';
        $arrBind = ['s', $sno];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $getData = gd_htmlspecialchars_stripslashes($getData);

        // 데이터 수정
        $tmpData = [];
        foreach ($getData as $key => $val) {
            // 배송비를 통화정책에 맞게 소수점 없애는 작업
            $val['price'] = gd_money_format($val['price'], false);

            // 단위 추가
            $val['unitText'] = $this->getUnitText($fixFl);
            $tmpData[$key] = $val;
        }

        if (count($tmpData) > 0) {
            return gd_htmlspecialchars_stripslashes($tmpData);
        } else {
            return false;
        }
    }

    public function getSnoDeliveryMultiCharge($getData, $fixFl = 'price', $deliveryConfigType = 'all')
    {
        $tmpData = $getData;
        if (in_array($fixFl, ['fixed', 'price', 'count', 'weight']) === true && $deliveryConfigType == 'etc') {
            unset($tmpData);
            $arrField = DBTableField::setTableField('tableScmDeliveryCharge', null, ['sno']);

            foreach ($getData as $key => $val) {
                // 배송비를 통화정책에 맞게 소수점 없애는 작업
                $val['price'] = gd_money_format($val['price'], false);

                // 단위 추가
                $val['unitText'] = $this->getUnitText($fixFl);
                $tmp[$val['method']][] = $val;
            }

            $tmpField = [];
            foreach ($arrField as $field) {
                $tmpField[$field] = '';
            }
            $this->firstCharge = array_shift($tmp);
            foreach ($this->firstCharge as $key => $val) {
                foreach ($this->deliveryMethodList['list'] as $v) {
                    if ($val['method'] == $v) {
                        $tmpData[$key][$v] = $val;
                    } else {
                        $tmpData[$key][$v] = $tmp[$v][$key] ?? $tmpField;
                    }
                }
            }
        }

        if (count($tmpData) > 0) {
            return gd_htmlspecialchars_stripslashes($tmpData);
        } else {
            return false;
        }
    }

    /**
     * 지역별 추가배송비 그룹 리스트 가져오기
     * 단, 공급사가 삭제된 경우 관련 내용 자동으로 삭제 시킨다.
     *
     * @param integer $sno 배송비조건 번호
     *
     * @return mixed
     * @throws AlertBackException
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getSnoDeliveryAreaGroup($sno)
    {
        // SCM ID 체크
        if (Validator::required(gd_isset($sno)) !== false && $sno != 0) {
            $arrField = DBTableField::setTableField('tableScmDeliveryAreaGroup', null);
            $strSQL = 'SELECT sno, ' . implode(', ', $arrField) . ' FROM ' . DB_SCM_DELIVERY_AREA_GROUP . ' WHERE sno = ? ORDER BY sno DESC';
            $arrBind = ['s', $sno];
            $getData = $this->db->query_fetch($strSQL, $arrBind, false);
            $getData = gd_htmlspecialchars_stripslashes($getData);

            if (empty($getData) === false && isset($getData['scmNo'])) {
                $strSQL = 'SELECT scmNo, companyNm FROM ' . DB_SCM_MANAGE . ' WHERE scmNo = ? ORDER BY scmNo ASC';
                if ($getData['scmNo'] == '0') {
                    $arrBind = ['s', '1'];
                } else {
                    $arrBind = ['s', $getData['scmNo']];
                }
                $getData['manage'] = $this->db->query_fetch($strSQL, $arrBind, false);

                if (empty($getData['manage']) === false) {
                    if (count($getData) > 0) {
                        return $getData;
                    }
                }
            }
            //            $this->db->set_delete_db(DB_SCM_DELIVERY_AREA_GROUP, 'scmNo = ?', ['i', $getData['scmNo']]);
            //            $this->db->set_delete_db(DB_SCM_DELIVERY_AREA, 'basicKey = ?', ['i', $getData['sno']]);
            //
            //            throw new AlertBackException(__('공급사가 삭제되어 더 이상 사용할 수 없습니다.'));
        }

        return false;
    }

    /**
     * SCM별 지역별 추가 배송비 정보 출력
     *
     * @param string $sno 지역별배송지 그룹 NO
     *
     * @return array 지역별 배송지 리스트
     * @throws Exception
     */
    public function getSnoDeliveryArea($sno)
    {
        // SCM ID 체크
        if (Validator::required(gd_isset($sno)) === false) {
            throw new Exception(self::ECT_INVALID_ARG, sprintf(self::TEXT_REQUIRE_VALUE, 'sno'));
        }

        $arrField = DBTableField::setTableField('tableScmDeliveryArea', null, ['sno']);
        $strSQL = 'SELECT sno, regDt, ' . implode(', ', $arrField) . ' FROM ' . DB_SCM_DELIVERY_AREA . ' WHERE basicKey = ? ORDER BY basicKey ASC, sno DESC';
        $arrBind = ['s', $sno];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $getData = gd_htmlspecialchars_stripslashes($getData);

        // 데이터 수정
        $tmpData = [];
        foreach ($getData as $key => $val) {
            // 배송비를 통화정책에 맞게 소수점 없애는 작업
            $val['addPrice'] = gd_money_format($val['addPrice'], false);
            $tmpData[$key] = $val;
        }

        if (count($tmpData) > 0) {
            return gd_htmlspecialchars_stripslashes($tmpData);
        } else {
            return false;
        }
    }

    /**
     * 관리자 주문 리스트를 위한 검색 정보
     *
     * @param string $searchMode 검색모드
     */
    public function setSearchDelivery($searchMode = null)
    {
        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableScmDeliveryBasic');

        // 통합 검색
        $this->search['combineSearch'] = [
            'all' => '=' . __('통합검색') . '=',
            'd.method' => __('배송비조건명'),
            'd.description' => __('배송비조건 설명'),
            'm.managerNm' => __('등록자'),
            //                                          'a.regUser' => __('등록자'),
        ];
        $this->search['deliveryMethodFlSearch']['all'] = '=' . __('통합검색') . '=';
        $this->search['deliveryMethodFlSearch'] = array_merge((array)$this->search['deliveryMethodFlSearch'], (array)$this->deliveryMethodList['name']);


        // --- 검색 설정
        $getValue = Request::get()->toArray();

        $this->search['detailSearch'] = gd_isset($getValue['detailSearch']);
        $this->search['key'] = gd_isset($getValue['key']);
        $this->search['keyword'] = gd_isset($getValue['keyword']);
        $this->search['treatDate'][] = gd_isset($getValue['treatDate'][0]);
        $this->search['treatDate'][] = gd_isset($getValue['treatDate'][1]);
        $this->search['goodsDeliveryFl'] = gd_isset($getValue['goodsDeliveryFl'], 'all');
        $this->search['fixFl'] = gd_isset($getValue['fixFl'], 'all');
        $this->search['collectFl'] = gd_isset($getValue['collectFl'], '');
        $this->search['areaFl'] = gd_isset($getValue['areaFl'], '');
        $this->search['scmFl'] = gd_isset($getValue['scmFl'], Session::get('manager.isProvider') ? 'n' : '');
        $this->search['scmNo'] = gd_isset($getValue['scmNo'], $getValue['scmNo'] ? $getValue['scmNo'] : (string)Session::get('manager.scmNo'));
        $this->search['scmNoNm'] = gd_isset($getValue['scmNoNm']);
        $this->search['deliveryMethodFl'] = gd_isset($getValue['deliveryMethodFl'], 'all');

        $this->checked['scmFl'][$this->search['scmFl']] =
        $this->checked['goodsDeliveryFl'][$this->search['goodsDeliveryFl']] =
        $this->checked['freeFl'][$this->search['freeFl']] =
        $this->checked['collectFl'][$this->search['collectFl']] =
        $this->checked['areaFl'][$this->search['areaFl']] = 'checked="checked"';


        $this->arrWhere[] = 'd.sno <> \'\'';

        // 공급사 선택
        if (Manager::isProvider()) {
            // 공급사로 로그인한 경우 기존 scm에 값 설정
            $this->arrWhere[] = 'd.scmNo = ?';
            $this->db->bind_param_push($this->arrBind, 'i', Session::get('manager.scmNo'));
        } else {
            if ($this->search['scmFl'] > 0 || $this->search['scmFl'] == 'y') {
                if (is_array($this->search['scmNo'])) {
                    foreach ($this->search['scmNo'] as $val) {
                        $tmpWhere[] = 'd.scmNo = ?';
                        $this->db->bind_param_push($this->arrBind, 's', $val);
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                    unset($tmpWhere);
                } else if ($this->search['scmNo'] > 0) {
                    $this->arrWhere[] = 'd.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmNo']);
                }
            } elseif ($this->search['scmFl'] == '0') {
                $this->arrWhere[] = 'd.scmNo = 1';
            }
        }

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = array_keys($this->search['combineSearch']);
                array_shift($tmpWhere);
                $arrWhereAll = [];
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                $this->useTable[] = 'd';
                $this->useTable = array_unique($this->useTable);
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                if (preg_match('/^d/', $this->search['key'])) {
                    $this->useTable[] = 'd';
                    $this->useTable = array_unique($this->useTable);
                }
            }
        }

        // 배송비 부과방법
        if ($this->search['goodsDeliveryFl'] && $this->search['goodsDeliveryFl'] != 'all') {
            if (is_array($this->search['goodsDeliveryFl'])) {
                foreach ($this->search['goodsDeliveryFl'] as $val) {
                    $tmpWhere[] = 'd.goodsDeliveryFl = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                    $this->checked['goodsDeliveryFl'][$val] = 'checked="checked"';
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = 'd.goodsDeliveryFl = ?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsDeliveryFl']);
                $this->checked['goodsDeliveryFl'][$this->search['goodsDeliveryFl']] = 'checked="checked"';
            }
        }

        // 처리일자 검색
        if ($this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $this->arrWhere[] = 'd.regDt BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        // 배송비유형
        if (isset($this->search['fixFl']) && $this->search['fixFl'] != 'all') {
            if (is_array($this->search['fixFl'])) {
                foreach ($this->search['fixFl'] as $val) {
                    if ($val != 'all') {
                        $tmpWhere[] = 'd.fixFl = ?';
                        $this->db->bind_param_push($this->arrBind, 's', $val);
                    }
                    $this->checked['fixFl'][$val] = 'checked="checked"';
                }
                if ($tmpWhere) {
                    $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                }
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = 'd.fixFl = ?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['fixFl']);
                $this->checked['fixFl'][$this->search['fixFl']] = 'checked="checked"';
            }

        }

        // 결제방법
        if ($this->search['collectFl']) {
            $this->arrWhere[] = 'd.collectFl = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['collectFl']);
        }

        // 지역별추가배송비
        if ($this->search['areaFl']) {
            $this->arrWhere[] = 'd.areaFl = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['areaFl']);
        }

        // 배송 방식
        if($this->search['deliveryMethodFl'] && $this->search['deliveryMethodFl'] !== 'all'){
            $this->arrWhere[] = 'INSTR(d.deliveryMethodFl, ?) > 0';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['deliveryMethodFl']);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * SCM 기본배송지 리스트
     *
     * @return array 해당 SCM 정보
     */
    public function getBasicDeliveryList($mode = null)
    {
        // --- 검색 설정
        $this->setSearchDelivery();

        $getValue = Request::get()->toArray();

        // --- 정렬 설정
        $sort['fieldName'] = 'd.regDt';
        $sort['sortMode'] = 'desc';

        // 페이지 설정
        if ($mode == 'layer') {
            // --- 페이지 기본설정
            if (gd_isset($getValue['pagelink'])) {
                $getValue['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
            } else {
                $getValue['page'] = 1;
            }
            gd_isset($getValue['pageNum'], 10);
        } else {
            // --- 페이지 기본설정
            gd_isset($getValue['page'], 1);
            gd_isset($getValue['pageNum'], 10);
        }

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 사용 필드
        $arrField[0] = DBTableField::setTableField('tableScmDeliveryBasic', null, null, 'd');
        $arrField[1] = DBTableField::setTableField('tableScmDeliveryBasic', null, null, 't');
        $arrField[2] = DBTableField::setTableField('tableScmDeliveryCharge', ['price', 'basicKey'], null, 'dc');
        $arrField[5] = DBTableField::setTableField('tableScmDeliveryCharge', ['price', 'basicKey'], null, 't');
        $arrField[3] = DBTableField::setTableField('tableScmManage', ['companyNm', 'zipcode', 'zonecode', 'address', 'addressSub'], null, 'sm');
        $arrField[6] = DBTableField::setTableField('tableScmManage', ['companyNm', 'zipcode', 'zonecode', 'address', 'addressSub'], null, 't');
        $arrField[4] = DBTableField::setTableField('tableManager', ['managerId', 'managerNm','isDelete'], null, 'm');
        $arrField[7] = DBTableField::setTableField('tableManager', ['managerId', 'managerNm','isDelete'], null, 't');

        // join 문
        $join[] = ' LEFT JOIN ' . DB_SCM_DELIVERY_BASIC . ' d ON dc.basicKey = d.sno ';
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON d.scmNo = sm.scmNo ';
        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' m ON d.managerNo = m.sno ';

        // 현 페이지 결과
        $this->db->strField =  implode(', ', $arrField[1]) . ', ' . implode(', ', $arrField[5]) . ', ' . implode(', ', $arrField[6]) . ', ' . implode(', ', $arrField[7]) . ', t.regDt, t.sno';
        $this->db->strJoin = implode(' ', $join);
        $this->db->strGroup = 'd.sno';
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = 'd.defaultFl desc, ' . $sort['fieldName'] . ' ' . $sort['sortMode'];
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM (SELECT d.sno, ' . implode(', ', $arrField[0]) . ', ' . implode(', ', $arrField[2]) . ', ' . implode(', ', $arrField[3]) . ', ' . implode(', ', $arrField[4]) . ', d.regDt FROM ' . DB_SCM_DELIVERY_CHARGE . ' dc ' . implode(' ', $query) . ') AS t ORDER BY t.defaultFl desc, t.sno desc';
        $tmp = $this->db->query_fetch($strSQL, $this->arrBind);

        // 검색 레코드 수
        $page->recode['total'] = array_shift($this->db->query_fetch('SELECT count(t.sno) FROM (SELECT d.sno, d.scmNo, d.managerNo FROM ' . DB_SCM_DELIVERY_CHARGE . ' dc ' . implode(' ', $join) . ' WHERE ' . implode(' AND ', gd_isset($this->arrWhere)) . ' GROUP BY d.sno) AS t ', $this->arrBind, false));
        if (Manager::isProvider()) {
            $page->recode['amount'] = count($this->db->query_fetch('SELECT t.sno FROM (SELECT d.sno, d.scmNo, d.managerNo FROM ' . DB_SCM_DELIVERY_CHARGE . ' dc ' . implode(' ', $join) . ' WHERE d.sno <> \'\' AND d.scmNo = ' . Session::get('manager.scmNo') . ' GROUP BY d.sno) AS t ', $this->arrBind, false)); // 전체 레코드 수

        } else {
            $page->recode['amount'] = count($this->db->query_fetch('SELECT t.sno FROM (SELECT d.sno, d.scmNo, d.managerNo FROM ' . DB_SCM_DELIVERY_CHARGE . ' dc ' . implode(' ', $join) . ' WHERE d.sno <> \'\' GROUP BY d.sno) AS t ', $this->arrBind, false)); // 전체 레코드 수
        }
        $page->setPage();

        // 배송방법과 표기방법 설정
        if (gd_isset($tmp)) {
            foreach ($tmp as $key => $val) {
                $data[$key] = $val;
                $data[$key]['fixFlText'] = $this->getFixFlText($val['fixFl']);
                $data[$key]['goodsDeliveryFlText'] = $this->getGoodsDeliveryFlText($val['goodsDeliveryFl']);
                $data[$key]['collectFlText'] = $this->getCollectFlText($val['collectFl']);
                $data[$key]['areaFlText'] = $this->getAddFlText($val['areaFl']);

                switch ($val['fixFl']) {
                    case 'price':
                        $data[$key]['price'] = 0;
                        break;
                    case 'weight':
                        $data[$key]['price'] = 0;
                        break;
                    case 'count':
                        $data[$key]['price'] = 0;
                        break;
                    case 'free':
                        $data[$key]['price'] = 0;
                        break;
                }

                $data[$key]['multipleDeliveryFl'] = false;
                $deliveryMethodFl = explode(STR_DIVISION, $val['deliveryMethodFl']);
                if ($val['fixFl'] != 'free' && $val['deliveryConfigType'] == 'etc' && (count($deliveryMethodFl) <= 1) === false) {
                    $data[$key]['multipleDeliveryFl'] = true;
                }
            }
        }
        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        if($getData['data'] == ''){
            $getData['data'] = null;
        }
        Manager::displayListData($getData['data']);
        return $getData;
    }

    /**
     * 지역별 추가배송비 관리를 위한 검색 정보
     *
     * @param string $searchMode 검색모드
     * @throws Exception
     */
    public function setSearchAddGroupDelivery($searchMode = null)
    {
        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableScmDeliveryAreaGroup');

        // 통합 검색
        $this->search['combineSearch'] = [
            'all' => '=' . __('통합검색') . '=',
            'd.method' => __('지역별 추가배송비명'),
            'd.description' => __('지역별 추가배송비 설명'),
            'm.managerNm' => __('등록자'),
        ];

        // 리퀘스트 데이터
        $getValue = Request::get()->toArray();

        // 공급사로 로그인시 scmNo
        $scmNo = Manager::isProvider() ? Session::get('manager.scmNo') : $getValue['scmNo'];

        // --- 검색 설정
        $this->search['detailSearch'] = gd_isset($getValue['detailSearch']);
        $this->search['key'] = gd_isset($getValue['key']);
        $this->search['keyword'] = gd_isset($getValue['keyword']);
        $this->search['treatDate'][] = gd_isset($getValue['treatDate'][0]);
        $this->search['treatDate'][] = gd_isset($getValue['treatDate'][1]);
        $this->search['scmFl'] = gd_isset($getValue['scmFl'], 'all');
        $this->search['scmNo'] = gd_isset($scmNo);
        $this->search['scmNoNm'] = gd_isset($getValue['scmNoNm']);

        // 체크 표기
        $this->checked['scmFl'][$this->search['scmFl']] = 'checked="checked"';

        // 조건절
        $this->arrWhere[] = 'd.sno <> \'\'';

        // 공급사 선택
        if (Manager::isProvider()) {
            // 공급사로 로그인한 경우 기존 scm에 값 설정
            $this->arrWhere[] = '(d.scmNo = ? OR d.scmNo = \'0\')';
            $this->db->bind_param_push($this->arrBind, 'i', Session::get('manager.scmNo'));
        } else {
            if ($this->search['scmFl'] == '1') {
                if (is_array($this->search['scmNo'])) {
                    foreach ($this->search['scmNo'] as $val) {
                        $tmpWhere[] = 'd.scmNo = ?';
                        $this->db->bind_param_push($this->arrBind, 's', $val);
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                    unset($tmpWhere);
                } else if ($this->search['scmNo']) {
                    $this->arrWhere[] = 'd.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmNo']);
                }
            } elseif ($this->search['scmFl'] == '0') {
                $this->arrWhere[] = 'd.scmNo = 1';
            }
        }

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = array_keys($this->search['combineSearch']);
                array_shift($tmpWhere);
                $arrWhereAll = [];
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                $this->useTable[] = 'd';
                $this->useTable = array_unique($this->useTable);
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                if (preg_match('/^d/', $this->search['key'])) {
                    $this->useTable[] = 'd';
                    $this->useTable = array_unique($this->useTable);
                }
            }
        }

        // 처리일자 검색
        if ($this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $this->arrWhere[] = 'd.regDt BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * getAddGroupDeliveryList
     *
     */
    public function getAreaGroupDeliveryList()
    {
        // 리퀘스트
        $getValue = Request::get()->toArray();

        // --- 검색 설정
        $this->setSearchAddGroupDelivery();

        // --- 정렬 설정
        $sort['fieldName'] = 'd.regDt';
        $sort['sortMode'] = 'desc';

        // --- 페이지 기본설정
        $getValue['page'] = gd_isset($getValue['page'], 1);
        $getValue['pageNum'] = gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 사용 필드
        $arrField[0] = DBTableField::setTableField('tableScmDeliveryAreaGroup', null, null, 'd');
        $arrField[1] = DBTableField::setTableField('tableScmManage', ['companyNm'], null, 'sm');
        $arrField[2] = DBTableField::setTableField('tableManager', ['managerId', 'managerNm', 'isDelete'], null, 'm');

        // join 문
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON d.scmNo = sm.scmNo ';
        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' m ON d.managerNo = m.sno ';

        // 현 페이지 결과
        $this->db->strField = implode(', ', $arrField[0]) . ', d.sno, d.regDt, ' . implode(', ', $arrField[1]) . ', ' . implode(', ', $arrField[2]);
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = 'defaultFl desc, ' . $sort['fieldName'] . ' ' . $sort['sortMode'];
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_DELIVERY_AREA_GROUP . ' d ' . implode(' ', $query);
        $tmp = $this->db->query_fetch($strSQL, $this->arrBind);
        Manager::displayListData($tmp);

        // 현 검색개수
        /*
        $this->db->strField = implode(', ', $arrField[0]) . ', d.sno, d.regDt, ' . implode(', ', $arrField[1]) . ', ' . implode(', ', $arrField[2]);
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = 'defaultFl desc, ' . $sort['fieldName'] . ' ' . $sort['sortMode'];
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_DELIVERY_AREA_GROUP . ' d ' . implode(' ', $query);
        $tmp = $this->db->query_fetch($strSQL, $this->arrBind);
        */
        unset($query['limit']);
        $strSQL = 'SELECT count(*) as cnt  FROM ' . DB_SCM_DELIVERY_AREA_GROUP . ' d ' . implode(' ', $query);
        $total = $this->db->query_fetch($strSQL, $this->arrBind, false)['cnt'];

        // 검색 레코드 수
        $page->recode['total'] = $total;

        // 전체 레코드 수
        if (Manager::isProvider()) {
            list($page->recode['amount']) = $this->db->fetch('SELECT COUNT(sno) FROM ' . DB_SCM_DELIVERY_AREA_GROUP . ' WHERE scmNo=' . Session::get('manager.scmNo') . ' OR scmNo = "0"', 'row');
        } else {
            unset($query['limit']);
            $strSQL = 'SELECT COUNT(*) AS total FROM ' . DB_SCM_DELIVERY_AREA_GROUP . ' d ';
            $totalResult = $this->db->query_fetch($strSQL, $this->arrBind, false);
            $page->recode['amount'] = $totalResult['total'];
        }
        $page->setPage();

        // 배송방법과 표기방법 설정
        if (gd_isset($tmp)) {
            foreach ($tmp as $key => $val) {
                $data[$key] = $val;
                $data[$key]['fixFlText'] = $this->getFixFlText($val['fixFl']);
                $data[$key]['goodsDeliveryFlText'] = $this->getGoodsDeliveryFlText($val['goodsDeliveryFl']);
                $data[$key]['collectFlText'] = $this->getCollectFlText($val['collectFl']);
                $data[$key]['areaFlText'] = $this->getAddFlText($val['areaFl']);

                switch ($val['fixFl']) {
                    case 'price':
                        $data[$key]['price'] = 0;
                        break;
                    case 'weight':
                        $data[$key]['price'] = 0;
                        break;
                    case 'count':
                        $data[$key]['price'] = 0;
                        break;
                    case 'free':
                        $data[$key]['price'] = 0;
                        break;
                }
            }
        }

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * 배송비 유형 상세내역
     *
     * @param integer $sno 배송비 조건 sno
     * @throws Exception
     */
    public function getChargeDeliveryDetail($sno)
    {
        $getData['basic'] = $this->getSnoDeliveryBasic($sno);
        $getData['charge'] = $this->getSnoDeliveryCharge($sno, $getData['basic']['fixFl']);
        $getData['multiCharge'] = $this->getSnoDeliverymultiCharge($getData['charge'], $getData['basic']['fixFl'], $getData['basic']['deliveryConfigType']);

        return $getData;
    }

    /**
     * 지역별 배송비 리스트
     *
     * @param integer $sno 지역배송비 sno
     * @throws Exception
     */
    public function getAreaDeliveryList($sno)
    {
        $getValue = Request::get()->toArray();

        //검색설정
        $this->search['sortList'] = [
            'a.addArea asc' => __('주소지↓'),
            'a.addArea desc' => __('주소지↑'),
            'regDt asc' => __('등록일↓'),
            'regDt desc' => __('등록일↑'),
            'a.addPice asc' => __('추가배송비↓'),
            'a.addPrice desc' => __('추가배송비↑'),
        ];
        $this->search['sort'] = gd_isset($getValue['sort'], 'regDt desc');
        $this->arrWhere = [];
        $this->arrWhere[] = 'a.sno <> \'\'';

        // --- 정렬 설정
        $sort['fieldName'] = gd_isset($_GET['sort']['name']);
        $sort['sortMode'] = gd_isset($_GET['sort']['mode']);
        if (!$sort['fieldName'] || !$sort['sortMode']) {
            $sort['fieldName'] = 'a.regDt';
            $sort['sortMode'] = 'desc';
        }

        // 사용 필드
        $arrField[0] = DBTableField::setTableField('tableScmDeliveryArea', null, null, 'a');

        // 현 페이지 결과
        $this->db->strField = implode(', ', $arrField[0]) . ', a.regDt';
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_DELIVERY_AREA . ' a ' . implode(' ', $query);
        $tmp = $this->db->query_fetch($strSQL, $this->arrBind);

        if (gd_isset($tmp)) {
            foreach ($tmp as $key => $val) {
                $data[$key] = gd_htmlspecialchars_stripslashes($val);
            }
        } else {
            return;
        }

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    public function saveInfoDeliveryEtc($arrData)
    {
        $params = [
            'mode' => $arrData['mode'],
            'basic' => $arrData['basic'],
            'scmFl' => $arrData['scmFl'],
            'scmNo' => $arrData['scmNo'],
            'scmNoNm' => $arrData['scmNoNm'],
        ];
        $chargeKey = [];
        $basicKey = '';
        foreach ($arrData['basic']['deliveryMethodFl'] as $val) {
            $params['charge'] = [
                /*'sno' => $arrData['charge']['sno'],*/
                'method' => $val,
                'unitStart' => $arrData['charge']['unitStart'],
                'unitEnd' => $arrData['charge']['unitEnd'],
                'price' => $arrData['charge']['price'][$val],
            ];
            $data = $this->saveInfoDelivery($params, $basicKey, $chargeKey);
            $basicKey = $data['basicKey'];
            $chargeKey += $data['chargeKey'];
        }
    }

    /**
     * 배송 정책 정보 저장
     *
     * @param array $arrData 저장할 정보의 배열
     * @throws Exception
     */
    public function saveInfoDelivery($arrData, $basicKey = '', $chargeKey = [])
    {
        // SCM ID 체크
        if (Validator::required(gd_isset($arrData['scmNo'])) === false) {
            throw new Exception(self::ECT_INVALID_ARG . sprintf(self::TEXT_REQUIRE_VALUE, 'scm NO'));
        }

        // 플러스샵 사용 중이면 실행
        if (GodoUtils::isPlusShop(PLUSSHOP_CODE_SCM)) {
            // 공급사로 로그인시 scmNo 처리
            $scmNo = (Manager::isProvider() ? Session::get('manager.scmNo') : $arrData['scmNo']);

            // 공급사가 아닌 경우 scmFl 선택에 따른 공급사 설정
            if (!Manager::isProvider() && $arrData['scmFl'] === '0') {
                $scmNo = DEFAULT_CODE_SCMNO;
            }
        } else {
            $scmNo = DEFAULT_CODE_SCMNO;
        }

        // 배송비 과세 / 비과세 설정 config 불러오기
        $deliveryTaxPolicy = gd_policy('goods.tax');

        // 기본 배송 정책 설정 배열 처리
        $data['basic']['sno'] = gd_isset($arrData['basic']['sno']);
        $data['basic']['managerNo'] = $arrData['basic']['managerNo'] == 0 ? Session::get('manager.sno') : (gd_isset($arrData['basic']['managerNo'], Session::get('manager.sno')));
        $data['basic']['scmNo'] = gd_isset($scmNo, 1);
        $data['basic']['method'] = gd_isset($arrData['basic']['method']);
        $data['basic']['description'] = gd_isset($arrData['basic']['description']);
        $data['basic']['defaultFl'] = ($this->getCountBasicDelivery() == 1 ? 'y' : gd_isset($arrData['basic']['defaultFl'], 'n'));
        $data['basic']['collectFl'] = gd_isset($arrData['basic']['collectFl'], 'n');
        $data['basic']['fixFl'] = gd_isset($arrData['basic']['fixFl'], 'price');
        $data['basic']['freeFl'] = gd_isset($arrData['basic']['freeFl'], 'n');
        $data['basic']['goodsDeliveryFl'] = gd_isset($arrData['basic']['goodsDeliveryFl'], 'y');
        $data['basic']['areaGroupNo'] = gd_isset($arrData['basic']['areaGroupNo'], '');
        $data['basic']['areaFl'] = gd_isset($arrData['basic']['areaFl'], 'n');
        $data['basic']['pricePlusStandard'] = gd_isset($arrData['basic']['pricePlusStandard']) ? implode(STR_DIVISION, $arrData['basic']['pricePlusStandard']) : '';
        $data['basic']['priceMinusStandard'] = gd_isset($arrData['basic']['priceMinusStandard']) ? implode(STR_DIVISION, $arrData['basic']['priceMinusStandard']) : '';
        $data['basic']['taxFreeFl'] = gd_isset($arrData['basic']['unstoringFl'], $deliveryTaxPolicy['deliveryTaxFreeFl']);
        $data['basic']['taxPercent'] = gd_isset($arrData['basic']['taxPercent'], $deliveryTaxPolicy['deliveryTaxPercent']);
        $data['basic']['unstoringFl'] = gd_isset($arrData['basic']['unstoringFl'], 'same');
        $data['basic']['unstoringZipcode'] = gd_isset($arrData['basic']['unstoringZipcode']);
        $data['basic']['unstoringZonecode'] = gd_isset($arrData['basic']['unstoringZonecode']);
        $data['basic']['unstoringAddress'] = gd_isset($arrData['basic']['unstoringAddress']);
        $data['basic']['unstoringAddressSub'] = gd_isset($arrData['basic']['unstoringAddressSub']);
        $data['basic']['returnFl'] = gd_isset($arrData['basic']['returnFl'], 'same');
        $data['basic']['returnZipcode'] = gd_isset($arrData['basic']['returnZipcode']);
        $data['basic']['returnZonecode'] = gd_isset($arrData['basic']['returnZonecode']);
        $data['basic']['returnAddress'] = gd_isset($arrData['basic']['returnAddress']);
        $data['basic']['returnAddressSub'] = gd_isset($arrData['basic']['returnAddressSub']);
        $data['basic']['dmVisitTypeFl'] = gd_isset($arrData['basic']['dmVisitTypeFl'], 'same');
        $data['basic']['dmVisitTypeZonecode'] = gd_isset($arrData['basic']['dmVisitTypeZonecode']);
        $data['basic']['dmVisitTypeZipcode'] = gd_isset($arrData['basic']['dmVisitTypeZipcode']);
        $data['basic']['dmVisitTypeAddress'] = gd_isset($arrData['basic']['dmVisitTypeAddress']);
        $data['basic']['dmVisitTypeAddressSub'] = gd_isset($arrData['basic']['dmVisitTypeAddressSub']);
        $data['basic']['dmVisitTypeDisplayFl'] = gd_isset($arrData['basic']['dmVisitTypeDisplayFl'], 'n');
        $data['basic']['rangeRepeat'] = gd_isset($arrData['basic']['rangeRepeat'], 'n');
        $data['basic']['addGoodsCountInclude'] = gd_isset($arrData['basic']['addGoodsCountInclude'], 'n');
        $data['basic']['deliveryVisitPayFl'] = gd_isset($arrData['basic']['deliveryVisitPayFl'], 'y');
        $data['basic']['dmVisitAddressUseFl'] = gd_isset($arrData['basic']['dmVisitAddressUseFl'], 'n');
        $data['basic']['sameGoodsDeliveryFl'] = gd_isset($arrData['basic']['sameGoodsDeliveryFl'], 'n');
        $data['basic']['deliveryConfigType'] = gd_isset($arrData['basic']['deliveryConfigType'], 'all');

        // 해외배송 무게 범위 제한 처리
        if ($data['basic']['fixFl'] == 'weight') {
            $data['basic']['rangeLimitFl'] = gd_isset($arrData['basic']['rangeLimitFl'], 'n');
            $data['basic']['rangeLimitWeight'] = gd_isset($arrData['basic']['rangeLimitWeight'], 0);
        } else {
            $data['basic']['rangeLimitFl'] = gd_isset($arrData['basic']['rangeLimitFl'], 'n');
            $data['basic']['rangeLimitWeight'] = 0;
        }

        //배송 방식
        $deliveryMethodParam = [];
        foreach($this->deliveryMethodList['list'] as $key => $value){
            $deliveryMethodParam[] = $arrData['basic']['deliveryMethodFl'][$value];
        }
        $data['basic']['deliveryMethodFl'] = implode(STR_DIVISION, $deliveryMethodParam);

        // 부가세율이 0이면 면세처리
        if ($data['basic']['taxPercent'] > 0) {
            $data['basic']['taxFreeFl'] = 't';
        } else {
            $data['basic']['taxFreeFl'] = 'f';
        }

        //배송비 조건이 무료로 바뀐경우 배송 금액이 0으로 들어가 있어야함.
        if($arrData['basic']['fixFl'] =='free') {
            $arrData['charge']['price'][0] = 0;
        }

        // 배송 기본설정 처리 (일괄 'n' 처리) - 공급사별 적용
        if ($data['basic']['defaultFl'] == 'y' && empty($chargeKey) === true) {
            $arrBind['param'][] = 'defaultFl = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', 'n');
            $arrBind['param'][] = 'scmNo = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $data['basic']['scmNo']);
            $this->db->set_update_db(DB_SCM_DELIVERY_BASIC, $arrBind['param'], 'defaultFl="y" AND scmNo = "' . $data['basic']['scmNo'] . '"', $arrBind['bind']);
        }

        // 공급사 기본 주소 정책 가져와서 처리
        $strSQL = 'SELECT scmNo, companyNm, zipcode, zonecode, address, addressSub, unstoringZipcode, unstoringZonecode,unstoringAddress, unstoringAddressSub FROM ' . DB_SCM_MANAGE . ' WHERE scmNo = ?';
        $arrBind = ['s', $data['basic']['scmNo']];
        $data['manage'] = $this->db->query_fetch($strSQL, $arrBind, false);

        if ($data['basic']['unstoringFl'] == 'same') {
            $data['basic']['unstoringZipcode'] = $data['manage']['zipcode'];
            $data['basic']['unstoringZonecode'] = $data['manage']['zonecode'];
            $data['basic']['unstoringAddress'] = $data['manage']['address'];
            $data['basic']['unstoringAddressSub'] = $data['manage']['addressSub'];
        }

        if ($data['basic']['returnFl'] == 'same') {
            $data['basic']['returnZipcode'] = $data['manage']['zipcode'];
            $data['basic']['returnZonecode'] = $data['manage']['zonecode'];
            $data['basic']['returnAddress'] = $data['manage']['address'];
            $data['basic']['returnAddressSub'] = $data['manage']['addressSub'];
        }

        if ($data['basic']['dmVisitTypeFl'] == 'same') {
            $data['basic']['dmVisitTypeZipcode'] = $data['manage']['zipcode'];
            $data['basic']['dmVisitTypeZonecode'] = $data['manage']['zonecode'];
            $data['basic']['dmVisitTypeAddress'] = $data['manage']['address'];
            $data['basic']['dmVisitTypeAddressSub'] = $data['manage']['addressSub'];
        }

        // 입력한 출고지 주소가 있는 경우 출고지 주소로
        if ($data['basic']['returnFl'] == 'unstoring') {
                $data['basic']['returnZipcode'] = $data['basic']['unstoringZipcode'];
                $data['basic']['returnZonecode'] = $data['basic']['unstoringZonecode'];
                $data['basic']['returnAddress'] = $data['basic']['unstoringAddress'];
                $data['basic']['returnAddressSub'] = $data['basic']['unstoringAddressSub'];
        }
        if ($data['basic']['dmVisitTypeFl'] == 'unstoring') {
                $data['basic']['dmVisitTypeZipcode'] = $data['basic']['unstoringZipcode'];
                $data['basic']['dmVisitTypeZonecode'] = $data['basic']['unstoringZonecode'];
                $data['basic']['dmVisitTypeAddress'] = $data['basic']['unstoringAddress'];
                $data['basic']['dmVisitTypeAddressSub'] = $data['basic']['unstoringAddressSub'];
        }

        // 배송비설정 배열 처리
        foreach ($arrData['charge'] as $key => $val) {
            foreach ($arrData['charge']['unitStart'] as $cKey => $cVal) {
                if ($key == 'method') {
                    $data['charge'][$key][] = gd_isset($val);
                } else {
                    $data['charge'][$key][] = gd_isset($arrData['charge'][$key][$cKey]);
                    if (!array_key_exists('basicKey', $val)) {
                        $data['charge']['basicKey'][$cKey] = $data['basic']['sno'];
                        $data['charge']['scmNo'][$cKey] = $data['basic']['scmNo'];
                    }
                }
            }
        }

        // 지역별추가배송비 배열 처리
        if (isset($arrData['add'])) {
            foreach ($arrData['add'] as $key => $val) {
                foreach ($arrData['add'][$key]['addPrice'] as $cKey => $cVal) {
                    $data['add']['basicKey'][] = $data['basic']['sno'];
                    $data['add']['scmNo'][$cKey] = $data['basic']['scmNo'];
                    $data['add']['sno'][] = gd_isset($arrData['add'][$key]['sno'][$cKey]);
                    $data['add']['addPrice'][] = gd_isset($arrData['add'][$key]['addPrice'][$cKey], 0);
                    $data['add']['addArea'][] = gd_isset($arrData['add'][$key]['addArea'][$cKey]);
                }
            }
        }

        // 공통 키값
        $arrDataKey = ['basicKey' => $arrData['basic']['sno']];

        // 수정시
        if (gd_isset($arrData['basic']['sno']) !== '') {
            // 기본배송정책 업데이트
            $arrBind = $this->db->get_binding(DBTableField::tableScmDeliveryBasic(), $data['basic'], 'update');
            $this->db->bind_param_push($arrBind['bind'], 'i', $data['basic']['sno']);
            $this->db->set_update_db(DB_SCM_DELIVERY_BASIC, $arrBind['param'], 'sno = ?', $arrBind['bind']);

            // 디비정보와 입력값 비교
            $getCharge = $this->getSnoDeliveryCharge($arrData['basic']['sno']); // SCM별 배송비 정보
            $getChargeCompare = $this->db->get_compare_array_data($getCharge, $data['charge']); // SCM별 배송비 정보
            if ($arrData['basic']['deliveryConfigType'] == 'etc' && empty($chargeKey) === false) {
                $getChargeCompare = array_diff_key($getChargeCompare, $chargeKey);
            }
            $this->db->set_compare_process(DB_SCM_DELIVERY_CHARGE, gd_isset($data['charge']), $arrDataKey, $getChargeCompare);

            // 배송비조건 수정한 사람에 대한 로그 기록
            \Logger::channel('delivery')->debug(__METHOD__, $data);

            // 신규등록시
        } else {
            // 기본배송정책 추가
            if (empty($chargeKey) === true) {
                $arrBind = $this->db->get_binding(DBTableField::tableScmDeliveryBasic(), $data['basic'], 'insert');
                $this->db->set_insert_db(DB_SCM_DELIVERY_BASIC, $arrBind['param'], $arrBind['bind'], 'y');
                $basicKey = $this->db->insert_id();
                unset($arrBind);
            }

            // 배송비설정 배열 처리
            foreach ($arrData['charge'] as $key => $val) {
                foreach ($arrData['charge']['unitStart'] as $cKey => $cVal) {
                    $data['charge']['basicKey'][$cKey] = $basicKey;
                }
            }

            // 지역별추가배송비 배열 처리
            if (isset($arrData['add'])) {
                foreach ($arrData['add'] as $key => $val) {
                    foreach ($arrData['add'][$key]['addPrice'] as $cKey => $cVal) {
                        $data['add']['basicKey'][] = $basicKey;
                    }
                }
            }

            // 디비정보와 입력값 비교
            $getChargeCompare = $this->db->get_compare_array_data([], $data['charge']); // SCM별 배송비 정보
            if ($arrData['basic']['deliveryConfigType'] == 'etc' && empty($chargeKey) === false) {
                $getChargeCompare = array_diff_key($getChargeCompare, $chargeKey);
            }

            // 디비 쿼리 처리
            $this->db->set_compare_process(DB_SCM_DELIVERY_CHARGE, gd_isset($data['charge']), $arrDataKey, $getChargeCompare);
        }

        //본사인 경우만 배송 방식 기타명 수정 가능
        if(!Manager::isProvider() && trim($arrData['basic']['deliveryMethodFl']['etc']) !== ''){
            $policy = \App::load('\\Component\\Policy\\Policy');
            $policy->setValue('delivery.deliveryMethodEtc', array('deliveryMethodEtc' => $arrData['basic']['deliveryMethodEtc']));
        }

        unset($arrBind);

        if ($arrData['basic']['deliveryConfigType'] == 'etc') {
            return ['basicKey' => $basicKey, 'chargeKey' => $this->db->insertId];
        }
    }

    /**
     * 배송비조건 선택된 부분 삭제 처리
     *
     * @param array $arrData 배송비조건SNO 배열
     *
     * @throws Exception
     */
    public function deleteInfoDelivery($arrData)
    {
        $total = count($arrData['deliverChk']);
        $success = 0;
        foreach ($arrData['deliverChk'] as $dataSno) {
            if (Validator::number($dataSno) === false) {
                throw new Exception(__('배송비 조건은 필수 항목입니다.'));
            }

            // 상품에서 사용하지 않는 경우만 배송비 조건 삭제 처리
            $getData = $this->getSnoDeliveryBasic($dataSno, true);
            if ($getData['goodsCount'] == 0) {
                $arrBind = array('i', $dataSno);
                $this->db->set_delete_db(DB_SCM_DELIVERY_BASIC, 'sno = ? AND defaultFl = "n"', $arrBind);
                $success++;
            }
        }

        if($total!=$success){
            $msg = __('배송조건을 포함하고있는 상품이 존재하는경우 삭제하실 수 없습니다.');
        }

        return sprintf(__('총 %1$d개의 배송비조건 중 %2$d건을 삭제했습니다.<br>'.$msg), $total, $success);
    }

    /**
     * 지역별 배송비 그룹 등록/수정
     *
     * @param $arrData
     *
     * @throws Exception
     */
    public function saveInfoAreaGroup($arrData)
    {
        // SCM ID 체크
        if (Validator::required(gd_isset($arrData['method'])) === false) {
            throw new Exception('추가배송비 그룹명은 필수 항목입니다.');
        }

        $data['sno'] = gd_isset($arrData['sno'], '');
        $data['managerNo'] = Session::get('manager.sno');
        if ($arrData['scmUseFl'] == '1') {
            $data['scmNo'] = '0';
        } else {
            $data['scmNo'] = gd_isset($arrData['scmNo'], (Manager::isProvider() ? Session::get('manager.scmNo') : DEFAULT_CODE_SCMNO));
        }
        $data['method'] = gd_isset($arrData['method']);
        $data['description'] = gd_isset($arrData['description']);
        $data['defaultFl'] = ($this->getCountAreaGroupDelivery($data['scmNo']) == 1 ? 'y' : gd_isset($arrData['defaultFl'], 'n'));
        $add = gd_isset($arrData['add'], []);

        // 엑셀데이터 처리를 통한 추가지역리스트 생성
        if (Request::files()->has('excel') && Request::files()->get('excel.error') == 0) {
            // 파일
            $files = Request::files()->toArray();

            // 엑셀데이터 추출
            $excel = new SpreadsheetExcelReader();
            $excel->setOutputEncoding('UTF-8');
            $chk = $excel->read($files['excel']['tmp_name']);

            // 엑셀데이터 체크
            if ($chk === false) {
                $failMsg = __('엑셀 화일을 확인해 주세요. 다운 로드 받으신 엑셀파일을 이용해서 "Excel 97-2003 통합문서" 로 저장이 된 엑셀 파일만 가능합니다.');
                throw new Exception($failMsg);
            }

            // 반드시 Excel 97-2003 통합문서로 저장이 되어야 하며, 1번째 줄은 설명, 2번째 줄부터 데이타 입니다.
            if ($excel->sheets[0]['numRows'] < 2) {
                $failMsg = __('엑셀 화일을 확인해 주세요. 엑셀 데이타가 존재하지 않습니다. 데이타는 2번째 줄부터 작성을 하셔야 합니다.');
                throw new Exception($failMsg);
            }

            // 1,000건 넘길 시 예외 처리
            if ($excel->sheets[0]['numRows'] > 1001) {
                throw new Exception(__('지역별 배송비 입력데이터는 1,000건 이상 처리할 수 없습니다.'));
            }

            // 엑셀 데이터를 추출해서 데이터 설정 (상품주문번호가 없는 경우 제외)
            for ($i = 2; $i <= $excel->sheets[0]['numRows']; $i++) {
                if (isset($excel->sheets[0]['cells'][$i][2])) {
                    $add['scmNo'][$i] = $data['scmNo'];
                    $add['basicKey'][$i] = $data['sno'];
                    $add['addAreaCode'][$i] = 0;
                    $add['addArea'][$i] = trim($excel->sheets[0]['cells'][$i][1]);
                    $add['addPrice'][$i] = trim($excel->sheets[0]['cells'][$i][2]);
                    $add['addRegDt'][$i] = '';
                }
            }
        } else {
            // 지역별추가배송비 배열 처리
            if (isset($add)) {
                foreach ($add as $key => $val) {
                    foreach ($val as $ckey => $cval) {
                        $add['scmNo'][$ckey] = $data['scmNo'];
                        $add['basicKey'][$ckey] = $data['sno'];
                    }
                }
            }
        }

        // 배송 기본설정 처리 (일괄 'n' 처리) - 전체 적용
        if ($data['defaultFl'] == 'y' && $data['scmNo'] == '0') {
            $arrBind['param'][] = 'defaultFl = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', 'n');
            $this->db->set_update_db(DB_SCM_DELIVERY_AREA_GROUP, $arrBind['param'], 'defaultFl="y"', $arrBind['bind']);
        } elseif ($data['defaultFl'] == 'y') { // 배송 기본설정 처리 (일괄 'n' 처리) - 공급사별 적용
            $arrBind['param'][] = 'defaultFl = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', 'n');
            $this->db->set_update_db(DB_SCM_DELIVERY_AREA_GROUP, $arrBind['param'], 'defaultFl="y" AND scmNo = "' . $data['scmNo'] . '"', $arrBind['bind']);
        }

        // 공통 키값
        $arrDataKey = ['basicKey' => $data['sno']];

        // 추가배송지 그룹 업데이트
        if ($data['sno'] !== '') {
            $arrBind = $this->db->get_binding(DBTableField::tableScmDeliveryAreaGroup(), $data, 'update');
            $this->db->bind_param_push($arrBind['bind'], 'i', $data['sno']);
            $this->db->set_update_db(DB_SCM_DELIVERY_AREA_GROUP, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            unset($arrBind);

            $getArea = $this->getSnoDeliveryArea($data['sno']); // SCM별 지역별 추가 배송비 정보

            // 디비정보와 입력값 비교
            $getAddCompare = $this->db->get_compare_array_data($getArea, $add); // SCM별 추가 배송비 정보
            $this->db->set_compare_process(DB_SCM_DELIVERY_AREA, gd_isset($add), $arrDataKey, $getAddCompare);
        } // 추가배송지 그룹 신규 등록
        else {
            $arrBind = $this->db->get_binding(DBTableField::tableScmDeliveryAreaGroup(), $data, 'insert');
            $this->db->set_insert_db(DB_SCM_DELIVERY_AREA_GROUP, $arrBind['param'], $arrBind['bind'], 'y');
            unset($arrBind);

            // 지역별추가배송비 배열 처리
            foreach ($add as $key => $val) {
                foreach ($val as $ckey => $cval) {
                    $add['basicKey'][$ckey] = $this->db->insert_id();
                }
            }

            // 디비정보와 입력값 비교
            $getAddCompare = $this->db->get_compare_array_data([], $add); // SCM별 추가 배송비 정보
            $this->db->set_compare_process(DB_SCM_DELIVERY_AREA, gd_isset($add), $arrDataKey, $getAddCompare);
        }
        unset($arrBind);
    }

    /**
     * 지역별 배송 정책 정보 저장
     *
     * @param array $arrData 저장할 정보의 배열
     * @throws Exception
     */
    public function addAreaDelivery($arrData)
    {
        // 시/도 체크
        if (Validator::required(gd_isset($arrData['newAreaSido'])) === false) {
            throw new Exception(__('시/도는 필수 항목입니다.'));
        }

        // 시/군/구 체크
        if (Validator::required(gd_isset($arrData['newAreaGugun'])) === false) {
            throw new Exception(__('시/군/구는 필수 항목입니다.'));
        }

        // 시/군/구 체크
        if (Validator::required(gd_isset($arrData['newPrice'])) === false) {
            throw new Exception(__('추가배송비는 필수 항목입니다.'));
        }

        // 공통 키값
        $arrDataKey = ['scmNo' => $arrData['scmNo']];

        // 지역별추가배송비 배열 처리
        foreach ($arrData['add'] as $key => $val) {
            foreach ($arrData['add'][$key]['addPrice'] as $cKey => $cVal) {
                $data['add']['basicKey'][] = $data['basic']['sno'];
                $data['add']['sno'][] = gd_isset($arrData['add'][$key]['sno'][$cKey]);
                $data['add']['addPrice'][] = gd_isset($arrData['add'][$key]['addPrice'][$cKey], 0);
                $data['add']['addArea'][] = gd_isset($arrData['add'][$key]['addArea'][$cKey]);
            }
        }

        // 디비정보와 입력값 비교
        $getChargeCompare = $this->db->get_compare_array_data([], $data['add']); // SCM별 배송비 정보

        $this->db->set_compare_process(DB_SCM_DELIVERY_AREA, gd_isset($data['add']), $arrDataKey, $getChargeCompare);

        unset($arrBind);
    }

    /**
     * 지역별 배송비 삭제시 그룹과 지역별배송비 리스트 삭제 처리
     *
     * @param $arrData
     *
     * @return int 삭제카운트
     *
     * @throws Exception
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function deleteInfoAreaGroup($arrData)
    {
        $deleteCount = 0;
        foreach ($arrData['deliverChk'] as $dataSno) {
            $tempCount = $this->checkAreaDeliveryInBasicDelivery($dataSno);

            if ($tempCount == 0) {
                if (Validator::number($dataSno) === false) {
                    throw new Exception(__('지역별 추가배송비는 필수 항목입니다.'));
                }
                $arrBind = array('i', $dataSno);
                $this->db->set_delete_db(DB_SCM_DELIVERY_AREA_GROUP, 'sno = ? AND defaultFl = "n"', $arrBind);
                $this->db->set_delete_db(DB_SCM_DELIVERY_AREA, 'basicKey = ?', $arrBind);

                $deleteCount++;
            }
        }

        return $deleteCount;
    }

    /**
     * 배송업체 출력
     *
     * @param string $deliverySno 배송업체 sno (기본 null)
     * @param bool|string $useFl 사용중인 배송업체만 출력여부 (false : 전부, true : 사용중인것만)
     * @param null $channel
     *
     * @return array 배송업체 정보
     */
    public function getDeliveryCompany($deliverySno = null, $useFl = false, $channel = null)
    {

        $arrBind = [];
        $arrWhere = [];
        $arrField = DBTableField::setTableField('tableManageDeliveryCompany');

        // 배송업체 sno 가 있는경우
        if (is_null($deliverySno) === false) {
            if ($channel == 'naverpay') {
                $arrWhere[] = 'naverPayCode = ?';
                $this->db->bind_param_push($arrBind, 's', $deliverySno);
            } else {
                $arrWhere[] = 'sno = ?';
                $this->db->bind_param_push($arrBind, 'i', $deliverySno);
            }

        }

        // 사용중인 배송업체만 출력
        if ($useFl === true) {
            $arrWhere[] = 'useFl = ?';
            $this->db->bind_param_push($arrBind, 's', 'y');
        }

        if ($channel == 'naverpay') {
            $arrWhere[] = " (naverPayCode is not NULL AND  naverPayCode != '') ";
        }

        if (empty($arrBind) === true) {
            $arrBind = null;
        }

        $this->db->strField = 'sno, ' . implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'companySort ASC, companyName ASC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGE_DELIVERY_COMPANY . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 배송추적
     *
     * @param integer $invoiceCompanySno 배송 업체 sno
     * @param string $invoiceNo 송장번호
     *
     * @return mixed
     * @throws Exception
     */
    public function getDeliveryTrace($invoiceCompanySno, $invoiceNo)
    {
        // 배송 업체 체크
        if (Validator::required(gd_isset($invoiceCompanySno)) === false) {
            throw new Exception(__('배송 업체는 필수 항목입니다.'));
        }

        // 송장 번호 체크
        if (Validator::required(gd_isset($invoiceNo)) === false) {
            throw new Exception(__('송장 번호는 필수 항목입니다.'));
        }

        // bind 데이타
        $this->db->bind_param_push($arrBind, 's', $invoiceCompanySno);

        $strSQL = 'SELECT companyName, traceUrl FROM ' . DB_MANAGE_DELIVERY_COMPANY . ' WHERE sno = ?';
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        if ($invoiceCompanySno === '7') { // 대신택배
            $invoiceNo = str_replace('-', '', $invoiceNo);
            $dsInvoiceNo[0] = substr($invoiceNo, 0, 4);
            $dsInvoiceNo[1] = substr($invoiceNo, 4, 3);
            $dsInvoiceNo[2] = substr($invoiceNo, 7, 6);
            $traceUrl = $getData['traceUrl'];
            foreach ($dsInvoiceNo as $key => $value) {
                $traceUrl = str_replace('__INVOICENO' . $key . '__', $value, $traceUrl);
            }
        } else {
            $traceUrl = str_replace('__INVOICENO__', str_replace('-', '', $invoiceNo), $getData['traceUrl']);
        }

        if (!$this->isDomainAvailible($traceUrl)) {
            throw new AlertCloseException(sprintf(__('[%s] 배송추적URL이 존재하지 않습니다.%s관리자에게 문의해주세요.'), $getData['companyName'], chr(10)));
        }

        return $traceUrl;
    }

    /**
     * 배송추적시 json 처리 해야할 배송업체인지 체크
     *
     * @param integer $invoiceCompanySno 배송 업체 sno
     *
     * @return boolean
     */
    public function checkDeliveryTraceJsonType($invoiceCompanySno)
    {
        $jsonTypeInvoiceCompanySno = array(
            91, //원더스퀵
        );

        if(in_array((int)$invoiceCompanySno, $jsonTypeInvoiceCompanySno)){
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * json 데이터 파싱 후 반환
     *
     * @param integer $invoiceCompanySno 배송 업체 sno
     * @param string $traceUrl 배송추적 url
     *
     * @return array $returnData
     */
    public function getDeliveryTraceJsonData($invoiceCompanySno, $traceUrl)
    {
        $deliveryData = $this->curlDeliveryTrace($traceUrl);

        $returnData = $this->syncDeliveryTraceVariable($invoiceCompanySno, $deliveryData);

        return $returnData;
    }

    /**
     * 배송추적 url curl 통신
     *
     * @param string $traceUrl 배송추적 url
     *
     * @return array $responseData
     */
    public function curlDeliveryTrace($traceUrl)
    {
        $responseData = '';

        if(trim($traceUrl) !== ''){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $traceUrl);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $result = curl_exec($ch);
            curl_close($ch);

            if($result){
                $responseData = json_decode($result, true);
            }
        }

        return $responseData;
    }

    /**
     * 배송추적 결과를 노출하기 위한 변수 통일
     *
     * @param integer $invoiceCompanySno 배송 업체 sno
     * @param array $deliveryData 통신 및 json 파싱후 결과 데이터
     *
     * @return array $deliveryTraceData
     */
    public function syncDeliveryTraceVariable($invoiceCompanySno, $deliveryData)
    {
        $deliveryTraceData = array();
        switch((int)$invoiceCompanySno){
            //합동택배(3), 경동택배(6)
            case 3 :
            case 6 :
                $deliveryTraceData = array(
                    'startPlace' => trim($deliveryData['info']['branch_start']), //발송 영업소
                    'endPlace' => trim($deliveryData['info']['branch_end']), //도착 영업소
                    'goodsNm' => trim($deliveryData['info']['prod']), //품명
                    'goodsCnt' => trim($deliveryData['info']['cnt']), //수량
                    'sendName' => trim($deliveryData['info']['send_name']), //보내는 분
                    'acceptName' => trim($deliveryData['info']['re_name']), //받으시는 분
                    'invoiceNumber' => trim($deliveryData['info']['barcode']), //송장번호
                    'stepLocationUseFl' => 'y', //담당 점소 사용여부
                    'stepTelUseFl' => 'y', //연락처 사용여부
                    'stepRegDateUseFl' => 'y', //처리일 사용여부
                    'stepStatusUseFl' => 'y', //처리현황 사용여부
                    'colspan' => 4,
                    'stepCount' => count($deliveryData['items']),
                );

                if(count($deliveryData['items']) > 0){
                    foreach($deliveryData['items'] as $key => $valueArray){
                        $deliveryTraceData['step'][] = array(
                            'location' => trim($valueArray['location']), //담당 점소
                            'tel' => trim($valueArray['tel']), //연락처
                            'regDate' => trim($valueArray['reg_date']), //처리일
                            'status' => trim($valueArray['stat']), //처리현황
                        );
                    }
                }
                break;
            //  원더스퀵(91)
            case 91 :
                $deliveryTraceData = [
                    'startPlace' => trim($deliveryData['data']['senderDongAdministration']), //발송 주소
                    'endPlace' => trim($deliveryData['data']['receiverDongAdministration']), //도착 주소
                    'goodsNm' => trim($deliveryData['data']['orderItem']['orderItem']), //품명
                    'sendName' => trim($deliveryData['data']['senderName']), //보내는 분
                    'acceptName' => trim($deliveryData['data']['receiverName']), //받으시는 분
                    'invoiceNumber' => trim($deliveryData['data']['invoiceNo']), //송장번호
                    'stepLocationUseFl' => 'y', //담당 점소 사용여부
                    'stepTelUseFl' => 'y', //연락처 사용여부
                    'stepRegDateUseFl' => 'y', //처리일 사용여부
                    'stepStatusUseFl' => 'y', //처리현황 사용여부
                    'colspan' => 4,
                    'stepCount' => count($deliveryData['data']['transportStatuses']),
                ];

                if (count($deliveryData['data']['transportStatuses']) > 0){
                    foreach($deliveryData['data']['transportStatuses'] as $key => $valueArray){
                        $traceData = explode(': ', $valueArray);
                        switch(mb_substr($traceData[0], 0, 2, 'UTF-8')){
                            case '픽업':
                                $traceLocation = $deliveryData['data']['pickupRiderName'];
                                $traceTel = $deliveryData['data']['pickupRiderPhone'];
                                break;
                            case '배송':
                                $traceLocation = $deliveryData['data']['deliverRiderName'];
                                $traceTel = $deliveryData['data']['deliverRiderPhone'];
                                break;
                        }
                        $deliveryTraceData['step'][] = [
                            'location' => trim($traceLocation), //담당 점소
                            'tel' => trim($traceTel), //연락처
                            'status' => trim($traceData[0]), //처리현황
                            'regDate' => trim($traceData[1]), //처리일
                        ];
                    }
                }
                break;
        }

        return $deliveryTraceData;
    }

    /**
     * isDomainAvailible
     *
     * @param $domain
     *
     * @return bool
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function isDomainAvailible($domain)
    {
        //check, if a valid url is provided
        if (!filter_var($domain, FILTER_VALIDATE_URL)) {
            return false;
        }

        //initialize curl
        $curlInit = curl_init($domain);
        curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curlInit, CURLOPT_HEADER, true);
        curl_setopt($curlInit, CURLOPT_NOBODY, true);
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlInit, CURLOPT_SSL_VERIFYPEER, false);

        //get answer
        $response = curl_exec($curlInit);

        curl_close($curlInit);

        if ($response) return true;

        return false;
    }

    /**
     * 배송 업체 등록/수정/삭제/순서변경 등 일괄 처리
     *
     * @todo 무조건 한개 이상이 남아있어야 하는 부분에 대한 처리가 필요
     *
     * @param string $getData 변경 데이타
     *
     * @throws Exception
     */
    public function saveDeliveryCompany($getData)
    {
        // 데이타 체크
        if (isset($getData['sno']) === false) {
            throw new Exception(self::ECT_INVALID_ARG, self::TEXT_ERROR_VALUE);
        }
        unset($getData['mode']);

        //        // 정렬을 위한 필드 생성
        //        foreach ($getData['sno'] as $key => $val) {
        //            $tmpField[] = 'WHEN \'' . $val . '\' THEN \'' . sprintf('%02s', $key) . '\'';
        //        }
        //
        //        // 정렬 기준값처리
        //        $strSetSQL = 'SET @newSort := ' . (count($getData['sno'])) . ';';
        //        $this->db->query($strSetSQL);
        //
        //        // 이동된 상품을 기준으로 소트
        //        $sortField = ' CASE sno ' . implode(' ', $tmpField) . ' ELSE \'\' END ';
        //        $strSQL = 'UPDATE ' . DB_MANAGE_DELIVERY_COMPANY . '
        //            SET companySort = ( @newSort := @newSort-1 ) ORDER BY (' . $sortField . ') DESC';
        //        $this->db->query($strSQL);

        // 배송업체 일괄 처리
        $company = $this->getDeliveryCompany();
        foreach ($getData['sno'] as $key => $val) {
            $getData['companySort'][$key] = $key + 1;
        }

        $arrDataKey = ['sno' => $getData['sno']];
        $getCompanyCompare = $this->db->get_compare_array_data($company, $getData); // SCM별 배송비 정보
        $this->db->set_compare_process(DB_MANAGE_DELIVERY_COMPANY, gd_isset($getData), $arrDataKey, $getCompanyCompare,['company','traceUrl','useFl','companySort','companyName']);
    }


    /**
     * 배송비 부과방법 / 배송비 유형에 따른 배송정보 - 상품검색에서 사용
     *
     * @param string $deliverySno 배송업체 sno (기본 null)
     * @param string $useFl 사용중인 배송업체만 출력여부 (false : 전부, true : 사용중인것만)
     *
     * @return array 배송업체 정보
     */
    public function getDeliveryGoods($arrData)
    {
        $arrBind = [];
        $arrWhere = [];


        $tmpFixFl = array_flip($arrData['goodsDeliveryFixFl']);
        unset($tmpFixFl['all']);

        if (count($tmpFixFl) || $arrData['goodsDeliveryFl']) {
            if (count($tmpFixFl) == 0) {
                $arrWhere[] = 'goodsDeliveryFl = ?';
                $this->db->bind_param_push($arrBind, 's', $arrData['goodsDeliveryFl']);
            } else {
                if ($arrData['goodsDeliveryFl']) {
                    $add_sql = "goodsDeliveryFl = '{$arrData['goodsDeliveryFl']}' AND";
                }

                foreach (array_flip($tmpFixFl) as $k => $v) {
                    $tmpWhere[] = $add_sql . ' fixFl = ?';
                    $this->db->bind_param_push($arrBind, 's', $v);
                }

                $arrWhere[] = "((" . implode(") OR (", $tmpWhere) . "))";
            }
        }

        $tmpWhere = [];
        // 공급사가 가 있는경우
        if (gd_isset($arrData['scmFl']) && $arrData['scmFl'] != 'all') {
            if (is_array($arrData['scmNo'])) {
                foreach ($arrData['scmNo'] as $val) {
                    $tmpWhere[] = 'scmNo = ?';
                    $this->db->bind_param_push($arrBind, 's', $val);
                }
                $arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            } else {
                $arrWhere[] = 'scmNo = ?';
                $this->db->bind_param_push($arrBind, 'i', $arrData['scmNo']);
            }

        }

        if (empty($arrBind) === true) {
            $arrBind = null;
        }

        $this->db->strField = 'sno';
        $this->db->strWhere = implode(' AND ', $arrWhere);


        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_DELIVERY_BASIC . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);


        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }


    /**
     * 배송비유형별 단위 텍스트 반환
     *
     * @param $code 배송비유형 코드
     *
     * @return string
     */
    public function getUnitText($code)
    {
        switch ($code) {
            case 'fixed':
            case 'free':
                $text = '';
                break;
            case 'price':
                $text = Globals::get('gCurrency.string');
                break;
            case 'weight':
                $text = Globals::get('gWeight.unit');
                break;
            case 'count':
                $text = __('개');
                break;
        }

        return $text;
    }

    /**
     * 배송비유형 코드를 텍스트로 변환
     *
     * @param $code 배송비유형 코드
     *
     * @return string
     */
    public function getFixFlText($code)
    {
        switch ($code) {
            case 'fixed':
                $text = __('고정배송비');
                break;
            case 'price':
                $text = __('금액별배송비');
                break;
            case 'weight':
                $text = __('무게별배송비');
                break;
            case 'count':
                $text = __('수량별배송비');
                break;
            case 'free':
                $text = __('배송비무료');
                break;
        }

        return $text;
    }

    /**
     * 배송비부과방법 코드를 텍스트로 변환
     *
     * @param $code 배송비부과방법 코드
     *
     * @return string
     */
    public function getGoodsDeliveryFlText($code)
    {
        switch ($code) {
            case 'y':
                $text = __('배송비조건별');
                break;
            case 'n':
                $text = __('상품별');
                break;
        }

        return $text;
    }

    /**
     * 배송비결제방법 코드를 텍스트로 변환
     *
     * @param $code 배송비결제방법 코드
     *
     * @return string
     */
    public function getCollectFlText($code)
    {
        switch ($code) {
            case 'pre':
                $text = __('선불');
                break;
            case 'later':
                $text = __('착불');
                break;
            case 'both':
                $text = __('선불/착불');
                break;
        }

        return $text;
    }

    /**
     * 지역별 배송비 여부를 텍스트로 변환
     *
     * @param $code 지역별배송비 여부
     *
     * @return string
     */
    public function getAddFlText($code)
    {
        switch ($code) {
            case 'y':
                $text = __('있음');
                break;
            case 'n':
                $text = __('없음');
                break;
        }

        return $text;
    }

    /**
     * SCM 최초 만들때 기본값 세팅 하는 로직
     *
     * @param $arrData
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function saveDeliveryDefaultData($arrData)
    {
        // 배송비 기본값 세팅
        $data = [
            [
                'basic' => [
                    'defaultFl' => 'y',
                    'method' => __('기본 - 고정배송비'),
                    'description' => __('기본 - 고정배송비'),
                    'fixFl' => 'fixed',
                    'goodsDeliveryFl' => 'y',
                    'collectFl' => 'both',
                ],
                'charge' => [
                    'unitStart' => [
                        0,
                    ],
                    'unitEnd' => [
                        0,
                    ],
                    'price' => [
                        2500,
                    ],
                ],
            ],
            [
                'basic' => [
                    'defaultFl' => 'n',
                    'method' => __('기본 - 금액별배송비'),
                    'description' => __('기본 - 금액별배송비'),
                    'fixFl' => 'price',
                    'goodsDeliveryFl' => 'y',
                    'collectFl' => 'pre',
                ],
                'charge' => [
                    'unitStart' => [
                        0,
                        50000,
                    ],
                    'unitEnd' => [
                        50000,
                    ],
                    'price' => [
                        2500,
                        0,
                    ],
                ],
            ],
            [
                'basic' => [
                    'defaultFl' => 'n',
                    'method' => __('기본 - 수량별배송비'),
                    'description' => __('기본 - 수량별배송비'),
                    'fixFl' => 'count',
                    'goodsDeliveryFl' => 'y',
                    'collectFl' => 'pre',
                ],
                'charge' => [
                    'unitStart' => [
                        0,
                        5,
                        10,
                    ],
                    'unitEnd' => [
                        5,
                        10,
                    ],
                    'price' => [
                        3000,
                        5000,
                        0,
                    ],
                ],
            ],
            [
                'basic' => [
                    'defaultFl' => 'n',
                    'method' => __('기본 - 배송비무료'),
                    'description' => __('기본 - 배송비무료'),
                    'fixFl' => 'free',
                    'goodsDeliveryFl' => 'y',
                    'collectFl' => 'pre',
                ],
                'charge' => [
                    'unitStart' => [
                        0,
                    ],
                    'unitEnd' => [
                        0,
                    ],
                    'price' => [
                        0,
                    ],
                ],
            ],
        ];

        // 상단의 기본 데이터 저장하기
        foreach ($data as $val) {
            $val['basic']['scmNo'] = $arrData['scmNo'];
            $val['basic']['deleteFl'] = 'n';
            $val['basic']['areaFl'] = 'n';
            $val['basic']['unstoringFl'] = 'same';
            $val['basic']['unstoringZonecode'] = $arrData['unstoringZonecode'];
            $val['basic']['unstoringZipcode'] = $arrData['unstoringZipcode'];
            $val['basic']['unstoringAddress'] = $arrData['unstoringAddress'];
            $val['basic']['unstoringAddressSub'] = $arrData['unstoringAddressSub'];
            $val['basic']['returnFl'] = 'same';
            $val['basic']['returnZonecode'] = $arrData['returnZonecode'];
            $val['basic']['returnZipcode'] = $arrData['returnZipcode'];
            $val['basic']['returnAddress'] = $arrData['returnAddress'];
            $val['basic']['returnAddressSub'] = $arrData['returnAddressSub'];

            $arrBind = $this->db->get_binding(DBTableField::tableScmDeliveryBasic(), $val['basic'], 'insert');
            $this->db->set_insert_db(DB_SCM_DELIVERY_BASIC, $arrBind['param'], $arrBind['bind'], 'y');
            unset($arrBind);

            // 배송비설정 배열 처리
            foreach ($val['charge'] as $cKey => $cVal) {
                foreach ($val['charge']['unitStart'] as $uKey => $uVal) {
                    $data['charge'][$cKey][] = gd_isset($val['charge'][$cKey][$uKey]);
                    $data['charge']['basicKey'][$uKey] = $this->db->insert_id();
                    $data['charge']['scmNo'][$uKey] = $arrData['scmNo'];
                }
            }

            // 디비정보와 입력값 비교
            $getChargeCompare = $this->db->get_compare_array_data([], $data['charge']); // SCM별 배송비 정보

            // 디비 쿼리 처리
            $this->db->set_compare_process(DB_SCM_DELIVERY_CHARGE, gd_isset($data['charge']), ['basicKey' => 0], $getChargeCompare);
            unset($data);
        }
    }

    /**
     * 배송 방식 데이터 셋팅
     *
     * @param array $deliveryMethodFlData
     * @return array $returnData
     *
     * @author <bumyul2000@godo.co.kr>
     */
    public function setDeliveryMethodData($deliveryMethodFlData)
    {
        $returnData = [];
        $arrData = explode(STR_DIVISION, $deliveryMethodFlData);
        foreach($this->deliveryMethodList['list'] as $key => $value){
            $returnData[$value] = $arrData[$key];
        }

        return $returnData;
    }

    /**
     * 택배 이외 배송방식의 sno를 얻기위한 셋팅
     */
    public function setDeliveryMethodCompanySno()
    {
        $arrBind = [];
        $arrWhere = [];

        //배송방식이 택배가 아닌것들
        $arrWhere[] = 'deliveryFl = ?';
        $this->db->bind_param_push($arrBind, 's', 'n');

        $this->db->strField = 'sno, companyKey';
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGE_DELIVERY_COMPANY . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) > 0) {
            foreach($getData as $key => $value){
                $this->deliveryMethodList['sno'][$value['companyKey']] = $value['sno'];
            }
        }
    }

    /**
     * 배송 방식 display
     * @param string $deliveryMethodFl 배송방식
     *
     * @return string $deliveryMethodHtml
     */
    public static function getDeliveryMethodDisplay($deliveryMethodFl)
    {
        $delivery = \App::load('\\Component\\Delivery\\Delivery');
        $request = \App::getInstance('request');
        $subDomain = $request->getSubdomainDirectory();

        $deliveryMethodHtml = '';
        $deliveryMethodFlArr = [];
        $deliveryMethodFlArr = array_filter(explode(STR_DIVISION, $deliveryMethodFl));
        if(count($deliveryMethodFlArr) > 0) {
            foreach ($deliveryMethodFlArr as $key => $value) {
                if ($value === 'etc') {
                    if($subDomain === 'front' || $subDomain === 'mobile'){
                        $deliveryMethodFlArr[$key] = $delivery->getDeliveryMethodEtcName();
                    }
                    else {
                        $deliveryMethodFlArr[$key] = $delivery->deliveryMethodList['name'][$value];
                    }
                } else {
                    $deliveryMethodFlArr[$key] = $delivery->deliveryMethodList['name'][$value];
                }
            }
            $deliveryMethodHtml = implode(" / ", $deliveryMethodFlArr);
        }

        return $deliveryMethodHtml;
    }

    /**
     * 배송 방식 기타명 반환
     * @param void
     *
     * @return string $etcName
     */
    public static function getDeliveryMethodEtcName()
    {
        $etcName = '';
        $deliveryMethodEtc = gd_policy('delivery.deliveryMethodEtc');
        if(trim($deliveryMethodEtc['deliveryMethodEtc']) === ''){
            if(!is_object($delivery)){
                $delivery = \App::load('\\Component\\Delivery\\Delivery');
            }
            $etcName = $delivery->deliveryMethodList['name']['etc'];
        }
        else {
            $etcName = $deliveryMethodEtc['deliveryMethodEtc'];
        }

        return $etcName;
    }

    /**
     * 방문 수령지 주소
     *
     * @param integer $deliverySno
     * @return string $visitAddress
     *
     * @author <bumyul2000@godo.co.kr>
     */
    public static function getVisitAddress($deliverySno, $baseFl = false)
    {
        if (!is_object($db)) {
            $db = \App::load('DB');
        }

        if(!$deliverySno){
            return '';
        }

        $visitAddress = '';
        $arrBind = [
            'i',
            $deliverySno
        ];
        $arrIncludeOd = [
            'dmVisitTypeFl',
            'dmVisitTypeAddress',
            'dmVisitTypeAddressSub',
        ];
        $strSQL = "SELECT " . implode(", ", $arrIncludeOd). " FROM " . DB_SCM_DELIVERY_BASIC . " WHERE sno = ? ";
        $getData = $db->query_fetch($strSQL, $arrBind);
        if(count($getData) > 0){
            if ($baseFl === true) {
                $baseInfo = gd_policy('basic.info');
                switch ($getData[0]['dmVisitTypeFl']) {
                    case 'same':
                        $visitAddress = trim($baseInfo['address']) . ' ' . trim($baseInfo['addressSub']);
                        break;
                    case 'unstoring':
                        if (empty($baseInfo['unstoringNoList']) === true || $baseInfo['unstoringNoList'][0] == 1) {
                            $visitAddress = trim($baseInfo['unstoringAddress']) . ' ' . trim($baseInfo['unstoringAddressSub']);
                        } else {
                            $visitAddress = trim($baseInfo['unstoringAddressList'][0]) . ' ' . trim($baseInfo['unstoringAddressSubList'][0]);
                        }
                        break;
                    case 'new':
                        $visitAddress = trim($getData[0]['dmVisitTypeAddress']) . ' ' . trim($getData[0]['dmVisitTypeAddressSub']);
                        break;
                }
            }
            if (empty(trim($visitAddress)) === true) $visitAddress = trim($getData[0]['dmVisitTypeAddress']) . ' ' . trim($getData[0]['dmVisitTypeAddressSub']);
        }

        return $visitAddress;
    }

    /**
     * 출고지 주소 반환
     *
     * @param integer $sno
     * @return string $unstoringAddress
     *
     * @author <dlwoen9@godo.co.kr>
     */
    public static function getUnstoringAddress($sno)
    {
        if (!is_object($db)) {
            $db = \App::load('DB');
        }

        $unstoringAddress = '';

        $arrInclude = [
            'unstoringZonecode',
            'unstoringZipcode',
            'unstoringAddress',
            'unstoringAddressSub',
        ];

        $arrBind = [
            'i',
            $sno,
        ];
        $strSQL = 'SELECT ' . implode(',', $arrInclude) . ' FROM ' . DB_SCM_DELIVERY_BASIC . ' WHERE sno = ?';

        $result = $db->query_fetch($strSQL, $arrBind, false);

        if(count($result) > 0){
            $unstoringAddress = implode(' ', $result);
        }

        return $unstoringAddress;
    }

    /**
     * 반품/교환지 주소 반환
     *
     * @param integer $sno
     * @return string $returnAddress
     *
     * @author <dlwoen9@godo.co.kr>
     */
    public static function getReturnAddress($sno)
    {
        if (!is_object($db)) {
            $db = \App::load('DB');
        }

        $returnAddress = '';

        $arrInclude = [
            'returnZonecode',
            'returnZipcode',
            'returnAddress',
            'returnAddressSub',
        ];

        $arrBind = [
            'i',
            $sno,
        ];

        $strSQL = 'SELECT ' . implode(',', $arrInclude) . ' FROM ' . DB_SCM_DELIVERY_BASIC . ' WHERE sno = ?';

        $result = $db->query_fetch($strSQL, $arrBind, false);

        if(count($result) > 0){
            $returnAddress = implode(' ', $result);
        }

        return $returnAddress;
    }

    /**
     * 배송지 유형, 조건명, 설명
     *
     * @param integer $deliverySno
     * @return string $deliveryInfo
     *
     * @author <dlwoen9@godo.co.kr>
     */
    public function getDeliveryType($deliverySno)
    {

        if(!$deliverySno){
            return '';
        }

        $arrBind = [
            'i',
            $deliverySno
        ];
        $arrWhere = 'WHERE sno = ?';

        $this->db->strField = 'fixFl, method, description';
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();

        $strSQL = "SELECT " . array_shift($query). " FROM " . DB_SCM_DELIVERY_BASIC . " WHERE sno = ? ";

        $getData = $this->db->query_fetch($strSQL, $arrBind);

        $getData['deliveryType'] = $this->getFixFlText($getData[0]['fixFl']);
        $getData['method'] = $getData[0]['method'];
        $getData['description'] = $getData[0]['description'];

        unset($getData[0]);

        return $getData;
    }

    /**
     * 공급사 중 기본 지역추가 배송비가 있는지 여부 확인
     *
     * @param string $sno 지역별배송지 그룹 NO
     *
     * @return array 지역별 배송지 리스트
     * @throws Exception
     */
    public function getCountDefaultDeliveryArea()
    {
        $strSQL = 'SELECT COUNT(sno) AS cnt FROM ' . DB_SCM_DELIVERY_AREA_GROUP . ' WHERE defaultFl = \'y\' AND scmNo != 1';
        $getData = $this->db->query_fetch($strSQL);

        if ($getData) {
            return $getData[0]['cnt'];
        } else {
            return 0;
        }
    }

    /**
     * 지역별추가배송비가 사용되는 기본배송정책이있는지 체크
     *
     * @param mixed $scmNo 공급사 번호
     * @return mixed
     */
    public function checkAreaDeliveryInBasicDelivery($areaGroupNo = null)
    {
        if ($areaGroupNo !== null) {
            $strWhere = ' WHERE areaGroupNo = \'' . $areaGroupNo . '\'';

            $strSQL = 'SELECT sno FROM ' . DB_SCM_DELIVERY_BASIC . $strWhere;
            $this->db->query_fetch($strSQL);

            return $this->db->affected_rows();
        } else {
            return 0;
        }
    }

    public function getVisitDeliveryInfo($data)
    {
        $setData = $tmpData = [];
        foreach ($data['visitDelivery'] as $key => $val) {
            foreach ($val as $goodsSno => $deliverySno) {
                if (empty($setData['address'][$key]) === true) {
                    $tmpData['goodsSno'][$key] = $goodsSno;
                    $tmpData['address'][$key] = $data['visitAddressInfo'][$key][$goodsSno];
                }
                if ($data['deliveryMethodFl'][$key][$goodsSno] == 'visit') {
                    $setData['goodsSno'][$key][] = $goodsSno;
                    $setData['address'][$key][] = $data['visitAddressInfo'][$key][$goodsSno];
                }
            }

            if (empty($setData['address'][$key]) === true) {
                $setData['goodsSno'][$key][] = $tmpData['goodsSno'][$key];
                $setData['address'][$key][] = $tmpData['address'][$key];
            }
        }
        unset($tmpData);

        return $setData;
    }
}

