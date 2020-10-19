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


namespace Bundle\Component\Member;

use Component\Admin\AdminMenu;
use Component\Database\DBTableField;
use Component\Member\Exception\LoginException;
use Component\Member\Exception\ManagerAuthException;
use Component\Member\Util\MemberUtil;
use Component\Policy\ManageSecurityPolicy;
use Component\Sms\Sms;
use Component\Sms\SmsMessage;
use Component\Sms\SmsSender;
use Component\Storage\Storage;
use Component\Validator\Validator;
use Encryptor;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Security\Digester;
use Framework\Security\Otp;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\GodoUtils;
use Framework\Utility\StringUtils;
use Password;
use Request;
use Session;

/**
 * Class Manager
 * @package Bundle\Component\Member
 * @author  Shin Donggyu <artherot@godo.co.kr>
 */
class Manager
{
    /** @var string 관리자 세션 */
    const SESSION_MANAGER_LOGIN = 'manager';
    /** @var string 임시 관리자 세션 */
    const SESSION_TEMP_MANAGER = 'tmpManager';
    /** 로그인 제한 걸린 관리자 아이디 */
    const SESSION_LIMIT_FLAG_ON_MANAGER_ID = 'SESSION_LIMIT_FLAG_ON_MANAGER_ID';
    const DELETE_DISPLAY = '삭제운영자';
    /** @var string 로그인 인증 쿠키 */
    const COOKIE_LOGIN_AUTH = 'GD5COOKIE_LOGIN_AUTH';
    /**
     * @var array SMS 자동발송 수신 종류
     */
    public $smsAutoReceiveKind = [];
    /**
     * @var \Framework\Database\DBTool $db
     */
    protected $db;
    /**
     * @var array manager filed type
     */
    protected $fieldTypes;
    /** @var bool 운영자 등록 시 인증 필수 여부 기본 true */
    protected $isRequireAuthentication = true;
    /**
     * @var array 리스트 검색관련
     */
    private $arrBind = [];
    private $arrWhere = [];
    private $checked = [];
    private $search = [];

    // __('삭제운영자')
    /**
     * @var \Bundle\Component\Storage\FtpStorage|\Bundle\Component\Storage\LocalStorage
     */
    private $_storage;

    /**
     * 생성자
     */
    public function __construct()
    {
        if (!\is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->fieldTypes = DBTableField::getFieldTypes('tableManager');

        // SMS 자동발송 수신 종류 설정
        $tmp = Sms::SMS_AUTO_RECEIVE_LIST;
        foreach ($tmp as $key => $val) {
            $newKey = 'smsAuto' . ucwords($key);
            $this->smsAutoReceiveKind[$newKey] = $val;
        }
        unset($tmp, $newKey);
    }

    /**
     * 관리자 로그인 여부 (관리자 전용)
     *
     * @static
     * @return bool
     */
    public static function isAdmin()
    {
        $session = \App::getInstance('session');
        $manager = $session->get('manager');

        return (isset($manager));
    }

    public static function useProvider()
    {
        return GodoUtils::isPlusShop(PLUSSHOP_CODE_SCM);
    }

    public static function displayListData(&$data)
    {
        if (count($data) > 0) {
            if (!$data[0]) {
                $data['deleteText'] = '';
                if ($data['isDelete'] == 'y') {
                    $data['deleteText'] = '<br><span class="text-red">(' . self::DELETE_DISPLAY . ')</span>';
                }
            } else if (is_array($data)) {
                foreach ($data as &$row) {
                    $row['deleteText'] = null;
                    if ($row['isDelete'] == 'y') {
                        $row['deleteText'] = '<br><span class="text-red">(' . self::DELETE_DISPLAY . ')</span>';
                    }
                }
            }
        }
    }

    /**
     * 사용가능한 관리자 메뉴
     * - 상세 운영자 권한 설정을 사용 하시려면 AdminMenu 의 accessMenu 로 처리하여야 합니다.(2016-10-05)
     * - 기존 운영자 권한 사용 시 사용
     *
     * @static
     * @return mixed
     */
    public static function accessMenu()
    {
        $session = \App::getInstance('session');
        $request = \App::getInstance('request');
        $encryptor = \App::getInstance('encryptor');
        $manamer = \App::load('\\Component\\Member\\Manager');

        // --- 관리자 존재 여부 체크
        $chkKey = 'permission';
        $arrPermission = [];
        foreach ($manamer->fieldTypes as $key => $val) {
            if (substr($key, 0, 10) == $chkKey) {
                $arrPermission[] = $key;
            }
        }

        $arrBind = [];
        $memPw = $encryptor->decrypt($session->get('manager.managerPw'));
        $manamer->db->strField = 'managerId, permissionFl, ' . implode(', ', $arrPermission);
        $manamer->db->strWhere = 'employeeFl != \'r\' AND managerId = ? AND managerPw = ?';
        $manamer->db->bind_param_push($arrBind, $manamer->fieldTypes['managerId'], $session->get('manager.managerId'));
        $manamer->db->bind_param_push($arrBind, $manamer->fieldTypes['managerPw'], $memPw);

        $query = $manamer->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER . implode(' ', $query);
        $data = $manamer->db->query_fetch($strSQL, $arrBind, false);


        // 게시판 권한 체크
        $userBoard = false;

        // 권한 체크
        if ($data['permissionFl'] == 'l') {
            $chkKey = $chkKey . ucfirst($request->getDirectoryUri());
            // 서브 권한이 있는 경우
            if (isset($data[$chkKey]) === true) {
                $accessible = true;
                if (preg_match('/sub_/', $data[$chkKey])) {
                    $manamer->accessMenu = explode(STR_DIVISION, str_replace('sub_', '', $data[$chkKey]));
                    if ($request->getDirectoryUri() != 'base') {
                        $chkCdNm = null;
                        $chkMenu = \App::load('Component\\Admin\\AdminMenu');
                        if ($request->getDirectoryUri() == 'provider') {
                            $adminMenuType = 's';
                        } else {
                            $adminMenuType = 'd';
                        }
                        $menuList = $chkMenu->getAdminMenuList($adminMenuType, $request->getDirectoryUri());
                        foreach ($menuList as $menuKey => $menuVal) {
                            if ($menuVal['depth'] == '3' && $menuVal['tUrl'] == $request->getFileUri()) {
                                $chkCdNm = $menuVal['sCode'];
                                break;
                            }
                        }
                        if (empty($chkCdNm) === false && in_array($chkCdNm, $manamer->accessMenu) === false) {
                            $accessible = false;
                        }
                    }
                    // 권한이 없는 경우
                } elseif (empty($data[$chkKey])) {
                    $manamer->accessMenu = [];
                    // 해당 대메뉴의 권한이 있는 경우
                } else {
                    $manamer->accessMenu = ['all'];
                }
            }

            // 사용자 모드 게시판 권한 체크
            if ($data['permissionBoard'] == 'board') {
                $userBoard = true;
            } elseif (preg_match('/sub_board/', $data['permissionBoard'])) {
                $userBoard = true;
            }
        } else {
            $manamer->accessMenu = ['all'];
        }

        return $manamer->accessMenu;
    }

    public function getEmployeeList()
    {
        return [
            'y' => __('직원'),
            't' => __('비정규직'),
            'p' => __('아르바이트'),
            'd' => __('파견직'),
            'r' => __('퇴사자'),
        ];
    }

    /**
     * 공급사번호로 운영자 조회
     *
     * @param $scmNo
     *
     * @return array|string
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getManagerListByScmNo($scmNo)
    {
        $strSQL = "SELECT m.* , sm.companyNm FROM " . DB_MANAGER . " as m LEFT OUTER JOIN " . DB_SCM_MANAGE . " as sm ON m.scmNo = sm.scmNo WHERE m.scmNo = ? ";
        $arrBind = [];
        $this->db->bind_param_push($arrBind, $this->fieldTypes['scmNo'], $scmNo);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        return gd_htmlspecialchars_stripslashes($data);
    }

    /**
     * 관리자 등록/수정 정보
     *
     * @param null $sno
     *
     * @return array 데이터
     * @throws Exception
     * @throws \Framework\Debug\Exception\DatabaseException
     * @internal param string $managerId 관리자 아이디
     */
    public function getManagerData($sno = null)
    {
        // 상품 기본정보
        $tmpField = DBTableField::tableManager();

        $query = "SELECT dispImage FROM " . DB_MANAGER . " where scmNo = ? AND isSuper = 'y' ";
        $this->db->bind_param_push($this->arrBind, $this->fieldTypes['scmNo'], Session::get('manager.scmNo'));
        $row = $this->db->query_fetch($query, $this->arrBind, false);
        $superDispImage = $row['dispImage'];
        $checked = [];

        // --- 등록인 경우
        if ($sno === null) {
            // 기본 정보
            $data['mode'] = 'register';
            $checked['scmFl']['n'] = 'checked';

            // 기본값 설정
            foreach ($tmpField as $key => $val) {
                if ($val['typ'] == 'i') {
                    $data[$val['val']] = (int) $val['def'];
                } else {
                    $data[$val['val']] = $val['def'];
                }
            }
            unset($tmpField);

            $data['dispImage'] = $superDispImage;
            $data['smsAutoFl'] = 'n';
            $data['scmNo'] = Session::get('manager.scmNo');
        } else {
            // 기본 정보
            $data = $this->getManagerInfo($sno); // 관리자 정보
            $data['mode'] = 'modify';

            // 기본값 설정
            foreach ($tmpField as $key => $val) {
                if ($val['def'] != null && !$data[$val['val']]) {
                    if ($val['typ'] == 'i') {
                        $data[$val['val']] = (int) $val['def'];
                    } else {
                        $data[$val['val']] = $val['def'];
                    }
                }
            }
            unset($tmpField);

            // 데이터 변형
            $data['email'] = explode('@', $data['email']);
            $data['phone'] = str_replace('-', '', $data['phone']);
            $data['cellPhone'] = str_replace('-', '', $data['cellPhone']);
            if ($data['extension'] == '0') {
                $data['extension'] = '';
            }

            $realPath = $this->storageDiskLocalScm()->getRealPath(basename($data['dispImage']));
            $data['isDispImage'] = 'n';
            if (file_exists($realPath)) {
                $data['isDispImage'] = 'y';
            }

            // SMS 자동발송 수신여부
            $arrTmp = explode(STR_DIVISION, $data['smsAutoReceive']);
            foreach ($this->smsAutoReceiveKind as $aKey => $aVal) {
                if (empty($data['smsAutoReceive']) === false) {
                    $data['smsAutoFl'] = 'y';
                    if (in_array($aKey, $arrTmp)) {
                        $data[$aKey] = 'y';
                    } else {
                        $data[$aKey] = 'n';
                    }
                } else {
                    $data['smsAutoFl'] = 'n';
                    $data[$aKey] = 'n';
                }
                $checked[$aKey][$data[$aKey]] = 'checked="checked"';
            }
            unset($data['smsAutoReceive'], $arrTmp);

            if ($data['scmNo'] == DEFAULT_CODE_SCMNO) {
                $checked['scmFl']['n'] = 'checked';
            } else {
                $checked['scmFl']['y'] = 'checked';
            }
            $disabled['modify'] = 'disabled';

            // 전체권한 관리자 상품 재고 수정 권한 체크
            if ($data['permissionFl'] == 's') {
                $functionAuth = json_decode($data['functionAuth'], true);
                if (empty($functionAuth['functionAuth']['goodsStockModify']) === false) {
                    $checked['functionAuth']['goodsStockModify'][$functionAuth['functionAuth']['goodsStockModify']] = 'checked="checked"';
                }
            }
        }


        $loginLimit = json_decode($data['loginLimit'], true);
        StringUtils::strIsSet($loginLimit['limitFlag'], 'n');
        $checked['loginLimitFlag'][$loginLimit['limitFlag']] =
        $checked['employeeFl'][$data['employeeFl']] =
        $checked['smsAutoFl'][$data['smsAutoFl']] =
        $checked['workPermissionFl'][$data['workPermissionFl']] =
        $checked['debugPermissionFl'][$data['debugPermissionFl']] =
        $checked['permissionFl'][$data['permissionFl']] = 'checked="checked"';

        $getData['disabled'] = $disabled;
        $getData['data'] = $data;
        $getData['checked'] = $checked;

        return $getData;
    }

    /**
     * 관리자 정보
     *
     * @param $sno
     *
     * @return array 데이터
     * @throws Exception
     * @internal param string $managerId 관리자 아이디
     */
    public function getManagerInfo($sno)
    {
        $strSQL = 'SELECT m.* , sm.companyNm,sm.scmPermissionInsert,sm.scmPermissionModify,sm.scmPermissionDelete FROM ' . DB_MANAGER . ' as m LEFT OUTER JOIN ' . DB_SCM_MANAGE . ' as sm ON m.scmNo = sm.scmNo WHERE m.sno = ?';
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);
        if (!$data) {
            throw new Exception(__('존재 하지 않는 운영자입니다.'));
        }

        return gd_htmlspecialchars_stripslashes($data);
    }

    /**
     * storageDiskLocalScm
     *
     * @return \Bundle\Component\Storage\FtpStorage|\Bundle\Component\Storage\LocalStorage
     * @throws Exception
     */
    public function storageDiskLocalScm()
    {
        if (($this->_storage instanceof \Bundle\Component\Storage\LocalStorage) === false) {
            $this->_storage = Storage::disk(Storage::PATH_CODE_SCM, 'local');
        }

        return $this->_storage;
    }

