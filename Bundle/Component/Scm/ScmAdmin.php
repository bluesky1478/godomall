<?php
/**
 * ScmAdmin Class
 *
 * @author    su
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Scm;

use App;
use Component\Member\Manager;
use Component\Storage\Storage;
use Component\Category\Category;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use Framework\Utility\ArrayUtils;
use Request;
use Session;

class ScmAdmin extends \Component\Scm\Scm
{
    const ECT_INVALID_ARG = 'Config.ECT_INVALID_ARG';
    const TEXT_INVALID_NOTARRAY_ARG = '%s이 배열이 아닙니다.';
    const TEXT_INVALID_EMPTY_ARG = '%s이 비어있습니다.';
    const TEXT_REQUIRE_VALUE = '%s은(는) 필수 항목 입니다.';
    const TEXT_USELESS_VALUE = '%s은(는) 사용할 수 없습니다.';
    const OPENAPI_SUCCESS_VALIDATION    = '000';     // scm 정보 유효성체크 성공 및 이메일 정보 있음
    const OPENAPI_NOT_MATCH_SCM         = '100';     // 매칭되는 공급사 없음
    const OPENAPI_EMPTY_MANAGER_EMAIL   = '200';     // 공급사 대표 이메일 없음
    const OPENAPI_ERROR_ETC             = '300';     // 그 외 오류사항
    const OPENAPI_NOT_AUTH_EMAIL        = '400';     // 공급사 대표 이메일은 있지만 미인증 상태인 경우

    protected $storage;

    /**
     * 생성자
     *
     * @author su
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->storage = Storage::disk(Storage::PATH_CODE_SCM, 'local');
    }

    /**
     * 공급사 정보 가져오기
     *
     * @author su
     */
    public function getScm($scmNo)
    {
        $data = [];
        // 공급사 정보 + 공급사 슈퍼운영자 정보
        $this->db->strField = "sm.*, m.managerId as managerId, m.managerNickNm as managerNickNm, m.dispImage as dispImage";
        $join[] = 'LEFT JOIN ' . DB_MANAGER . ' as m ON m.scmNo=sm.scmNo and m.isSuper="y" ';
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = "sm.scmNo=?";
        $this->db->bind_param_push($arrBind, 'i', $scmNo);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_MANAGE . ' as sm ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        if ($data) {
            $getData = gd_htmlspecialchars_stripslashes($data);
        }
        unset($data);

        //추가 수수료 가져오기
        $scmCommission = App::load(\Component\Scm\ScmCommission::class);
        $addCommission = $scmCommission->getScmCommission($scmNo);
        //추가 수수료 일정 체크
        $addCommission = $scmCommission->compareWithScmCommissionSchedule($addCommission);
        //기본 수수료 일정 체크
        $defaultCommission[0]['sno'] = '0';
        $defaultCommission[0]['scmNo'] = $scmNo;
        $defaultCommission[0]['commissionType'] = 'sell';
        $defaultCommission[1]['sno'] = '0';
        $defaultCommission[1]['scmNo'] = $scmNo;
        $defaultCommission[1]['commissionType'] = 'delivery';
        $defaultCommission = $scmCommission->compareWithScmCommissionSchedule($defaultCommission);

        //판매수수료 동일적용 버튼 비활성화 (기본 배송비 수수료)
        if ($defaultCommission[1]['scmCommissionSameCheckBox']) {
            $getData['scmCommissionSameCheckBox'] = $defaultCommission[1]['scmCommissionSameCheckBox'];
        }
        //판매수수료 동일적용 버튼 비활성화 (추가 배송비 수수료)
        foreach ($addCommission as $key => $val) {
            if ($val['scmCommissionSameCheckBox']) {
                $getData['scmCommissionSameCheckBox'] = $val['scmCommissionSameCheckBox'];
            }
        }
        //기본 수수료 일정에 있을 경우 수정 금지
        if ($defaultCommission[0]['scmCommissionInput']) {
            $getData['scmCommissionInput'] = $defaultCommission[0]['scmCommissionInput'];
        }
        if ($defaultCommission[1]['scmCommissionDeliveryInput']) {
            $getData['scmCommissionDeliveryInput'] = $defaultCommission[1]['scmCommissionDeliveryInput'];
        }
        unset($defaultCommission);

        if ($addCommission) {
            $getData['addCommissionData'] = $addCommission;
        }
        unset($addCommission);

        return $getData;
    }

    /**
     * 공급사 상호명 중복확인
     *
     * @author su
     */
    public function getDuplicateScmCompanyNm($scmCompanyNm)
    {
        // 공급사 정보
        $this->db->strField = "sm.scmNo";
        $this->db->strWhere = "sm.companyNm=?";
        $this->db->bind_param_push($arrBind, 's', $scmCompanyNm);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_MANAGE . ' as sm ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        if (count($data) > 0) {
            unset($data);
            return true;
        } else {
            unset($data);
            return false;
        }
    }

    /**
     * 공급사 최고 관리자 아이디 정보
     *
     * @param  int scmNo 공급사 고유번호
     *
     * @return string managerId 공급사 아이디
     */
    public function getScmSuperManagerId($scmNo)
    {
        $arrBind = [];
        $query = "SELECT managerId FROM " . DB_MANAGER . " where scmNo = ? and isDelete = 'n' AND isSuper = 'y' ";
        $this->db->bind_param_push($arrBind, 'i', $scmNo);
        $getData = $this->db->query_fetch($query, $arrBind, false);
        unset($arrBind);

        return $getData['managerId'];
    }

    /**
     * 공급사 최고 관리자 정보
     *
     * @param  int scmNo 공급사 고유번호
     *
     * @return array 관리자 정보
     */
    public function getScmSuperManager($scmNo)
    {
        $arrBind = [];
        $query = "SELECT sno, managerId, isSuper, scmNo, permissionFl, permissionMenu, writeEnabledMenu, functionAuth, workPermissionFl, debugPermissionFl FROM " . DB_MANAGER . " where scmNo = ? and isDelete = 'n' AND isSuper = 'y' ";
        $this->db->bind_param_push($arrBind, 'i', $scmNo);
        $getData = $this->db->query_fetch($query, $arrBind, false);
        unset($arrBind);

        return $getData;
    }

    /**
     * getScmOrderAdjust
     *
     * @param int $scmNo
     *
     * @return array $scmOrderAdjustArr
     */
    public function getScmOrderAdjust($scmNo)
    {
        $order = App::load(\Component\Order\Order::class);
        $getData = $order->getEachOrderStatus(null, $scmNo, 0);

        $scmOrderAdjustArr = [];
        foreach ($getData as $key => $val) {
            if ($val['active'] == 'active' && $val['name']) {
                $scmOrderAdjustArr['order'][$key]['name'] = $val['name'];
                $scmOrderAdjustArr['order'][$key]['count'] = $val['count'];
            }
        }

        $scmAdjust = App::load(\Component\Scm\ScmAdjust::class);
        $getScmData = $scmAdjust->getScmAdjustStateCount($scmNo);

        $scmOrderAdjustArr['adjust'] = $getScmData;

        return $scmOrderAdjustArr;
    }

    /**
     * 공급사 리스트 가져오기
     *
     * @author su
     */
    public function getScmAdminList($mode = null)
    {
        $getValue = Request::get()->toArray();
        $this->setScmSearch($getValue);
        $sort['fieldName'] = gd_isset($getValue['sort']['name']);
        $sort['sortMode'] = gd_isset($getValue['sort']['mode']);
        if (empty($sort['fieldName']) || empty($sort['sortMode'])) {
            $sort['fieldName'] = 'sm.regDt';
            $sort['sortMode'] = 'desc';
        } else {
            $sort['fieldName'] = 'sm.' . $sort['fieldName'];
        }

        if ($mode == 'layer') {
            // --- 페이지 기본설정
            if (gd_isset($getValue['pagelink'])) {
                $getValue['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
                // --- 공급사만 출력하기 위해 사용
                $getValue['scmCommissionSet'] = strpos($getValue['pagelink'], 'scmCommissionSet=p');
            } else {
                $getValue['page'] = 1;
            }
            gd_isset($getValue['pageNum'], "10");
        } else {
            // --- 페이지 기본설정
            gd_isset($getValue['page'], 1);
            gd_isset($getValue['pageNum'], 10);
        }
        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수

        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_SCM_MANAGE .' WHERE delFl!="y"';

        $res = $this->db->query_fetch($strSQL, null, false);
        $page->recode['amount'] = $res['cnt']; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());


        $this->arrWhere[] = " sm.delFl != 'y' ";

        if (gd_isset($getValue['scmCommissionSet'])) {
            $this->arrWhere[] = " sm.scmKind = 'p' ";
        }

        $this->db->strField = "sm.*, m.managerId, m.managerNickNm, m.dispImage,m.isDelete";
        $join[] = 'LEFT JOIN ' . DB_MANAGER . ' as m ON m.scmNo=sm.scmNo and isDelete = "n" and m.isSuper="y" ';
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_MANAGE . ' as sm ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        Manager::displayListData($data);

        // 검색 레코드 수
        $table = DB_SCM_MANAGE . ' as sm';
        $page->recode['total'] = $this->db->query_count($query, $table, $this->arrBind);
        $page->setPage();

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        //추가 수수료 넣어주기
        $scmCommission = App::load(\Component\Scm\ScmCommission::class);
        foreach ($getData['data'] as $key => &$val) {
            $addCommissionData = $scmCommission->getScmCommission($val['scmNo']);
            $val['addCommissionData'] = $addCommissionData;
        }

        return $getData;
    }

    /**
     * 공급사 리스트 가져오기 엑셀
     *
     * @author su
     */
    public function getScmAdminListExcel($getValue)
    {
        $this->setScmSearch($getValue);

        if ($getValue['addGoodsNo'] && is_array($getValue['addGoodsNo'])) {
            $this->arrWhere[] = 'addGoodsNo IN (' . implode(',', $getValue['addGoodsNo']) . ')';
        }

        $sort = 'ag.regDt desc';

        $this->arrWhere[] = " sm.delFl != 'y' ";

        $this->db->strField = "sm.*, m.managerId, m.managerNickNm, m.dispImage";
        $join[] = 'LEFT JOIN ' . DB_MANAGER . ' as m ON m.scmNo=sm.scmNo and isDelete = "n" and m.isSuper="y" ';
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));


        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_MANAGE . ' as sm ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        //추가 수수료 넣어주기
        $scmCommission = App::load(\Component\Scm\ScmCommission::class);
        foreach ($data as $key => &$val) {
            //상품 수수료, 배송 수수료 값 바꾸기
            $val['scmCommission'] .= '(기본)';
            $val['scmCommissionDelivery'] .= '(기본)';

            $addCommissionData = $scmCommission->getScmCommission($val['scmNo']);
            foreach ($addCommissionData as $commissionKey => $commissionVal) {
                if ($commissionVal['commissionType'] == 'sell' && $commissionVal['commissionValue'] != 0.00) {
                    $val['scmCommission'] .= ' / '.$commissionVal['commissionValue'];
                }
                if ($commissionVal['commissionType'] == 'delivery' && $commissionVal['commissionValue'] != 0.00) {
                    $val['scmCommissionDelivery'] .= ' / '.$commissionVal['commissionValue'];
                }
            }

            //동일 적용 비교
            $commissionSameFl = $scmCommission->compareWithScmCommission($addCommissionData);
            if ($commissionSameFl && $val['scmCommission'] == val['scmCommissionDelivery']) {
                $val['scmCommissionDelivery'] = '판매수수료 동일 적용';
            }
        }
        $getData = gd_htmlspecialchars_stripslashes(gd_isset($data));

        return $getData;
    }

    /**
     * 공급사 등록
     *
     * @author su
     *
     * @param array &$req $_POST
     * @param array &$files $_FILES
     *
     * @return int scmNo
     */
    public function saveScm(&$arrData, &$files)
    {
        // 삭제 여부 ( 삭제 안함 )
        $arrData['delFl'] = 'n';

        // 출고지 주소
        if ($arrData['chkSameUnstoringAddr'] == 'y') {
            $arrData['unstoringZonecode'] = $arrData['zonecode'];
            $arrData['unstoringZipcode'] = $arrData['zipcode'];
            $arrData['unstoringAddress'] = $arrData['address'];
            $arrData['unstoringAddressSub'] = $arrData['addressSub'];
        } else {

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
        } else {

        }
        // 담당자 정보
        $staff = [];
        $staffNum = count($arrData['staffType']);
        for ($i = 0; $i < $staffNum; $i++) {
            $staff[$i]['staffType'] = $arrData['staffType'][$i];
            $staff[$i]['staffName'] = $arrData['staffName'][$i];
            $staff[$i]['staffTel'] = $arrData['staffTel'][$i];
            $staff[$i]['staffPhone'] = $arrData['staffPhone'][$i];
            $staff[$i]['staffEmail'] = $arrData['staffEmail'][$i];
        }
        $staff = gd_htmlspecialchars_addslashes($staff);
        $arrData['staff'] = json_encode($staff, JSON_UNESCAPED_UNICODE);

        if($arrData['isProvider'] == 'n') { // 본사에서 저장 시에만 계좌 정보 값 저장 ( 공급사 > 기본정보설정에서 저장시 제외 )
            $specialChar = ['<', '>', '\\', '"', '\'', '`']; // 특수문자 제거
            // 계좌 정보
            $account = [];
            $accountNum = count($arrData['accountType']);
            for ($i = 0; $i < $accountNum; $i++) {
                $account[$i]['accountType'] = $arrData['accountType'][$i];
                $account[$i]['accountNum'] = $arrData['accountNum'][$i];
                $account[$i]['accountName'] = str_replace($specialChar, '', $arrData['accountName'][$i]);
                $account[$i]['accountMemo'] = str_replace($specialChar, '', $arrData['accountMemo'][$i]);
            }
            $account = gd_htmlspecialchars_addslashes($account);
            $arrData['account'] = json_encode($account, JSON_UNESCAPED_UNICODE);
        }

        // 사업자 등록증 이미지
        if ($arrData['isBusinessImageDelete'] == 'y' && $arrData['oldBusinessLicenseImage']) {
            $this->storage->delete(basename($arrData['oldBusinessLicenseImage']));
            $arrData['businessLicenseImage'] = '';
        }
        if (ArrayUtils::isEmpty($files['businessLicenseImage']) === false) {
            $file = $files['businessLicenseImage'];
            if ($file['error'] == 0 && $file['size']) {
                $saveFileName = $arrData['businessNo'] . '_bl_' . substr(md5(microtime()), 0, 8);
                $arrData['businessLicenseImage'] = $this->storage->upload($file['tmp_name'], $saveFileName);
            }
        }
        // Validation
        $validator = new Validator();
        if (substr($arrData['mode'], 0, 6) == 'modify') {
            $validator->add('scmNo', 'number', true); // 공급사 고유번호
        } else {
            if ($this->getDuplicateScmCompanyNm($arrData['companyNm'])) {
                throw new \Exception(__('이미 존재하는 공급사명입니다.'));
            }

            $arrData['scmInsertAdminId'] = Session::get('manager.managerId');
            $arrData['managerNo'] = Session::get('manager.sno');
            // 기존 아이디 중복 확인 같으면 정상, 같이 않으면 오류
            if ($arrData['managerId'] != $arrData['managerDuplicateId']) {
                throw new \Exception(__('아이디 중복확인이 되지 않았습니다.'));
            }
            $validator->add('managerId', 'userid', true, null, true, false); // 공급사 아이디
            $validator->add('managerId', 'minlen', true, null, 4); // 아이디 최소길이
            $validator->add('managerId', 'maxlen', true, null, 50); // 아이디 최대길이
            $validator->add('managerDuplicateId', 'userid', true); // 공급사 아이디 중복확인 아이디
            $validator->add('managerPw', 'password', true); // 공급사 비밀번호
            $validator->add('managerPw', 'minlen', true, null, 10); // 비밀번호 최소길이
            $validator->add('managerPw', 'maxlen', true, null, 16); // 비밀번호 최대길이
            $validator->add('scmInsertAdminId', 'userid', true); // 공급사 등록하는 관리자 아이디
            $validator->add('managerNo', 'number', true); // 공급사 관리자 키
        }


        $scmCommission = \App::load(\Component\Scm\ScmCommission::class);
        //기본 판매 수수료 범위 체크
        if ( $arrData['scmCommission'] > 100 ||  $arrData['scmCommission'] < 0) {
            throw new \Exception(__('수수료는') . ' 0 ~ 100 % ' . __('입니다.'));
        }
        //저장된 추가 판매수수료
        if (gd_isset($arrData['scmCommissionInDB'])) {
            $scmCommission->checkScmCommissionValue($arrData['scmCommissionInDB']);
            $addCommissionArrData['scmCommissionInDB'] = $arrData['scmCommissionInDB'];
            unset($arrData['scmCommissionInDB']);
        }
        //추가된 추가 판매수수료
        if (gd_isset($arrData['scmCommissionNew'])) {
            $scmCommission->checkScmCommissionValue($arrData['scmCommissionNew']);
            $addCommissionArrData['scmCommissionNew'] = $arrData['scmCommissionNew'];
            unset($arrData['scmCommissionNew']);
        }
        //판매수수료 동일적용
        if ($arrData['scmSameCommission'] == 'Y') {
            $arrData['scmCommissionDelivery'] = $arrData['scmCommission'];
            $addCommissionArrData['scmSameCommission'] = $arrData['scmSameCommission'];
            unset($arrData['scmSameCommission']);
        } else {
            //기본 배송비 수수료 범위 체크
            if ( $arrData['scmCommissionDelivery'] > 100 ||  $arrData['scmCommissionDelivery'] < 0) {
                throw new \Exception(__('수수료는') . ' 0 ~ 100 % ' . __('입니다.'));
            }
            //저장된 추가 판매수수료
            if (gd_isset($arrData['scmCommissionDeliveryInDB'])) {
                $scmCommission->checkScmCommissionValue($arrData['scmCommissionDeliveryInDB']);
                $addCommissionArrData['scmCommissionDeliveryInDB'] = $arrData['scmCommissionDeliveryInDB'];
                unset($arrData['scmCommissionDeliveryInDB']);
            }
            //추가된 추가 판매수수료
            if (gd_isset($arrData['scmCommissionDeliveryNew'])) {
                $scmCommission->checkScmCommissionValue($arrData['scmCommissionDeliveryNew']);
                $addCommissionArrData['scmCommissionDeliveryNew'] = $arrData['scmCommissionDeliveryNew'];
                unset($arrData['scmCommissionDeliveryNew']);
            }
        }

        $validator->add('mode', 'alpha', true); // 모드
        $validator->add('companyNm', '', true); // 공급사명
        $validator->add('scmType', '', true); // 공급사상태-운영('y'), 일시정지('n'), 탈퇴('x')
        $validator->add('managerNickNm', '', ''); // 닉네임
        $validator->add('scmCommission', '', true); // 판매수수료-%로 소수점 2자리
        $validator->add('scmCommissionDelivery', '', true); // 배송비수수료-%로 소수점 2자리
        $validator->add('scmKind', '', true); // 공급사종류 - 공급사('p'),본사('c')
        $validator->add('scmCode', '', ''); // 공급사코드
        $validator->add('imageStorage', '', ''); // 이미지 저장소 위치
        $validator->add('scmPermissionInsert', '', true); // 상품등록권한-자동승인('a'),관리자승인('c')
        $validator->add('scmPermissionModify', '', true); // 상품수정권한-자동승인('a'),관리자승인('c')
        $validator->add('scmPermissionDelete', '', true); // 상품삭제권한-자동승인('a'),관리자승인('c')
        $validator->add('ceoNm', '', true); // 대표자
        $validator->add('businessNo', '', true); // 사업자 번호
        $validator->add('businessLicenseImage', '', ''); // 사업자 등록증 이미지
        $validator->add('service', '', true); // 업태
        $validator->add('item', '', true); // 종목
        $validator->add('phone', '', true); // 대표전화
        $validator->add('centerPhone', '', ''); // 고객센터
        $validator->add('zipcode', '', ''); // 구 우편번호
        $validator->add('zonecode', '', true); // 우편번호
        $validator->add('address', '', true); // 주소
        $validator->add('addressSub', '', true); // 상세주소
        $validator->add('unstoringZipcode', '', ''); // 구 우편번호
        $validator->add('unstoringZonecode', '', ''); // 우편번호
        $validator->add('unstoringAddress', '', ''); // 주소
        $validator->add('unstoringAddressSub', '', ''); // 상세주소
        $validator->add('returnZipcode', '', ''); // 구 우편번호
        $validator->add('returnZonecode', '', ''); // 우편번호
        $validator->add('returnAddress', '', ''); // 주소
        $validator->add('returnAddressSub', '', ''); // 상세주소
        if (substr($arrData['mode'], 0, 6) == 'modify' && $arrData['scmNo'] == DEFAULT_CODE_SCMNO) { // 본사 수정시 기능권한 저장 패스
            // empty statement
        } else if (!gd_is_provider()) { // 공급사 등록/수정시 기능권한 저장
            // 공급사 기능 권한 설정
            if (count($arrData['functionAuth']) > 0) {
                $functionAuth = [
                    'functionAuth' => $arrData['functionAuth'],
                ];
            } else {
                $functionAuth = null;
            }
            $arrData['functionAuth'] = json_encode($functionAuth, JSON_UNESCAPED_UNICODE); // 운영자 기능 권한 설정
            $validator->add('functionAuth', ''); // 공급사 기능권한
        }
        $validator->add('staff', '', ''); // 담당자 정보
        $validator->add('account', '', ''); // 계좌 정보
        $validator->add('delFl', 'yn', true); // 삭제여부
        if ($validator->act($arrData, true) === false) {
            throw new \Exception(implode("<br/>", $validator->errors));
        }
        //        $arrData = ArrayUtils::removeEmpty($arrData);
        switch (substr($arrData['mode'], 0, 6)) {
            case 'insert':
                try {
                    $this->db->begin_tran();

                    // 저장
                    $arrBind = $this->db->get_binding(DBTableField::tableScmManage(), $arrData, 'insert', array_keys($arrData), ['scmNo']);
                    $this->db->set_insert_db(DB_SCM_MANAGE, $arrBind['param'], $arrBind['bind'], 'y');

                    // 등록된 공급사고유번호
                    $scmNo = $this->db->insert_id();

                    //추가 판매수수료, 배송비수수료
                    if (gd_isset($addCommissionArrData)) {
                        $addCommissionArrData['scmNo'] = $scmNo;
                        $scmCommission->saveScmCommission($addCommissionArrData, 'insert');
                    }
                    //로그
                    $scmLog = $scmCommission->getScmLogData($scmNo);
                    $scmCommission->setScmLog('scm', 'insert', $scmNo, '', $scmLog);

                    // 공급사 관리자 등록 / es_manage 에 등록
                    $manager = \App::load(Manager::class);
                    $arrManager = [];
                    $arrManager['scmNo'] = $scmNo;
                    $arrManager['mode'] = 'register';
                    $arrManager['isSuper'] = 'y';
                    $arrManager['permissionFl'] = 's';
                    $arrManager['managerId'] = $arrData['managerId'];
                    $arrManager['managerNm'] = $arrData['ceoNm'];
                    $arrManager['managerPw'] = $arrData['managerPw'];
                    $arrManager['managerNickNm'] = $arrData['managerNickNm'];
                    // 대표운영자 상품재고 권한 부여
                    $arrManager['functionAuth']['goodsStockModify'] = 'y';
                    $arrManagerFiles = [];
                    $arrManagerFiles['dispImage'] = $files['scmImage'];
                    $manager->setIsRequireAuthentication(false);
                    $manager->saveManagerData($arrManager, $arrManagerFiles);

                    // 배송기본 정보 추가
                    $arrDelivery['scmNo'] = $scmNo;
                    $arrDelivery['unstoringZipcode'] = $arrData['unstoringZipcode'];
                    $arrDelivery['unstoringZonecode'] = $arrData['unstoringZonecode'];
                    $arrDelivery['unstoringAddress'] = $arrData['unstoringAddress'];
                    $arrDelivery['unstoringAddressSub'] = $arrData['unstoringAddressSub'];
                    $arrDelivery['returnZipcode'] = $arrData['returnZipcode'];
                    $arrDelivery['returnZonecode'] = $arrData['returnZonecode'];
                    $arrDelivery['returnAddress'] = $arrData['returnAddress'];
                    $arrDelivery['returnAddressSub'] = $arrData['returnAddressSub'];
                    $delivery = \App::load(\Component\Delivery\Delivery::class);
                    $delivery->saveDeliveryDefaultData($arrDelivery);

                    // 엑셀양식기본양식추가
                    $arrExcelForm['scmNo'] = $scmNo;
                    $excelForm = \App::load('\\Component\\Excel\\ExcelForm');
                    $excelForm->saveExcelFormDefaultData($arrExcelForm);

                    $this->db->commit();

                } catch (Exception $e) {
                    $this->db->rollback();
                    throw new \Exception(__('정산 요청 중 오류가 발생하였습니다. 다시 시도해 주세요.'));
                }
                break;
            case 'modify' :
                try {
                    $this->db->begin_tran();
                    //로그
                    $scmPrevData = $scmCommission->getScmLogData($arrData['scmNo']);

                    // 공급사 수정
                    $arrBind = $this->db->get_binding(DBTableField::tableScmManage(), $arrData, 'update', array_keys($arrData), ['scmNo']);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['scmNo']);
                    $this->db->set_update_db(DB_SCM_MANAGE, $arrBind['param'], 'scmNo = ?', $arrBind['bind'], false);

                    //추가 판매수수료, 배송비수수료
                    if (gd_isset($addCommissionArrData)) {
                        $addCommissionArrData['scmNo'] = $arrData['scmNo'];
                        $scmCommission->saveScmCommission($addCommissionArrData, 'update');
                    } else {
                        $addCommissionArrData['scmNo'] = $arrData['scmNo'];
                        $scmCommission->saveScmCommission($addCommissionArrData, 'update');
                    }

                    //로그
                    $scmUpdateData = $scmCommission->getScmLogData($arrData['scmNo']);
                    $scmCommission->setScmLog('scm', 'update', $arrData['scmNo'], $scmPrevData, $scmUpdateData);

                    // 공급사 관련 정보 수정
                    if ($arrData['scmType'] == 'x') {
                        $arrGoodsData['scmNo'] = $arrData['scmNo'];
                        $arrGoodsData['goodsDisplayFl'] = 'n';
                        $arrGoodsData['goodsDisplayMobileFl'] = 'n';
                        $arrGoodsData['goodsSellFl'] = 'n';
                        $arrGoodsData['goodsSellMobileFl'] = 'n';
                        $arrGoodsBind = $this->db->get_binding(DBTableField::tableGoods(), $arrGoodsData, 'update', array_keys($arrGoodsData), ['sno']);
                        $this->db->bind_param_push($arrGoodsBind['bind'], 'i', $arrGoodsData['scmNo']);
                        $this->db->set_update_db(DB_GOODS, $arrGoodsBind['param'], 'scmNo = ?', $arrGoodsBind['bind'], false);
                        unset($arrGoodsBind);

                        $goodsDivisionFl = gd_policy('goods.config')['divisionFl'] ;
                        if($goodsDivisionFl =='y') {
                            //상품검색테이블 업데이트
                            $arrGoodsBind = $this->db->get_binding(DBTableField::tableGoodsSearch(), $arrGoodsData, 'update', array_keys($arrGoodsData), ['sno']);
                            $this->db->bind_param_push($arrGoodsBind['bind'], 'i', $arrGoodsData['scmNo']);
                            $this->db->set_update_db(DB_GOODS_SEARCH, $arrGoodsBind['param'], 'scmNo = ?', $arrGoodsBind['bind'], false);
                            unset($arrGoodsBind);
                        }
                        // 공급사 수수료 일정 삭제(탈퇴)
                        $scmCommission = App::load(\Component\Scm\ScmCommission::class);
                        $scmCommission->deleteScmScheduleCommissionBatch($arrData['scmNo'], 'once');
                    }

                    $this->db->commit();
                } catch (Exception $e) {
                    $this->db->rollback();
                    throw new \Exception(__('정산 요청 중 오류가 발생하였습니다. 다시 시도해 주세요.'));
                }
                break;
        }

        if (substr($arrData['mode'], 0, 6) == 'insert') {
            return $scmNo;
        } else {
            return $arrData['scmNo'];
        }
    }

    /**
     * 공급사 권한 저장
     *
     * @param array $arrData
     * @param string $scmNo
     */
    public function savePermissionScm($arrData, $scmNo)
    {
        if (!gd_is_provider()) {
            // 최고 관리자 번호 정의
            if (empty($arrData['superManagerSno']) === false) {
                $superManagerSno = $arrData['superManagerSno'];
            } else {
                $tmp = $this->getScmSuperManager($scmNo);
                $superManagerSno = $tmp['sno'];
            }

            // 공급사 관리자 수정 / es_manage 에 수정
            $manager = \App::load(Manager::class);
            if (method_exists($manager, 'saveManagerPermissionData')) {
                $arrManager = [];
                $arrManager['sno'] = $superManagerSno;
                $arrManager['scmNo'] = $scmNo;
                $arrManager['isSuper'] = 'y';
                $arrManager['permissionFl'] = $arrData['permissionFl'];
                $arrManager['permission_1'] = $arrData['permission_1'];
                $arrManager['permission_2'] = $arrData['permission_2'];
                $arrManager['permission_3'] = $arrData['permission_3'];
                $arrManager['writeEnabledMenu'] = $arrData['writeEnabledMenu'];
                $arrManager['functionAuth'] = $arrData['functionAuth'];
                $manager->saveManagerPermissionData($arrManager);
            }
        }
    }

    /**
     * 권한정보 리팩
     * @param array $scmData
     * @return array
     */
    public function getRepackManagerRegisterPermission($scmData = [])
    {
        if (empty($scmData['scmNo']) === false) { // 공급사 수정시
            $managerData = $this->getScmSuperManager($scmData['scmNo']);
            $manager = \App::load(Manager::class);
            if (method_exists($manager, 'getRepackManagerRegisterPermission')) {
                $repack = $manager->getRepackManagerRegisterPermission((array) $managerData, (array) $scmData);
                $repack['sno'] = $managerData['sno'];
            } else {
                $repack = null;
            }
        } else { // 공급사 등록시
            $manager = \App::load(Manager::class);
            if (method_exists($manager, 'getRepackManagerRegisterPermission')) {
                $repack = $manager->getRepackManagerRegisterPermission(['isSuper'=>'y']);
                $repack['sno'] = '';
            } else {
                $repack = null;
            }
        }
        return $repack;
    }

    public function setScmSearch($getValue = null)
    {
        if (is_null($getValue)) $getValue = Request::get()->toArray();

        // 통합 검색
        $this->search['combineSearch'] = [
            'all' => '=' . __('통합검색') . '=',
            'companyNm' => __('공급사명'),
            'scmCode' => __('공급사코드'),
            'businessNo' => __('사업자등록번호'),
            'ceoNm' => __('대표자'),
        ];

        $fieldType = DBTableField::getFieldTypes('tableScmManage');
        $this->search['key'] = gd_isset($getValue['key']);
        $this->search['keyword'] = gd_isset($getValue['keyword']);
        $this->search['scmType'] = gd_isset($getValue['scmType']);
        $this->search['scmPermissionInsert'] = gd_isset($getValue['scmPermissionInsert']);
        $this->search['scmPermissionModify'] = gd_isset($getValue['scmPermissionModify']);
        $this->search['scmPermissionDelete'] = gd_isset($getValue['scmPermissionDelete']);

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = ['companyNm', 'scmCode', 'businessNo', 'ceoNm'];
                $arrWhereAll = [];
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(sm.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, $fieldType[$keyNm], $this->search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            } else {
                $this->arrWhere[] = 'sm.' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, $fieldType[$this->search['key']], $this->search['keyword']);
            }
        }
        if ($this->search['scmType']) {
            $this->arrWhere[] = 'sm.scmType =?';
            $this->db->bind_param_push($this->arrBind, $fieldType['scmType'], $this->search['scmType']);
        }
        if ($this->search['scmPermissionInsert']) {
            $this->arrWhere[] = 'sm.scmPermissionInsert=?';
            $this->db->bind_param_push($this->arrBind, $fieldType['scmPermissionInsert'], $this->search['scmPermissionInsert']);
        }
        if ($this->search['scmPermissionModify']) {
            $this->arrWhere[] = 'sm.scmPermissionModify=?';
            $this->db->bind_param_push($this->arrBind, $fieldType['scmPermissionModify'], $this->search['scmPermissionModify']);
        }
        if ($this->search['scmPermissionDelete']) {
            $this->arrWhere[] = 'sm.scmPermissionDelete=?';
            $this->db->bind_param_push($this->arrBind, $fieldType['scmPermissionDelete'], $this->search['scmPermissionDelete']);
        }

        $this->checked['scmType'][$this->search['scmType']] =
        $this->checked['scmPermissionInsert'][$this->search['scmPermissionInsert']] =
        $this->checked['scmPermissionModify'][$this->search['scmPermissionModify']] =
        $this->checked['scmPermissionDelete'][$this->search['scmPermissionDelete']] = "checked='checked'";

        $this->selected['sort'][$this->search['sort']] = "selected='selected'";

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * deleteScm
     *
     * @param $scmNoArr
     *
     * @return int
     */
    public function deleteScm($scmNoArr)
    {
        $falseScmCount = 0;
        $manager = new Manager();
        foreach ($scmNoArr as $key => $val) {
            // 공급사 상품 체크
            $this->db->strField = 'count(goodsNo) as count';
            $this->db->strWhere = 'scmNo = ' . $val;
            $this->db->strOrder = 'goodsNo asc';

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $this->arrBind);

            if ($getData['count'] > 0) {
                $falseScmCount++;
                continue;
            }

            // 공급사 주문 체크
            $this->db->strField = 'count(sno) as count';
            $this->db->strWhere = 'scmNo = ' . $val;
            $this->db->strOrder = 'sno asc';

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $this->arrBind);

            if ($getData['count'] > 0) {
                $falseScmCount++;
                continue;
            }

            $superManagerData = $manager->getManagerListByScmNo($val);
            foreach ($superManagerData as $managerData) {
                $_isSuper = $managerData['isSuper'] == 'y' ? true : false;
                $manager->setManagerDelete($managerData['sno'], $_isSuper);
            }
            $arrData['scmNo'] = $val;
            $arrData['ceoNm'] = '';
            $arrData['businessNo'] = '';
            $arrData['businessLicenseImage'] = '';
            $arrData['mailOrderNo'] = '';
            $arrData['onlineOrderSerial'] = '';
            $arrData['service'] = '';
            $arrData['item'] = '';
            $arrData['email'] = '';
            $arrData['zipcode'] = '';
            $arrData['zonecode'] = '';
            $arrData['address'] = '';
            $arrData['addressSub'] = '';
            $arrData['unstoringZipcode'] = '';
            $arrData['unstoringZonecode'] = '';
            $arrData['unstoringAddress'] = '';
            $arrData['unstoringAddressSub'] = '';
            $arrData['returnZipcode'] = '';
            $arrData['returnZonecode'] = '';
            $arrData['returnAddress'] = '';
            $arrData['returnAddressSub'] = '';
            $arrData['phone'] = '';
            $arrData['centerPhone'] = '';
            $arrData['fax'] = '';
            $arrData['staff'] = '';
            $arrData['account'] = '';
            $arrData['delFl'] = 'y';
            $arrBind = $this->db->get_binding(DBTableField::tableScmManage(), $arrData, 'update', array_keys($arrData), ['scmNo']);
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['scmNo']);
            $this->db->set_update_db(DB_SCM_MANAGE, $arrBind['param'], 'scmNo = ?', $arrBind['bind'], false);
            unset($arrData);
            unset($arrBind);

            // 배송비조건 관리 내역 삭제
            $delivery = \App::load(\Component\Delivery\Delivery::class);
            $delivery->deleteWholeScmDelivery($val);

            // 공급사 수수료 일정 삭제(공급사 삭제)
            $scmCommission = App::load(\Component\Scm\ScmCommission::class);
            $scmCommission->deleteScmScheduleCommissionBatch($val);
        }

        return $falseScmCount;
    }

    /**
     * getScmFunctionAuth
     * 공급사 권한 설정 - 기능권한
     *
     * @param $scmNo
     *
     * @return mixed
     */
    public function getScmFunctionAuth($scmNo)
    {
        $arrBind = [];
        $query = "SELECT functionAuth FROM " . DB_SCM_MANAGE . " where scmNo = ?";
        $this->db->bind_param_push($arrBind, 'i', $scmNo);
        $data = $this->db->query_fetch($query, $arrBind, false);

        // 공급사 기능 권한 설정
        $functionAuth = json_decode($data['functionAuth'], true);

        return $functionAuth;
    }

    /**
     *
     * getScmSelectList
     * 공급사 선택 리스트
     *
     * @param $scmNo
     *
     * @return $data
     */
    public function getScmSelectList($scmNo)
    {
        if (empty($scmNo) === true) return;
        $scmNo = explode(INT_DIVISION, $scmNo);

        $query = 'SELECT scmNo, companyNm FROM ' . DB_SCM_MANAGE . ' WHERE scmNo IN (' . @implode(',', $scmNo) . ')';
        $data = $this->db->query_fetch($query);

        return $data;
    }

    /**
     * getScmListByName
     * openApi 공급사 리스트 검색 (공급사명)
     *
     * @param string $scmNm
     *
     * @return array $data
     */
    public function getScmListByName($scmNm)
    {
        if (empty($scmNm)) {
            return [];
        }

        // 데이터 처리
        $arrWhere[] = "companyNm LIKE concat('%', ?, '%')";
        $this->db->bind_param_push($arrBind, 's', $scmNm);
        $arrWhere[] = 'delFl = ?';
        $this->db->bind_param_push($arrBind, 's', 'n');

        $this->db->strField = 'scmNo, companyNm as scmNm';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . 'FROM ' . DB_SCM_MANAGE . ' AS m ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        return $data;
    }

    /**
     * getScmManagerEmail
     * 공급사 대표 email 및 API 결과 코드 반환
     *
     * @param integer $scmNo
     * @param string $scmId
     *
     * @return array $data
     */
    public function getScmManagerEmail($scmNo, $scmId)
    {
        $arrWhere = $arrBind = [];

        if (empty($scmNo) || empty($scmId)) {
            $result['code'] = self::OPENAPI_ERROR_ETC;
            return $result;
        }

        // 데이터 처리
        $arrWhere[] = 'm.scmNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $scmNo);
        $arrWhere[] = 'm.managerId = ?';
        $this->db->bind_param_push($arrBind, 's', $scmId);

        $this->db->strField = 'm.scmNo, m.managerId, m.email, m.isDelete, m.isSuper, m.isEmailAuth';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strJoin = ' LEFT JOIN ' . DB_SCM_MANAGE . ' AS sm ON m.scmNo = sm.scmNo';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . 'FROM ' . DB_MANAGER . ' AS m ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        // api 결과 코드 매칭
        if (empty($data)) {
            $result['code'] = self::OPENAPI_NOT_MATCH_SCM;
        } else {
            if ($data['isDelete'] == 'y' || $data['isSuper'] == 'n') {
                $result['code'] = self::OPENAPI_ERROR_ETC;
            } else if (empty($data['email'])) {
                $result['code'] = self::OPENAPI_EMPTY_MANAGER_EMAIL;
            } else if ($data['isEmailAuth'] != 'y') {
                $result['code'] = self::OPENAPI_NOT_AUTH_EMAIL;
            } else {
                $result['code'] = self::OPENAPI_SUCCESS_VALIDATION;
                $result['scmEmail'] = $data['email'];
                $result['shopSno'] = \Globals::get('gLicense.godosno');
            }
        }

        return $result;
    }
}
