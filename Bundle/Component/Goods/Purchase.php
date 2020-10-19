<?php
/**
 * 매입사
 * @author atomyang
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Goods;

use Component\Database\DBTableField;
use Component\Validator\Validator;
use Globals;
use LogHandler;
use Request;
use Session;
use Exception;
use Framework\Debug\Exception\AlertBackException;


class Purchase
{
    const ECT_INVALID_ARG = 'Config.ECT_INVALID_ARG';
    const TEXT_INVALID_NOTARRAY_ARG = '%s이 배열이 아닙니다.';
    const TEXT_INVALID_EMPTY_ARG = '%s이 비어있습니다.';
    const TEXT_REQUIRE_VALUE = '%s은(는) 필수 항목 입니다.';
    const TEXT_USELESS_VALUE = '%s은(는) 사용할 수 없습니다.';

    protected $db;


    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

    }

    /**
     * 매입처 코드 생성
     *
     * @author atomyang
     * @return array 매입처 코드
     */
    protected function getNewPurchaseNo()
    {
        $data = $this->getInfoPurchase(null, 'if(max(purchaseNo) > 0, (max(purchaseNo) + 1), ' . DEFAULT_CODE_PURCHASENO . ') as newPurchaseNo');
        return $data['newPurchaseNo'];
    }

    /**
     * 상품 번호를 Goods 테이블에 저장
     *
     * @return string 저장된 상품 번호
     */
    protected function doPurchaseNoInsert()
    {
        $newPurchaseNo = $this->getNewPurchaseNo();
        $this->db->set_insert_db(DB_PURCHASE, 'purchaseNo', array('i', $newPurchaseNo), 'y');
        return $newPurchaseNo;
    }


    /**
     * saveInfoAddGoods
     *
     * @param $arrData
     * @return string
     * @throws Except
     */
    public function saveInfoPurchase($arrData)
    {
        // 추가상품명 체크
        if (Validator::required(gd_isset($arrData['purchaseNm'])) === false) {
            throw new \Exception(__('매입처명은 필수 항목입니다.'), 500);
        }

        // 출고지 주소
        if ($arrData['chkSameUnstoringAddr'] == 'y') {
            $arrData['unstoringZonecode'] = $arrData['zonecode'];
            $arrData['unstoringZipcode'] = $arrData['zipcode'];
            $arrData['unstoringAddress'] = $arrData['address'];
            $arrData['unstoringAddressSub'] = $arrData['addressSub'];
        }

        // 반품/교환 주소
        if ($arrData['chkSameReturnAddr'] == 'y') {
            $arrData['returnZonecode'] = $arrData['zonecode'];
            $arrData['returnZipcode'] = $arrData['zipcode'];
            $arrData['returnAddress'] = $arrData['address'];
            $arrData['returnAddressSub'] = $arrData['addressSub'];
        } else if ($arrData['chkSameReturnAddr'] == 'x') {
            $arrData['returnZonecode'] = $arrData['unstoringZonecode'];
            $arrData['returnZipcode'] = $arrData['unstoringZipcode'];
            $arrData['returnAddress'] = $arrData['unstoringAddress'];
            $arrData['returnAddressSub'] = $arrData['unstoringAddressSub'];
        }

        // 담당자 정보
        $staff = [];
        $staffNum = count($arrData['staffName']);
        for ($i = 0; $i < $staffNum; $i++) {
            $staff[$i]['staffName'] = $arrData['staffName'][$i];
            $staff[$i]['staffTel'] = $arrData['staffTel'][$i];
            $staff[$i]['staffPhone'] = $arrData['staffPhone'][$i];
            $staff[$i]['staffEmail'] = $arrData['staffEmail'][$i];
            $staff[$i]['staffMemo'] = $arrData['staffMemo'][$i];
        }
        $arrData['staff'] = json_encode(gd_htmlspecialchars_addslashes($staff), JSON_UNESCAPED_UNICODE);

        // 매입처 정보 저장
        if ($arrData['mode'] == 'register') {
            $arrData['purchaseNo'] = $this->getNewPurchaseNo();
            $arrBind = $this->db->get_binding(DBTableField::tablePurchase(), $arrData, 'insert');
            $this->db->set_insert_db(DB_PURCHASE, $arrBind['param'], $arrBind['bind'], 'y');
        } else {
            //이전 데이터 가져오기
            $strSQL = ' SELECT *  FROM ' . DB_PURCHASE . ' WHERE purchaseNo = ?';
            $orgData = $this->db->query_fetch($strSQL, ['s', $arrData['purchaseNo']], false);
            $arrBind = $this->db->get_binding(DBTableField::tablePurchase(), $arrData, 'update');
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['purchaseNo']);
            $this->db->set_update_db(DB_PURCHASE, $arrBind['param'], 'purchaseNo = ?', $arrBind['bind']);
            unset($arrBind);
        }

        if ($arrData['mode'] == 'modify') {
            // 전체 로그를 저장합니다.
            $orgData = ['before' => $orgData];
            LogHandler::wholeLog('goods_Purc', null, 'modify', $arrData['purchaseNo'], json_encode($orgData, JSON_UNESCAPED_UNICODE));
        }

    }

    /**
     * saveInfoAddGoods
     *
     * @param $arrData
     * @return string
     * @throws Except
     */
    public function getAdminListPurchase($mode = null, $pageNum = 10)
    {
        $getValue = Request::get()->toArray();

        //삭제된것 제외
        $this->arrWhere[] = 'p.delFl = ?';
        $this->db->bind_param_push($this->arrBind, 's', 'n');

        // --- 검색 설정
        $this->setSearchPurchase($getValue);

        // --- 정렬 설정
        $sort = gd_isset($getValue['sort']);
        if (empty($sort)) {
            $sort = 'p.purchaseNo desc';
        }

        if ($mode == 'layer') {
            // --- 페이지 기본설정
            if (gd_isset($getValue['pagelink'])) {
                $getValue['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
            } else {
                $getValue['page'] = 1;
            }
            gd_isset($getValue['pageNum'], '10');
        } else {
            // --- 페이지 기본설정
            gd_isset($getValue['page'], 1);
            gd_isset($getValue['pageNum'], 10);
        }

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수

        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_PURCHASE.' WHERE delFl="n"';
        $res = $this->db->query_fetch($strSQL,null,false);
        unset($arrBind);

        $page->recode['amount'] = $res['cnt']; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 현 페이지 결과
        $this->db->strField = "p.*";
        if(gd_isset($this->arrWhere)) $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        // 검색 카운트
        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_PURCHASE.' as p WHERE delFl="n" AND ' . $this->db->strWhere;
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_PURCHASE . ' as p ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }


    public function getAdminListPurchaseExcel($getValue)
    {
        // --- 검색 설정
        $this->setSearchPurchase($getValue);

        if($getValue['sno'] && is_array($getValue['sno'])) {
            $this->arrWhere[] = 'sno IN (' . implode(',', $getValue['sno']) . ')';
        }

        // --- 정렬 설정
        $sort = 'p.regDt desc';

        // 현 페이지 결과
        $this->db->strJoin = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = p.scmNo ';
        $this->db->strField = "p.*,s.companyNm as scmNm";
        if(gd_isset($this->arrWhere)) $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_PURCHASE . ' as p ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 각 데이터 배열화
        return gd_htmlspecialchars_stripslashes(gd_isset($data));
    }


    /**
     * setSearchPurchase
     *
     * @param $searchData
     * @param int $searchPeriod
     */
    public function setSearchPurchase($searchData)
    {
        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tablePurchase');

        //검색키 설정
        /* @formatter:off */
        $this->search['combineSearch'] =[
            'all' => __('=통합검색='),
            'p.purchaseNm' => __('매입처명'),
            'p.purchaseNo' => __('매입처코드'),
            'p.purchaseCd' => __('매입처 자체코드'),
            'p.category' => __('상품유형')]
        ;
        /* @formatter:on */

        //검색설정
        /* @formatter:off */
        $this->search['sortList'] = array(
            'purchaseNo desc' => '등록일 ↓',
            'purchaseNo asc' => '등록일 ↑',
            'purchaseNm asc' => '매입처명 ↓',
            'purchaseNm desc' => '매입처명 ↑',
            'category asc' => '상품유형 ↓',
            'category desc' => '상품유형 ↑'
        );
        /* @formatter:on */


        // --- 검색 설정
        $this->search['sort'] = gd_isset($searchData['sort'], 'p.purchaseNo desc');
        $this->search['key'] = gd_isset($searchData['key']);
        $this->search['keyword'] = gd_isset($searchData['keyword']);
        $this->search['detailSearch'] = gd_isset($searchData['detailSearch']);
        $this->search['searchDateFl'] = gd_isset($searchData['searchDateFl'], 'regDt');

        // 매입처 선택 레이어 디폴트 all / 그외 7일
        if (gd_php_self() == '/share/layer_purchase.php' || gd_php_self() =='/goods/purchase_list.php') {
            $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod'], '-1');
        } else {
            $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod'],'6');
        }

        $this->search['useFl'] = gd_isset($searchData['useFl'],'all');
        $this->search['businessFl'] = gd_isset($searchData['businessFl'],'all');

        if( $this->search['searchPeriod']  < 0) {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0]);
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1]);
        } else {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], date('Y-m-d', strtotime('-6 day')));
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], date('Y-m-d'));
        }

        $this->checked['useFl'][$searchData['useFl']] = $this->checked['businessFl'][$searchData['businessFl']] = "checked='checked'";
        $this->selected['searchDateFl'][$this->search['searchDateFl']] = $this->selected['sort'][$this->search['sort']] = "selected='selected'";
        $this->checked['searchPeriod'][$this->search['searchPeriod']] ="active";

        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = array('p.purchaseNm', 'p.purchaseNo', 'p.purchaseCd', 'p.category');
                $arrWhereAll = array();
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            } else {
                $this->arrWhere[] = '' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
            }
        }

        // 처리일자 검색
        if ($this->search['searchDateFl'] && $this->search['searchDate'][0] && $this->search['searchDate'][1]) {
            $this->arrWhere[] = 'p.' . $this->search['searchDateFl'] . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }

        //사용여부
        if ($this->search['useFl'] != 'all') {
            $this->arrWhere[] = 'p.useFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['useFl'], $this->search['useFl']);
        }

        //거래여부
        if ($this->search['businessFl'] != 'all') {
            $this->arrWhere[] = 'p.businessFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['businessFl'], $this->search['businessFl']);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

    }


    /**
     * getDataPurchase
     *
     * @param null $sno
     * @return mixed
     */
    public function getDataPurchase($sno = null)
    {
        // --- 등록인 경우
        if (is_null($sno)) {
            // 기본 정보
            $data['mode'] = 'register';
            $data['sno'] = null;

            // 기본값 설정
            DBTableField::setDefaultData('tablePurchase', $data);

            // --- 수정인 경우
        } else {
            // 기본 정보
            $data = $this->getInfoPurchase($sno); // 사은품 기본 정보

            if (Session::get('manager.isProvider')) {
                if($data['scmNo'] !='0' && $data['scmNo'] != Session::get('manager.scmNo')) {
                    throw new AlertBackException(__("타 공급사의 자료는 열람하실 수 없습니다."));
                }
            }

            $data['mode'] = 'modify';

            if ($data['info']) {
                $data['addPurchase'] = json_decode($data['info'], true);
            }

            // 기본값 설정
            DBTableField::setDefaultData('tablePurchase', $data);
        }

        // --- 기본값 설정
        gd_isset($data['stockFl'], 'n');

        if ($data['zonecode'] == $data['unstoringZonecode'] && $data['addressSub'] == $data['unstoringAddressSub']) {
            $data['chkSameUnstoringAddr'] = 'y';
        } else {
            $data['chkSameUnstoringAddr'] = 'n';
        }
        if ($data['zonecode'] == $data['returnZonecode'] && $data['addressSub'] == $data['returnAddressSub']) {
            $data['chkSameReturnAddr'] = 'y';
        } else if ($data['unstoringZonecode'] == $data['returnZonecode'] && $data['unstoringAddressSub'] == $data['returnAddressSub']) {
            $data['chkSameReturnAddr'] = 'x';
        } else {
            $data['chkSameReturnAddr'] = 'n';
        }

        //담당자 정보
        if ($data['staff']) {
            $data['staff'] =  json_decode(gd_htmlspecialchars_stripslashes($data['staff']));
        }

        if(count($data['staff']) == 0) {
            $data['staff'] = [''];
        }

        //은행 정보
        $data['bankList'] = gd_array_change_key_value(gd_code('04002'));

        $checked = [];
        $checked['chkSameReturnAddr'][$data['chkSameReturnAddr']] = $checked['chkSameUnstoringAddr'][$data['chkSameUnstoringAddr']] = $checked['businessFl'][$data['businessFl']] = $checked['useFl'][$data['useFl']] = 'checked="checked"';

        $getData['data'] = $data;
        $getData['checked'] = $checked;

        return $getData;
    }


    /**
     * getInfoAddGoods
     *
     * @param null $goodsNo
     * @param null $goodsField
     * @param null $arrBind
     * @param bool|false $dataArray
     * @return string
     */
    public function getInfoPurchase($sno = null, $goodsField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($sno) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " p.purchaseNo = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " p.purchaseNo = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $sno);
        }
        if ($goodsField) {
            if ($this->db->strField) {
                $this->db->strField = $goodsField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $goodsField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_PURCHASE . ' p ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * setDelStatePurchas
     *
     * @param $purchaseNo
     */
    public function setDelStatePurchase($purchaseNo)
    {
        $strWhere = "purchaseNo IN ('" . implode("','", $purchaseNo) . "')";
        $this->db->set_update_db(DB_PURCHASE, array("delFl = 'y',delDt = '" . date('Y-m-d H:i:s') . "'"), $strWhere);
    }

    /**
     * getJsonListPurchase
     *
     * @param $arrData
     * @return string
     * @throws Except
     */
    public function getJsonListPurchase($scmNo)
    {

        $arrWhere[] = 'scmNo = ?';
        $this->db->bind_param_push($arrBind, 's', $scmNo);
        $strWhere = 'WHERE ' . implode(' AND ', $arrWhere);

        $strSQL = 'SELECT sno,PurchaseNm FROM ' . DB_PURCHASE . ' ' . $strWhere;
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) > 0) {
            return json_encode($getData);
        } else {
            return false;
        }

    }
}