    /**
     * 관리자 리스트
     *
     * @param $req
     *
     * @return array 데이터
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getManagerList($req)
    {
        //정렬목록
        $this->search['sortList'] = [
            'desc' => __('등록일') . '↓',
            'asc'  => __('등록일') . '↑',
        ];

        if ($req['sort'] == 'asc') {
            $sortQuery = "regDt asc";
        } else {
            $sortQuery = "regDt desc";
        }
        // --- 검색 설정
        $this->search['detailSearch'] = gd_isset($req['detailSearch']);
        $this->search['key'] = gd_isset($req['key']);
        $this->search['keyword'] = gd_isset($req['keyword']);
        $this->search['smsAutoReceive'] = gd_isset($req['smsAutoReceive']);
        $this->search['departmentCd'] = gd_isset($req['departmentCd']);
        $this->search['positionCd'] = gd_isset($req['positionCd']);
        $this->search['dutyCd'] = gd_isset($req['dutyCd']);
        $this->search['employeeFl'] = gd_isset($req['employeeFl']);
        $this->search['scmFl'] = gd_isset($req['scmFl'], 'all');
        $this->search['scmNo'] = gd_isset($req['scmNo'], DEFAULT_CODE_SCMNO);
        $this->search['scmNoNm'] = gd_isset($req['scmNoNm']);
        $this->search['sort'] = $req['sort'];
        $this->search['isSuper'] = $req['isSuper'];

        $this->checked['employeeFl'][$this->search['employeeFl']] =
        $this->checked['smsAutoReceive'][$this->search['smsAutoReceive']] =
        $this->checked['scmFl'][$this->search['scmFl']] = 'checked = "checked"';

        // 키워드 검색
        if ($this->search['keyword']) {
            if (empty($this->search['key'])) {
                $this->arrWhere[] = ' (managerId LIKE concat(\'%\',?,\'%\') or managerNm LIKE concat(\'%\',?,\'%\') or m.email LIKE concat(\'%\',?,\'%\') or m.managerNickNm LIKE concat(\'%\',?,\'%\') or m.phone LIKE concat(\'%\',?,\'%\') or m.cellPhone LIKE concat(\'%\',?,\'%\') )';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
            } else {
                $this->arrWhere[] = 'm.' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, $this->fieldTypes[$this->search['key']], $this->search['keyword']);
            }
        }

        $fieldTypeGoods = DBTableField::getFieldTypes('tableGoods');

        //공급사
        if (Manager::isProvider()) {
            $scmWhere = ' AND m.scmNo = ' . Session::get('manager.scmNo');
        } else {
            if ($this->search['scmFl'] != 'all') {
                if (is_array($this->search['scmNo'])) {
                    foreach ($this->search['scmNo'] as $val) {
                        $arrWhere[] = 'm.scmNo = ?';
                        $this->db->bind_param_push($this->arrBind, 's', $val);
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $arrWhere) . ')';
                    unset($tmpWhere);
                } else {
                    $this->arrWhere[] = 'm.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['scmNo'], $this->search['scmNo']);
                }
            }

            // 공급사 대표운영자 조회
            if ($this->search['isSuper']) {
                $this->arrWhere[] = 'isSuper = ?';
                $this->arrWhere[] = 'm.scmNo != ' . DEFAULT_CODE_SCMNO;
                $this->db->bind_param_push($this->arrBind, $this->fieldTypes['isSuper'], $this->search['isSuper']);
            }
        }

        // SMS 자동발송 수신 여부 검색
        if ($this->search['smsAutoReceive']) {
            if ($this->search['smsAutoReceive'] === 'n') {
                $this->arrWhere[] = '(smsAutoReceive = \'\' OR smsAutoReceive IS NULL)';
            } else if ($this->search['smsAutoReceive'] === 'all') {
            } else {
                $this->arrWhere[] = 'smsAutoReceive LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, $this->fieldTypes['smsAutoReceive'], $this->search['smsAutoReceive']);
            }
        } else {
            $this->checked['smsAutoReceive']['all'] = 'checked';
        }

        // 부서 검색
        if ($this->search['departmentCd']) {
            $this->arrWhere[] = 'departmentCd = ?';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['departmentCd'], $this->search['departmentCd']);
        }

        // 직급 검색
        if ($this->search['positionCd']) {
            $this->arrWhere[] = 'positionCd = ?';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['positionCd'], $this->search['positionCd']);
        }

        // 직책 검색
        if ($this->search['dutyCd']) {
            $this->arrWhere[] = 'dutyCd = ?';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['dutyCd'], $this->search['dutyCd']);
        }

        // 직원 여부 검색
        if ($this->search['employeeFl']) {
            $this->arrWhere[] = 'employeeFl = ?';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['employeeFl'], $this->search['employeeFl']);
        } else {
            $this->checked['employeeFl']['all'] = 'checked';
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

        if ($req['mode'] == 'layer') {
            // --- 페이지 기본설정
            if (gd_isset($req['pagelink'])) {
                $req['page'] = (int) str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($req['pagelink'])));
            } else {
                $req['page'] = 1;
            }
            gd_isset($req['pageNum'], '10');

        } else {
            // --- 페이지 기본설정
            gd_isset($req['page'], 1);
            gd_isset($req['pageNum'], 10);
        }

        $page = \App::load('\\Component\\Page\\Page', $req['page']);
        $page->page['list'] = $req['pageNum']; // 페이지당 리스트 수
        $page->recode['amount'] = $this->db->fetch("SELECT COUNT(sno) FROM " . DB_MANAGER . " as m WHERE isSuper IN ('y', 'n') AND isDelete = 'n' " . $scmWhere, 'row')[0]; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $strWhere = '';
        if ($this->arrWhere) {
            $strWhere = ' AND ' . implode(' AND ', gd_isset($this->arrWhere));
        }
        $strSQL = "SELECT m.*, sm.companyNm FROM " . DB_MANAGER . " as m LEFT OUTER JOIN " . DB_SCM_MANAGE . " as sm ON m.scmNo = sm.scmNo
        WHERE isSuper IN ('y', 'n') AND isDelete='n'  " . $scmWhere . $strWhere . " ORDER BY " . $sortQuery . " LIMIT " . $page->recode['start'] . ',' . $req['pageNum'];
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        $strSQLCount = "SELECT COUNT(sno) as cnt FROM " . DB_MANAGER . " as m  WHERE isSuper IN ('y', 'n') AND isDelete='n'  " . $strWhere . $scmWhere;
        $_cnt = $this->db->query_fetch($strSQLCount, $this->arrBind, false);

        $page->recode['total'] = $_cnt['cnt'];

        // 검색 레코드 수
        $page->setPage();
        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        //$getData['sort'] = $req['sort'];

        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * 공급사 로그인 여부 (관리자 전용)
     * 앱마켓에서 사용으로 체크된 경우만 공급사 체크
     *
     * @static
     * @return bool
     */
    public static function isProvider()
    {
        $session = \App::getInstance('session');
        $manager = $session->get('manager');
        $scmNo = $session->get('manager.scmNo');

        return (isset($manager) && $scmNo != DEFAULT_CODE_SCMNO);
    }

    /**
     * 관리자 등록
     *
     * @param array        $arrData
     * @param string|array $files 운영자 이미지
     * @return string
     *
     * @throws \Exception
     */
    public function saveManagerData($arrData, $files)
    {
        $session = \App::getInstance('session');
        $checkMode = $arrData['mode'];
        $validator = new Validator();

        $arrData['memo'] = strip_tags($arrData['memo']);//관리자 메모 XSS방지

        if ($checkMode === 'modify') {
            $managerData = $this->getManagerInfo($arrData['sno']);
            $arrData['isSmsAuth'] = $managerData['isSmsAuth'];
            $arrData['isEmailAuth'] = $managerData['isEmailAuth'];
            $arrData['scmNo'] = $managerData['scmNo'];
            if (gd_is_provider() && $managerData['scmNo'] !== $session->get('manager.scmNo')) {
                //본인이 속한 공급사 계정만 수정 또는 추가 가능
                throw new \RuntimeException(__('본인이 속한 공급사 운영자 정보만 수정가능합니다.'));
            }

            if ($arrData['isModManagerPw'] === 'y') {
                $validator->add('managerPw', 'password', true); // 비밀번호
                $validator->add('managerPw', 'minlen', true, null, 10); // 비밀번호 최소길이
                $validator->add('managerPw', 'maxlen', true, null, 20); // 비밀번호 최대길이
                $arrData['managerPw'] = $arrData['modManagerPw'];

                // 관리자 비밀번호 재사용 제한 : 체크
                if (method_exists($this, 'getBeforePassword') === true) {
                    $beforePasswords = $this->getBeforePassword($arrData['managerPw'], $managerData['managerPw'], $managerData['sno']);
                    if ($beforePasswords === true) {
                        throw new Exception('이전에 사용한 비밀번호는 다시 사용하실 수 없습니다. 다른 비밀번호를 입력해 주세요.');
                    }
                }

                //비밀번호 변경일 업데이트
                $passwordDt = new \DateTime();
                $passwordDtFormat = $passwordDt->format('Y-m-d H:i:s');
                $arrData['changePasswordDt'] = $passwordDtFormat;
                $validator->add('changePasswordDt', '');
            }

        } else {  //등록시
            if ($this->overlapManagerId($arrData['managerId'])) {
                throw new \RuntimeException(sprintf(__('%s는 이미 등록된 %s 입니다'), $arrData['managerId'], __('아이디')));
            }
            $validator->add('isSuper', 'yn'); // 권한 종류
            if (gd_is_provider()) {
                $arrData['scmNo'] = $session->get('manager.scmNo');
            } elseif (!$arrData['scmNo']) {
                $arrData['scmNo'] = $arrData['scmFl'] === 'y' ? $arrData['scmNo'] : DEFAULT_CODE_SCMNO;
            }

            $validator->add('managerPw', 'password', true); // 비밀번호
            $validator->add('managerPw', 'minlen', true, null, 10); // 비밀번호 최소길이
            $validator->add('managerPw', 'maxlen', true, null, 20); // 비밀번호 최대길이
            $validator->add('scmNo', 'number', true); // 공급사
        }

        $validator->add('managerId', 'userid', true, null, true, false); // 아이디
        $validator->add('managerId', 'minlen', true, null, 4); // 아이디
        $validator->add('managerId', 'maxlen', true, null, 50); // 아이디
        // 데이터 조합
        if (isset($arrData['email']) && \is_array($arrData['email']) === true) {
            $arrData['email'] = (implode('', $arrData['email']) === '' ? '' : implode('@', $arrData['email']));
        }

        if (isset($arrData['phone'])) {
            if (\is_array($arrData['phone']) === true) {
                $arrData['phone'] = (implode('', $arrData['phone']) === '' ? '' : implode('-', $arrData['phone']));
            } elseif (\is_string($arrData['phone']) === true) {
                $arrData['phone'] = str_replace("-", "", $arrData['phone']);
                $arrData['phone'] = StringUtils::numberToPhone($arrData['phone']);
            }
        }

        if (isset($arrData['cellPhone'])) {
            if (\is_array($arrData['cellPhone']) === true) {
                $cellPhone = implode('', $arrData['cellPhone']) === '';
                $arrData['cellPhone'] = ($cellPhone ? '' : implode('-', $arrData['cellPhone']));
            } elseif (true === \is_string($arrData['cellPhone'])) {
                $arrData['cellPhone'] = str_replace('-', '', $arrData['cellPhone']);
                $arrData['cellPhone'] = StringUtils::numberToPhone($arrData['cellPhone']);
            }
        }

        if ($arrData['isImageDelete'] === 'y') {
            $arrData['dispImage'] = '';
        }

        //파일업로드
        $dispImage = $files['dispImage'];
        if ($dispImage['tmp_name']) {
            //이미지체크
            if (!gd_file_uploadable($dispImage, 'image')) {
                throw new \RuntimeException(__('이미지파일만 가능합니다.'));
            }

            $fileInfo = pathinfo($dispImage['name']);
            $nm = $fileInfo['filename'];
            $ext = $fileInfo['extension'];

            $scmFilename = 'scm_' . $arrData['scmNo'] . '_' . $arrData['managerId'] . '.' . $ext;
            $arrData['dispImage'] = $this->storageDiskLocalScm()->upload($dispImage['tmp_name'], $scmFilename);
        } else {
            $arrData['dispImage'] = $arrData['dispImage'];
        }

        // SMS 자동발송 수신여부
        $arrTmp = [
            'smsAutoOrder',
            'smsAutoMember',
            'smsAutoPromotion',
            'smsAutoBoard',
            'smsAutoAdmin',
        ];
        $arrSms = [];
        if ($arrData['smsAutoFl'] === 'y') {
            foreach ($arrTmp as $aVal) {
                if (empty($arrData[$aVal]) === false) {
                    $arrSms[] = $aVal;
                }
            }
            if (empty($arrSms)) {
                throw new Exception('SMS 자동발송 수신범위를 설정해주세요.');
            }
        }
        if (empty($arrSms) === false && empty($arrData['cellPhone']) === false) {
            $arrData['smsAutoReceive'] = implode(STR_DIVISION, $arrSms);
        } else {
            $arrData['smsAutoReceive'] = null;
        }
        unset($arrTmp, $arrSms);

        // 개발권한 설정
        $globals = \App::getInstance('globals');
        $license = $globals->get('gLicense');
        if ($license['ecCode'] === 'rental_mx_pro') {
            if ((int) $arrData['scmNo'] !== DEFAULT_CODE_SCMNO) {
                $arrData['workPermissionFl'] = 'n';
                $arrData['debugPermissionFl'] = 'n';
            } elseif ($managerData['isSuper'] === 'y') {
                $arrData['workPermissionFl'] = 'y';
                $arrData['debugPermissionFl'] = 'y';
            }
        } else {
            $arrData['workPermissionFl'] = 'n';
            $arrData['debugPermissionFl'] = 'n';
        }

        // 개발권한 변경에 따른 세션값 변경 (로그인한 회원 당사자만 세션 적용되도록)
        if ($arrData['sno'] === $session->get('manager.sno')) {
            $session->set('manager.workPermissionFl', $arrData['workPermissionFl']);
            $session->set('manager.debugPermissionFl', $arrData['debugPermissionFl']);
        }

        // 운영자 권한 설정
        if ($arrData['permissionFl'] === 'l') { // 운영권한 - 권한선택
            // 운영자 접근 권한 설정
            $permission = [
                'permission_1' => $arrData['permission_1'],
                'permission_2' => $arrData['permission_2'],
                'permission_3' => $arrData['permission_3'],
            ];
            $functionAuth = [
                'functionAuth' => $arrData['functionAuth'],
            ]; // 운영자 기능 권한 설정
        } else { // 운영권한 - 전체권한 or 공급사 등록에서 넘어온 공급사 대표운영자 저장 시
            $permission = null; // 운영자 접근 권한 설정
            $functionAuth = null; // 운영자 기능 권한 설정
            if (empty($arrData['functionAuth']['goodsStockModify']) === false) {
                $functionAuth['functionAuth']['goodsStockModify'] = $arrData['functionAuth']['goodsStockModify'];
            }
            if (empty($arrData['functionAuth']['goodsStockExceptView']) === false) {
                $functionAuth['functionAuth']['goodsStockExceptView'] = $arrData['functionAuth']['goodsStockExceptView'];
            }
        }
        $arrData['permissionMenu'] = json_encode($permission, JSON_UNESCAPED_UNICODE); // 운영자 접근 권한 설정
        $arrData['functionAuth'] = json_encode($functionAuth, JSON_UNESCAPED_UNICODE); // 운영자 기능 권한 설정

        $securityPolicy = \App::load(ManageSecurityPolicy::class);
        $manageSecurity = $securityPolicy->getValue(ManageSecurityPolicy::KEY);

        $smsAuth = $session->get(Member::SESSION_USER_CERTIFICATION, ['isAdminSmsAuth' => false]);
        $emailAuth = $session->get(Member::SESSION_USER_MAIL_CERTIFICATION, ['isAdminEmailAuth' => false]);

        if ($this->isRequireAuthentication) {
            $this->setAuthenticationInfo($arrData, $smsAuth, $emailAuth);
        }

        if ($checkMode === 'modify' && (($managerData['cellPhone'] !== '' && $arrData['cellPhone'] === '')
                || ($managerData['email'] !== '' && $arrData['email'] === ''))) {
            if ($arrData['cellPhone'] === '' && $manageSecurity['smsSecurityFl'] === 'y'
                || ($securityPolicy->useSmsAuth() && !$securityPolicy->isSmsAuthGodo())) {
                $securityType[] = '휴대폰번호';
            }
            if ($arrData['email'] === '' && $manageSecurity['emailSecurityFl'] === 'y' || $securityPolicy->useEmailAuth()) {
                $securityType[] = '이메일';
            }

            if (empty($securityType) === false) {
                $format = __('기본설정>관리정책>운영보안설정 화면에서 보안인증수단으로 설정된 정보(%s)는 삭제할 수 없습니다.');
                throw new \RuntimeException(sprintf($format, implode(', ', $securityType)));
            }
        }

        // Validation
        $validator->add('loginLimitFlag', 'yn'); // 로그인제한
        $validator->add('managerNm', '', true); // 이름
        $validator->add('dispImage', ''); // 대표이미지
        $validator->add('managerNickNm', ''); // 닉네임
        $validator->add('departmentCd', 'number'); // 부서
        $validator->add('employeeFl', ''); // 직원여부
        $validator->add('positionCd', 'number'); // 직급
        $validator->add('dutyCd', 'number'); // 직책
        $validator->add('smsAutoReceive', ''); // SMS 자동발송 수신여부 종류
        $validator->add('extension', ''); // 내선번호
        $validator->add('email', 'email'); // 이메일
        $validator->add('phone', ''); // 전화번호
        $validator->add('cellPhone', ''); // 휴대폰
        $validator->add('workPermissionFl', 'yn'); // 개발 권한 종류
        $validator->add('debugPermissionFl', 'yn'); // 디버그 권한 종류
        $validator->add('permissionFl', ''); // 권한 종류 - s(전체권한), l(일부권한)
        $validator->add('permissionMenu', ''); // 운영자 권한 - 일부 권한 일 경우 허용된 메뉴 접근 권한
        $validator->add('functionAuth', ''); // 운영자 권한 - 일부 권한 일 경우 허용된 기능 권한
        $validator->add('isSmsAuth', 'yn'); // 휴대폰인증여부
        $validator->add('isEmailAuth', 'yn'); // 이메일인증여부
        $validator->add('memo', ''); // 메모
        if ($validator->act($arrData, true) === false) {
            throw new \RuntimeException(implode("\n", $validator->errors));
        }

        // 저장
        if (isset($arrData['managerPw'])) {
            if(GodoUtils::sha256Fl()) {
                $arrData['managerPw'] = Digester::digest($arrData['managerPw']);
            } else {
                $arrData['managerPw'] = \App::getInstance('password')->hash($arrData['managerPw']);
            }
        }

        if ($checkMode === 'register') {
            $arrBind = $this->db->get_binding(DBTableField::tableManager(), $arrData, 'insert', array_keys($arrData));
            $this->db->set_insert_db(DB_MANAGER, $arrBind['param'], $arrBind['bind'], 'y');

            return $this->db->insert_id();
        }

        if ($managerData['isSuper'] === 'y') {
            $exceptField = [
                'managerId',
                'scmNo',
                'permissionFl',
            ];

            if ($managerData['scmNo'] === 1) {
                $exceptField[] = 'loginLimit';
            }
        } else {
            $exceptField = [
                'managerId',
                'scmNo',
            ];
        }

        if (isset($arrData['loginLimitFlag'])) {
            $arrData['loginLimit'] = [
                'limitFlag'      => $arrData['loginLimitFlag'],
                'loginFailLog'   => [],
                'loginFailCount' => 0,
                'onLimitDt'      => '0000-00-00 00:00:00',
            ];

            if ($arrData['loginLimitFlag'] === 'y') {
                $arrData['loginLimit']['onLimitDt'] = DateTimeUtils::dateFormat('Y-m-d G:i:s', 'now');
            }
        }

        $arrBind = $this->db->get_binding(DBTableField::tableManager(), $arrData, 'update', array_keys($arrData), $exceptField);

        $this->db->bind_param_push($arrBind['bind'], $this->fieldTypes['managerId'], $arrData['managerId']);
        $affectedRows = $this->db->set_update_db(DB_MANAGER, $arrBind['param'], 'managerId = ?', $arrBind['bind']);

        if ($affectedRows > 0) {
            // 회원 정보 수정 시 세션의 인증정보를 갱신
            $session = \App::getInstance('session');
            $sessionManager = $session->get(self::SESSION_MANAGER_LOGIN, []);

            if ($sessionManager['managerId'] === $arrData['managerId']) {
                $sessionManager['isSmsAuth'] = $arrData['isSmsAuth'];
                $sessionManager['isEmailAuth'] = $arrData['isEmailAuth'];
                $sessionManager['email'] = $arrData['email'];
                $sessionManager['cellPhone'] = $arrData['cellPhone'];
                $session->set(self::SESSION_MANAGER_LOGIN, $sessionManager);
            }

            // 관리자 비밀번호 재사용 제한 : 이전비밀번호목록 저장
            if (method_exists($this, 'saveBeforePassword') === true) {
                if ($checkMode === 'modify' && is_array($beforePasswords)) {
                    $this->saveBeforePassword($beforePasswords, $managerData['sno']);
                }
            }
        }

        return $arrData['sno'];
    }

