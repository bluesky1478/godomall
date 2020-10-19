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

namespace Bundle\Component\Goods;

use Component\Storage\Storage;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use Exception;
use Globals;
use LogHandler;
use Request;
use UserFilePath;
use Session;
use Framework\Debug\Exception\AlertBackException;

/**
 * 추가 상품 관련 관리자 클래스
 * @author Jung Youngeun <atomyang@godo.co.kr>
 */
class AddGoodsAdmin extends \Component\Goods\AddGoods
{
    const TEXT_REQUIRE_VALUE = '%s은(는) 필수 항목 입니다.';

    private $addGoodsNo;

    public $etcIcon;

    // 이미지 저장 경로
    private $imagePath;

    // 이미지 저장소
    protected $storage;

    /**
     * 생성자
     *
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        parent::__construct();

        // 이미지 저장소
//        $this->storageHandler = new StorageHandler(StorageHandler::STORAGE_NAME_ADD_GOODS);

        if (gd_is_provider()) {
            $manager = \App::load('\\Component\\Member\\Manager');
            $managerInfo = $manager->getManagerInfo(\Session::get('manager.sno'));
            \Session::set("manager.scmPermissionInsert",$managerInfo['scmPermissionInsert']);
            \Session::set("manager.scmPermissionModify",$managerInfo['scmPermissionModify']);
            \Session::set("manager.scmPermissionDelete",$managerInfo['scmPermissionDelete']);
        }
    }

    /**
     * getDataAddGoods
     *
     * @param null $addGoodsNo
     * @return mixed
     */
    public function getDataAddGoods($addGoodsNo = null)
    {
        $checked = [];
        $getValue = Request::get()->toArray();

        //등록인 경우
        if (empty($addGoodsNo) === true) {
            $data['mode'] = 'register';

            $data['goodsNo'] = null;
            $data['scmNo'] = (string)Session::get('manager.scmNo');

            if (Session::get('manager.isProvider')) {
                $scm = \App::load('\\Component\\Scm\\ScmAdmin');
                $scmInfo = $scm->getScmInfo($data['scmNo'], 'companyNm,scmCommission');
                $data['scmNoNm'] = $scmInfo['companyNm'];
                $data['commission'] = $scmInfo['scmCommission'];
            }

            DBTableField::setDefaultData('tableAddGoods', $data);

            //글로벌설정
            if($this->gGlobal['isUse']) {
                foreach($this->gGlobal['useMallList'] as $k => $v) {
                    $checked['goodsNmGlobalFl'][$v['sno']] = "checked='checked'";
                }
            }

        // --- 수정인 경우
        } else {
            // 추가상품 정보
            $data = $this->getInfoAddGoods($addGoodsNo);

            if (Session::get('manager.isProvider')) {
                if($data['scmNo'] != Session::get('manager.scmNo')) {
                    throw new AlertBackException(__("타 공급사의 자료는 열람하실 수 없습니다."));
                }
            }

            $data['mode'] = 'modify';

            // 기본값 설정
            DBTableField::setDefaultData('tableAddGoods', $data);

            if($this->gGlobal['isUse']) {
                $tmpGlobalData = $this->getDataAddGoodsGlobal($data['addGoodsNo']);
                $globalData = array_combine(array_column($tmpGlobalData,'mallSno'),$tmpGlobalData);

                foreach($this->gGlobal['useMallList'] as $k => $v) {
                    if(!$globalData[$v['sno']]) {
                        $checked['goodsNmGlobalFl'][$v['sno']] = "checked='checked'";
                    }
                }

                $data['globalData'] = $globalData;
            }
        }

        if (gd_is_provider() === false && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && $data['purchaseNo']) {
            $purchase = \App::load('\\Component\\Goods\\Purchase');
            $purchaseInfo = $purchase->getInfoPurchase($data['purchaseNo'],'purchaseNm,delFl');
            if($purchaseInfo['delFl'] =='n') $data['purchaseNoNm'] = $purchaseInfo['purchaseNm'];
        }

        if ($data['scmNo'] == DEFAULT_CODE_SCMNO) {
            $data['scmFl'] = "n";
        } else {
            $data['scmFl'] = "y";
        }

        $getData['data'] = $data;
        $checked['taxFreeFl'][$data['taxFreeFl']] = $checked['stockUseFl'][$data['stockUseFl']] = $checked['scmFl'][$data['scmFl']] = "checked = 'checked'";

        $getData['checked'] = $checked;

        return $getData;
    }

    /**
     * getNewAddGoodsno
     *
     * @return mixed
     */
    private function getNewAddGoodsno()
    {
        $data = $this->getInfoAddGoods(null, 'if(max(addGoodsNo) > 0, (max(addGoodsNo) + 1), ' . DEFAULT_CODE_ADD_GOODSNO . ') as newAddGoodsNo');
        return $data['newAddGoodsNo'];
    }

    /**
     * doAddGoodsNoInsert
     *
     * @return mixed
     */
    private function doAddGoodsNoInsert()
    {
        $newAddGoodsNo = $this->getNewAddGoodsno();
        $this->db->set_insert_db(DB_ADD_GOODS, 'addGoodsNo', array('i', $newAddGoodsNo), 'y');

        return $newAddGoodsNo;
    }


