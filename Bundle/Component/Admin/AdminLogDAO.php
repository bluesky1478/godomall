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

namespace Bundle\Component\Admin;

use App;
use Component\Member\Member;
use Component\Database\DBTableField;
use Component\Member\Manager;
use Framework\Utility\ArrayUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;

/**
 * Class AdminLogDAO
 * @package Core\Base\Interceptor
 * @author Nam-ju Lee <lnjts@godo.co.kr>
 */
class AdminLogDAO
{
    protected $_db;
    const LOG_TYPE_P6M =  'p6m';
    const LOG_TYPE_P2Y =  'p2y';
    const LOG_TYPE_P5Y = 'p5y';
    const LOG_TYPE_NOMAL = 'n';
    const DELIMITER = '.';
    const MODE = 'mode';
    const KEY = 'key';
    const MENU = 'menu';
    const DEFAULT = 'default';
    const WILD_CARD = '*';
    const API_KIND = 'api_kind';

    public function __construct()
    {
        $this->_db = App::load('DB');;
    }

    public function save($arrData)
    {
        $arrBind = $this->_db->get_binding(DBTableField::tableAdminLog(), $arrData, 'insert', array_keys($arrData));
        $this->_db->set_insert_db(DB_ADMIN_LOG, $arrBind['param'], $arrBind['bind'], 'y');
        return $this->_db->insert_id();
    }

    public function getList($request, $arrFields = [], $pageNum = 20 )
    {
        $arrBind = [];
        $request['page'] = $request['page']  ?? 1;

        if($arrFields == '*') {
            $queryField = 'a.*';
        }
        else if(is_array($arrFields)) {
            $queryField  = 'a.'.implode(',a.',$arrFields);
        }
        else {
            $queryField = 'a.*';
        }

        if ($request['managerId']) {
            $where[] = "a.managerId LIKE concat('%',?,'%') ";
            $this->_db->bind_param_push($arrBind, 's', $request['managerId']);
        }

        if ($request['searchDate']) {
            $request['searchDate'][0] = $request['searchDate'][0] ? $request['searchDate'][0] : date('Y-m-d');
            $request['searchDate'][1] = $request['searchDate'][1] ? $request['searchDate'][1] : date('Y-m-d');
            // 최대 검색 일자 (6개월)
            $maximumSearchDays = 179;
            $diffDate = DateTimeUtils::intervalDay($request['searchDate'][0], $request['searchDate'][1]);
            if ($diffDate < 0) {
                $request['searchDate'][0] = $request['searchDate'][1];
            } elseif ($diffDate > $maximumSearchDays) {
                $request['searchDate'][0] = date('Y-m-d', strtotime($request['searchDate'][1] . ' -' . $maximumSearchDays . ' day'));
            }
            // 최대 검색기간
            if ($request['searchDate'][0] < date('Y-m-d', strtotime('-2 year'))){
                $request['searchDate'][0] = date('Y-m-d', strtotime('-2 year'));
            }
            $where[] = '(a.regDt BETWEEN ? AND ?)';
            $this->_db->bind_param_push($arrBind, 's', $request['searchDate'][0]);
            $this->_db->bind_param_push($arrBind, 's', $request['searchDate'][1] . ' 23:59:59');
        } else {
            if (empty($request['cremaFl'])) {
                $request['searchDate'][0] = date('Y-m-d', strtotime('-7 day'));
                $request['searchDate'][1] = date('Y-m-d');
                $where[] = '(a.regDt BETWEEN ? AND ?) ';
                $this->_db->bind_param_push($arrBind, 's', $request['searchDate'][0]);
                $this->_db->bind_param_push($arrBind, 's', $request['searchDate'][1] . ' 23:59:59');
            }
        }

        // 기본 uri 조건
        if ($request['baseUri']) {
            $where[] = 'a.baseUri = ? ';
            $this->_db->bind_param_push($arrBind, 's', $request['baseUri']);
        }

        // 페이지 분기
        if ($request['adminAccessPage']) {
            if ($request['adminAccessFl'] === true) {
                $where[] = 'a.page = ? ';
            } else {
                $where[] = 'a.page != ? ';
            }
            $this->_db->bind_param_push($arrBind, 's', $request['adminAccessPage']);
        }

        $offset = ($request['page'] - 1) * $pageNum;
        $strSQL = " SELECT ".$queryField.",m.isDelete  FROM " . DB_ADMIN_LOG . " as a LEFT OUTER JOIN " .DB_MANAGER. " as m ON a.managerNo = m.sno WHERE ";
        if ($where) {
            $strSQL .= implode(' AND ', $where);
        }
        $strSQL .= " ORDER BY a.regDt DESC LIMIT {$offset},{$pageNum} ";
        $result['list'] = $this->_db->query_fetch($strSQL, $arrBind, gd_isset($request['dataArray'], true));
        $result['list'] = $this->setDisplayAdminLogList($result['list']);
        Manager::displayListData($result['list']);
        $strCountSQL = " SELECT COUNT(sno) as cnt FROM " . DB_ADMIN_LOG . " as a WHERE ";
        if ($where) {
            $strCountSQL .= implode(' AND ', $where);
        }
        $result['count'] = $this->_db->query_fetch($strCountSQL, $arrBind, false)['cnt'];
        $page = \App::load('\\Component\\Page\\Page', $request['page']);
        $page->page['list'] = $pageNum; // 페이지당 리스트 수
        $page->recode['amount'] = $page->recode['total'] =$result['count'];
        $page->setPage();
        $page->setUrl(\Request::getQueryString());
        $result['page'] = $page->getPage();
        $result['request'] = $request;
        return $result;
    }