    /**
     * 관리자 개발/디버그권한 정의
     *
     * @param array $arrData
     */
    public function setManagerWorkDebugPermissionFl(&$arrData)
    {
        $arrData['workPermissionFl'] = ($arrData['functionAuth']['workPermissionFl'] == 'y' ? 'y' : 'n');
        $arrData['debugPermissionFl'] = ($arrData['functionAuth']['debugPermissionFl'] == 'y' ? 'y' : 'n');
        if (isset($arrData['functionAuth']['workPermissionFl']) === true) unset($arrData['functionAuth']['workPermissionFl']);
        if (isset($arrData['functionAuth']['debugPermissionFl']) === true) unset($arrData['functionAuth']['debugPermissionFl']);
    }

    /**
     * 관리자 쓰기권한 저장
     *
     * @param array $arrData
     * @param string $sno
     *
     * @throws \Exception
     */
    public function saveManagerWritePermissionData($arrData, $sno = '')
    {
        if ($arrData['mode'] === 'modify') {
            $sno = $arrData['sno'];
            $managerData = $this->getManagerInfo($arrData['sno']);
        }

        // 운영자 권한 추가 정보 설정
        if ($arrData['permissionFl'] === 'l') { // 운영권한 - 권한선택
            $writeEnabledMenu = $arrData['writeEnabledMenu']; // 운영자 쓰기 권한 설정
        } else { // 운영권한 - 전체권한 or 공급사 등록에서 넘어온 공급사 대표운영자 저장 시
            $writeEnabledMenu = null; // 운영자 쓰기 권한 설정
        }
        $arrData['writeEnabledMenu'] = json_encode($writeEnabledMenu, JSON_UNESCAPED_UNICODE); // 운영자 쓰기 권한 설정

        $arrBind = [];
        $strSQL = "UPDATE " . DB_MANAGER . " SET `permissionFl` = ?, `writeEnabledMenu` = ? WHERE `sno` = ?";
        $this->db->bind_param_push($arrBind, 's', $arrData['permissionFl']);
        $this->db->bind_param_push($arrBind, 's', $arrData['writeEnabledMenu']);
        $this->db->bind_param_push($arrBind, 's', $sno);
        $this->db->bind_query($strSQL, $arrBind);

        // 공급사 기능권한 수정
        if (gd_is_provider() !== true && $managerData['scmNo'] && (int)$managerData['scmNo'] !== DEFAULT_CODE_SCMNO && $managerData['isSuper'] === 'y') {
            $functionAuth = ['functionAuth' => $arrData['functionAuth']];
            $arrData['functionAuth'] = json_encode($functionAuth, JSON_UNESCAPED_UNICODE); // 운영자 기능 권한 설정

            $arrBind = [];
            $strSQL = "UPDATE " . DB_SCM_MANAGE . " SET `functionAuth` = ? WHERE `scmNo` = ?";
            $this->db->bind_param_push($arrBind, 's', $arrData['functionAuth']);
            $this->db->bind_param_push($arrBind, 'i', $managerData['scmNo']);
            $this->db->bind_query($strSQL, $arrBind);
        }
    }

    /**
     * 관리자 권한 수정
     *
     * @param array        $arrData
     *
     * @throws \Exception
     */
    public function saveManagerPermissionData($arrData)
    {
        $session = \App::getInstance('session');
        $validator = new Validator();
        $isSuper = $arrData['isSuper'];
        $scmNo = $arrData['scmNo'];

        // 개발권한 설정
        $this->setManagerWorkDebugPermissionFl($arrData);
        $globals = \App::getInstance('globals');
        $license = $globals->get('gLicense');
        if ($license['ecCode'] === 'rental_mx_pro') {
            if ((int) $scmNo !== DEFAULT_CODE_SCMNO) {
                $arrData['workPermissionFl'] = 'n';
                $arrData['debugPermissionFl'] = 'n';
            } elseif ($isSuper === 'y') {
                $arrData['workPermissionFl'] = 'y';
                $arrData['debugPermissionFl'] = 'y';
            }
        } else {
            $arrData['workPermissionFl'] = 'n';
            $arrData['debugPermissionFl'] = 'n';
        }

        // 개발권한 변경에 따른 세션값 변경 (로그인한 회원 당사자만 세션 적용되도록)
        if ($arrData['sno'] === $session->get('manager.sno')) {
            $session->set('manager.workPermissionFl', $arrData['workPermissionFl']);
            $session->set('manager.debugPermissionFl', $arrData['debugPermissionFl']);
        }

        // 운영자 권한 설정
        if ($arrData['permissionFl'] === 'l') { // 운영권한 - 권한선택
            // 운영자 접근 권한 설정
            $permission = [
                'permission_1' => $arrData['permission_1'],
                'permission_2' => $arrData['permission_2'],
                'permission_3' => $arrData['permission_3'],
            ];
            // 운영자 쓰기 권한 설정
            $writeEnabledMenu = $arrData['writeEnabledMenu'];
            $functionAuth = [
                'functionAuth' => $arrData['functionAuth'],
            ]; // 운영자 기능 권한 설정
        } else { // 운영권한 - 전체권한 or 공급사 등록에서 넘어온 공급사 대표운영자 저장 시
            $permission = null; // 운영자 접근 권한 설정
            $writeEnabledMenu = null; // 운영자 쓰기 권한 설정
            $functionAuth = null; // 운영자 기능 권한 설정
            if (empty($arrData['functionAuth']['goodsStockModify']) === false) {
                $functionAuth['functionAuth']['goodsStockModify'] = $arrData['functionAuth']['goodsStockModify'];
            }
            if (empty($arrData['functionAuth']['goodsStockExceptView']) === false) {
                $functionAuth['functionAuth']['goodsStockExceptView'] = $arrData['functionAuth']['goodsStockExceptView'];
            }
        }
        $arrData['permissionMenu'] = json_encode($permission, JSON_UNESCAPED_UNICODE); // 운영자 접근 권한 설정
        $arrData['writeEnabledMenu'] = json_encode($writeEnabledMenu, JSON_UNESCAPED_UNICODE); // 운영자 쓰기 권한 설정
        $arrData['functionAuth'] = json_encode($functionAuth, JSON_UNESCAPED_UNICODE); // 운영자 기능 권한 설정

        // Validation
        $validator->add('sno', 'number');
        $validator->add('workPermissionFl', 'yn'); // 개발 권한 종류
        $validator->add('debugPermissionFl', 'yn'); // 디버그 권한 종류
        $validator->add('permissionFl', ''); // 권한 종류 - s(전체권한), l(일부권한)
        $validator->add('permissionMenu', ''); // 운영자 권한 - 일부 권한 일 경우 허용된 메뉴 접근 권한
        $validator->add('writeEnabledMenu', ''); // 운영자 권한 - 일부 권한 일 경우 허용된 메뉴 쓰기 권한
        $validator->add('functionAuth', ''); // 운영자 권한 - 일부 권한 일 경우 허용된 기능 권한
        if ($validator->act($arrData, true) === false) {
            throw new \RuntimeException(implode("\n", $validator->errors));
        }

        // 필드 제외
        $exceptField = [];
        if ((int) $scmNo === DEFAULT_CODE_SCMNO && $isSuper === 'y') { // 본사 최고운영자 경우
            $exceptField = array_merge($exceptField, [
                'workPermissionFl',
                'debugPermissionFl',
                'permissionFl',
                'permissionMenu',
                'writeEnabledMenu',
            ]);
        } else if ((int) $scmNo !== DEFAULT_CODE_SCMNO && $isSuper === 'y' && gd_is_provider()) { // 공급사 ADMIN 대표운영자 수정 경우
            $exceptField = array_merge($exceptField, [
                'workPermissionFl',
                'debugPermissionFl',
                'permissionFl',
                'permissionMenu',
                'writeEnabledMenu',
                'functionAuth',
            ]);
        }

        $arrBind = $this->db->get_binding(DBTableField::tableManager(), $arrData, 'update', array_keys($arrData), $exceptField);
        if (count($arrBind['param']) > 0) {
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['sno']);
            $this->db->set_update_db(DB_MANAGER, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        }
    }

