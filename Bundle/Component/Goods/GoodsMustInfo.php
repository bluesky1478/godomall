<?php
/**
 * 상품노출형태 관리
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


class GoodsMustInfo
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
     * saveInfoAddGoods
     *
     * @param $arrData
     * @return string
     * @throws Except
     */
    public function saveInfoMustInfo($arrData)
    {
        // 추가상품명 체크
        if (Validator::required(gd_isset($arrData['mustInfoNm'])) === false) {
            throw new \Exception(__('옵필수정보명은 필수 항목입니다.'), 500);
        }


        $arrData['info'] = json_encode($arrData['addMustInfo'], JSON_UNESCAPED_UNICODE);

        if($arrData['scmFl'] == 'a')  $arrData['scmNo'] = "0";

        // 테마명 정보 저장
        if ($arrData['mode'] == 'modify') {
            $arrBind = $this->db->get_binding(DBTableField::tableGoodsMustInfo(), $arrData, 'update');
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['sno']);
            $this->db->set_update_db(DB_GOODS_MUST_INFO, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        } else {
            $arrBind = $this->db->get_binding(DBTableField::tableGoodsMustInfo(), $arrData, 'insert');
            $this->db->set_insert_db(DB_GOODS_MUST_INFO, $arrBind['param'], $arrBind['bind'], 'y');
            $arrData['sno'] = $this->db->insert_id();
        }

        unset($arrBind);

        if ($arrData['mode'] == 'modify') {
            // 전체 로그를 저장합니다.
            LogHandler::wholeLog('goods_mustinfo', null, 'modify', $arrData['sno'], $arrData['mustInfoNm']);
        }

    }

    /**
     * saveInfoAddGoods
     *
     * @param $arrData
     * @return string
     * @throws Except
     */
    public function getAdminListMustInfo($arrData = null,$mode= null)
    {
        $getValue = Request::get()->toArray();

        // --- 검색 설정
        $this->setSearchMustInfo($getValue);

        // --- 정렬 설정
        $sort = gd_isset($getValue['sort']);
        if (empty($sort)) {
            $sort = 'g.regDt desc';
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

        if (Session::get('manager.isProvider')) $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GOODS_MUST_INFO . ' WHERE scmNo = \'' . Session::get('manager.scmNo') . '\'';
        else $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GOODS_MUST_INFO;
        $res = $this->db->query_fetch($strSQL,null,false);
        unset($arrBind);

        $page->recode['amount'] = $res['cnt']; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());


        // 현 페이지 결과
        $this->db->strJoin = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = g.scmNo ';
        $this->db->strField = "g.*,s.companyNm as scmNm";
        if(gd_isset($this->arrWhere)) $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        // 검색 카운트
        if (Session::get('manager.isProvider'))
            $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GOODS_MUST_INFO . ' as g ' . $this->db->strJoin;
        else
            $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GOODS_MUST_INFO .' as g ' . $this->db->strJoin;

        if($this->db->strWhere) {
            $strSQL .= ' WHERE ' . $this->db->strWhere;;
        }
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_MUST_INFO . ' as g ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }


    public function getAdminListMustInfoExcel($getValue)
    {
        // --- 검색 설정
        $this->setSearchMustInfo($getValue);

        if($getValue['sno'] && is_array($getValue['sno'])) {
            $this->arrWhere[] = 'sno IN (' . implode(',', $getValue['sno']) . ')';
        }

        // --- 정렬 설정
        $sort = 'g.regDt desc';

        // 현 페이지 결과
        $this->db->strJoin = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = g.scmNo ';
        $this->db->strField = "g.*,s.companyNm as scmNm";
        if(gd_isset($this->arrWhere)) $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_MUST_INFO . ' as g ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 각 데이터 배열화
        return gd_htmlspecialchars_stripslashes(gd_isset($data));
    }


    /**
     * setSearchMustInfo
     *
     * @param $searchData
     * @param int $searchPeriod
     */
    public function setSearchMustInfo($searchData)
    {
        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableGoodsMustInfo');


        //검색설정
        /* @formatter:off */
        $this->search['sortList'] = array(
            'regDt desc' => '등록일 ↓',
            'regDt asc' => '등록일 ↑',
            'mustInfoNm asc' => '필수 정보명 ↓',
            'mustInfoNm desc' => '필수 정보명 ↑',
            'companyNm asc' => '공급사 ↓',
            'companyNm desc' => '공급사 ↑'
        );
        /* @formatter:on */

        if($searchData['scmFl'] =='a') $searchData['scmNo'] = "0";

            // --- 검색 설정
        $this->search['sort'] = gd_isset($searchData['sort'], 'regDt desc');
        $this->search['detailSearch'] = gd_isset($searchData['detailSearch']);
        $this->search['searchDateFl'] = gd_isset($searchData['searchDateFl'], 'regDt');

        // 필수정보 선택 레이어 디폴트 all / 상품필수정보관리메뉴 디폴트 all
        $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod'], '-1');

        $this->search['mustInfoNm'] = gd_isset($searchData['mustInfoNm']);
        $this->search['scmFl'] = gd_isset($searchData['scmFl'], Session::get('manager.isProvider') ? 'n' : 'all');
        $this->search['scmNo'] = gd_isset($searchData['scmNo'], (string)Session::get('manager.scmNo'));
        $this->search['scmNoNm'] = gd_isset($searchData['scmNoNm']);


        if( $this->search['searchPeriod']  < 0) {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0]);
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1]);
        } else {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], date('Y-m-d', strtotime('-6 day')));
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], date('Y-m-d'));
        }

        $this->checked['scmFl'][$searchData['scmFl']] = "checked='checked'";
        $this->selected['searchDateFl'][$this->search['searchDateFl']] = $this->selected['sort'][$this->search['sort']] = "selected='selected'";

        $this->checked['searchPeriod'][$this->search['searchPeriod']] ="active";


        // 처리일자 검색
        if ($this->search['searchDateFl'] && $this->search['searchDate'][0] && $this->search['searchDate'][1]) {
            $this->arrWhere[] = 'g.' . $this->search['searchDateFl'] . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }

        // 필수상품 명 검색
        if ($this->search['mustInfoNm']) {
            $this->arrWhere[] = 'mustInfoNm LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($this->arrBind, $fieldType['mustInfoNm'], $this->search['mustInfoNm']);
        }


        if ($this->search['scmFl'] != 'all') {
            if (is_array($this->search['scmNo'])) {
                foreach ($this->search['scmNo'] as $val) {
                    $tmpWhere[] = 'g.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = 'g.scmNo = ?';
                $this->db->bind_param_push($this->arrBind, $fieldType['scmNo'], $this->search['scmNo']);
            }

        }


        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

    }


    /**
     * getDataMustInfo
     *
     * @param null $sno
     * @return mixed
     */
    public function getDataMustInfo($sno = null)
    {
        // --- 등록인 경우
        if (is_null($sno)) {
            // 기본 정보
            $data['mode'] = 'register';
            $data['sno'] = null;

            // 기본값 설정
            DBTableField::setDefaultData('tableGoodsMustInfo', $data);

            // --- 수정인 경우
        } else {
            // 기본 정보
            $data = $this->getInfoMustInfo($sno); // 사은품 기본 정보

            if (Session::get('manager.isProvider')) {
                if($data['scmNo'] !='0' && $data['scmNo'] != Session::get('manager.scmNo')) {
                    throw new AlertBackException(__("타 공급사의 자료는 열람하실 수 없습니다."));
                }
            }


            $data['mode'] = 'modify';

            if ($data['info']) {
                $data['addMustInfo'] = json_decode($data['info'], true);
            }

            // 기본값 설정
            DBTableField::setDefaultData('tableGoodsMustInfo', $data);
        }

        // --- 기본값 설정
        gd_isset($data['stockFl'], 'n');

        if ($data['scmNo'] == '0') {
            $data['scmFl'] = "a";
        } else if ($data['scmNo'] == DEFAULT_CODE_SCMNO) {
            $data['scmFl'] = "n";
        } else {
            $data['scmFl'] = "y";
        }

        $checked = [];
        $checked['scmFl'][$data['scmFl']] = 'checked="checked"';

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
    public function getInfoMustInfo($sno = null, $goodsField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($sno) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " g.sno = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " g.sno = ?";
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
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_MUST_INFO . ' g ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return $getData[0];
        }

        return $getData;
    }

    /**
     * deleteAddGoodsGroup
     *
     * @param $sno
     * @param $groupCd
     */
    public function deleteMustInfo($sno)
    {
        try {

            $strWhere = "sno IN ('" . implode("','", $sno) . "')";
            $this->db->set_delete_db(DB_GOODS_MUST_INFO, $strWhere);

        } catch (Except $e) {
            echo $e->ectMessage;
        }
    }

    /**
     * setCopyMustInfo
     *
     * @param $sno
     */
    public function setCopyMustInfo($sno)
    {

        try {

            //그룹 복사
            $mustInfoTableNm = DB_GOODS_MUST_INFO;

            $fieldData = DBTableField::setTableField('tableGoodsMustInfo', null);

            $strSQL = 'INSERT INTO ' . $mustInfoTableNm . ' (' . implode(', ', $fieldData) . $addField . ', regDt) SELECT ' . implode(', ', $fieldData) . $addData . ', now() FROM ' . $mustInfoTableNm . ' WHERE sno = ' . $sno;
            $this->db->query($strSQL);

            unset($this->arrBind);

            // 전체 로그를 저장합니다.
            LogHandler::wholeLog('goods_mustinfo', null, 'modify', $sno, '상품복사');

            //return $newGoodsNo;
        } catch (Except $e) {
            echo $e->ectMessage;
        }
    }



    /**
     * saveInfoAddGoods
     *
     * @param $arrData
     * @return string
     * @throws Except
     */
    public function getJsonListMustInfo($scmNo)
    {

        $arrWhere[] = 'scmNo = ?';
        $this->db->bind_param_push($arrBind, 's', $scmNo);
        $strWhere = 'WHERE ' . implode(' AND ', $arrWhere);

        $strSQL = 'SELECT sno,mustInfoNm FROM ' . DB_GOODS_MUST_INFO . ' ' . $strWhere;
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) > 0) {
            return json_encode($getData);
        } else {
            return false;
        }

    }
}