    /**
     * 수행업무 상세로그 노출 여부 확인
     *
     * @param array $data 로그데이터
     *
     * @return array
     */
    public function setDisplayAdminLogList($data){
        foreach ($data as $lKey => $lVal) {
            $data[$lKey]['displayDetailLogFl'] = 'n';
            $fileName = end(explode('/', $lVal['baseUri']));

            // 회원정보 조회를 할 경우에만 상세로그 노출
            switch ($fileName) {
                case 'member_crm.php':
                case 'member_modify.php':
                case 'member_list.php':
                case 'layer_excel_ps.php':
                case 'crema_ps.php':
                case 'layer_godo_sms_ps.php':
                case 'layer_godo_mail_ps.php':
                case 'layer_excel_auth_ps.php':
                    $data[$lKey]['displayDetailLogFl'] = 'y';
                    break;
                case 'member_ps.php':
                    $lVal['data'] = json_decode($lVal['data'], true);
                    if ($lVal['data']['POST']['mode'] == 'modify') {
                        $data[$lKey]['displayDetailLogFl'] = 'y';
                    }
                    break;
                case 'manage_ps.php':
                    if ($lVal['page'] == 'adminInfo') {
                        $data[$lKey]['displayDetailLogFl'] = 'y';
                    }
                    break;
                case 'login_ps.php':
                    $lVal['data'] = json_decode($lVal['data'], true);
                    if (empty($lVal['data']['POST']['authTarget']) === false) {
                        $data[$lKey]['displayDetailLogFl'] = 'y';
                    }
                    break;
            }
        }
        return $data;
    }

    /**
     * 수행업무 상세로그
     *
     * @param integer $sno 로그 일련번호
     * @param array $arrFields 필드
     *
     * @return array
     */
    public function getDetailAdminLogInfo($sno, $arrFields = [])
    {
        $arrBind = [];

        if(is_array($arrFields) && count($arrFields) > 0) {
            $queryField  = 'a.'.implode(',a.',$arrFields);
        }
        else {
            $queryField = 'a.*';
        }

        $where[] = 'a.sno = ?';
        $this->_db->bind_param_push($arrBind, 'i', $sno);

        $strSQL = " SELECT ".$queryField.",m.isDelete  FROM " . DB_ADMIN_LOG . " as a LEFT OUTER JOIN " .DB_MANAGER. " as m ON a.managerNo = m.sno WHERE ";

        if ($where) {
            $strSQL .= implode(' AND ', $where);
        }

        $data = $this->_db->query_fetch($strSQL, $arrBind, false);
        Manager::displayListData($data);

        $data['data'] = json_decode($data['data'], true);
        $result = $this->setDisplayAdminLogInfo($data);

        return $result;
    }

