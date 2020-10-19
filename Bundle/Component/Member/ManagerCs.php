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


use Component\Database\DBTableField;
use Component\Validator\Validator;
use Framework\Debug\Exception\DatabaseException;
use Framework\Security\Digester;
use Framework\Security\Otp;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\GodoUtils;
/**
 * Class ManagerCs
 * @package Bundle\Component\Member
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class ManagerCs extends \Component\Member\Manager
{
    /** @var string CS 용 계정 아이디 프리픽스 */
    const PREFIX_CS_ID = 'G5CS_';

    /** @var string CS계정 수동생성 용 아이디 프리픽스 */
    const PREFIX_M_CS_ID = 'G5MCS_';

    /** @var int 생성 시 유효일 당일포함 15일 */
    protected $createValidDay = 14;

    /** @var int 인증 시 유효일 당일포함 3일 */
    protected $authenticationValidDay = 2;

    /**
     * CS 계정 생성
     *
     * @param array $permissions CS 계정 권한 설정 정보
     * @param int   $scmNo       CS 계정이 생성될 공급사번호
     *
     * @return int
     */
    public function createManagerCs($permissions, $scmNo, $mCsAccount = null)
    {
        if ($scmNo < 0) {
            throw new \InvalidArgumentException('공급사 정보를 찾을 수 없습니다.');
        }

        if ($scmNo > 1 && !parent::useProvider()) {
            throw new \InvalidArgumentException('공급사를 사용하지 않는 경우 공급사 CS 계정을 생성할 수 없습니다.');
        }

        if ($permissions['permissionFl'] !== 'all' && !(\is_array($permissions['permissionMenu'])
                && \count($permissions['permissionMenu']) > 0)) {
            throw new \InvalidArgumentException('설정된 접근권한이 없습니다.');
        }

        if ($this->hasCs($scmNo)) {
            throw new \RuntimeException('이미 CS 계정이 생성된 공급사입니다.');
        }

        try {
            if ($permissions['permissionFl'] === 'all') {
                $permissions['permissionMenu'] = $permissions['functionAuth'] = [];
            }

            $csPw = $this->generatePassword();
            // cs 수동 생성 계정값
            if($mCsAccount['createType'] == 'm'){
                $managerCsId = self::PREFIX_M_CS_ID . $mCsAccount['csId'];
                if(GodoUtils::sha256Fl()) {
                    $managerCsPw = Digester::digest($mCsAccount['csPw']);
                } else {
                    $managerCsPw = \App::getInstance('password')->hash($mCsAccount['csPw']);
                }
            }else{
                $managerCsId = self::PREFIX_CS_ID . time();
                if(GodoUtils::sha256Fl()) {
                    $managerCsPw = Digester::digest($csPw);
                } else {
                    $managerCsPw = \App::getInstance('password')->hash($csPw);
                }
            }

            $managerInfo = [
                'scmNo'             => $scmNo,
                'managerId'         => $managerCsId,
                'managerPw'         => $managerCsPw,
                'managerNm'         => '고도몰5고객지원',
                'permissionFl'      => $permissions['permissionFl'] === 'all' ? 's' : 'l',
                'isSuper'           => 'cs',
                'workPermissionFl'  => 'y',
                'debugPermissionFl' => 'y',
            ];
            $this->convertPermissions($permissions, $managerInfo);
            $this->db->begin_tran();
            $this->db->query_reset();
            $arrInclude = array_keys($managerInfo);
            $arrBind = $this->db->get_binding(DBTableField::tableManager(), $managerInfo, 'insert', $arrInclude);
            $this->db->set_insert_db(DB_MANAGER, $arrBind['param'], $arrBind['bind'], 'y');
            $managerSno = $this->db->insert_id();
            unset($arrInclude, $managerInfo);

            if ($managerSno < 1) {
                $this->db->rollback();

                return 0;
            }

            // cs 수동 생성 계정값
            if($mCsAccount['createType'] == 'm'){
                $managerCustomerServiceCsId = static::PREFIX_M_CS_ID . $mCsAccount['csId'];
                $managerCustomerServiceCsPw = $mCsAccount['csPw'];
            }else{
                $managerCustomerServiceCsId = static::PREFIX_CS_ID . Otp::getOtp(8, Otp::OTP_TYPE_STRING);
                $managerCustomerServiceCsPw = $csPw;
            }

            $encryptor = \App::getInstance('encryptor');
            $csManagerInfo = [
                'scmNo'          => $scmNo,
                'managerSno'     => $managerSno,
                'csId'           => $encryptor->mysqlAesEncrypt($managerCustomerServiceCsId),
                'csPw'           => $encryptor->mysqlAesEncrypt($managerCustomerServiceCsPw),
                'permissionFl'   => $permissions['permissionFl'] === 'all' ? 's' : 'l',
                'permissionMenu' => json_encode(['menu' => $permissions['permissionMenu']], JSON_UNESCAPED_UNICODE),
                'functionAuth'   => null,
                'expireDate'     => DateTimeUtils::dateFormat('Y-m-d', '+ ' . $this->createValidDay . ' days'),
            ];

            if (\is_array($permissions['functionAuth']) && \count($permissions['functionAuth']) > 0) {
                $csManagerInfo['functionAuth'] = json_encode($permissions['functionAuth'], JSON_UNESCAPED_UNICODE);
            }
            $this->db->query_reset();
            $tableManagerCustomerService = DBTableField::tableManagerCustomerService();
            $arrInclude = array_keys($csManagerInfo);
            $arrBind = $this->db->get_binding($tableManagerCustomerService, $csManagerInfo, 'insert', $arrInclude);
            $this->db->set_insert_db(DB_MANAGER_CUSTOMER_SERVICE, $arrBind['param'], $arrBind['bind'], 'y');
            $csSno = $this->db->insert_id();
            unset($arrInclude, $tableManagerCustomerService, $csManagerInfo);
            $this->db->commit();
        } catch (DatabaseException $e) {
            $this->db->rollback();

            return 0;
        }

        return $csSno;
    }

    /**
     * CS 계정 비번 생성
     *
     * @return string
     */
    public function generatePassword()
    {
        $password = Otp::getOtp(12, Otp::OTP_TYPE_MIX);
        if (!Validator::password($password, true)) {
            $inValid = true;
            while ($inValid) {
                $password = Otp::getOtp(12, Otp::OTP_TYPE_MIX);
                if (Validator::password($password, true)) {
                    $inValid = false;
                }
            }
        }

        return $password;
    }

    /**
     * CS 테이블의 권한 정보 변환 후 관리자 권한 정보에 설정
     *
     * @param array $permissions CS 권한정보 permissionMenu, functionAuth, permissionFl
     * @param array $managerInfo 관리자 권한정보
     */
    protected function convertPermissions($permissions, &$managerInfo)
    {
        if ($permissions['permissionFl'] !== 'all') {
            $permission3 = [];

            foreach ($permissions['permissionMenu'] as $permission) {
                if (array_key_exists($permission[1], $permission3)) {
                    $permission3[$permission[1]][] = $permission[2];
                } else {
                    $permission3[$permission[1]] = [$permission[2]];
                }
            }

            $permissionMenu = [
                'permission_1' => null,
                'permission_2' => null,
                'permission_3' => $permission3,
            ];
            $managerInfo['permissionMenu'] = json_encode($permissionMenu, JSON_UNESCAPED_UNICODE);
            $functionAuth = ['functionAuth' => null];

            if (\is_array($permissions['functionAuth']) && \count($permissions['functionAuth']) > 0) {
                $functionAuth = ['functionAuth' => $permissions['functionAuth']];
            }
            $managerInfo['functionAuth'] = json_encode($functionAuth, JSON_UNESCAPED_UNICODE);
        } else {
            // 전체권한인 경우 관리자 권한 정보에 상품 재고 수정 권한 저장
            $functionAuth['functionAuth']['goodsStockModify'] = 'y';
            $managerInfo['functionAuth'] = json_encode($functionAuth, JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * CS 계정 정보 수정
     *
     * @param array $permissions CS 계정 권한 정보
     * @param int   $sno         CS 계정 번호
     *
     * @return int
     */
    public function updateManagerCs($permissions, $sno)
    {
        if ($sno < 0) {
            throw new \InvalidArgumentException('수정할 CS 계정을 찾을 수 없습니다.');
        }

        if ($permissions['permissionFl'] !== 'all' && !(\is_array($permissions['permissionMenu'])
                && \count($permissions['permissionMenu']) > 0)) {
            throw new \InvalidArgumentException('설정된 접근권한이 없습니다.');
        }

        try {
            $managerInfo = [
                'permissionFl'      => $permissions['permissionFl'] === 'all' ? 's' : 'l',
                'workPermissionFl'  => 'y',
                'debugPermissionFl' => 'y',
            ];

            if ($permissions['permissionFl'] === 'all') {
                $managerInfo['permissionMenu'] = json_encode(null, JSON_UNESCAPED_UNICODE);
                $managerInfo['functionAuth'] = json_encode(null, JSON_UNESCAPED_UNICODE);
            }

            $this->convertPermissions($permissions, $managerInfo);
            $this->db->begin_tran();
            $this->db->query_reset();
            $arrInclude = array_keys($managerInfo);
            $arrBind = $this->db->get_binding(DBTableField::tableManager(), $managerInfo, 'update', $arrInclude);
            $table = DB_MANAGER . ' AS m, (SELECT sno, managerSno FROM ' . DB_MANAGER_CUSTOMER_SERVICE;
            $table .= ' WHERE sno = ?) AS msc';
            $bindType = array_shift($arrBind['bind']);
            array_unshift($arrBind['bind'], 'i' . $bindType, $sno);

            $this->db->set_update_db($table, $arrBind['param'], ' m.sno = msc.managerSno', $arrBind['bind']);
            $affectedRows = $this->db->affected_rows();
            unset($managerInfo, $arrBind, $arrInclude);

            if ($affectedRows < 1) {
                $this->db->rollback();

                return 0;
            }

            $csManagerInfo = [
                'permissionFl'   => $permissions['permissionFl'] === 'all' ? 's' : 'l',
                'permissionMenu' => json_encode(['menu' => $permissions['permissionMenu']], JSON_UNESCAPED_UNICODE),
                'functionAuth'   => json_encode($permissions['functionAuth'], JSON_UNESCAPED_UNICODE),
            ];
            $this->db->query_reset();
            $tableManagerCustomerService = DBTableField::tableManagerCustomerService();
            $arrInclude = array_keys($csManagerInfo);
            $arrBind = $this->db->get_binding($tableManagerCustomerService, $csManagerInfo, 'update', $arrInclude);
            $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
            $this->db->set_update_db(DB_MANAGER_CUSTOMER_SERVICE, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            $affectedRows = $this->db->affected_rows();
            unset($csManagerInfo, $arrBind, $arrInclude);
            $this->db->commit();
        } catch (DatabaseException $e) {
            $this->db->rollback();

            return 0;
        }

        return $affectedRows;
    }

    /**
     * CS 계정 테이블 조회
     * ID, PW 복호화
     *
     * @return array
     * @throws DatabaseException
     */
    public function getDecryptListAll()
    {
        $arrList = $this->selectListAll();
        $encryptor = \App::getInstance('encryptor');
        foreach ($arrList as $index => &$item) {
            $item['csId'] = $encryptor->mysqlAesDecryptByCS($item['csId']);
            $item['csPw'] = $encryptor->mysqlAesDecryptByCS($item['csPw']);
        }

        return $arrList;
    }

    /**
     * CS 계정 테이블 조회
     *
     * @return array
     * @throws DatabaseException
     */
    public function selectListAll()
    {
        $this->db->query_reset();
        $this->db->strField = 'sno, scmNo, permissionFl, permissionMenu, functionAuth, csId, csPw, expireDate';
        $this->db->strOrder = 'regDt ASC';
        $this->db->strWhere = 'expireDate >= ' . DateTimeUtils::dateFormat('Y-m-d', 'now');
        if (!parent::useProvider()) {
            $this->db->strWhere .= ' AND scmNo = ' . DEFAULT_CODE_SCMNO;
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER_CUSTOMER_SERVICE . implode(' ', $query);

        return $this->db->query_fetch($strSQL);
    }

    protected function hasCs($scmNo)
    {
        $fields = DBTableField::getFieldTypes(DBTableField::getFuncName(DB_MANAGER_CUSTOMER_SERVICE));
        $this->db->query_reset();
        $this->db->strField = 'COUNT(*) AS cnt';
        $this->db->strOrder = 'regDt ASC';
        $this->db->strWhere = 'expireDate >= ' . DateTimeUtils::dateFormat('Y-m-d', 'now');
        $this->db->strWhere .= ' AND scmNo = ?';
        $this->db->bind_param_push($arrBind, $fields['scmNo'], $scmNo);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER_CUSTOMER_SERVICE . implode(' ', $query);
        $cnt = $this->db->query_fetch($strSQL, $arrBind, false)['cnt'];
        StringUtils::strIsSet($cnt, 0);

        return $cnt;
    }

    /**
     * CS 계정 로그인 검증 정보 조회
     *
     * @param array $loginData
     *
     * @return array
     * @throws DatabaseException
     */
    public function getManagerByLogin(array $loginData)
    {
        $this->db->query_reset();
        $this->db->strField = '*';
        $this->db->strWhere = 'csId=? AND csPw=? AND expireDate >= ' . DateTimeUtils::dateFormat('Y-m-d', 'now');
        $encryptor = \App::getInstance('encryptor');
        $fields = DBTableField::getFieldTypes(DBTableField::getFuncName(DB_MANAGER_CUSTOMER_SERVICE));
        $this->db->bind_param_push($arrBind, $fields['csId'], $encryptor->mysqlAesEncrypt($loginData['managerId']));
        $this->db->bind_param_push($arrBind, $fields['csPw'], $encryptor->mysqlAesEncrypt($loginData['managerPw']));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER_CUSTOMER_SERVICE . implode(' ', $query);
        $result = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($query, $fields, $arrBind);

        if (array_key_exists('managerSno', $result) && array_key_exists('sno', $result)
            && $result['managerSno'] > 0 && $result['sno'] > 0) {
            $this->db->query_reset();
            //@formatter:off
            $arrInclude = [
                'managerId', 'managerNickNm', 'managerNm', 'managerPw', 'cellPhone', 'isSmsAuth', 'employeeFl',
                'workPermissionFl', 'debugPermissionFl', 'permissionFl', 'isSuper', 'changePasswordDt',
                'guidePasswordDt', 'permissionMenu', 'loginLimit',
            ];
            //@formatter:on
            $arrField = DBTableField::setTableField('tableManager', $arrInclude);
            $arrBind = [];
            $arrJoin[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON sm.scmNo = m.scmNo ';
            $this->db->strField = implode(', ', $arrField);
            $this->db->strJoin = implode('', $arrJoin);
            $this->db->strWhere = "m.sno = ? AND m.isDelete = 'n' AND sm.scmType = 'y' "; // 공급사 운영상태 일 경우만 로그인 가능
            $this->db->bind_param_push($arrBind, 'i', $result['managerSno']);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT sno,m.modDt AS mModDt, m.managerId, m.managerNickNm, m.regDt AS mRegDt, m.isEmailAuth,';
            $strSQL .= ' m.email, sm.scmNo,sm.scmKind,sm.scmPermissionInsert,sm.scmPermissionModify,';
            $strSQL .= ' sm.scmPermissionDelete, sm.companyNm,sm.scmType, ' . array_shift($query);
            $strSQL .= ' FROM ' . DB_MANAGER . ' as m ' . implode(' ', $query);

            $data = $this->db->query_fetch($strSQL, $arrBind, false);
            unset($arrBind);
            $loginLimit = json_decode($data['loginLimit'], true);
            $data['loginLimit'] = $loginLimit;
            $data['csSno'] = $result['sno'];

            return $data;
        }

        return parent::getManagerByLogin($loginData);
    }

    /**
     * CS 계정 유효성 검사 및 인증 처리 후 세션 생성
     *
     * @param array $data
     *
     * @throws DatabaseException
     */
    public function afterManagerLogin($data)
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $logger = \App::getInstance('logger');

        $this->db->query_reset();
        $data['lastLoginIp'] = $request->getRemoteAddress();
        // --- 관리자 테이블 갱신
        $arrBind = [];
        $arrUpdate[] = 'lastLoginDt = now()';
        $arrUpdate[] = 'lastLoginIp = \'' . $data['lastLoginIp'] . '\'';
        $arrUpdate[] = 'loginCnt = loginCnt + 1';
        $this->db->bind_param_push($arrBind, 'i', $data['sno']);
        $this->db->set_update_db(DB_MANAGER, $arrUpdate, 'sno = ?', $arrBind);
        $logger->info(__METHOD__ . ', update columns => ', $arrUpdate);
        unset($arrUpdate, $arrBind);

        $this->db->query_reset();
        $tableManagerCustomerService = DBTableField::tableManagerCustomerService();
        $params = ['expireDate' => DateTimeUtils::dateFormat('Y-m-d', '+ ' . $this->authenticationValidDay . ' days'),];
        $arrInclude = array_keys($params);
        $arrBind = $this->db->get_binding($tableManagerCustomerService, $params, 'update', $arrInclude);
        $this->db->bind_param_push($arrBind['bind'], 'i', $data['csSno']);
        $this->db->set_update_db(DB_MANAGER_CUSTOMER_SERVICE, $arrBind['param'], 'sno = ?', $arrBind['bind']);

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
    }

    /**
     * 관리자 보안로그인 인증 유효기간 설정
     * CS 계정은 유효기간이 없다
     *
     * @param $tmpManager
     */
    public function setLoginAuthCookie($tmpManager)
    {
        $logger = \App::getInstance('logger');
        $logger->info('disable customer service account authentication save function. csSno=' . $tmpManager['csSno']);
    }

    /**
     * 만료일에 해당하는 CS 계정정보 삭제
     *
     * @param $expireDate
     *
     * @return int
     * @throws DatabaseException
     */
    public function expireManagerCs($expireDate)
    {
        $this->db->query_reset();
        $dbTable = 'mcs, m USING ' . DB_MANAGER_CUSTOMER_SERVICE . ' AS mcs JOIN ' . DB_MANAGER . ' AS m';
        $where = 'mcs.expireDate = ? AND mcs.managerSno = m.sno';
        $fields = DBTableField::getFieldTypes(DBTableField::getFuncName(DB_MANAGER_CUSTOMER_SERVICE));
        $this->db->bind_param_push($arrBind, $fields['expireDate'], $expireDate);

        return $this->db->set_delete_db($dbTable, $where, $arrBind);
    }

    /**
     * CS 계정 확인 함수
     *
     * @param $managerSno
     *
     * @return bool
     * @throws DatabaseException
     */
    public function isCustomerService($managerSno)
    {
        $fields = DBTableField::getFieldTypes(DBTableField::getFuncName(DB_MANAGER_CUSTOMER_SERVICE));
        $this->db->query_reset();
        $this->db->strWhere = 'managerSno = ?';
        $this->db->bind_param_push($arrBind, $fields['managerSno'], $managerSno);
        $this->db->strField = 'COUNT(*) AS cnt';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER_CUSTOMER_SERVICE . implode(' ', $query);
        $cnt = $this->db->query_fetch($strSQL, $arrBind, false)['cnt'];
        StringUtils::strIsSet($cnt, 0);

        return $cnt > 0;
    }

    /**
     * 관리자 로그인 시 IP 체크 하는 함수
     * CS 계정은 별도의 IP 체크를 하기 때문에 검증하지 않는다.
     *
     * @param $remoteIp
     * @param $manageSecurity
     */
    protected function validateIpByLogin($remoteIp, $manageSecurity)
    {
        $logger = \App::getInstance('logger');
        $logger->info(__METHOD__ . ', cs account pass ip check');
    }

    /**
     * cs 계정 수동 생성 시, 중복체크
     * @param $id
     * @return bool
     */
    public function manualAccountOverlapChk($id)
    {
        $this->db->strField = 'managerId';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER . implode(' ', $query);
        $managerData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        foreach($managerData as $data){
            $arrSelectId[] = $data['managerId'];
        }

        if(in_array(self::PREFIX_M_CS_ID . $id, $arrSelectId) == true){
            return true;
        }
    }
}