    /**
     * saveInfoAddGoods
     *
     * @param $arrData
     * @return string
     * @throws Except
     */
    public function saveInfoAddGoods($arrData)
    {
        // 추가상품명 체크
        if (Validator::required(gd_isset($arrData['goodsNm'])) === false) {
            throw new \Exception(__('추가상품명은 필수 항목입니다.'), 500);
        }


        // addGoodsNo 처리
        if ($arrData['mode'] == 'register' || $arrData['mode'] == 'register_ajax') {
            $arrData['addGoodsNo'] = $this->doAddGoodsNoInsert();
        } else {
            // addGoodsNo 체크
            if (Validator::required(gd_isset($arrData['addGoodsNo'])) === false) {
                throw new \Exception(__('추가상품번호은 필수 항목입니다.'), 500);
            }
        }
        $this->goodsNo = $arrData['addGoodsNo'];

        if (empty($arrData['imagePath'])) {
            $this->imagePath = $arrData['imagePath'] = DIR_ADDGOODS_IMAGE . $arrData['addGoodsNo'] . '/';
        } else {
            $this->imagePath = $arrData['imagePath'];
        }


        if ($arrData['imgData'] && $arrData['imageStorage'] != 'url') {
            if (gd_file_uploadable($arrData['imgData'], 'image')) {


                $imageExt = strrchr( $arrData['imgData']['name'], '.');
                //$newImageName = str_replace(' ', '', trim(substr( $arrData['imgData']['name'], 0, -strlen($imageExt)))) .$imageExt; // 이미지 공백 제거 및 각 복사에 따른 종류를 화일명에 넣기

                $saveImageName =  $arrData['addGoodsNo'].'_'.rand(1,100) .  $imageExt; // 이미지 공백 제거 및 각 복사에 따른 종류를 화일명에 넣기

                $targetImageFile = $this->imagePath . $saveImageName;
                $tmpImageFile = $arrData['imgData']['tmp_name'];

//                $this->storageHandler->upload($tmpImageFile, $arrData['imageStorage'], $targetImageFile);
                Storage::disk(Storage::PATH_CODE_ADD_GOODS,$arrData['imageStorage'])->upload($tmpImageFile,$targetImageFile);

                $arrData['imageNm'] = $saveImageName;

                // 계정용량 갱신 - 추가상품
                gd_set_du('add_goods');
            }
        }

        // 매입처 삭제 체크시 매입처 초기화
        if ($arrData['purchaseNoDel'] == 'y') $arrData['purchaseNo'] = '';

        // 브랜드 삭제 체크시 브랜드 초기화
        if ($arrData['brandCdDel'] == 'y') $arrData['brandCd'] = '';

        if ($arrData['mode'] == 'modify') {
            $updateFl = 'n';
            $getAddGoods = $this->getInfoAddGoods($arrData['addGoodsNo']);

            DBTableField::setDefaultData('tableAddGoods', $arrData);

            $result = [];
            $expectField = array('modDt', 'regDt', 'applyDt', 'applyFl', 'applyMsg', 'applyType');

            // 기존 정보를 변경
            foreach ($getAddGoods as $key => $val) {
                if ($val != $arrData[$key] && !in_array($key, $expectField)) {
                    $result[$key] = $arrData[$key];
                }
            }

            if ($result) {
                $updateFl = "y";
                $this->setAddGoodsLog( $arrData['addGoodsNo'], $getAddGoods, $result);
            }
        }

        //공급사이면서 자동승인이 아닌경우 상품 승인신청 처리
        if (Session::get('manager.isProvider') && (($arrData['mode'] == 'modify' && Session::get('manager.scmPermissionModify') == 'c') || (($arrData['mode'] == 'register' || $arrData['mode'] == 'register_ajax') && Session::get('manager.scmPermissionInsert') == 'c'))) {
            if (($arrData['mode'] == 'modify' && $updateFl == 'y') || $arrData['mode'] == 'register' || $arrData['mode'] == 'register_ajax') {
                $arrData['applyFl'] = 'a';
                $arrData['applyDt'] = date('Y-m-d H:i:s');
            }


            $arrData['applyType'] = strtolower(substr($arrData['mode'], 0, 1));

        } else  {
            $arrData['applyFl'] = 'y';
        }

        // 상품 정보 저장
        if ($arrData['mode'] == 'modify') {
            // 운영자 기능권한 처리
            if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.addGoodsCommission') != 'y') {
                $arrExclude[] = 'commission';
            }
            if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.addGoodsNm') != 'y') {
                $arrExclude[] = 'goodsNm';
            }
            $arrBind = $this->db->get_binding(DBTableField::tableAddGoods(), $arrData, 'update', null, $arrExclude);
        } else {
            if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.addGoodsCommission') != 'y') {
                if ($arrData['scmNo'] != DEFAULT_CODE_SCMNO) {
                    $scm = \App::load('\\Component\\Scm\\ScmAdmin');
                    $scmInfo = $scm->getScmInfo($arrData['scmNo'], 'scmCommission');
                    $arrData['commission'] = $scmInfo['scmCommission'];
                }
            }
            $arrBind = $this->db->get_binding(DBTableField::tableAddGoods(), $arrData, 'update', null, $arrExclude);
        }
        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['addGoodsNo']);
        $this->db->set_update_db(DB_ADD_GOODS, $arrBind['param'], 'addGoodsNo = ?', $arrBind['bind']);

        unset($arrBind);

        if ($arrData['mode'] == 'modify') {
            // 전체 로그를 저장합니다.
            LogHandler::wholeLog('add_goods', null, 'modify', $arrData['addGoodsNo'], $arrData['goodsNm']);
        }

        return $arrData;

    }


    /**
     * saveInfoAddGoodsGlobal
     *
     * @param $arrData
     * @return string
     * @throws Except
     */
    public function saveInfoAddGoodsGlobal($arrData)
    {
        if($this->gGlobal['isUse']) {
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $arrData['addGoodsNo']);
            $this->db->set_delete_db(DB_ADD_GOODS_GLOBAL, 'addGoodsNo = ?', $arrBind);
            unset($arrBind);

            foreach($arrData['globalData'] as $k => $v) {
                if(array_filter(array_map('trim',$v))) {
                    $globalData = $v;
                    $globalData['mallSno'] = $k;
                    $globalData['addGoodsNo'] = $arrData['addGoodsNo'];

                    $arrBind = $this->db->get_binding(DBTableField::tableAddGoodsGlobal(), $globalData, 'insert');
                    $this->db->set_insert_db(DB_ADD_GOODS_GLOBAL, $arrBind['param'], $arrBind['bind'], 'y');
                    unset($arrBind);
                }
            }

        }
    }

    public function setAddGoodsLog($addGoodsNo, $prevData, $updateData)
    {

        $arrData['addGoodsNo'] = $addGoodsNo;
        $arrData['managerId'] = (string)Session::get('manager.managerId');
        $arrData['managerNo'] = Session::get('manager.sno');
        $arrData['prevData'] = json_encode($prevData, JSON_UNESCAPED_UNICODE);
        $arrData['updateData'] = json_encode($updateData, JSON_UNESCAPED_UNICODE);

        //공급사이면서 자동승인이 아닌경우 상품 승인신청 처리
        if (Session::get('manager.isProvider') && Session::get('manager.scmPermissionModify') == 'c') {
            $arrData['applyFl'] = 'a';
        } else  $arrData['applyFl'] = 'y';


        $arrBind = $this->db->get_binding(DBTableField::tableLogAddGoods(), $arrData, 'insert');
        $this->db->set_insert_db(DB_LOG_ADD_GOODS, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrData);

    }



    public function getAdminListAddGoodsLog($addGoodsNo)
    {
        $arrField = DBTableField::setTableField('tableLogAddGoods');

        $strWhere = 'addGoodsNo = ?';
        $this->db->bind_param_push($arrBind, 's', $addGoodsNo);

        $strSQL = 'SELECT sno,regDt, ' . implode(', ', $arrField) . ' FROM ' . DB_LOG_ADD_GOODS . ' WHERE ' . $strWhere . ' ORDER BY sno DESC';
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if ($getData) {
            foreach ($getData as $key => $val) {

                $goodsField = DBTableField::getFieldNames('tableAddGoods');

                $prevTmpStr = [];
                $updateTmpStr = [];
                $prevData = json_decode(gd_htmlspecialchars_stripslashes($val['prevData']), true);
                $getData[$key]['prevData'] = $prevData;

                $updateData = json_decode(gd_htmlspecialchars_stripslashes($val['updateData']), true);
                $getData[$key]['updateData'] = $updateData;


                foreach ($updateData as $k => $v) {
                    $prevTmpStr[] = $goodsField[$k] . " : " . $prevData[$k];
                    $updateTmpStr[] = $goodsField[$k] . " : " . $v;
                }

                $getData[$key]['prevDataSet'] = implode("<br/>", $prevTmpStr);
                $getData[$key]['updateDataSet'] = implode("<br/>", $updateTmpStr);


            }

        }

        return $getData;

    }

    /**
     * getAdminListAddGoods
     *
     * @return mixed
     */
    public function getAdminListAddGoods()
    {
        $getValue = Request::get()->toArray();

        // --- 검색 설정
        $this->setSearchAddGoods($getValue);

        // --- 정렬 설정
        $sort = gd_isset($getValue['sort']);
        if (empty($sort)) {
            $sort = 'ag.regDt desc';
        }

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);


        $page = \App::load('\\Component\\Page\\Page', $getValue['page'],0,0,$getValue['pageNum']);
        $page->setCache(true); // 페이지당 리스트 수
        if (Session::get('manager.isProvider')) {
            $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_ADD_GOODS . ' WHERE scmNo = \'' . Session::get('manager.scmNo') . '\'';
        } else {
            $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_ADD_GOODS;
        }

        if ($page->hasRecodeCache('amount') === false) {
            $res = $this->db->query_fetch($strSQL, null, false);
            $page->recode['amount'] = $res['cnt']; // 전체 레코드 수
        }

        // 현 페이지 결과
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as sm ON sm.scmNo = ag.scmNo ';
        $join[] = ' LEFT JOIN ' . DB_CATEGORY_BRAND . ' as cb ON cb.cateCd = ag.brandCd ';
        $this->db->strField = "ag.*, sm.companyNm as scmNm, cb.cateNm as brandNm";

        if (gd_is_provider() === false && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) {
            $join[] = ' LEFT JOIN ' . DB_PURCHASE . ' as p ON p.purchaseNo = ag.purchaseNo  AND p.delFl = "n"';
            $this->db->strField .= ",p.purchaseNm";
        }
        $this->db->strJoin = implode('', $join);

        if (gd_isset($this->arrWhere)){
            $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        }
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        // 검색 카운트
        if (Session::get('manager.isProvider')) {
            $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_ADD_GOODS . ' as ag ' . implode('', $join);
            if($this->db->strWhere) {
                $strSQL.=' WHERE ' . $this->db->strWhere;
            }
        } else {
            $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_ADD_GOODS . ' as ag ' . implode('', $join);
            if($this->db->strWhere) {
                $strSQL.=' WHERE ' . $this->db->strWhere;
            }
        }

        if ($page->hasRecodeCache('total') === false) {
            $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
            $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        }
        $page->setUrl(\Request::getQueryString());
        $page->setPage();

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADD_GOODS . ' as ag ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }


    /**
     * getAdminListAddGoods
     *
     * @return mixed
     */
    public function getAdminListAddGoodsExcel($getValue)
    {
        // --- 검색 설정
        $this->setSearchAddGoods($getValue);

        if($getValue['addGoodsNo'] && is_array($getValue['addGoodsNo'])) {
            $this->arrWhere[] = 'addGoodsNo IN (' . implode(',', $getValue['addGoodsNo']) . ')';
        }

        $sort = 'ag.regDt desc';

        // 현 페이지 결과
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as sm ON sm.scmNo = ag.scmNo ';
        $join[] = ' LEFT JOIN ' . DB_CATEGORY_BRAND . ' as cb ON cb.cateCd = ag.brandCd ';
        $this->db->strField = "ag.*, sm.companyNm as scmNm, cb.cateNm as brandNm";
        if(gd_is_provider() === false && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) {
            $join[] = ' LEFT JOIN ' . DB_PURCHASE . ' as p ON p.purchaseNo = ag.purchaseNo AND p.delFl = "n"';
            $this->db->strField .= ",  p.purchaseNm";
        }
        $this->db->strJoin = implode('', $join);

        if(gd_isset($this->arrWhere)) $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADD_GOODS . ' as ag ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 각 데이터 배열화
        return gd_htmlspecialchars_stripslashes(gd_isset($data));
    }


    /**
     * setSearchAddGoods
     *
     * @param $searchData
     * @param int $searchPeriod
     */
    public function setSearchAddGoods($searchData, $searchPeriod = '-1')
    {
        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableAddGoods');
        /* @formatter:off */
        $this->search['combineSearch'] =[
            'ag.goodsNm' => __('상품명'),
            'ag.addGoodsNo' => __('상품코드'),
            'ag.goodsCd' => __('자체상품코드'),
            'ag.makerNm' => __('제조사'),
            'ag.goodsModelNo' => __('모델번호'),
        ];
        /* @formatter:on */

        if(gd_is_provider() === false) {
            $this->search['combineSearch']['companyNm'] = __('공급사명');
            if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true ) $this->search['combineSearch']['purchaseNm'] = __('매입처명');
        }

        /* @formatter:off */
        $this->search['sortList'] = [
            'ag.regDt desc' => __('등록일 ↓'),
            'ag.regDt asc' => __('등록일 ↑'),
            'ag.goodsNm asc' => __('상품명 ↓'),
            'ag.goodsNm desc' => __('상품명 ↑'),
            'ag.goodsPrice asc' => __('판매가 ↓'),
            'ag.goodsPrice desc' => __('판매가 ↑'),
            'sm.companyNm asc' => __('공급사 ↓'),
            'sm.companyNm desc' => __('공급사 ↑'),
            'ag.makerNm asc' => __('제조사 ↓'),
            'ag.makerNm desc' => __('제조사 ↑')
        ];
        /* @formatter:on */

        // --- 검색 설정
        $this->search['sort'] = gd_isset($searchData['sort'], 'ag.regDt desc');
        $this->search['key'] = gd_isset($searchData['key']);
        $this->search['keyword'] = gd_isset($searchData['keyword']);
        $this->search['detailSearch'] = gd_isset($searchData['detailSearch']);
        $this->search['searchDateFl'] = gd_isset($searchData['searchDateFl'], 'ag.regDt');
        $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod'],'-1');
        $this->search['scmFl'] = gd_isset($searchData['scmFl'], Session::get('manager.isProvider')? 'n' : 'all');
        $this->search['scmNo'] = gd_isset($searchData['scmNo'], (string)Session::get('manager.scmNo'));
        $this->search['scmNoNm'] = gd_isset($searchData['scmNoNm']);
        $this->search['purchaseNo'] = gd_isset($searchData['purchaseNo']);
        $this->search['purchaseNoNm'] = gd_isset($searchData['purchaseNoNm']);
        $this->search['makerNm'] = gd_isset($searchData['makerNm']);
        $this->search['stockUseFl'] = gd_isset($searchData['stockUseFl'], 'all');
        $this->search['goodsPrice'][] = gd_isset($searchData['goodsPrice'][0]);
        $this->search['goodsPrice'][] = gd_isset($searchData['goodsPrice'][1]);
        $this->search['brandCd'] = gd_isset($searchData['brandCd']);
        $this->search['brandCdNm'] = gd_isset($searchData['brandCdNm']);
        $this->search['viewFl'] = gd_isset($searchData['viewFl'],'all');
        $this->search['soldOutFl'] = gd_isset($searchData['soldOutFl'],'all');
        $this->search['applyType'] = gd_isset($searchData['applyType'], 'all');
        $this->search['applyFl'] = gd_isset($searchData['applyFl'], 'all');
        $this->search['brandNoneFl'] = gd_isset($searchData['brandNoneFl']);
        $this->search['purchaseNoneFl'] = gd_isset($searchData['purchaseNoneFl']);


        if( $this->search['searchPeriod']  < 0) {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0]);
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1]);
        } else {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], date('Y-m-d', strtotime('-6 day')));
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], date('Y-m-d'));
        }
        $this->checked['searchPeriod'][$this->search['searchPeriod']] ="active";
        $this->checked['purchaseNoneFl'][$this->search['purchaseNoneFl']]= $this->checked['brandNoneFl'][$this->search['brandNoneFl']]= $this->checked['applyType'][$this->search['applyType']] = $this->checked['applyFl'][$this->search['applyFl']] = $this->checked['viewFl'][$searchData['viewFl']] = $this->checked['soldOutFl'][$searchData['soldOutFl']] = $this->checked['scmFl'][$searchData['scmFl']] = $this->checked['stockUseFl'][$searchData['stockUseFl']] = "checked='checked'";
        $this->selected['searchDateFl'][$this->search['searchDateFl']] = $this->selected['sort'][$this->search['sort']] = "selected='selected'";

        // 처리일자 검색
        if ($this->search['searchDateFl'] && $this->search['searchDate'][0] && $this->search['searchDate'][1]) {
            $this->arrWhere[] =$this->search['searchDateFl'] . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }

        // 테마명 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = array('ag.goodsNm', 'ag.addGoodsNo', 'ag.goodsCd', 'ag.makerNm');
                $arrWhereAll = array();
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }

                if(gd_is_provider() === false && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) {
                    /* 매입처명 검색 추가 */
                    $arrWhereAll[] = 'p.purchaseNm LIKE concat(\'%\',?,\'%\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }

                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            } else {
                if ($this->search['key'] == 'companyNm') {
                    $this->arrWhere[] = 'sm.' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                } else {
                    $this->arrWhere[] = '' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
            }
        }

        if ($this->search['scmFl'] != 'all') {
            if (is_array($this->search['scmNo'])) {
                foreach ($this->search['scmNo'] as $val) {
                    $tmpWhere[] = 'ag.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = 'ag.scmNo = ?';
                $this->db->bind_param_push($this->arrBind, $fieldType['scmNo'], $this->search['scmNo']);

                $this->search['scmNo'] = array($this->search['scmNo']);
                $this->search['scmNoNm'] = array($this->search['scmNoNm']);
            }

        }


        if (($this->search['brandCd'] && $this->search['brandCdNm'])) {
            if (!$this->search['brandCd'] && $this->search['brand'])
                $this->search['brandCd'] = $this->search['brand'];
            $this->arrWhere[] = 'ag.brandCd = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['brandCd'], $this->search['brandCd']);
        } else $this->search['brandCd'] = '';


        //브랜드 미지정
        if ($this->search['brandNoneFl']) {
            $this->arrWhere[] = 'ag.brandCd  = ""';
        }

        // 매입처 검색
        if (($this->search['purchaseNo'] && $this->search['purchaseNoNm'])) {
            if (is_array($this->search['purchaseNo'])) {
                foreach ($this->search['purchaseNo'] as $val) {
                    $tmpWhere[] = 'ag.purchaseNo = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            }
        }

        //매입처 미지정
        if ($this->search['purchaseNoneFl']) {
            $this->arrWhere[] = '(ag.purchaseNo IS NULL OR ag.purchaseNo  = "")';
        }

        if ($this->search['makerNm']) {
            $this->arrWhere[] = 'ag.makerNm = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['makerNm'], $this->search['makerNm']);
        }

        if ($searchData['goodsPrice'][0] && $searchData['goodsPrice'][1]) {
            $this->arrWhere[] = '(ag.goodsPrice >= ? and ag.goodsPrice <= ?)';
            $this->db->bind_param_push($this->arrBind, $fieldType['goodsPrice'], $this->search['goodsPrice'][0]);
            $this->db->bind_param_push($this->arrBind, $fieldType['goodsPrice'], $this->search['goodsPrice'][1]);
        }


        if ($this->search['stockUseFl'] != 'all') {
            switch ($this->search['stockUseFl']) {
                case 'n': {
                    $this->arrWhere[] = 'ag.stockUseFl = ?';
                    $this->db->bind_param_push($this->arrBind, $fieldType['stockUseFl'], '0');
                    break;
                }
                case 'u' : {
                    $this->arrWhere[] = '(ag.stockUseFl = ? and ag.stockCnt > 0)';
                    $this->db->bind_param_push($this->arrBind, $fieldType['stockUseFl'], '1');
                    break;
                }
                case 'z' : {
                    $this->arrWhere[] = '(ag.stockUseFl = ? and ag.stockCnt = 0)';
                    $this->db->bind_param_push($this->arrBind, $fieldType['stockUseFl'], '1');
                    break;
                }

            }
        }

        //노출여부
        if ($this->search['viewFl'] != 'all') {
            $this->arrWhere[] = 'ag.viewFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['viewFl'], $this->search['viewFl']);
        }

        //품절여부
        if ($this->search['soldOutFl'] != 'all') {
            $this->arrWhere[] = 'ag.soldOutFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['soldOutFl'], $this->search['soldOutFl']);
        }


        //승인구분
        if ($this->search['applyType'] != 'all') {
            $this->arrWhere[] = 'ag.applyType = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['applyType'], $this->search['applyType']);
        }

        //승인상태
        if ($this->search['applyFl'] != 'all') {
            $this->arrWhere[] = 'ag.applyFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['applyFl'], $this->search['applyFl']);
        }


        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

    }

    /**
     * deleteAddGoods
     *
     * @param $addGoodsNo
     */
    public function deleteAddGoods($addGoodsNo)
    {
        if (Session::get('manager.isProvider') && Session::get('manager.scmPermissionDelete') == 'c') {

            $arrBind = [];
            $arrUpdate[] = "applyType = 'd'";
            $arrUpdate[] = "applyFl = 'a'";
            $this->db->bind_param_push($arrBind, 's', $addGoodsNo);
            $this->db->set_update_db(DB_ADD_GOODS, $arrUpdate, 'addGoodsNo = ?', $arrBind);

            return "a";

        } else {
            $arrBind = [];
            // 이미지 저장소 및 이미지 경로 정보
            $strWhere = 'addGoodsNo = ?';
            $this->db->bind_param_push($this->arrBind, 'i', $addGoodsNo);
            $this->db->strWhere = $strWhere;
            $data = $this->getInfoAddGoods(null, 'goodsNm, imageStorage, imagePath', $this->arrBind);


            $this->db->bind_param_push($arrBind['bind'], 's', $addGoodsNo);
            $this->db->set_delete_db(DB_ADD_GOODS, 'addGoodsNo = ?', $arrBind['bind']);

            unset($this->arrBind);
            unset($arrBind);


            // --- 이미지 삭제 처리
            if ($data['imageStorage'] != 'url' && $data['imagePath']) {
                //            $this->storageHandler->delete($data['imageStorage'], $data['imagePath']);
                Storage::disk(Storage::PATH_CODE_ADD_GOODS,$data['imageStorage'])->deleteDir($data['imagePath']);

                // 계정용량 갱신 - 추가상품
                gd_set_du('add_goods');
            }
            return "y";
        }

    }

    /**
     * 상품철회
     *
     * @param $goodsNo
     */
    public function setApplyWithdrawAddGoods($addGoodsNo)
    {
        $strWhere = "addGoodsNo IN ('" . implode("','", $addGoodsNo) . "')";
        $this->db->set_update_db(DB_ADD_GOODS, array("applyFl = 'n'"), $strWhere);

        $strWhere = "addGoodsNo IN ('" . implode("','", $addGoodsNo) . "') AND applyFl = 'a'";
        $this->db->set_update_db(DB_LOG_ADD_GOODS, array("applyFl = 'n'"), $strWhere);
    }

    /**
     * 상품승인
     *
     * @param $goodsNo
     */
    public function setApplyAddGoods($addGoodsNo, $mode = null)
    {

        $arrBind = [];
        $arrUpdate[] = "applyFl = 'y'";
        $this->db->bind_param_push($arrBind, 's', $addGoodsNo);
        $this->db->set_update_db(DB_ADD_GOODS, $arrUpdate, 'addGoodsNo = ?', $arrBind);
        unset($arrBind);
        unset($arrUpdate);

        if ($mode == 'd') {
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $addGoodsNo);
            $this->db->set_delete_db(DB_ADD_GOODS, 'addGoodsNo = ?', $arrBind);
            unset($arrBind);
        } else {
            $arrBind = [];
            $arrUpdate[] = "applyFl = 'y'";
            $this->db->bind_param_push($arrBind, 's', $addGoodsNo);
            $this->db->bind_param_push($arrBind, 's', 'a');
            $this->db->set_update_db(DB_LOG_ADD_GOODS, $arrUpdate, 'addGoodsNo = ? and applyFl = ?', $arrBind);
            unset($arrBind);
            unset($arrUpdate);
        }

    }


    /**
     * 상품반려
     *
     * @param $goodsNo
     */
    public function setApplyRejectAddGoods($addGoodsNo, $applyMsg)
    {

        $strWhere = "addGoodsNo IN ('" . implode("','", $addGoodsNo) . "')";
        $this->db->set_update_db(DB_ADD_GOODS, array("applyFl = 'r' ,applyMsg = '" . $applyMsg . "'"), $strWhere);

        $strWhere = "addGoodsNo IN ('" . implode("','", $addGoodsNo) . "') AND applyFl = 'a'";
        $this->db->set_update_db(DB_LOG_ADD_GOODS, array("applyFl = 'r'"), $strWhere);
    }


    /**
     * setCopyGoods
     *
     * @param $addGoodsNo
     * @return string
     */
    public function setCopyGoods($addGoodsNo)
    {
        // 새로운 상품 번호
        $newAddGoodsNo = $this->getNewAddGoodsno();

        // 이미지 저장소 및 이미지 경로 정보
        $strWhere = 'addGoodsNo = ?';
        $this->db->bind_param_push($this->arrBind, 'i', $addGoodsNo);
        $this->db->strWhere = $strWhere;
        $data = $this->getInfoAddGoods(null, 'goodsNm, imageStorage, imagePath', $this->arrBind);


        $newImagePath = DIR_ADDGOODS_IMAGE . $newAddGoodsNo . '/';

        $goodsTableNm = DB_ADD_GOODS;

        $fieldData = DBTableField::setTableField('tableAddGoods', null, array('addGoodsNo', 'imagePath','applyFl'));
        $addField = ',imagePath';
        $addData = ',\'' . $newImagePath . '\'';

        if (Session::get('manager.isProvider')  &&  (Session::get('manager.scmPermissionInsert') == 'c')) {
            $applyFl = "a";
        } else  {
            $applyFl = "y";
        }

        // --- 이미지 복사 처리
        if ($data['imageStorage'] != 'url') {
            //$this->storageHandler->copy($data['imageStorage'], $data['imagePath'], $newImagePath);
            Storage::copy(Storage::PATH_CODE_ADD_GOODS,$data['imageStorage'],$data['imagePath'],$data['imageStorage'],$newImagePath);

            // 계정용량 갱신 - 추가상품
            gd_set_du('add_goods');
        }

        $strSQL = 'INSERT INTO ' . $goodsTableNm . ' (addGoodsNo, ' . implode(', ', $fieldData) . $addField . ', applyFl,regDt) SELECT \'' . $newAddGoodsNo . '\', ' . implode(', ', $fieldData) . $addData . ',"'.$applyFl.'", now() FROM ' . $goodsTableNm . ' WHERE addGoodsNo = ' . $addGoodsNo;
        $this->db->query($strSQL);

        unset($this->arrBind);

        // 전체 로그를 저장합니다.
        $addLogData = $addGoodsNo . ' -> ' . $newAddGoodsNo . ' 상품 복사' . chr(10);
        LogHandler::wholeLog('add_goods', null, 'copy', $newAddGoodsNo, $data['goodsNm'], $addLogData);

        return $applyFl;

    //return $newAddGoodsNo;

    }


    /**
     * getDataAddGoodsGroup
     *
     * @param null $sno
     * @return mixed
     * @throws AlertBackException
     */
    public function getDataAddGoodsGroup($sno = null)
    {
        // --- 등록인 경우
        if (!$sno) {
            // 기본 정보
            $data['mode'] = 'register';
            // 기본값 설정
            DBTableField::setDefaultData('tableAddGoodsGroup', $data);
            $addGoodsList = array();

            // --- 수정인 경우
        } else {
            // 추가상품 정보
            $data = $this->getInfoAddGoodsGroup($sno);
            if (Session::get('manager.isProvider')) {
                if($data['scmNo'] != Session::get('manager.scmNo')) {
                    throw new AlertBackException(__("타 공급사의 자료는 열람하실 수 없습니다."));
                }
            }

            $addGoodsList = $this->getInfoAddGoodsGroupGoods($data['groupCd']);

            $data['mode'] = 'modify';

            // 기본값 설정
            DBTableField::setDefaultData('tableAddGoodsGroup', $data);
        }

        $checked = array();

        if ($data['scmNo'] == DEFAULT_CODE_SCMNO) $data['scmFl'] = "n";
        else  $data['scmFl'] = "y";

        $getData['data'] = $data;
        $getData['addGoodsList'] = $addGoodsList;
        $checked['scmFl'][$data['scmFl']] = "checked = 'checked'";

        $getData['checked'] = $checked;

        return $getData;
    }

    /**
     * setAddGoodsGroupList
     *
     * @param $arrData
     * @return string
     */
    public function setAddGoodsGroupList($arrData)
    {
        $listHtml = "";
        $listHtml .= '<tr id="tbl_add_goods_' . $arrData['addGoodsNo'] . '" class="add_goods_free">';
        $listHtml .= '<td class="center"><input type="checkbox" name="addGoodsNo[]" id="layer_goods_' . $arrData['addGoodsNo'] . '"  value="' . $arrData['addGoodsNo'] . '"/></td>';
        $listHtml .= '<td class="center number" id="addGoodsNumber_' . $arrData['addGoodsNo'] . '"></td>';
        $listHtml .= '<td>' . __('이미지') . '</td>';
        $listHtml .= '<td>' . $arrData['goodsNm'] . '<input type="hidden" name="addGoodsNoData[]" value="' . $arrData['addGoodsNo'] . '" /></td>';
        $listHtml .= '<td>' . $arrData['optionNm'] . '</td>';
        $listHtml .= '<td>' . $arrData['goodsPrice'] . '</td>';
        $listHtml .= '<td>' . $arrData['scmNoNm'] . '</td>';
        $listHtml .= '<td class="cla_view display-none">' . $arrData['brandCdNm'] . '</td>';
        $listHtml .= '<td class="cla_view display-none">' . $arrData['makerNm'] . '</td>';
        $listHtml .= '<td>' . $arrData['stockCnt'] . '</td>';
        ($arrData['stockCnt'] == "0") ? $stockMsg = __('품절') : $stockMsg = "";
        $listHtml .= '<td>' . $stockMsg . '</td></tr>';

        return $listHtml;
    }

    /**
     * newGroupCode
     *
     * @return string
     */
    private function newGroupCode()
    {
        $strSQL = 'SELECT MAX(substring(groupCd,2)) FROM ' . DB_ADD_GOODS_GROUP;
        list($tmp) = $this->db->fetch($strSQL, 'row');
        return sprintf('%07d', ($tmp + 1));
    }


    /**
     * saveInfoAddGoodsGroup
     *
     * @param $arrData
     * @throws Except
     */
    public function saveInfoAddGoodsGroup($arrData)
    {
        // 추가상품명 체크
        if (Validator::required(gd_isset($arrData['groupNm'])) === false) {
            throw new \Exception(__('추가상품 그룹명은 필수 항목입니다.'), 500);
        }


        $arrData['addGoodsCnt'] = count($arrData['addGoodsNoData']);

        // 테마명 정보 저장
        if ($arrData['mode'] == 'group_modify') {

            if (Validator::required(gd_isset($arrData['sno'])) === false) {
                throw new Except('REQUIRE_VALUE', sprintf(__('%s은(는) 필수 항목 입니다.'), '추가상품 그룹번호'));
            }

            $arrBind = $this->db->get_binding(DBTableField::tableAddGoodsGroup(), $arrData, 'update');
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['sno']);
            $this->db->set_update_db(DB_ADD_GOODS_GROUP, $arrBind['param'], 'sno = ?', $arrBind['bind']);


        } else {
            $arrData['groupCd'] = $this->newGroupCode();
            $arrBind = $this->db->get_binding(DBTableField::tableAddGoodsGroup(), $arrData, 'insert');
            $this->db->set_insert_db(DB_ADD_GOODS_GROUP, $arrBind['param'], $arrBind['bind'], 'y');
            $arrData['sno'] = $this->db->insert_id();
        }

        if ($arrData['addGoodsNoData']) {

            //관련 상품 새로 지우고 새로 등록
            $this->db->set_delete_db(DB_ADD_GOODS_GROUP_GOODS, 'groupCd = "' . $arrData['groupCd'] . '"');

            foreach ($arrData['addGoodsNoData'] as $k => $v) {
                $groupDatap['groupCd'] = $arrData['groupCd'];
                $groupDatap['addGoodsNo'] = $v;
                $groupDatap['sort'] = $k + 1;
                $arrBind = $this->db->get_binding(DBTableField::tableAddGoodsGroupGoods(), $groupDatap, 'insert');
                $this->db->set_insert_db(DB_ADD_GOODS_GROUP_GOODS, $arrBind['param'], $arrBind['bind'], 'y');
            }
        }


        unset($arrBind);

        if ($arrData['mode'] == 'modify') {
            // 전체 로그를 저장합니다.
            LogHandler::wholeLog('add_goods', null, 'modify', $arrData['addGoodsNo'], $arrData['goodsNm']);
        }

    }

    /**
     * getAdminListAddGoodsGroup
     *
     * @return mixed
     */
    public function getAdminListAddGoodsGroup()
    {
        $getValue = Request::get()->toArray();

        // --- 검색 설정
        $this->setSearchAddGoodsGroupConfig($getValue);

        // --- 정렬 설정
        $sort = gd_isset($getValue['sort']);
        if (empty($sort)) {
            $sort = 'regDt desc';
        }

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수

        if(Session::get('manager.isProvider')) $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_ADD_GOODS_GROUP . ' WHERE scmNo = \''.Session::get('manager.scmNo').'\'';
        else $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_ADD_GOODS_GROUP ;
        $res = $this->db->query_fetch($strSQL, null, false);
        $page->recode['amount'] = $res['cnt']; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());


        // 현 페이지 결과
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = g.scmNo ';
        $this->db->strJoin = implode('', $join);
        $this->db->strField = "g.*,s.companyNm as scmNm";
        if(gd_isset($this->arrWhere))   $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        // 검색 카운트
        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_ADD_GOODS_GROUP .' as g ' . implode('', $join);

        if($this->db->strWhere){
            $strSQL .= ' WHERE ' . $this->db->strWhere;
        }
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADD_GOODS_GROUP . ' as g ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }


    /**
     * setSearchAddGoodsGroupConfig
     *
     * @param $searchData
     * @param int $searchPeriod
     */
    public function setSearchAddGoodsGroupConfig($searchData, $searchPeriod ='-1')
    {
        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableAddGoodsGroup');

        $this->search['combineSearch'] = array('all' => __('=통합검색='), 'groupNm' => __('그룹명'), 'groupCd' => __('그룹코드'), 'companyNm' => __('공급사명'));

        /* @formatter:off */
        $this->search['sortList'] = [
            'regDt desc' => __('등록일 ↓'),
            'regDt asc' => __('등록일 ↑'),
            'groupNm asc' => __('그룹명 ↓'),
            'groupNm desc' => __('그룹명 ↑'),
            'companyNm asc' => __('공급사명 ↓'),
            'companyNm desc' => __('공급사명 ↑')
        ];
        /* @formatter:on*/

        $getValue = Request::get()->toArray();


        // --- 검색 설정
        $this->search['sort'] = gd_isset($searchData['sort'], 'regDt desc');
        $this->search['key'] = gd_isset($getValue['key']);
        $this->search['keyword'] = gd_isset($getValue['keyword']);
        $this->search['searchDateFl'] = gd_isset($searchData['searchDateFl'], 'regDt');
        $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod'],'-1');
        $this->search['scmFl'] = gd_isset($searchData['scmFl'], Session::get('manager.isProvider')? 'n' : 'all');
        $this->search['scmNo'] = gd_isset($searchData['scmNo'], (string)Session::get('manager.scmNo'));
        $this->search['scmNoNm'] = gd_isset($getValue['scmNoNm']);

        if( $this->search['searchPeriod']  < 0) {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0]);
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1]);
        } else {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], date('Y-m-d', strtotime('-6 day')));
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], date('Y-m-d'));
        }

        $this->checked['scmFl'][ $this->search['scmFl']] = "checked='checked'";
        $this->checked['searchPeriod'][$this->search['searchPeriod']] ="active";
        $this->selected['searchDateFl'][$this->search['searchDateFl']] = $this->selected['sort'][$this->search['sort']] = "selected='selected'";


        // 처리일자 검색
        if ($this->search['searchDateFl'] && $this->search['searchDate'][0] && $this->search['searchDate'][1]) {
            $this->arrWhere[] = 'g.' . $this->search['searchDateFl'] . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }

        // 테마명 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = array('g.groupNm', 'g.groupCd', 's.companyNm');
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

                $this->search['scmNo'] = array($this->search['scmNo']);
                $this->search['scmNoNm'] = array($this->search['scmNoNm']);
            }

        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

    }

    /**
     * deleteAddGoodsGroup
     *
     * @param $sno
     * @param $groupCd
     */
    public function deleteAddGoodsGroup($sno, $groupCd)
    {

        $this->db->bind_param_push($arrBind['bind'], 's', $sno);
        $this->db->set_delete_db(DB_ADD_GOODS_GROUP, 'sno = ?', $arrBind['bind']);

        unset($this->arrBind);

        $this->db->set_delete_db(DB_ADD_GOODS_GROUP_GOODS, 'groupCd = "' . $groupCd . '"');

    }

    /**
     * setCopyGoodsGroup
     *
     * @param $sno
     * @param $groupCd
     */
    public function setCopyGoodsGroup($sno, $groupCd)
    {
        // 새로운 상품 번호

        $newgroupCd = $this->newGroupCode();

        //그룹 복사
        $groupTableNm = DB_ADD_GOODS_GROUP;

        $fieldData = DBTableField::setTableField('tableAddGoodsGroup', null, array('groupCd'));

        $addField = ',groupCd';
        $addData = ',\'' . $newgroupCd . '\'';


        $strSQL = 'INSERT INTO ' . $groupTableNm . ' (' . implode(', ', $fieldData) . $addField . ', regDt) SELECT ' . implode(', ', $fieldData) . $addData . ', now() FROM ' . $groupTableNm . ' WHERE sno = ' . $sno;
        $this->db->query($strSQL);


        //그룹 상품 복사

        $groupGoodsTableNm = DB_ADD_GOODS_GROUP_GOODS;

        $fieldData = DBTableField::setTableField('tableAddGoodsGroupGoods', null, array('groupCd'));

        $addField = ',groupCd';
        $addData = ',\'' . $newgroupCd . '\'';


        $strSQL = 'INSERT INTO ' . $groupGoodsTableNm . ' (' . implode(', ', $fieldData) . $addField . ', regDt) SELECT ' . implode(', ', $fieldData) . $addData . ', now() FROM ' . $groupGoodsTableNm . ' WHERE groupCd = ' . $groupCd;
        $this->db->query($strSQL);

        unset($this->arrBind);

        // 전체 로그를 저장합니다.
        $addLogData = $sno . ' -> ' . $newgroupCd . ' 상품 복사' . chr(10);
        LogHandler::wholeLog('add_goods', null, 'copy', $newgroupCd, $newgroupCd, $addLogData);

    }

    /**
     * saveInfoAddGoods
     *
     * @param $arrData
     * @return string
     * @throws Except
     */
    public function getJsonListAddGoodsGroup($scmNo)
    {

        $arrWhere[] = 'scmNo = ?';
        $this->db->bind_param_push($arrBind, 's', $scmNo);
        $strWhere = 'WHERE ' . implode(' AND ', $arrWhere);

        $strSQL = 'SELECT sno,groupNm FROM ' . DB_ADD_GOODS_GROUP . ' ' . $strWhere;
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) > 0) {
            return json_encode(gd_htmlspecialchars_stripslashes($getData));
        } else {
            return false;
        }
    }

}