    /**
     * 수행업무 상세로그 메시지 정의
     *
     * @param array $data 수행업무 상세로그 데이터
     *
     * @return array
     */
    public function setDisplayAdminLogInfo($data)
    {
        $fileUri = explode('/', $data['baseUri']);
        $fileName = end($fileUri);
        $result['searchCnt'] = $data['data']['searchCnt'];
        $result['searchData'] = $data['data']['searchData'];
        $result['key'] = $data['data']['GET']['key'];
        $result['keyword'] = $data['data']['GET']['keyword'];

        if ($data['data']['searchCnt'] > 1) {
            $searchData = ' (' . $data['data']['searchData'] . ' 외 ' . (number_format($data['data']['searchCnt'] - 1)) . '명)';
        } else if ($data['data']['searchCnt'] == '1') {
            $searchData = ' (' . $data['data']['searchData'] . ')';
        } else {
            $searchData = '';
        }

        if (empty($data['data']['GET']['keyword']) == false) {
            $keyword = ' (' . $data['data']['GET']['keyword'] . ')';
        } else {
            $keyword = '';
        }

        switch ($fileName) {
            case 'member_list.php':
                $keyData = Member::COMBINE_SEARCH;
                if (empty($data['data']['GET']['key']) || $result['key'] = $data['data']['GET']['key'] == 'all') {
                    $result['keyValue'] = '통합검색';
                } else {
                    $result['keyValue'] = $keyData[$data['data']['GET']['key']];
                }
                break;
            case 'member_crm.php':
            case 'member_modify.php':
            case 'member_ps.php':
                $result['keyValue'] = '상세조회';
                break;
            case 'layer_excel_ps.php':
                $tmpFileName = $data['data']['POST']['fileName'];
                $dividedFileName = explode('.', $tmpFileName);
                // 확장자가 zip인경우 xls로 변환하여 노출
                if (end($dividedFileName) == 'zip') {
                    $dividedFileName[count($dividedFileName) - 1] = 'xls';
                    $tmpFileName = implode('.', $dividedFileName);
                }
                $result['searchTargetMsg'] = '다운로드 양식명 : ' . $data['data']['searchData'];
                $result['searchConditionMsg'] = '파일대상 : ' . $data['data']['POST']['downloadFileName'] . ' (' . $tmpFileName . ')';
                if (empty($data['data']['POST']['excelDownloadReason']) === false) {
                    $result['downloadReason'] = '사유 : ' . $data['data']['POST']['excelDownloadReason'];
                }
                break;
            case 'crema_ps.php':
                $result['searchTargetMsg'] = '크리마 간편리뷰 설정을 위한 CSV 파일 다운로드';
                if (empty($data['data']['GET']['excelDownloadReason']) === false) {
                    $result['downloadReason'] = '사유 : ' . $data['data']['GET']['excelDownloadReason'];
                }
                break;
            case 'layer_godo_sms_ps.php':
            case 'layer_godo_mail_ps.php':
            case 'layer_excel_auth_ps.php':
            case 'manage_ps.php':
            case 'login_ps.php':
                if (end($fileUri) == 'login_ps.php' && empty($data['data']['POST']['authTarget'])) {
                    break;
                }
                $mode = $data['data']['POST']['mode'];
                // 인증 절차 복호화
                if (end($fileUri) == 'manage_ps.php') {
                    if ($mode == 'authSms') {
                        $encryptData = $data['data']['POST']['cellPhone'];
                    } else {
                        $encryptData = $data['data']['POST']['email'];
                    }
                    $decryptAuthTarget = \Encryptor::decryptJson($encryptData);
                } else {
                    $decryptAuthTarget = \Encryptor::decryptJson($data['data']['POST']['authTarget']);
                }
                // 인증 절차 마스킹
                switch ($mode) {
                    case 'getSmsAuthKey':
                    case 'authSms':
                    case 'smsReSend':
                        $decryptAuthTarget = StringUtils::numberToCellPhone($decryptAuthTarget);
                        $tmpAuthTarget = explode('-', $decryptAuthTarget);
                        $tmpAuthTarget[1] = StringUtils::mask($tmpAuthTarget[1]);
                        $tmpAuthTarget[2] = StringUtils::mask($tmpAuthTarget[2], 0, 2);
                        $authTarget = implode('-', $tmpAuthTarget);
                        break;
                    case 'getMailAuthKey':
                    case 'authEmail':
                    case 'emailSend':
                        $tmpAuthTarget = explode('@', $decryptAuthTarget);
                        $tmpAuthTarget[0] = StringUtils::mask($tmpAuthTarget[0], 2);
                        $tmpMailDomain = explode('.', $tmpAuthTarget[1]);
                        $tmpMailDomain[0] = StringUtils::mask($tmpMailDomain[0], 2);
                        $tmpAuthTarget[1] = implode('.', $tmpMailDomain);
                        $authTarget = implode('@', $tmpAuthTarget);
                        break;
                }
                // 인증 절차 작성
                if (empty($authTarget) === false) {
                    $result['searchTargetMsg'] = '인증 절차 : ' . $authTarget;
                }

                // 인증 정보
                $referer = end(explode('/', $data['referer']));
                $targetPage = substr($referer, 0, strrpos($referer, '.'));
                if ($fileName == 'layer_excel_auth_ps.php' || $fileName == 'login_ps.php') {
                    $targetPage = $fileName;
                }
                switch ($targetPage) {
                    case 'find_id':
                        $result['searchConditionMsg'] = '인증 정보 : 아이디 찾기 인증';
                        break;
                    case 'find_password':
                        $result['searchConditionMsg'] = '인증 정보 : 비밀번호 찾기 인증';
                        break;
                    case 'manage_register':
                    case 'layer_excel_auth_ps.php':
                    case 'login_ps.php':
                        $result['searchConditionMsg'] = '인증 정보 : 운영자 인증';
                        break;
                }
                break;
            default:
                break;
        }

        // 조회문구 제외 페이지
        $exceptPage = [
            'layer_excel_ps.php',
            'crema_ps.php',
            'layer_godo_sms_ps.php',
            'layer_godo_mail_ps.php',
            'layer_excel_auth_ps.php',
            'manage_ps.php',
            'login_ps.php',
        ];

        if (in_array(end($fileUri), $exceptPage) === false) {
            $searchTarget = $data['data']['searchCnt'] . $searchData;
            $searchCondition = $result['keyValue'] . $keyword;
            if ($searchTarget != '') {
                $result['searchTargetMsg'] = '조회대상 : ' . $searchTarget;
            }
            if (empty($searchCondition) == false) {
                $result['searchConditionMsg'] = '조회조건 : ' . $searchCondition;
            }
        }
        // 수행업무 상세
        if (empty($result['searchTargetMsg']) == false) {
            $result['logContents'] = $result['searchTargetMsg'];
        }
        if (empty($result['searchConditionMsg']) == false) {
            $result['logContents'] .= empty($result['logContents']) === false ? '<br />' . $result['searchConditionMsg'] : $result['searchConditionMsg'];
        }
        if (empty($result['downloadReason']) == false) {
            $result['logContents'] .= empty($result['logContents']) === false ? '<br />' . $result['downloadReason'] : $result['downloadReason'];
        }

        return $result;
    }