    /**
     * 운영자 권한 설정 저장
     * @param array $arrData
     * @throws Exception
     *
     * $arrData 파라미터
     *    manage_sno[] : 운영자 목록
     *    permissionFl : 권한 범위 (s, l)
     *    [permission_1] : 1차 메뉴 권한 => 모바일앱과 마켓연동 설정만 저장시 사용함.
     *    Array(
     *      [godo00001]	=> writable
     *    )
     *    [permission_2] : 2차 메뉴 권한 => 저장시 사용하지 않음.
     *    Array(
     *      [godo00001]	=> Array(
     *       [godo00009]	=> writable
     *      )
     *    )
     *    [permission_3] : 3차 메뉴 권한 => 권한없음(empty) 필터 후 저장시 사용함.
     *    Array(
     *      [godo00001]	=> Array(
     *       [godo00009]	=> Array(
     *              [godo00010]	=> writable
     *          )
     *      )
     *    )
     *    functionAuth[] : 기능권한
     */
    public function saveManagersPermission($arrData)
    {
        if (is_array($arrData['manage_sno']) === false || count($arrData['manage_sno']) < 1) {
            throw new Exception(__('운영자를 선택해 주세요.'));
        }

        $permissionData = [
            'permissionFl' => $arrData['permissionFl'],
            'permission_1' => NULL, // 선택권한(l) 일 때 데이터 형식 : permission_1[] = godo00001
            'permission_2' => NULL, // 선택권한(l) 일 때 데이터 형식 : permission_2[godo00001][] = godo00009
            'permission_3' => NULL, // 선택권한(l) 일 때 데이터 형식 : permission_3[godo00009][] = godo00010
            'writeEnabledMenu' => NULL, // 선택권한(l) 일 때 데이터 형식 : writeEnabledMenu[godo00009][] : godo00010
            'functionAuth' => $arrData['functionAuth'], // 데이터 형식 : functionAuth[goodsNm] : y
        ];

        // 운영권한이 권한선택(l)일 때 기능권한 및 쓰기권한 정의
        if ($permissionData['permissionFl'] === 'l') {
            // 3차 메뉴 권한 권한없음(empty) 필터
            foreach ($arrData['permission_3'] as $topKey => $midMenus) {
                foreach ($midMenus as $midKey => $lastMenus) {
                    $arrData['permission_3'][$topKey][$midKey] = array_filter($lastMenus);
                    if (empty($arrData['permission_3'][$topKey][$midKey]) === true) {
                        unset($arrData['permission_3'][$topKey][$midKey]);
                    }
                }
            }
            $arrData['permission_3'] = array_filter($arrData['permission_3']);

            // 기능권한 및 쓰기권한 정의
            foreach ($arrData['permission_3'] as $topKey => $midMenus) {
                $permissionData['permission_1'][] = $topKey;

                foreach ($midMenus as $midKey => $lastMenus) {
                    $permissionData['permission_2'][$topKey][] = $midKey;

                    foreach ($lastMenus as $lastKey => $status) {
                        $permissionData['permission_3'][$midKey][] = $lastKey;
                        if ($status == 'writable') $permissionData['writeEnabledMenu'][$midKey][] = $lastKey;
                    }
                }
            }

            // 본사 - [모바일앱 서비스] 경우 하위 메뉴 정의
            $status = $arrData['permission_1']['godo00778'];
            if ($status != '') {
                $topKey = 'godo00778';
                $midKey = 'godo00780';
                $lastKey = 'godo00781';
                $permissionData['permission_1'][] = $topKey;
                $permissionData['permission_2'][$topKey][] = $midKey;
                $permissionData['permission_3'][$midKey][] = $lastKey;
                if ($status == 'writable') $permissionData['writeEnabledMenu'][$midKey][] = $lastKey;
            }

            // 본사 - [샵링커 서비스] 경우 하위 메뉴 정의
            $status = $arrData['permission_1']['godo00801'];
            if ($status != '') {
                $topKey = 'godo00801';
                $midKey = 'godo00802';
                $lastKey = 'godo00803';
                $permissionData['permission_1'][] = $topKey;
                $permissionData['permission_2'][$topKey][] = $midKey;
                $permissionData['permission_3'][$midKey][] = $lastKey;
                if ($status == 'writable') $permissionData['writeEnabledMenu'][$midKey][] = $lastKey;
            }
        }

        // 운영자 정보 조회
        $session = \App::getInstance('session');
        $managersData = [];
        foreach ($arrData['manage_sno'] as $sno) {
            $data = $this->getManagerInfo($sno);
            //본인이 속한 공급사 계정만 수정 또는 추가 가능
            if (gd_is_provider() && $data['scmNo'] !== $session->get('manager.scmNo')) {
                throw new Exception(__('본인이 속한 공급사 운영자 정보만 수정가능합니다.'));
            }

            // 대표운영자와 부운영자를 같이 저장 요청한 경우 최고(대표)운영자는 저장 패스
            if ($data['isSuper'] == 'y' && count($arrData['manage_sno']) > 1) {
                $superText = ($data['scmNo'] != DEFAULT_CODE_SCMNO ? '대표운영자' : '최고운영자');
                throw new Exception(sprintf(__('%s와 부운영자를 동시에 수정할 수 없습니다.'), $superText));
            }

            $managersData[] = $data;
        }

        // 운영자 권한 저장
        foreach ($managersData as $data) {
            $arrManager = [];
            $arrManager['sno'] = $data['sno'];
            $arrManager['scmNo'] = $data['scmNo'];
            $arrManager['isSuper'] = $data['isSuper'];
            $arrManager = array_merge($arrManager, $permissionData);
            $this->saveManagerPermissionData($arrManager);

            // 공급사 기능권한 수정
            if (gd_is_provider() !== true && $data['scmNo'] && (int) $data['scmNo'] !== DEFAULT_CODE_SCMNO && $data['isSuper'] === 'y') {
                $functionAuth = [
                    'functionAuth' => $arrManager['functionAuth'],
                ];
                $functionAuth = json_encode($functionAuth, JSON_UNESCAPED_UNICODE);
                $arrBind = [];
                $strSQL = "UPDATE " . DB_SCM_MANAGE . " SET `functionAuth` = ? WHERE `scmNo` = ?";
                $this->db->bind_param_push($arrBind, 's', $functionAuth);
                $this->db->bind_param_push($arrBind, 'i', $data['scmNo']);
                $this->db->bind_query($strSQL, $arrBind);
            }
        }
    }