    public function setAdminLog()
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $baseDirName = $request->getDirectoryUri();
        if (empty($baseDirName)) {
            return;
        }
        $configClass = \App::getConfig('log.' . $baseDirName);
        $baseName = $request->getFileUri();
        $fileName = substr($baseName, 0, strrpos($baseName, '.'));
        $method = 'get' . ucwords($fileName);
        $pageData = null;
        $requestData = $this->getRequestData(false);
        $mode = $requestData['POST']['mode'] ?? $requestData['GET']['mode'] ?? $requestData['POST']['api_kind'];
        if (is_object($configClass)) {
            $pageData = $configClass->$method();
            $menu = $configClass->get__menu__() ?? $baseDirName;   //메뉴명
            if (is_array($menu)) {
                $menu = $this->getMenuName($menu);
            }
        }

        $page = $this->getPageName($pageData);

        if ($this->isPrivate($pageData) === false || $page === null) {        //개인정보 로그가 아닌 개설로그면 파일로 로그생성
            $fullUrl = $request->getDomainUrl() . $request->getRequestUri();
            $logger = \App::getInstance('logger')->channel('adminLog');
            // 파일로그 생성시 개인정보 암호화
            if (isset($requestData['SESSION']['manager'])) {
                $encryptKey = ['cellPhone', 'email', 'managerNm'];
                foreach ($requestData['SESSION']['manager'] as $rKey => $rVal) {
                    if (in_array($rKey, $encryptKey)) {
                        $requestData['SESSION']['manager'][$rKey] = \Encryptor::encryptJson($rVal);
                    }
                }
            }
            $logger->info($fullUrl, $requestData);
            if (($fileName == 'manage_ps' && ($mode == 'authEmail' || $mode == 'authSms')) === false) {
                return;
            }
        }

        $managerNo = $session->get('manager.sno', $requestData['POST']['sno']);
        $managerId = $session->get('manager.managerId', $requestData['POST']['managerId']);

        // 관리자 로그인/로그아웃 파일로그 개별 생성
        if ($mode === 'login' || $mode === 'logout') {
            if ($mode === 'login') {
                $adminLoginFl = $request->request()->get('adminLoginFl', false);
                $logData['adminLoginFl'] = $adminLoginFl;
                if ($adminLoginFl === true) {
                    $message = 'admin login success';
                } else {
                    $message = 'admin login fail';
                }
            } else {
                $message = 'admin logout';
            }
            if ($requestData['POST']['mobileappFl'] === true) {
                $message = 'mobileapp ' . $message;
            }
            $logData['ip'] = $requestData['REMOTE_ADDR'];
            $logData['managerId'] = $managerId;
            $logData['referer'] = $requestData['REFERER'];
            $logData['user_agent'] = $requestData['USER_AGENT'];
            \Logger::channel('adminAccess')->info($message, $logData);
        }

        $logType = $pageData['type'] ?? AdminLogDAO::LOG_TYPE_P2Y;
        $action = $this->getActionName($pageData);
        $data['baseUri'] = strtok($request->getRequestUri(), '?');
        $data['uri'] = $request->getRequestUri();
        $data['data'] = $this->getRequestData();
        $data['menu'] = $menu;
        $data['referer'] = $request->getReferer();
        $data['type'] = $logType;
        $data['page'] = $page;
        $data['action'] = $action;
        $data['managerNo'] = $managerNo;
        $data['managerId'] = $managerId;
        $data['scmNo'] = $session->get('manager.scmNo');
        $data['ip'] = $request->getRemoteAddress();

        if ($data['data']['POST']['mode'] === 'login') {
            unset($data['data']['POST']['managerPw']);
        }