    /**
     * 권한정보 리팩
     * @param array $managerData
     * @param array $scmData
     * @return array
     */
    public function getRepackManagerRegisterPermission($managerData, $scmData = [])
    {
        // Default Format
        $data = [
            'permissionFl'  => $managerData['permissionFl'],
            'permissionMenu'    => $managerData['permissionMenu'],
            'writeEnabledMenu'  => $managerData['writeEnabledMenu'],
            'functionAuth'  => $managerData['functionAuth'],
        ];

        // 운영자번호가 없는 경우(운영자등록) 초기값
        if ($managerData['sno'] == '') {
            $data = [
                'permissionFl'  => 'l', // 디폴트 : 권한 범위-선택권한, 설정설정-권한없음
                'permissionMenu'    => '',
                'writeEnabledMenu'  => '',
                'functionAuth'  => json_encode($this->getFunctionAuthInit($managerData['scmNo'], $managerData['isSuper'], $scmData)),
            ];
        }

        // 공급사 대표운영자 기능권한 조정
        if ($managerData['isSuper'] == 'y' && $managerData['scmNo'] != '' && $managerData['scmNo'] != DEFAULT_CODE_SCMNO) {
            if (empty($scmData['functionAuth']) === true) {
                $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
                $scmData = $scmAdmin->getScm($managerData['scmNo']);
            }

            // 공급사 기능 권한
            // AdminFunctionAuth 인터셉터 내 공급사 대표운영자 기능권한 조건
            //   1) 공급사 대표운영자(운영자 권한=전체권한) 경우 공급사 기능권한(es_scmManage.functionAuth)만 적용
            //   2) 즉, 운영자 기능권한(es_manager.functionAuth)은 배제
            //   3) 단, 운영자 기능권한(es_manager.functionAuth)에 상품 재고 수정 권한(goodsStockModify) 없으면 공급사 기능권한(es_smsManage.functionAuth)에서 goodsStockModify 초기화됨.
            //   4) 덧, 운영자 기능권한(es_manager.functionAuth)에 상품 상세 재고 수정 제외 권한(goodsStockExceptView) 없으면 공급사 기능권한(es_smsManage.functionAuth)에서 goodsStockExceptView 초기화됨.
            // 위 조건에 의거하여
            //   1) 설정화면 구성 : 기능권한 데이터로 공급사 기능권한(es_scmManage.functionAuth)과 병합
            //   2) 저장 : 운영자 기능권한(es_manager.functionAuth)에 상품 재고 수정 권한(goodsStockModify)과 상품 상세 재고 수정 제외 권한(goodsStockExceptView)만 저장하고 공급사 기능권한(es_scmManage.functionAuth) 에는 전체 저장
            $managerFunctionAuth = json_decode($data['functionAuth'], true);
            $scmFunctionAuth = json_decode($scmData['functionAuth'], true);
            if (empty($managerFunctionAuth['functionAuth']['goodsStockModify'])) {
                unset($scmFunctionAuth['functionAuth']['goodsStockModify']);
            }
            if (empty($managerFunctionAuth['functionAuth']['goodsStockExceptView'])) {
                unset($scmFunctionAuth['functionAuth']['goodsStockExceptView']);
            }
            $data['functionAuth'] = json_encode($scmFunctionAuth);
        }

        // 리턴 Format 정의
        if ($data['permissionFl'] == 's') { // 운영권한 - 전체권한
            $data['permissionMenu'] = '';
            $data['functionAuth'] = json_decode($data['functionAuth'], true);
            $data['writeEnabledMenu'] = '';
        } else {
            $data['permissionMenu'] = json_decode($data['permissionMenu'], true);
            $data['functionAuth'] = json_decode($data['functionAuth'], true);
            $data['writeEnabledMenu'] = json_decode($data['writeEnabledMenu'], true);
        }

        if ($managerData['sno']) {
            // 고도몰5pro 사용하고 본사 운영자인 경우 개발권한 및 디버그권한을 기능권한으로 마이그레이션
            //   1) 설정화면 구성 : 개발소스관리 메뉴 내 추가설정(기능권한)으로 배치
            //   2) 저장 : functionAuth['workPermissionFl'] => es_manager.workPermissionFl, functionAuth['debugPermissionFl'] => es_manager.debugPermissionFl 으로 저장하고 es_manager.functionAuth 에는 2개 항목 제외하여 저장
            // 고도몰5pro 사용여부 체크
            $globals = \App::getInstance('globals');
            $license = $globals->get('gLicense');
            if ($license['ecCode'] === 'rental_mx_pro' && $managerData['scmNo'] == DEFAULT_CODE_SCMNO) {
                // 개발권한 : 관리자 상단의 [개발소스보기]가 활성화되어 쇼핑몰 개발소스를 확인/복사할 수 있습니다.
                if ($managerData['workPermissionFl'] == 'y') {
                    $data['functionAuth']['functionAuth']['workPermissionFl'] = 'y';
                    // 디버그권한 : 오류가 발생하면 오류페이지 템플릿 하단에 Exception 메시지가 별도로 출력됩니다.
                    if ($managerData['debugPermissionFl'] == 'y') {
                        $data['functionAuth']['functionAuth']['debugPermissionFl'] = 'y';
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 기능권한 초기값 리턴
     * @param $scmNo
     * @param $isSuper
     * @param array $scmData
     * @return array
     */
    function getFunctionAuthInit($scmNo, $isSuper, $scmData = [])
    {
        $data = $functionList = array();

        // 본사/공급사 운영자등록시 기능권한 Default
        $adminMenuType = ((int) $scmNo === DEFAULT_CODE_SCMNO ? 'd' : 's');
        $adminMenu = \App::load('Component\\Admin\\AdminMenu');
        if (method_exists($adminMenu, 'getMenuFunction') === true) {
            $functionList = $adminMenu->getMenuFunction($adminMenuType);
        }
        foreach ($functionList as $lists) {
            foreach ($lists as $code => $name) {
                if ($code == 'goodsStockExceptView') continue;
                $data['functionAuth'][$code] = 'y';
            }
        }

        // 공급사 부운영자 일 경우 공급사 대표운영자 기능권한 범위 내만 허용 그외 제외
        if ($adminMenuType == 's' && $isSuper != 'y') {
            if (empty($scmData['functionAuth']) === true) {
                $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
                $scmData['functionAuth'] = $scmAdmin->getScmFunctionAuth($scmNo);
            }
            foreach ($data['functionAuth'] as $code => $value) {
                if ($scmData['functionAuth']['functionAuth'][$code] !== 'y') {
                    unset($data['functionAuth'][$code]);
                }
            }
        }

        return $data;
    }

    /**
     * 관리자 아이디 중복 확인
     *
     * @param string $managerId 관리자 아이디
     *
     * @return bool
     * @throws \Exception
     */
    public function overlapManagerId($managerId)
    {
        if (Validator::userid($managerId, true, false) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), __('아이디')));
        }

        if (Validator::minlen(4, $managerId, true) === false) {
            throw new \Exception('입력된 아이디 길이가 너무 짧습니다.');
        }

        if (Validator::maxlen(50, $managerId, true) === false) {
            throw new Exception(sprintf(Validator::TEXT_MAXLEN_INVALID, __('아이디'), 50));
        }

        $strSQL = "SELECT managerId FROM " . DB_MANAGER . " WHERE managerId=?  AND isDelete = 'n' ";
        $arrBind = [];
        $this->db->bind_param_push($arrBind, $this->fieldTypes['managerId'], $managerId);
        $this->db->query_fetch($strSQL, $arrBind);
        if ($this->db->num_rows() > 0) {
            return true;
        }
    }

    /**
     * 운영자 정보 등록/수정 시 인증수단 상태 검증 및 설정
     *
     * 인증수단정보를 변경하기 위해선 인증을 진행한 상태여야 함.
     * 인증을 하지 않은 경우 인증정보를 변경할 수 없음.
     * 최소 1개의 인증수단을 가져야함.
     *
     * @param array $arrData   입력되는 운영자 정보
     * @param array $smsAuth   휴대폰인증 정보
     * @param array $emailAuth 이메일인증 정보
     */
    protected function setAuthenticationInfo(&$arrData, $smsAuth, $emailAuth)
    {
        if ($smsAuth['isAdminSmsAuth'] === true) {
            if (str_replace('-', '', $smsAuth['authCellPhone']) !== str_replace('-', '', $arrData['cellPhone'])) {
                throw new ManagerAuthException(ManagerAuthException::CODE_NOT_EQUALS_CELLPHONE);
            }
            $arrData['isSmsAuth'] = 'y';
        } elseif ($arrData['isSmsAuth'] === 'y') {
            unset($arrData['cellPhone']);
        } else {
            unset($arrData['cellPhone']);
            $arrData['isSmsAuth'] = 'n';
        }

        if ($emailAuth['isAdminEmailAuth'] === true) {
            if ($emailAuth['authEmail'] !== $arrData['email']) {
                throw new ManagerAuthException(ManagerAuthException::CODE_NOT_EQUALS_EMAIL);
            }
            $arrData['isEmailAuth'] = 'y';
        } elseif ($arrData['isEmailAuth'] === 'y') {
            unset($arrData['email']);
        } else {
            unset($arrData['email']);
            $arrData['isEmailAuth'] = 'n';
        }

        if ($arrData['isSmsAuth'] === 'n' && $arrData['isEmailAuth'] === 'n') {
            throw new ManagerAuthException(ManagerAuthException::CODE_NOT_FOUND_AUTH);
        }
    }

    /**
     * 운영자 삭제
     *
     * @param      $sno
     *
     * @param bool $isSuper true일대만 슈퍼운영자 삭제가능
     *
     * @throws Exception
     */
    public function setManagerDelete($sno, $isSuper = false)
    {

        $selectQuery = "SELECT *   FROM " . DB_MANAGER . " WHERE sno = ?  ";
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $result = gd_htmlspecialchars_stripslashes($this->db->query_fetch($selectQuery, $arrBind, false));

        if ($isSuper === false) {
            if ($result['isSuper'] == 'y') {  //슈퍼관리자는 본사든 공급사든 삭제못함
                throw new \Exception(__('최고 운영자 계정은 삭제할 수 없습니다.'));
            }
        }

        if (Session::get('manager.scmNo') != DEFAULT_CODE_SCMNO) {    //공급사인경우
            if ($result['scmNo'] != Session::get('manager.scmNo')) {  //본인이 속한 공급사 계정만 삭제 가능
                throw new \Exception(__('최고 운영자 계정은 삭제할 수 없습니다.'));
            }
        }

        $strSql = "UPDATE " . DB_MANAGER . " SET isDelete = 'y' WHERE sno = ?";
        $this->db->bind_query(
            $strSql,
            [
                'i',
                $sno,
            ]
        );
    }

    /**
     * 관리자 로그인
     *
     * @param array $loginData 로그인에 필요한 맵 (managerId, managerPw)
     *
     * @throws \Exception
     */
    public function managerLogin($loginData)
    {
        $result = $this->validateManagerLogin($loginData);
        $this->afterManagerLogin($result);
    }

    /**
     * ID/PW 체크 후 매니저 정보 반환
     *
     * @param array   $loginData   로그인에 필요한 맵 managerId|managerPw
     * @param boolean $adminAccess 관리자 접속 가능 여부
     *
     * @return mixed
     * @throws \Exception
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function validateManagerLogin($loginData, $adminAccess = true)
    {
        $request = \App::getInstance('request');
        $loginIp = Request::getRemoteAddress();

        // 동일 IP 접근제한 로그 (로그인 시도)
        $this->setLogIpLoginTry($loginIp);

        // --- 접속 권한 IP 체크
        $manageSecurity = gd_policy('manage.security');
        if ($manageSecurity['ipAdminSecurity'] === 'y' && $manageSecurity['ipAdmin']) {
            $this->validateIpByLogin($request->getRemoteAddress(), $manageSecurity);
        }

        // --- 아이디 체크
        if (Validator::required($loginData['managerId']) === false) {
            throw new \InvalidArgumentException(sprintf(__('%s은(는) 필수 항목 입니다.'), __('쇼핑몰 관리 아이디')));
        }

        if (Validator::userid($loginData['managerId'], true, false) === false) {
            throw new \InvalidArgumentException(sprintf(__('입력하신 %s(이)가 올바르지 않아 접속할 수 없습니다.'), __('쇼핑몰 관리 정보')));
        }

        // --- 패스워드 체크
        if (Validator::required($loginData['managerPw']) === false) {
            throw new \InvalidArgumentException(sprintf(__('%s은(는) 필수 항목 입니다.'), __('쇼핑몰 관리 정보')));
        }

        try {
            $data = $this->getManagerByLogin($loginData);
            if ($data['employeeFl'] === 'r') {
                throw new \RuntimeException(__('관리자님은 퇴사자 이므로 로그인이 제한됩니다.'));
            }

            if ($data['scmType'] === 'x') {
                throw new \RuntimeException(__('탈퇴 상태인 공급사는 로그인할 수 없습니다. 본사에 문의해주세요.'));
            }

            if ((int) $data['scmNo'] > DEFAULT_CODE_SCMNO && GodoUtils::isPlusShop(PLUSSHOP_CODE_SCM) === false) {
                throw new \RuntimeException(__('아래 "플러스샵 > 공급사관리" 링크로 이동하셔서 설치하신 후 시도해주세요.<br><a href="http://plus.godo.co.kr/goods/view.gd?idx=6" target="_blank">>공급사 관리 설치하기</a>'));
            }

            // 개발권한 여부 설정 (pro가 아닌 경우 무조건 권한 제거)
            if (array_key_exists('workPermissionFl', $data)) {
                $globals = \App::getInstance('globals');
                $license = $globals->get('gLicense');
                if ($license['ecCode'] !== 'rental_mx_pro') {
                    $data['workPermissionFl'] = 'n';
                }
            }

            if (array_key_exists('debugPermissionFl', $data)) {
                $globals = \App::getInstance('globals');
                $license = $globals->get('gLicense');
                if ($license['ecCode'] !== 'rental_mx_pro') {
                    $data['debugPermissionFl'] = 'n';
                }
            }

            $adminLog = \App::load('Component\\Admin\\AdminLogDAO');

            // 동일 IP 로그인 연속 시도에 의한 접속제한 처리
            $logIpLoginTryInfo = $adminLog->selectLogIpLoginTry($loginIp, 'Y');
            if ($logIpLoginTryInfo['limitFlag'] === 'Y') {
                if ($this->isCheckLoginTimeout($logIpLoginTryInfo['onLimitDt'])) {
                    $adminLog->setAdminLog();
                    throw new \Exception('존재하지 않거나 잘못된 정보로 잦은 로그인 시도하였습니다. 정보보호를 위해 15분간 접속이 차단됩니다.', 500);
                }
            }

            if ($data['loginLimit']['limitFlag'] === 'y') {
                $adminLog->setAdminLog();
                if ($data['isSuper'] === 'y' && (int) $data['scmNo'] === 1) {
                    throw new \Component\Member\Exception\LoginException('', null, \Component\Member\Exception\LoginException::CODE_SUPER_MANAGER_LOGIN_FAIL_LIMIT_FLAG_ON);
                }

                throw new \Component\Member\Exception\LoginException('', null, \Component\Member\Exception\LoginException::CODE_MANAGER_LOGIN_FAIL_LIMIT_FLAG_ON);
            }

            if (Digester::isValid($data['managerPw'], $loginData['managerPw']) === false) {
                // validate credential.
                if (\App::getInstance('password')->verify($loginData['managerPw'], $data['managerPw']) === false) {
                    // 이나무 이전 패스워드 교차확인 / 프론트 로그인 부분 그대로 관리자로그인에도 적용 from credentialMemberPassword - Component/Member/Member.php
                    // 지우지 마세요.
                    // legacy code - mysql password or md5 or old_password or sha512
                    // old_passwords variable is deprecated as of MySQL 5.7.6 and will be removed in a future MySQL release.
                    // old_password function remove
                    // http://dev.mysql.com/doc/refman/5.7/en/server-system-variables.html#sysvar_old_passwords
                    $strSQL = 'select if (managerPw in (password(?), md5(?), sha2(?, 512)), 1, 0) as result from ' . DB_MANAGER . ' where sno = ?';
                    $arrBind = [
                        'sssi',
                        $loginData['managerPw'],
                        $loginData['managerPw'],
                        $loginData['managerPw'],
                        $data['sno'],
                    ];
                    $result = $this->db->query_fetch($strSQL, $arrBind, false);
                    if ($result['result']) {
                        // password hash and update member.
                        if(GodoUtils::sha256Fl()) {
                            $digesterPwd = Digester::digest($loginData['managerPw']);
                        } else {
                            $digesterPwd = \App::getInstance('password')->hash($loginData['managerPw']);
                        }
                        $this->db->bind_query(
                            'update ' . DB_MANAGER . ' set managerPw = ? where sno = ?', [
                                'si',
                                $digesterPwd,
                                $data['sno'],
                            ]
                        );
                        $data['managerPw'] = $digesterPwd;
                    } else {
                        // managerPw 다른 경우 로그 기록
                        if (isset($result['result']) && $result['result'] == 0) {
                            if (empty($request->post()->get('managerId'))) {
                                $request->post()->set('managerId', $loginData['managerId']);
                            }
                            $adminLog->setAdminLog();
                        }
                        throw new LoginException($data['managerId'], __('쇼핑몰 관리자 정보가 존재하지 않거나 잘못된 정보 입니다. 다시 확인 바랍니다.'));
                    }
                } else {
                    if(GodoUtils::sha256Fl()) {
                        $digesterPwd = Digester::digest($loginData['managerPw']);
                        $this->db->bind_query(
                            'update ' . DB_MANAGER . ' set managerPw = ? where sno = ?', [
                                'si',
                                $digesterPwd,
                                $data['sno'],
                            ]
                        );
                        $data['managerPw'] = $digesterPwd;
                    }
                }
            }

            if ((int) $data['loginLimit']['loginFailCount'] > 0) {
                $this->initLimitLoginLog($data);
            }

            // --- 정보 암호화, 승인체크, 세션저장
            $arrEncode = ['managerPw'];
            $encryptor = \App::getInstance('encryptor');
            $managerInfo = [];

            foreach ($data as $key => $val) {
                if (\in_array($key, $arrEncode)) {
                    $val = $encryptor->encrypt($val);
                }
                $managerInfo[$key] = gd_htmlspecialchars_addslashes($val);
            }

            if ($managerInfo['scmKind'] === 'c') {
                $managerInfo['isProvider'] = false;
            } else {
                $managerInfo['isProvider'] = true;
            }

            unset($managerInfo['permissionFl'], $managerInfo['employeeFl'], $data);

            return $managerInfo;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 로그인 시 사용되는 관리자정보
     *
     * @param array $loginData
     *
     * @return array
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    protected function getManagerByLogin(array $loginData)
    {
        //@formatter:off
        $arrInclude = [
            'managerId', 'managerNickNm', 'managerNm', 'managerPw', 'cellPhone', 'isSmsAuth', 'employeeFl',
            'workPermissionFl', 'debugPermissionFl', 'permissionFl', 'isSuper', 'changePasswordDt', 'guidePasswordDt',
            'permissionMenu', 'loginLimit', 'lastLoginAuthDt', 'isOrderSearchMultiGrid',
        ];
        //@formatter:on
        $arrField = DBTableField::setTableField('tableManager', $arrInclude);
        $arrBind = [];
        $arrJoin[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON sm.scmNo = m.scmNo ';
        $this->db->strField = implode(', ', $arrField);
        $this->db->strJoin = implode('', $arrJoin);
        $this->db->strWhere = "m.managerId = ? AND m.isDelete = 'n' AND sm.scmType = 'y' "; // 공급사 운영상태 일 경우만 로그인 가능
        $this->db->bind_param_push($arrBind, $this->fieldTypes['managerId'], $loginData['managerId']);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT sno, m.modDt AS mModDt, m.managerId, m.managerNickNm, m.regDt AS mRegDt, m.isEmailAuth, ';
        $strSQL .= 'm.email, sm.scmNo, sm.scmKind, sm.scmPermissionInsert, sm.scmPermissionModify, ';
        $strSQL .= 'sm.scmPermissionDelete, sm.companyNm,sm.scmType, ';
        $strSQL .= array_shift($query) . ' FROM ' . DB_MANAGER . ' as m ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);
        $loginLimit = json_decode($data['loginLimit'], true);
        $data['loginLimit'] = $loginLimit;

        return $data;
    }

    /**
     * 로그인 제한 관련 로그 데이터 초기화
     *
     * @param array $params
     *
     * @return array
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function initLimitLoginLog(array $params)
    {
        $result = ['affectedRows' => 0];
        $managerByUpdate = [
            'loginLimit' => [
                'limitFlag'      => 'n',
                'onLimitDt'      => '0000-00-00 00:00:00',
                'loginFailCount' => 0,
                'loginFailLog'   => [],
            ],
        ];
        $db = \App::getInstance('DB');
        $tableField = \App::load('Component\\Database\\DBTableField');
        $managerTableFields = $tableField::getFieldTypes($tableField::getFuncName(DB_MANAGER));
        $db->query_reset();
        $arrBind = $db->get_binding($tableField::tableManager(), $managerByUpdate, 'update', ['loginLimit']);
        $db->bind_param_push($arrBind['bind'], $managerTableFields['managerId'], $params['managerId']);
        $db->set_update_db(DB_MANAGER, $arrBind['param'], 'managerId=?', $arrBind['bind'], false);
        unset($arrBind);
        $result['affectedRows'] = $db->affected_rows();
        if ($result['affectedRows'] > 0) {
            $logger = \App::getInstance('logger');
            $logger->info(__METHOD__, $params);
        }

        return $result;
    }

    /**
     * 로그인한 매니저 유효성 검사 후 세션 설정
     *
     * @param array $data 세션에 담을 매니저 정보
     *
     * @throws \Exception
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function afterManagerLogin($data)
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');

        $data['lastLoginIp'] = $request->getRemoteAddress();
        // --- 관리자 테이블 갱신
        $arrBind = [];
        $arrUpdate[] = 'lastLoginDt = now()';
        $arrUpdate[] = 'lastLoginIp = \'' . $data['lastLoginIp'] . '\'';
        $arrUpdate[] = 'loginCnt = loginCnt + 1';

        if (array_key_exists('lastLoginAuthDt', $data) && $data['lastLoginAuthDt'] !== '0000-00-00 00:00:00') {
            $arrUpdate[] = 'lastLoginAuthDt = ?';
            $this->db->bind_param_push($arrBind, 's', $data['lastLoginAuthDt']);
        }
        $strWhere = 'sno = ? /* QID-bac519b1-533b-41b7-bb91-3cfe249d62fb */';
        $this->db->bind_param_push($arrBind, 'i', $data['sno']);
        /*
         * QID-bac519b1-533b-41b7-bb91-3cfe249d62fb
         * @작성자 yjwee 관리자 로그인 시 정보갱신 - 필수조건(sno)
         */
        $this->db->set_update_db(DB_MANAGER, $arrUpdate, $strWhere, $arrBind);
        unset($arrUpdate, $arrBind);

        // 관리자 세션 허용 시간 제한 (관리자 처리 없이 아래 시간까지 지난 경우 세션 아웃처리됨)
        $managerSecurity = ComponentUtils::getPolicy('manage.security');
        if ($managerSecurity['sessionLimitUseFl'] === 'y') {
            $sessionLimitTime = time() + $managerSecurity['sessionLimitTime'];
        } else {
            // 관리자 자동 로그아웃 사용 안해도 값을 저장하고 있어야 설정 사용 저장 시 로그아웃이 안됨
            $sessionLimitTime = time() + 21600;
        }
        $data['sessionLimitTime'] = $sessionLimitTime;
        $session->del(self::SESSION_TEMP_MANAGER);
        $session->set(self::SESSION_MANAGER_LOGIN, $data);

        $this->setManagerOldMenuAccessChange(); // 운영자 접속 권한 - 세부 권한으로 마이그레이션
        $this->setManagerFunctionAuthChange(); // 운영자 기능 권한 - 기능 권한으로 마이그레이션
        $this->setManagerScmFunctionAuthChange(); // 공급사 기능 권한 - 기능 권한으로 마이그레이션
        // 관리자 로그인 성공 로그
        $request->request()->set('adminLoginFl', true);
    }

    /**
     * setManagerOldMenuAccessChange
     * 운영자 세부 접속 권한 마이그레이션
     */
    public function setManagerOldMenuAccessChange()
    {
        $this->arrWhere[] = 'm.permissionMenu is null'; // DB null 이면 초기 데이터, null 문자 저장되면 신규 데이터
        $this->arrWhere[] = 'm.permissionFl = ?';
        $this->db->bind_param_push($this->arrBind, 's', 'l');
        $this->arrWhere[] = 'm.sno = ?';
        $this->db->bind_param_push($this->arrBind, 'i', Session::get('manager.sno'));

        $this->db->strField = "m.sno, m.scmNo, m.permissionMenu, m.permissionBase, m.permissionPolicy, m.permissionGoods, m.permissionDesign, m.permissionOrder, m.permissionMember, m.permissionBoard, m.permissionScm, m.permissionPromotion, m.permissionService, m.permissionMarketing, m.permissionStatistics, m.permissionMobile";
        $this->db->strWhere = implode(' AND ', $this->arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER . ' as m ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        if ($getData) {

            // 기존 접속권한 디비 테이블 필드
            $oldPermissionDbTable = [
                'base'       => 'permissionBase',
                'policy'     => 'permissionPolicy',
                'goods'      => 'permissionGoods',
                'design'     => 'permissionDesign',
                'order'      => 'permissionOrder',
                'member'     => 'permissionMember',
                'board'      => 'permissionBoard',
                'scm'        => 'permissionScm',
                'promotion'  => 'permissionPromotion',
                'service'    => 'permissionService',
                'marketing'  => 'permissionMarketing',
                'statistics' => 'permissionStatistics',
                'mobile'     => 'permissionMobile',
            ];

            $oldPermissionCenter = []; // 기존 본사 접속 권한
            $oldPermissionScm = []; // 기존 공급사 접속 권한
            unset($oldPermission);
            foreach ($oldPermissionDbTable as $permissionKey => $permissionVal) {
                if ($getData[0][$permissionVal]) {
                    ${$permissionKey} = explode(STR_DIVISION, $getData[0][$permissionVal]);
                    foreach (${$permissionKey} as $wordKey => $wordVal) {
                        $oldPermission[$permissionKey][] = substr($wordVal, 4);
                    }
                }
            }
            if ($getData[0]['scmNo'] == DEFAULT_CODE_SCMNO) {
                $oldPermissionCenter = $oldPermission;
            } else {
                $oldPermissionScm = $oldPermission;
            }

            $adminMenu = new AdminMenu();
            if (count($oldPermissionCenter) > 0) { // 본사 운영자
                $menuListCenter = $adminMenu->getAdminMenuList('d', null, true); // 본사 메뉴리스트

                foreach ($menuListCenter as $menuKey => $menuVal) {
                    if ($menuVal['depth'] == 3) {
                        $depth_3[$menuVal['fCode']][$menuVal['sCode']][] = $menuVal['tNo'];
                    } else if ($menuVal['depth'] == 2) {
                        $depth_2[$menuVal['fCode']][$menuVal['sCode']] = $menuVal['sNo'];
                    } else if ($menuVal['depth'] == 1) {
                        $depth_1[$menuVal['fCode']] = $menuVal['fNo'];
                    }
                }

                $access = [];
                foreach ($oldPermissionCenter as $oldKey => $oldVal) { // 기존 접근권한 loop
                    foreach ($oldVal as $key => $val) {

                        if (!in_array($depth_1[$oldKey], $access['permission_1'])) {
                            $access['permission_1'][] = $depth_1[$oldKey];
                        }
                        if (!in_array($depth_2[$oldKey][$val], $access['permission_2'][$depth_1[$oldKey]])) {
                            $access['permission_2'][$depth_1[$oldKey]][] = $depth_2[$oldKey][$val];
                            sort($access['permission_2'][$depth_1[$oldKey]]);
                        }
                        $access['permission_3'][$depth_2[$oldKey][$val]] = $depth_3[$oldKey][$val];
                        sort($access['permission_3'][$depth_2[$oldKey][$val]]);

                    }
                }
            } else if (count($oldPermissionScm) > 0) { // 공급사 운영자
                $menuListCenter = $adminMenu->getAdminMenuList('s', null, true); // 공급사 메뉴리스트

                foreach ($menuListCenter as $menuKey => $menuVal) {
                    if ($menuVal['depth'] == 3) {
                        $depth_3[$menuVal['fCode']][$menuVal['sCode']][] = $menuVal['tNo'];
                    } else if ($menuVal['depth'] == 2) {
                        $depth_2[$menuVal['fCode']][$menuVal['sCode']] = $menuVal['sNo'];
                    } else if ($menuVal['depth'] == 1) {
                        $depth_1[$menuVal['fCode']] = $menuVal['fNo'];
                    }
                }

                $access = [];
                foreach ($oldPermissionScm as $oldKey => $oldVal) { // 기존 접근권한 loop
                    foreach ($oldVal as $key => $val) {

                        if (!in_array($depth_1[$oldKey], $access['permission_1'])) {
                            $access['permission_1'][] = $depth_1[$oldKey];
                        }
                        if (!in_array($depth_2[$oldKey][$val], $access['permission_2'][$depth_1[$oldKey]])) {
                            $access['permission_2'][$depth_1[$oldKey]][] = $depth_2[$oldKey][$val];
                            sort($access['permission_2'][$depth_1[$oldKey]]);
                        }
                        $access['permission_3'][$depth_2[$oldKey][$val]] = $depth_3[$oldKey][$val];
                        sort($access['permission_3'][$depth_2[$oldKey][$val]]);

                    }
                }
            }

            sort($access['permission_1']);
            ksort($access['permission_2']);
            ksort($access['permission_3']);
            $arrData['permissionMenu'] = json_encode($access, JSON_UNESCAPED_UNICODE); // 운영자 접근 권한 설정

            $arrBind = $this->db->get_binding(DBTableField::tableManager(), $arrData, 'update', array_keys($arrData));
            $this->db->bind_param_push($arrBind['bind'], 'i', Session::get('manager.sno'));
            $this->db->set_update_db(DB_MANAGER, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            unset($arrData);
            unset($arrBind);
        }
    }

    /**
     * setManagerFunctionAuthChange
     * 운영자 기능 권한 마이그레이션
     */
    public function setManagerFunctionAuthChange()
    {
        $this->arrWhere[] = 'm.functionAuth is null'; // DB null 이면 초기 데이터, null 문자 저장되면 신규 데이터
        $this->arrWhere[] = 'm.permissionFl = ?';
        $this->db->bind_param_push($this->arrBind, 's', 'l');
        $this->arrWhere[] = 'm.sno = ?';
        $this->db->bind_param_push($this->arrBind, 'i', Session::get('manager.sno'));

        $this->db->strField = "m.sno, m.scmNo, m.functionAuth";
        $this->db->strWhere = implode(' AND ', $this->arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER . ' as m ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        if ($getData) {

            if ($getData['scmNo'] == DEFAULT_CODE_SCMNO) {
                $functionAuth = [
                    'functionAuth' => [
                        'goodsDelete'         => 'y',
                        'goodsExcelDown'      => 'y',
                        'goodsCommission'     => 'y',
                        'goodsNm'             => 'y',
                        'goodsSalesDate'      => 'y',
                        'goodsPrice'          => 'y',
                        'addGoodsCommission'  => 'y',
                        'addGoodsNm'          => 'y',
                        'orderState'          => 'y',
                        'orderExcelDown'      => 'y',
                        'orderBank'           => 'y',
                        'bankdaManual'        => 'y',
                        'orderReceiptProcess' => 'y',
                        'memberHack'          => 'y',
                        'memberExcelDown'     => 'y',
                        'boardDelete'         => 'y',
                    ],
                ];
            } else {
                $functionAuth = [
                    'functionAuth' => [
                        'goodsDelete'        => 'y',
                        'goodsExcelDown'     => 'y',
                        'goodsCommission'    => 'y',
                        'goodsNm'            => 'y',
                        'goodsSalesDate'     => 'y',
                        'goodsPrice'         => 'y',
                        'addGoodsCommission' => 'y',
                        'addGoodsNm'         => 'y',
                        'orderState'         => 'y',
                        'orderExcelDown'     => 'y',
                        'boardDelete'        => 'y',
                    ],
                ];
            }

            $arrData['functionAuth'] = json_encode($functionAuth, JSON_UNESCAPED_UNICODE); // 운영자 접근 권한 설정

            $arrBind = $this->db->get_binding(DBTableField::tableManager(), $arrData, 'update', array_keys($arrData));
            $this->db->bind_param_push($arrBind['bind'], 'i', Session::get('manager.sno'));
            $this->db->set_update_db(DB_MANAGER, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            unset($arrData);
            unset($arrBind);
        }
    }

    /**
     * setManagerScmFunctionAuthChange
     * 공급사 기능 권한 마이그레이션
     */
    public function setManagerScmFunctionAuthChange()
    {
        $this->arrWhere[] = 'sm.functionAuth is null'; // DB null 이면 초기 데이터, null 문자 저장되면 신규 데이터
        $this->arrWhere[] = 'sm.scmNo = ?';
        $this->db->bind_param_push($this->arrBind, 'i', Session::get('manager.scmNo'));

        $this->db->strField = "sm.scmNo, sm.functionAuth";
        $this->db->strWhere = implode(' AND ', $this->arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SCM_MANAGE . ' as sm ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        if ($getData) {

            if ($getData['scmNo'] == DEFAULT_CODE_SCMNO) {
                $functionAuth = [
                    'functionAuth' => [
                        'goodsDelete'         => 'y',
                        'goodsExcelDown'      => 'y',
                        'goodsCommission'     => 'y',
                        'goodsNm'             => 'y',
                        'goodsSalesDate'      => 'y',
                        'goodsPrice'          => 'y',
                        'addGoodsCommission'  => 'y',
                        'addGoodsNm'          => 'y',
                        'orderState'          => 'y',
                        'orderExcelDown'      => 'y',
                        'orderBank'           => 'y',
                        'bankdaManual'        => 'y',
                        'orderReceiptProcess' => 'y',
                        'memberHack'          => 'y',
                        'memberExcelDown'     => 'y',
                        'boardDelete'         => 'y',
                    ],
                ];
            } else {
                $functionAuth = [
                    'functionAuth' => [
                        'goodsDelete'        => 'y',
                        'goodsExcelDown'     => 'y',
                        'goodsCommission'    => 'y',
                        'goodsNm'            => 'y',
                        'goodsSalesDate'     => 'y',
                        'goodsPrice'         => 'y',
                        'addGoodsCommission' => 'y',
                        'addGoodsNm'         => 'y',
                        'orderState'         => 'y',
                        'orderExcelDown'     => 'y',
                        'boardDelete'        => 'y',
                    ],
                ];
            }

            $arrData['functionAuth'] = json_encode($functionAuth, JSON_UNESCAPED_UNICODE); // 운영자 접근 권한 설정

            $arrBind = $this->db->get_binding(DBTableField::tableScmManage(), $arrData, 'update', array_keys($arrData));
            $this->db->bind_param_push($arrBind['bind'], 'i', Session::get('manager.scmNo'));
            $this->db->set_update_db(DB_SCM_MANAGE, $arrBind['param'], 'scmNo = ?', $arrBind['bind']);
            unset($arrData);
            unset($arrBind);
        }
    }

    /**
     * sendSmsAuthNumber
     *
     * @param $managerInfo
     *
     * @return array
     * @throws \Exception
     */
    public function sendSmsAuthNumber($managerInfo)
    {
        $smsPoint = Sms::getPoint();
        if ($smsPoint >= 1) {
            if (!$managerInfo['cellPhone']) {
                throw new \Exception(__('관리자 보안을 위해 휴대폰 인증 로그인을 사용중입니다. 로그인하신 관리 아이디는 인증된 휴대폰번호가 없어 로그인할 수 없으니, 대표운영자에게 문의하시기 바랍니다.'));
            }

            // 인증번호 생성
            $adminSecuritySmsAuthNumber = Otp::getOtp(8);
            $contents = sprintf(__('관리자님 인증번호는 [%s] 입니다.'), $adminSecuritySmsAuthNumber);
            $receiver[]['cellPhone'] = $managerInfo['cellPhone'];
            $smsSender = \App::load(SmsSender::class);
            $smsSender->validPassword(\App::load(\Component\Sms\SmsUtil::class)->getPassword());
            $smsSender->setSmsPoint($smsPoint);
            $smsSender->setMessage(new SmsMessage($contents));
            $smsSender->setSmsType('user');
            $smsSender->setReceiver($receiver);
            $smsSender->setLogData(['disableResend' => true]);
            $smsSender->setContentsMask([$adminSecuritySmsAuthNumber]);
            $smsResult = $smsSender->send();
            $smsAuth['smsAuthNumber'] = $adminSecuritySmsAuthNumber;
            if ($smsResult['success'] === 1) {
                $smsAuth['message'] = 'OK';
            } else {
                $smsAuth['message'] = 'SMS Send Fail';
            }
        } else {
            $smsAuth['message'] = 'SMS Point Fail';
        }

        return $smsAuth;
    }

    /**
     * 관리자 인증 체크
     * 관리자 로그인 되어있는지를 체크를 하며, 잘못된 로그인 일경우 관리자 세션을 초기화함
     *
     * @param array $arrPage 로그인없이 접근 가능한 페이지들
     *
     * @return bool
     * @throws AlertRedirectException
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function setCertification($arrPage)
    {
        if (in_array(Request::getFileUri(), $arrPage) === true) {
            return true;
        }

        $managerLogin = true;

        if (Session::has('manager')) {
            // 관리자 세션 허용 시간 제한 (관리자 처리 없이 아래 시간까지 지난 경우 세션 아웃처리됨)
            $managerSecurity = gd_policy('manage.security');
            if ($managerSecurity['sessionLimitUseFl'] == 'y') {
                if (!Request::isAjax()) {
                    if (Session::get('manager.sessionLimitTime') <= time()) {
                        $this->managerLogout();
                        throw new AlertRedirectException(__('오랫동안 관리자 사용이 없어 보안을 위해 자동 로그아웃 합니다. 관리자를 사용하시려면 다시 로그인해주세요.'), null, null, URI_ADMIN . 'base/login.php', 'top');
                    } else {
                        $sessionLimitTime = time() + $managerSecurity['sessionLimitTime'];
                        Session::set('manager.sessionLimitTime', $sessionLimitTime);
                    }
                }
            } else {
                // 관리자 자동 로그아웃 사용 안해도 값을 저장하고 있어야 설정 사용 저장 시 로그아웃이 안됨
                $sessionLimitTime = time() + 21600;
                Session::set('manager.sessionLimitTime', $sessionLimitTime);
            }

            if (Session::get('manager') === null) {
                $managerLogin = false;
            }

            if (Session::get('manager.managerId') === null) {
                $managerLogin = false;
            }

            if (Session::get('manager.scmNo') === null || Session::get('manager.scmNo') == 0) {
                $managerLogin = false;
            }
        } else {
            $managerLogin = false;
        }
        if ($managerLogin === true) {
            // --- 관리자 존재 여부 체크
            $arrBind = [];
            $memPw = Encryptor::decrypt(Session::get('manager.managerPw'));
            $this->db->strField = 'managerId, permissionFl, permissionMenu';
            $this->db->strWhere = 'employeeFl != \'r\' AND managerId = ? AND managerPw = ? AND isDelete = \'n\' ';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['managerId'], Session::get('manager.managerId'));
            $this->db->bind_param_push($arrBind, $this->fieldTypes['managerPw'], $memPw);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $arrBind, false);

            // --- 정보가 없는 경우
            if (empty($data) || empty($data['managerId'])) {
                $managerLogin = false;
                // --- 정보가 있는 경우 권한 체크
            } else {
                // 로그인 페이지인 경우 공급사구분에 따라 공급사 관리자 혹은 수퍼관리자 페이지로 강제 이동 처리
                if (Request::getFileUri() == 'login.php' && Request::getDirectoryUri() == 'base') {
                    switch (Session::get('manager.scmNo')) {
                        // 본사로 로그인한 경우 본사 관리자로 이동
                        case DEFAULT_CODE_SCMNO:
                            throw new AlertRedirectException(null, null, null, URI_ADMIN . 'base/index.php', 'parent');
                            break;

                        // 공급사로 로그인한 경우 공급사 관리자로 이동
                        default:
                            exit;
                            throw new AlertRedirectException(null, null, null, URI_PROVIDER . 'index.php', 'parent');
                            break;
                    }
                }

                // share 폴더가 아닌 경우 처리
                // 공급사로 로그인시 provider 폴더 이외로 접근시 강제 이동 처리
                if (gd_is_provider() && !AdminMenu::isProviderDirectory()) {
                    if (!AdminMenu::isAdminShareDirectory()) {
                        throw new AlertRedirectException(null, null, null, URI_PROVIDER . 'index.php');
                    }
                }

                // 본사로 로그인시 provider 폴더로 접근시 강제 이동 처리 (혹시 모르는 상황일때를 위해 이중 처리함)
                if (!gd_is_provider() && AdminMenu::isProviderDirectory()) {
                    throw new AlertRedirectException(null, null, null, URI_ADMIN . 'index.php');
                }
            }
        }
        if ($managerLogin === false) {
            $arrPage = ['login.php'];
            if (in_array(Request::getFileUri(), $arrPage) === false) {
                $this->managerLogout();
                throw new AlertRedirectException(null, null, null, URI_ADMIN . 'base/login.php', 'top');
            }
        }
    }

    /**
     * 관리자 로그아웃
     */
    public function managerLogout()
    {
        $session = \App::getInstance('session');
        $cookie = \App::getInstance('cookie');

        // 관리자 로그아웃 로그 작성
        $adminLog = \App::load('Component\\Admin\\AdminLogDAO');
        $adminLog->setAdminLog();

        // 세션을 없애기전에 세션 쿠키를 지웁니다.
        if ($cookie->has($session->getName())) {
            $cookie->set($session->getName(), '', time() - 42000, '/');
        }

        // 관리자만 로그아웃하는 경우 세션 클리어
        if (MemberUtil::isLogin()) {
            $session->del(self::SESSION_MANAGER_LOGIN);
            $session->del(\Component\Excel\ExcelForm::SESSION_SECURITY_AUTH);
        } else {
            $session->clear();
        }
    }

    /**
     * 관리자앱 관리자 인증 체크
     * 관리자 로그인 되어있는지를 체크를 하며, 잘못된 로그인 일경우 관리자 세션을 초기화함
     *
     * @param array $arrPage 로그인없이 접근 가능한 페이지들
     *
     * @return bool
     * @throws AlertRedirectException
     */
    public function setCertificationMobileapp($arrPage)
    {
        if (in_array(Request::getFileUri(), $arrPage) === true) {
            return true;
        }

        $managerLogin = true;

        if (Session::has('manager')) {
            // 관리자 자동 로그아웃 사용 안해도 값을 저장하고 있어야 설정 사용 저장 시 로그아웃이 안됨
            $sessionLimitTime = time() + 21600;
            Session::set('manager.sessionLimitTime', $sessionLimitTime);

            if (Session::get('manager') === null) {
                $managerLogin = false;
            }

            if (Session::get('manager.managerId') === null) {
                $managerLogin = false;
            }

            if (Session::get('manager.scmNo') === null || Session::get('manager.scmNo') == 0) {
                $managerLogin = false;
            }
        } else {
            $managerLogin = false;
        }
        if ($managerLogin === true) {
            // --- 관리자 존재 여부 체크
            $arrBind = [];
            $memPw = Encryptor::decrypt(Session::get('manager.managerPw'));
            $this->db->strField = 'managerId, permissionFl, permissionMenu';
            $this->db->strWhere = 'employeeFl != \'r\' AND managerId = ? AND managerPw = ? AND isDelete = \'n\' ';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['managerId'], Session::get('manager.managerId'));
            $this->db->bind_param_push($arrBind, $this->fieldTypes['managerPw'], $memPw);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $arrBind, false);

            // --- 정보가 없는 경우
            if (empty($data) || empty($data['managerId'])) {
                $managerLogin = false;
                // --- 정보가 있는 경우 권한 체크
                throw new AlertRedirectException('잘못된 접근입니다.', null, null, 'https://mobileapp.godo.co.kr/new2/app/login.php', null);
            }
        }
    }

    /**
     * getManagerName
     *
     * @return array
     */
    public function getManagerName()
    {
        $arrData = $this->getAllManager(
            [
                'sno',
                'managerNm',
            ]
        );

        $result = [];
        foreach ($arrData as $index => $item) {
            $result[$item['sno']] = $item['managerNm'];
        }

        return $result;
    }

    /**
     * 전체 관리자 정보
     *
     * @param array $fields 필드명
     */
    public function getAllManager($fields)
    {
        $strSQL = 'SELECT ' . implode(',', $fields) . ' FROM ' . DB_MANAGER;
        $data = $this->db->query_fetch($strSQL, null);

        return $data;
    }

    /**
     * getManagerSmsAuthCheck
     * 핸드폰 인증한 본사 운영자의 수
     *
     * @return integer 본사 운영자의 핸드폰 인증한 운영자의 수
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getManagerSmsAuthCheck()
    {
        $strSQL = 'SELECT count(scmNo) as smsAuthCount FROM ' . DB_MANAGER . ' WHERE scmNo = ' . DEFAULT_CODE_SCMNO . ' AND isSmsAuth = \'y\'';
        $data = $this->db->query_fetch($strSQL, null, false);

        return $data['smsAuthCount'];
    }

    public function changePassword($oldPassword, $password)
    {
        $type = Request::post()->get('type');

        $this->validatePassword($password);

        if ($type == 'reset') {
            $managerSession['managerId'] = Request::post()->get('managerId');
        } else {
            $managerSession = Session::get(self::SESSION_MANAGER_LOGIN);
        }
        if ($type != 'reset' && Digester::isValid(Encryptor::decrypt($managerSession['managerPw']), $oldPassword) == false) {
            if ($type != 'reset' && !Password::verify($oldPassword, Encryptor::decrypt($managerSession['managerPw']))) {
                throw new Exception(__('입력하신 현재 비밀번호가 정확하지 않습니다. 확인 후 다시 입력하시기 바랍니다.'));
            }
        }

        $arrBind = [];
        $query = "SELECT sno, managerPw FROM " . DB_MANAGER . " where managerId = ?";
        $this->db->bind_param_push($arrBind, $this->fieldTypes['managerId'], $managerSession['managerId']);
        $managerData = $this->db->query_fetch($query, $arrBind, false);
        $managerData = gd_htmlspecialchars_stripslashes($managerData);

        // 관리자 비밀번호 재사용 제한 : 체크
        if (method_exists($this, 'getBeforePassword') === true) {
            $beforePasswords = $this->getBeforePassword($password, $managerData['managerPw'], $managerData['sno']);
            if ($beforePasswords === true) {
                throw new Exception(__('이전에 사용한 비밀번호는 다시 사용하실 수 없습니다. 다른 비밀번호를 입력해 주세요.'));
            }
        }

        if ($oldPassword == $password) {
            throw new Exception(__('현재 비밀번호와 동일한 비밀번호로 변경할 수 없습니다.'));
        }
        if(GodoUtils::sha256Fl()) {
            $hashPassword = Digester::digest($password);
        } else {
            $hashPassword = Password::hash($password);
        }
        $passwordDt = new \DateTime();
        $passwordDtFormat = $passwordDt->format('Y-m-d H:i:s');
        $arrData = [
            'managerPw'        => $hashPassword,
            'managerId'        => $managerSession['managerId'],
            'changePasswordDt' => $passwordDtFormat,
            'guidePasswordDt'  => $passwordDtFormat,
        ];

        $includes = [
            'managerPw',
            'changePasswordDt',
            'guidePasswordDt',
        ];
        $arrBind = $this->db->get_binding(DBTableField::tableManager(), $arrData, 'update', $includes);

        $this->db->bind_param_push($arrBind['bind'], 's', $arrData['managerId']);
        $result = $this->db->set_update_db(DB_MANAGER, $arrBind['param'], 'managerId = ? AND isDelete = \'n\'  ', $arrBind['bind']);

        // 관리자 비밀번호 재사용 제한 : 이전비밀번호목록 저장
        if ($result > 0 && method_exists($this, 'saveBeforePassword') === true) {
            if (is_array($beforePasswords)) {
                $this->saveBeforePassword($beforePasswords, $managerData['sno']);
            }
        }

        if ($result > 0 && $type != 'reset') {
            Manager::managerLogout();
        }
    }

    public function validatePassword($password)
    {
        if (Validator::required($password) === false) {
            throw new \Exception(__('입력하신 비밀번호가 형식에 맞지 않습니다. 비밀번호는 영문대문자/영문소문자/숫자/특수문자 중 2가지 이상 조합, 10~16자리 이하로 설정하셔야 합니다.'));
        } else {
            if (Validator::password($password, true) === false) {
                throw new \Exception(__('입력하신 비밀번호가 형식에 맞지 않습니다. 비밀번호는 영문대문자/영문소문자/숫자/특수문자 중 2가지 이상 조합, 10~16자리 이하로 설정하셔야 합니다.'));
            }
        }
    }

    public function changePasswordLater()
    {
        $managerSession = Session::get(self::SESSION_MANAGER_LOGIN);
        $passwordDt = new \DateTime();
        $passwordDtFormat = $passwordDt->format('Y-m-d H:i:s');
        $arrData = [
            'managerId'       => $managerSession['managerId'],
            'guidePasswordDt' => $passwordDtFormat,
        ];

        $includes = [
            'guidePasswordDt',
        ];
        $arrBind = $this->db->get_binding(DBTableField::tableManager(), $arrData, 'update', $includes);

        $this->db->bind_param_push($arrBind['bind'], 's', $arrData['managerId']);
        $result = $this->db->set_update_db(DB_MANAGER, $arrBind['param'], 'managerId = ? AND isDelete = \'n\'  ', $arrBind['bind']);

        if ($result > 0) {
            Session::set(self::SESSION_MANAGER_LOGIN . '.guidePasswordDt', $passwordDtFormat);
        }
    }

    public function getManagerAuthData()
    {
        $strSQL = 'SELECT sno, isSmsAuth, isEmailAuth, cellPhone, managerId FROM ' . DB_MANAGER . ' WHERE scmNo = ' . DEFAULT_CODE_SCMNO . ' AND isSuper = \'y\' ';
        if (Session::get('manager.scmKind') == 'c' && Session::get('manager.isSuper') == 'y') {
            $strSQL .= ' AND sno=' . Session::get('manager.sno');
        }
        $strSQL .= ' order by regDt asc limit 1';
        $data = $this->db->query_fetch($strSQL, null, false);

        return $data;
    }

    /**
     * getManagerPermissionMenu
     * 운영자 권한 설정 - 접근권한
     *
     * @param $sno
     *
     * @return mixed
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getManagerPermissionMenu($sno)
    {
        $arrBind = [];
        $query = "SELECT permissionFl, permissionMenu FROM " . DB_MANAGER . " where sno = ?";
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $data = $this->db->query_fetch($query, $arrBind, false);

        // 운영자 접근 권한 설정
        if ($data['permissionFl'] == 'l') { // 운영권한 - 권한선택
            $permission = json_decode($data['permissionMenu'], true);
        } else {
            $permission = 'all';
        }

        return $permission;
    }

    /**
     * getManagerWriteEnabledMenu
     * 운영자 권한 설정 - 쓰기권한
     *
     * @param $sno
     *
     * @return mixed
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getManagerWriteEnabledMenu($sno)
    {
        $arrBind = [];
        $query = "SELECT permissionFl, writeEnabledMenu FROM " . DB_MANAGER . " where sno = ?";
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $data = $this->db->query_fetch($query, $arrBind, false);

        // 운영자 접근 권한 설정
        if ($data['permissionFl'] == 'l') { // 운영권한 - 권한선택
            $permission = json_decode($data['writeEnabledMenu'], true);
        } else {
            $permission = 'all';
        }

        return $permission;
    }

    /**
     * getManagerFunctionAuth
     * 운영자 권한 설정 - 기능권한
     *
     * @param $sno
     *
     * @return mixed
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getManagerFunctionAuth($sno)
    {
        $arrBind = [];
        $query = "SELECT permissionFl, functionAuth FROM " . DB_MANAGER . " where sno = ?";
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $data = $this->db->query_fetch($query, $arrBind, false);

        // 운영자 기능 권한 설정
        $functionAuth = json_decode($data['functionAuth'], true);
        if ($data['permissionFl'] == 's') { // 운영권한 - 전체권한
            $functionAuth['permissionRange'] = 'all';
        }

        return $functionAuth;
    }

    /**
     * 관리자 용량 체크
     *
     * @param boolean $reCheck 용량 재체크 여부
     *
     * @return array $diskData 사용디스크용량 정보
     */
    public function adminDiskCheck($reCheck = false)
    {
        $rentalDisk = \App::load('\\Component\\Mall\\RentalDisk');
        $diskData = $rentalDisk->diskUsage();
        $diskData['adminAccess'] = true;

        if ($diskData['usedPer'] == 100) {
            if ($reCheck === true) {
                if ($diskData['fullLimitDate'] > $rentalDisk::DISK_LIMIT_DATE) {
                    // 사용량이 100%이고 체크파일 생성기간이 3일 이상일 경우 관리자 로그인 불가
                    $diskData['adminAccess'] = false;
                }
            } else {
                $rentalDisk->setDu('all');

                return self::adminDiskCheck(true);
            }

        }

        return $diskData;
    }

    /**
     * 운영자 비밀번호 찾기 ID 비교
     *
     * @param string $managerId 입력한 id
     *
     * @return bool
     */
    public function compareManagerId($managerId)
    {
        $result = false;
        $data = $this->getSpecificManagerInfo(['sno' => '1'], 'managerId');

        if ($managerId == $data['managerId']) {
            $result = true;
        }

        return $result;
    }

    /**
     * 운영자 정보 조회
     *
     * @param mixed   $where     조건절
     * @param string  $column    조회할 컬럼
     * @param boolean $dataArray 반환되는 데이터 배열여부
     * @param string  $prefix    테이블 별칭
     *
     * @return array|null|object 테이블 조회 결과
     */
    public function getSpecificManagerInfo($where = null, $column = '*', $dataArray = false, $prefix = null)
    {
        return $this->db->getData(DB_MANAGER, array_values($where), array_keys($where), $column, $dataArray, $prefix);
    }

    /**
     * saveMemo
     *
     * @param array $manager
     * @param array $params
     *
     * @return bool
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function saveMemo(array $manager, array $params)
    {
        if ($manager['managerSno'] < 1 && $manager['scmNo'] < 1) {
            return false;
        }
        $currentMemo = $this->getMemo($manager);
        $currentMemoSno = 0;
        if ($params['code'] == 'save') {
            if ($params['viewAuth'] == 'all') {
                $params['typeFl'] = 'all';
                if (key_exists('all', $currentMemo)) {
                    $currentMemoSno = $currentMemo['all']['sno'];
                }
            } elseif ($params['viewAuth'] == 'self') {
                $params['typeFl'] = 'self';
                if (key_exists('self', $currentMemo)) {
                    $currentMemoSno = $currentMemo['self']['sno'];
                }
            }
        } elseif ($params['code'] == 'viewAuth' || $params['code'] == 'isVisible') {
            $currentMemoSno = $currentMemo['self']['sno'];
        } else {
            return false;
        }
        $db = \App::getInstance('DB');
        if ($currentMemoSno > 0) {
            $exclude = ['typeFl'];
            if (Validator::yn($params['isVisible'], true)) {
                $updateParams['isVisible'] = $params['isVisible'];
            }
            if (Validator::pattern('/^(self|all)$/', $params['viewAuth'], true)) {
                $updateParams['viewAuth'] = $params['viewAuth'];
            }
            if ($params['code'] == 'save') {
                $updateParams['contents'] = StringUtils::htmlSpecialChars($params['memo'][$params['viewAuth']]);
            }
            $updateParams['managerSno'] = $manager['sno'];
            $updateParams['scmNo'] = $manager['scmNo'];
            $binds = $db->get_binding(DBTableField::tableManagerMemo(), $updateParams, 'update', array_keys($updateParams), $exclude);
            $where = 'sno=?';
            $db->bind_param_push($binds['bind'], 'i', $currentMemoSno);
            $db->set_update_db(DB_MANAGER_MEMO, $binds['param'], $where, $binds['bind']);
            unset($updateParams, $currentMemo, $binds);

            return $db->affected_rows() > 0;
        } else {
            $insertParams = [];
            $insertParams['isVisible'] = $params['isVisible'];
            if (Validator::pattern('/^(self|all)$/', $params['typeFl'])) {
                $insertParams['typeFl'] = $params['typeFl'];
            }
            $insertParams['viewAuth'] = $params['viewAuth'];
            $insertParams['contents'] = StringUtils::htmlSpecialChars($params['memo'][$params['viewAuth']]);
            $insertParams['managerSno'] = $manager['sno'];
            $insertParams['scmNo'] = $manager['scmNo'];
            $binds = $db->get_binding(DBTableField::tableManagerMemo(), $insertParams, 'insert');
            $db->set_insert_db(DB_MANAGER_MEMO, $binds['param'], $binds['bind'], 'y');
            unset($insertParams, $currentMemo, $binds);

            return $db->insert_id() > 0;
        }
    }

    /**
     * getMemo
     *
     * @param array $manager
     *
     * @return array
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getMemo(array $manager)
    {
        $tableFieldTypes = DBTableField::getFieldTypes(DBTableField::getFuncName(DB_MANAGER_MEMO));
        $db = \App::getInstance('DB');
        $db->strField = '*';
        $db->strWhere = '(managerSno = ? AND typeFl=\'self\') OR (scmNo = ? AND viewAuth = \'all\' AND typeFl=\'all\')';
        $db->bind_param_push($binds, $tableFieldTypes['managerSno'], $manager['sno']);
        $db->bind_param_push($binds, $tableFieldTypes['scmNo'], $manager['scmNo']);
        $query = $db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER_MEMO . implode(' ', $query);
        $resultSet = $this->db->query_fetch($strSQL, $binds);

        // 날짜 체크 및 new 아이콘
        $fixedDate = 3 * 24 * 60 * 60; // new 아이콘 기준 3일
        $keys = array_keys($resultSet);
        foreach ($resultSet as $index => &$item) {
            $intervalDay = DateTimeUtils::intervalDay($item['modDt'], null, 'sec');
            $item['isChanged'] = ((empty($item['contents']) === false) && ($intervalDay > 0 && $intervalDay <= $fixedDate)) ? 'y' : 'n';
            $resultSet[$item['typeFl']] = $item;
            if (in_array($index, $keys, true)) {
                unset($resultSet[$index]);
            }
        }

        return StringUtils::htmlSpecialCharsStripSlashes($resultSet);
    }

    /**
     * @param bool $isRequireAuthentication
     */
    public function setIsRequireAuthentication(bool $isRequireAuthentication)
    {
        $this->isRequireAuthentication = $isRequireAuthentication;
    }

    /**
     * 관리자 보안로그인 인증 유효기간 저장
     *
     * @param $tmpManager
     * @deprecated
     */
    public function setLoginAuthCookie($tmpManager)
    {
        $cookie = \App::getInstance('cookie');
        $encryptor = \App::getInstance('encryptor');
        $loginAuthCookie = $cookie->get(self::COOKIE_LOGIN_AUTH, []);
        $loginAuthCookie = $encryptor->decryptJson($loginAuthCookie);
        $loginAuthCookie = json_decode($loginAuthCookie, true);
        $securityPolicy = \App::load(ManageSecurityPolicy::class);
        if ($securityPolicy->useSecurityLogin()
            && $securityPolicy->hasAuthenticationLoginPeriod()) {
            $loginAuthCookie[$tmpManager['sno']] = [
                'managerId' => $tmpManager['managerId'],
                'date'      => $securityPolicy->getValidAuthDate(),
            ];
            $loginAuthCookie = json_encode($loginAuthCookie);
            $loginAuthCookie = $encryptor->encryptJson($loginAuthCookie);
            $cookie->set(self::COOKIE_LOGIN_AUTH, $loginAuthCookie);
        }
    }

    /**
     * 관리자 로그인 시 IP 체크 하는 함수
     *
     * @param $remoteIp
     * @param $manageSecurity
     */
    protected function validateIpByLogin($remoteIp, $manageSecurity)
    {
        $chkIpAdminSecurity = false;
        // 고도 아이피
        $godoIp = \App::getConfig('host.godoip');
        $ipArea = $godoIp->getIparea();
        foreach ($ipArea as $godoIpKey => $godoIpVal) {
            $godoIpRange = explode('-', $godoIpVal);
            if ($godoIpRange[1]) {
                $godoIpArr = explode('.', $godoIpRange[0]);
                for ($i = $godoIpArr[3]; $i <= $godoIpRange[1]; $i++) {
                    $checkGodoIp = $godoIpArr[0] . '.' . $godoIpArr[1] . '.' . $godoIpArr[2] . '.' . $i;
                    if ($checkGodoIp === $remoteIp) {
                        $chkIpAdminSecurity = true;
                        break;
                    }
                }
            }
        }
        // 관리자 등록 아이피
        foreach ($manageSecurity['ipAdmin'] as $ipKey => $ipVal) {
            $ipAdmin = implode('.', $ipVal);
            if ($ipAdmin === $remoteIp) {
                $chkIpAdminSecurity = true;
                break;
            }

            //대역 IP가 설정되어 있을 경우
            if (trim($manageSecurity['ipAdminBandWidth'][$ipKey]) !== '') {
                $ipAdminClass_C = preg_replace('/\.\d{1,3}$/', '', $ipAdmin);
                $ipRemoteClass_C = preg_replace('/\.\d{1,3}$/', '', $remoteIp);
                //설정한 IP가 4자리이고, C class 까지의 아이피가 동일한 경우 대역 비교
                if ($ipAdminClass_C === $ipRemoteClass_C && \count($ipVal) === 4) {
                    $remoteIPArray = explode('.', $remoteIp);
                    //고객의 D class IP가 설정한 IP보다 같거나 크거나, 대역IP보다 같거나 작을때
                    if ((int) $remoteIPArray[3] >= (int) $ipVal[3]
                        && (int) $remoteIPArray[3] <= $manageSecurity['ipAdminBandWidth'][$ipKey]) {
                        $chkIpAdminSecurity = true;
                        break;
                    }
                }
            }
        }

        // 관리자앱 IP 체크 하지 않음
        if (stripos(\Request::getDirectoryUri(), 'mobileapp') !== false) {
            $chkIpAdminSecurity = true;
        }

        if ($chkIpAdminSecurity === false) {
            // 고도 아이피
            $godoIp = \App::getConfig('host.godoip');
            $ipArea = $godoIp->getIparea();
            foreach ($ipArea as $godoIpKey => $godoIpVal) {
                $godoIpRange = explode('-', $godoIpVal);
                if ($godoIpRange[1]) {
                    $godoIpArr = explode('.', $godoIpRange[0]);
                    for ($i = $godoIpArr[3]; $i <= $godoIpRange[1]; $i++) {
                        $checkGodoIp = $godoIpArr[0] . '.' . $godoIpArr[1] . '.' . $godoIpArr[2] . '.' . $i;
                        if ($checkGodoIp === $remoteIp) {
                            $chkIpAdminSecurity = true;
                            break;
                        }
                    }
                } elseif ($godoIpRange[0] === $remoteIp) {
                    $chkIpAdminSecurity = true;
                    break;
                }
            }
        }
        if (!$chkIpAdminSecurity) {
            throw new \RuntimeException(__('접속 가능한 IP가 아니므로 관리자에 접속할 수 없습니다.'));
        }
    }

    /**
     * 관리자 비밀번호 재사용 제한
     * @param $newPw 신규 비밀번호
     * @param $nowPw 지금 비밀번호
     * @param $managerSno 관리자번호
     * @return array|bool|null
     */
    public function getBeforePassword($newPw, $nowPw, $managerSno)
    {
        $beforeNums = 3;
        $beforePws = [];
        if (empty($managerSno) === true) {
            return null;
        }

        // 이전 비밀번호 재정의 : 지금 비밀번호 병합
        $beforePws[] = ['pw' => $nowPw, 'sno' => '', 'type' => 'in'];

        // 이전 비밀번호 재정의 : 이전 비밀번호 병합
        $strSQL = "SELECT sno, managerPw FROM " . DB_MANAGER_BEFORE_PASSWORDS . " WHERE managerSno = '" . $managerSno . "' ORDER BY sno DESC LIMIT 0," . ($beforeNums - 1);
        $result = $this->db->query($strSQL);
        while ($data = $this->db->fetch($result)) {
            $beforePws[] = ['pw' => $data['managerPw'], 'sno' => $data['sno'], 'type' => ''];
        }

        // 신규 비밀번호와 이전 비밀번호 비교 : 이전 비밀번호 재사용이면 true 리턴
        foreach ($beforePws as $k => $data) {
            if (Password::verify($newPw, $data['pw']) || Digester::isValid($data['pw'], $newPw)) {
                return true;
            };
        }

        // 이전 비밀번호 재정의 : 3개이면 삭제 대상으로 정의
        if (count($beforePws) == $beforeNums) {
            $beforePws[($beforeNums - 1)]['type'] = 'out';
        }

        // 이전 비밀번호 리턴
        return $beforePws;
    }

    /**
     * 이전비밀번호목록 갱신
     * @param $beforePws 이전비밀번호목록
     * @param $managerSno 관리자번호
     * @return void
     */
    public function saveBeforePassword($beforePws, $managerSno)
    {
        if (is_array($beforePws) === false) {
            return;
        }
        if (empty($managerSno) === true) {
            return;
        }
        foreach ($beforePws as $data) {
            if ($data['type'] == 'in') {
                $arrBind = [];
                $query = "INSERT INTO " . DB_MANAGER_BEFORE_PASSWORDS . "(managerSno, managerPw) VALUES(?,?)";
                $this->db->bind_param_push($arrBind, 'i', $managerSno);
                $this->db->bind_param_push($arrBind, 's', $data['pw']);
                $this->db->bind_query($query, $arrBind);
            } else if ($data['type'] == 'out') {
                $arrBind = [];
                $this->db->bind_param_push($arrBind, 'i', $managerSno);
                $this->db->bind_param_push($arrBind, 's', $data['sno']);
                $this->db->set_delete_db(DB_MANAGER_BEFORE_PASSWORDS, 'managerSno = ? and sno <= ?', $arrBind);
            }
        }
    }

    /**
     * 로그 - 동일 IP 로그인 접속 시도 저장
     *
     * @param string $ip 로그인시도 접속 IP
     *
     * @return bool
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    protected function setLogIpLoginTry($ip)
    {
        $manageSecurity = gd_policy('manage.security');
        $chkIpLoginTryAdmin = false;

        // 관리자 IP 접근시도 예외 등록 아이피
        foreach ($manageSecurity['ipLoginTryAdmin'] as $ipKey => $ipVal) {
            $ipLoginTryAdmin = implode('.', $ipVal);
            if ($ipLoginTryAdmin === $ip) {
                $chkIpLoginTryAdmin = true;
                break;
            }

            //대역 IP가 설정되어 있을 경우
            if (trim($manageSecurity['ipLoginTryAdminBandWidth'][$ipKey]) !== '') {
                $ipAdminClass_C = preg_replace('/\.\d{1,3}$/', '', $ipLoginTryAdmin);
                $ipRemoteClass_C = preg_replace('/\.\d{1,3}$/', '', $ip);
                //설정한 IP가 4자리이고, C class 까지의 아이피가 동일한 경우 대역 비교
                if ($ipAdminClass_C === $ipRemoteClass_C && \count($ipVal) === 4) {
                    $remoteIPArray = explode('.', $ip);
                    //고객의 D class IP가 설정한 IP보다 같거나 크거나, 대역IP보다 같거나 작을때
                    if ((int) $remoteIPArray[3] >= (int) $ipVal[3]
                        && (int) $remoteIPArray[3] <= $manageSecurity['ipLoginTryAdminBandWidth'][$ipKey]) {
                        $chkIpLoginTryAdmin = true;
                        break;
                    }
                }
            }
        }

        // 관리자 IP 접근시도 예외 등록 아이피 설정시에는 사용안하도록 추가
        if ($chkIpLoginTryAdmin === true) {
            return;
        }

        $adminLog = \App::load('Component\\Admin\\AdminLogDAO');
        $logIpLoginTryInfo = $adminLog->selectLogIpLoginTry($ip, 'N'); // 마지막 로그인 실패 시간
        $isContinuousLoginAttempts = (empty($logIpLoginTryInfo) === false) ? $this->isContinuousLoginAttempts($logIpLoginTryInfo['loginFailDt']) : false;
        $getOneMinLoginFailCount = $adminLog->getOneMinLoginFailCount(Request::getRemoteAddress()); // 1분전 로그인 실패 횟수
        $now = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');

        $data['loginFailIp'] = $ip;
        $data['loginFailDt'] = $now;
        $data['limitFlag'] = ($isContinuousLoginAttempts || $getOneMinLoginFailCount > 10) ? 'Y' : 'N';
        $data['loginType'] = 'admin';

        // 현재 접속제한 상태인 경우에는 저장안함
        $isLimitFlag = $adminLog->selectLogIpLoginTry($ip, 'Y');
        if ($isLimitFlag['limitFlag'] === 'Y') {
            if ($this->isCheckLoginTimeout($isLimitFlag['onLimitDt'])) {
                return;
            }
        }

        // 동일 IP 연속 로그인 시도(실패)에 대한 제한시간 설정
        if ($isContinuousLoginAttempts || $getOneMinLoginFailCount > 10) {
            $data['onLimitDt'] = $now;
        }
        $arrBind = $this->db->get_binding(DBTableField::tableLogIpLoginTry(), $data, 'insert', array_keys($data));
        $this->db->set_insert_db(DB_LOG_IPLOGINTRY, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);

        $request = \App::getInstance('request');
        $logData = $data;
        $logData['referer'] = $request->getReferer();
        $logData['user_agent'] = $request->getUserAgent();
        \Logger::channel('adminLoginTry')->info('admin login try', $logData);

        return true;
    }

    /**
     * 동일 IP 연속 로그인 시도(실패)한 이전 시간이 1초 미만 인지 확인
     *
     * @param string $loginFailDt 로그 - 동일 IP 접속시도(실패) 데이터
     *
     * @return bool true 1초 미만, false 1초 이상
     */
    protected function isContinuousLoginAttempts($loginFailDt)
    {
        $interval = DateTimeUtils::intervalDay($loginFailDt, DateTimeUtils::dateFormat('Y-m-d G:i:s', 'now'), 'sec');

        return $interval < 1;
    }

    /**
     * 동일 IP 연속 접근에 대한 로그인 제한시간 15분이 지났는지 체크
     *
     * @param string $onLimitDt 로그인 제한 시간 (마지막 접속)
     *
     * @return bool true 15분 미만, false 15분 이상
     */
    protected function isCheckLoginTimeout($onLimitDt)
    {
        $interval = DateTimeUtils::intervalDay($onLimitDt, DateTimeUtils::dateFormat('Y-m-d G:i:s', 'now'), 'min');

        return $interval < 15;
    }
}