        $this->save($data);
    }

    /**
     * 개인정보로그인지 여부체크
     *
     * @param $pageData
     *
     * @return bool
     */
    private function isPrivate($pageData)
    {
        if (!$pageData) {   //해당URI가 존재 하지 않으면
            return false;
        }

        $result = true;
        $page = $pageData['page'] ?? '';
        if (is_array($page)) {
            $value = $this->getArrayData($page);
            if ($value === null) {
                $result = false;
            }
        }
        $action = $pageData['action'] ?? '';
        if (is_array($action)) {
            $value = $this->getArrayData($action);
            if ($value === null) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * 기술지원지 필요한 정보들
     *
     * @param bool $isJson
     *
     * @return mixed
     */
    private function getRequestData($isJson = true)
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $cookie = \App::getInstance('cookie');
        $data['POST'] = $request->post()->toArray();
        $data['GET'] = $request->get()->toArray();
        $data['USER_AGENT'] = $request->getUserAgent();
        $data['SESSION'] = $session->all();
        $data['COOKIE'] = $cookie->all();
        $data['REFERER'] = $request->getReferer();
        $data['REMOTE_ADDR'] = $request->getRemoteAddress();

        $controller = \App::getController();
        $pageName = explode('/', $controller->getPageName());

        switch (end($pageName)) {
            case 'member_crm':
                $data['searchData'] = $controller->getData('memberData')['memId'];
                $data['searchCnt'] = 1;
                break;
            case 'member_list':
                $data['searchData'] = $controller->getData('data')[0]['memId'];
                $data['searchCnt'] = $controller->getData('page')->recode['total'];
                break;
            case 'member_modify':
                $data['searchData'] = $controller->getData('data')['memId'];
                $data['searchCnt'] = 1;
                break;
            case 'member_ps':
                if ($data['POST']['mode'] == 'modify') {
                    $data['searchData'] = $data['POST']['memId'];
                    $data['searchCnt'] = 1;
                }
                break;
            case 'layer_excel_ps':
                if (empty($data['POST']['excelTitle']) == false) {
                    $data['searchData'] = $data['POST']['excelTitle'];
                } else {
                    $data['searchData'] = urldecode($data['POST']['excelFileName']);
                }
                break;
            case 'login_ps':
                if (empty($data['POST']['managerPw']) === false) {
                    unset($data['POST']['managerPw']);
                }
                if (empty($data['POST']['authTarget']) === false) {
                    $data['POST']['authTarget'] = \Encryptor::encryptJson($data['POST']['authTarget']);
                }
                break;
            case 'layer_godo_sms_ps':
            case 'layer_godo_mail_ps':
            case 'layer_excel_auth_ps':
                if (empty($data['POST']['authTarget']) === false) {
                    $data['POST']['authTarget'] = \Encryptor::encryptJson($data['POST']['authTarget']);
                }
                $manager = \App::load('\\Component\\Member\\Manager');
                $managerSno = 1;
                $data['POST']['managerId'] = $manager->getSpecificManagerInfo(['sno' => $managerSno], 'managerId')['managerId'];
                $data['POST']['sno'] = $managerSno;
                break;
            case 'manage_ps':
                // 암호화 적용 필드
                $encryptTarget = [
                    'email',
                    'cellPhone',
                    'managerPw',
                    'managerPwRe',
                    'modManagerPw',
                    'modManagerPwRe',
                ];
                foreach ($encryptTarget as $target) {
                    if (empty($data['POST'][$target]) === false) {
                        $data['POST'][$target] = \Encryptor::encryptJson($data['POST'][$target]);
                    }
                }
                break;
            default:
                break;
        }

        if ($isJson) {
            return json_encode($data);
        }

        return $data;
    }

    private function getPageName($pageData)
    {
        return $this->getValue('page', $pageData);
    }

    private function getActionName($pageData)
    {
        return $this->getValue('action', $pageData, '조회');
    }

    private function getMenuName($menu)
    {
        return $this->getValue('menu', $menu);
    }

    private function getValue($key, $pageData, $default = '')
    {
        if (!$pageData) {
            return '';
        }

        if (($pageData[$key])) {
            $page = $pageData[$key];
            if (is_array($page)) {
                return $this->getArrayValue($page);
            }
        }

        $page = $page ? $page : $default;

        return $page;
    }

    /**
     * getArrayValue
     *
     * @param $page
     *
     * @return string
     * @internal param string $default
     */
    private function getArrayValue($page)
    {
        if (is_array($page) === false) {
            return '';
        }

        $result = $this->getArrayData($page);

        return $result;
    }

    /**
     * getArrayData
     *
     * @param $page
     *
     * @return bool
     * @internal param string $mode
     */
    private function getArrayData($page)
    {
        $defaultPage = $page[self::DEFAULT];
        $flag = null;
        $request = \App::getInstance('request');

        foreach ($page as $key => $val) {
            if (strpos($key, self::DELIMITER) !== false) {
                list($_key, $_val) = explode(self::DELIMITER, $key);
                switch ($_key) {
                    case self::MODE :
                        if ($request->request()->get('mode') == $_val) {
                            $flag = $val;
                        }
                        if (is_array($flag)) {
                            $flag = $this->getArrayData($flag);
                        }
                        break;
                    case self::KEY :
                        if ($_val == self::WILD_CARD) {
                            if (is_array($val)) {
                                $result = array_diff_key($request->request()->toArray(), array_flip($val['expect']));
                                if ($result) {
                                    $flag = $val[self::DEFAULT];
                                } else {
                                    $flag = $defaultPage;
                                }
                            } else {
                                if ($request->request()->all()) {
                                    $flag = $val;
                                }
                            }
                        } else {
                            if ($request->request()->get($_val)) {
                                $flag = $val;
                            }
                        }
                        break;
                    case self::MENU :
                        $referer = explode('/', $request->getParserReferer()->path);
                        $adminMenu = ArrayUtils::first($referer);
                        if ($adminMenu === 'provider') {
                            $adminMenu = ArrayUtils::removeEmpty($referer)[array_search($adminMenu, $referer) + 1];
                        }
                        if ($adminMenu == $_val) {
                            $flag = $val;
                        }
                        break;
                    case self::API_KIND :
                        if ($request->request()->get('api_kind') == $_val) {
                            $flag = $val;
                        }
                        if (is_array($flag)) {
                            $flag = $this->getArrayData($flag);
                        }
                        break;
                    default :
                        continue;
                }
            }
        }

        if ($flag === null) {
            $flag = $defaultPage ? $defaultPage : $flag;
        }

        return $flag;
    }

    /**
     * 로그인 시도(실패)한 IP의 마지막 로그인 실패 데이터
     *
     * @param string $ip 로그인시도 접속 IP
     * @param string $IsLimitFlag 접속제한 여부 조회 (Y : 제한조회, N : 미제한조회)
     *
     * @return array
     */
    public function selectLogIpLoginTry($ip, $IsLimitFlag)
    {
        if ($ip == '' || $ip == null) {
            return null;
        }
        $arrBind = [];
        $this->_db->strField = 'limitFlag, onLimitDt, loginFailDt';
        $this->_db->strWhere = 'loginFailIp = ? AND limitFlag = ? AND loginType = ?';
        $this->_db->strOrder = 'sno DESC';
        $this->_db->strLimit = '1';
        $this->_db->bind_param_push($arrBind, 's', $ip);
        $this->_db->bind_param_push($arrBind, 's', $IsLimitFlag);
        $this->_db->bind_param_push($arrBind, 's', 'admin');
        $query = $this->_db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_LOG_IPLOGINTRY . implode(' ', $query);
        $result = $this->_db->query_fetch($strSQL, $arrBind);

        return $result[0];
    }

    /**
     * 로그인 시도(실패)시 1분 동안 몇회 시도 했는지 확인
     *
     * @param string $ip 로그인시도 접속 IP
     *
     * @return int
     */
    public function getOneMinLoginFailCount($ip) {

        $loginFailDtStart = DateTimeUtils::dateFormat('Y-m-d H:i:s', '-1min');
        $loginFailDtEnd = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');

        $arrBind = [];
        $arrWhere[] = 'loginFailDt BETWEEN ? AND ? ';
        $this->_db->bind_param_push($arrBind, 's', $loginFailDtStart);
        $this->_db->bind_param_push($arrBind, 's', $loginFailDtEnd);

        $arrWhere[] = 'loginFailIp = ?';
        $this->_db->bind_param_push($arrBind, 's', $ip);

        $arrWhere[] = 'limitFlag = ?';
        $this->_db->bind_param_push($arrBind, 's', 'N');

        $arrWhere[] = 'loginType = ?';
        $this->_db->bind_param_push($arrBind, 's', 'admin');

        $this->_db->strField = ' COUNT(loginFailIp) AS cnt ';
        $this->_db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->_db->query_complete();
        $strSQL = ' SELECT '. array_shift($query) .' FROM ' . DB_LOG_IPLOGINTRY . implode(' ', $query);

        return $this->_db->query_fetch($strSQL, $arrBind, false)['cnt'];
    }
}
