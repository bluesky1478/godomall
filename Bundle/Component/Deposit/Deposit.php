<?php
/**
 *
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 *
 */

namespace Bundle\Component\Deposit;

use App;
use Component\Database\DBTableField;
use Component\Mail\MailMimeAuto;
use Component\Member\Manager;
use Component\Member\MemberDAO;
use Component\Sms\Code;
use Component\Sms\SmsAuto;
use Component\Sms\SmsAutoCode;
use Component\Validator\Validator;
use Exception;
use Framework\Object\SimpleStorage;
use Framework\Utility\ArrayUtils;
use Framework\Utility\StringUtils;
use Globals;
use Request;
use Session;

/**
 * 예치금 기능 담당 클래스
 * @package Bundle\Component\Deposit
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class Deposit extends \Component\AbstractComponent
{
    /** 예치금 지급/차감사유 그룹 코드 */
    const REASON_CODE_GROUP = '01006';
    /** 예치금 지급/차감사유 환불 시 사용 예치금 환불 */
    const REASON_CODE_ORDER_CANCEL = '001';
    /** 예치금 지급/차감사유 환불 시 예치금환불 처리 */
    const REASON_CODE_DEPOSIT_REFUND = '002';
    /** 예치금 지급/차감사유 상품구매 */
    const REASON_CODE_GOODS_BUY = '003';
    /** 예치금 지급/차감사유 임의조정 */
    const REASON_CODE_MANUAL = '004';
    /** 예치금 지급/차감사유 클래임 보상 */
    const REASON_CODE_CLAIM_REWARD = '005';
    /** 예치금 지급/차감사유 기타 */
    const REASON_CODE_ETC = '006';
    /** 예치금 지급/차감사유 취소 시 사용 예치금 환불 */
    const REASON_CODE_ADD_BUY_CANCEL = '501';
    /** 예치금 지급/차감사유 기프트쿠폰 적립 */
    const REASON_CODE_GIFT_COUPON = '505';
    /** 통합검색 배열 */
    // '=' . __('통합검색') . '='
    // __('이름')
    // __('아이디')
    // __('닉네임')
    // __('처리자')
    const COMBINE_SEARCH = [
        'all'       => '=통합검색=',
        'memNm'     => '이름',
        'memId'     => '아이디',
        'nickNm'    => '닉네임',
        'managerId' => '처리자',
    ];
    /** @var bool $allowSendSms 예치금 지급/차감 SMS 발송 여부 상태 값. 관리자 일괄 지급/차감 에서 발송 설정을 하지 않은 경우를 제외하고 모두 발송 */
    protected $allowSendSms = true;
    /** @var string $smsReserveTime SMS 의 예약시간 */
    protected $smsReserveTime;
    protected $arrWhere;
    protected $arrBind;
    /** @var \Bundle\Component\Mail\MailMimeAuto $mailMimeAuto */
    private $mailMimeAuto;
    /** @var  SimpleStorage $resultStorage 예치금 처리 결과가 담기는 Storage */
    private $resultStorage;
    /** @var  DepositDAO $depositDAO 예치금 관련 처리를 담당하는 DAO */
    private $depositDAO;
    /** @var  SmsAuto $smsAuto SMS 자동발송 클래스 */
    private $smsAuto;

    /**
     * 2016-10-06 yjwee 기존 DepositDAO 객체를 파라미터로 받던 것을 배열로 받게끔 수정
     *
     * @param array|DepositDAO $config
     */
    public function __construct($config = [])
    {
        parent::__construct();
        if (is_array($config)) {
            $this->depositDAO = is_object($config['depositDao']) ? $config['depositDao'] : new DepositDAO();
            $this->smsAuto = is_object($config['smsAuto']) ? $config['smsAuto'] : new SmsAuto();
        } else {
            // 사용자 측에서 생성자로 DepositDAO 객체를 파라미터를 넘길 경우를 대비한 소스 코드 적용
            if (get_class($config) !== DepositDAO::class) {
                $config = new DepositDAO();
            }
            $this->depositDAO = $config;
        }
        $this->tableFunctionName = 'tableMemberDeposit';
        $this->mailMimeAuto = App::load('\\Component\\Mail\\MailMimeAuto');
    }

    /**
     * 지급된 예치금이 1건 이상있는지 체크하는 함수
     *
     * @return bool
     */
    public function depositIsExists()
    {
        return $this->getCount(DB_MEMBER, 'deposit', ' WHERE deposit > 0') > 0;
    }

    /**
     * 회원 예치금 내역
     *
     * @param int $memNo   회원 번호
     * @param int $page    페이지 번호
     * @param int $perPage 페이지당 리스트수
     *
     * @return mixed
     * @deprecated 2018-05-17 사용되지 않는 함수입니다. 추후 제거될 수 있습니다.
     */
    public function getMemberDepositList($memNo, $page = 1, $perPage = 10)
    {
        $arrBind = [];
        $start = (gd_isset($page, 1) - 1) * $perPage;

        $this->db->strField = "*, substr(regDt, 1, 10) AS regdate";
        $this->db->strWhere = " memNo=? ";
        $this->db->strLimit = "?,?";
        $this->db->strOrder = "sno DESC";
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $this->db->bind_param_push($arrBind, 'i', $start);
        $this->db->bind_param_push($arrBind, 'i', $perPage);

        $query = $this->db->query_complete();
        $strSQL = "SELECT " . array_shift($query) . " FROM " . DB_MEMBER_DEPOSIT . " " . implode(" ", $query);
        $list = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind, $query, $strSQL);
        $this->db->strField = 'COUNT(*) AS cnt';
        $this->db->strWhere = 'memNo=?';
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_DEPOSIT . ' ' . implode(' ', $query);
        $searchCnt = $this->db->query_fetch($strSQL, $arrBind, false)['cnt'];
        unset($arrBind, $query);

        $getData['list'] = gd_htmlspecialchars_stripslashes($list);
        $getData['searchCnt'] = $searchCnt;

        return $getData;
    }

    /**
     * 현재예치금, 회원명 반환
     *
     * @param $memNo
     *
     * @return string
     */
    public function getMemberDeposit($memNo)
    {
        $arrBind = [];
        $this->db->strField = "deposit, memNm";
        $this->db->strJoin = DB_MEMBER;
        $this->db->strWhere = " memNo=? ";
        $this->db->bind_param_push($arrBind, 'i', $memNo);

        $query = $this->db->query_complete();
        $strSQL = "SELECT " . array_shift($query) . " FROM " . array_shift($query) . " " . implode(" ", $query);
        $memInfo = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind, false));
        unset($arrBind);

        return $memInfo;
    }

    /**
     * 예치금 지급/차감 결과 함수
     *
     * @return SimpleStorage
     */
    public function getResultStorage()
    {
        return $this->resultStorage;
    }

    /**
     * 회원별 사용한 예치금 반환
     *
     * @author sj
     *
     * @param integer $memNo 회원 번호
     *
     * @return array|string
     */
    public function getMemberSumUsedDeposit($memNo)
    {
        $arrBind = [];
        $this->db->strField = "SUM(deposit) AS deposit";
        $this->db->strJoin = DB_MEMBER_DEPOSIT;
        $this->db->strWhere = " memNo=? AND handleMode='o' ";
        $this->db->bind_param_push($arrBind, 'i', $memNo);

        $query = $this->db->query_complete();
        $strSQL = "SELECT " . array_shift($query) . " FROM " . array_shift($query) . " " . implode(" ", $query);
        $usedDeposit = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind, false));
        unset($arrBind);

        return $usedDeposit['deposit'];
    }

    /**
     * view의 셀렉트 박스 셀렉티드 처리 변수를 반환
     *
     * @param $requestParams
     *
     * @return array
     */
    public function setSelected($requestParams)
    {
        $selected = [];
        $selected['memberType'][gd_isset($requestParams['memberType'], 'select')] = $selected['pageNum'][$requestParams['pageNum']] = $selected['reasonCd'][$requestParams['reasonCd']] = $selected['groupSno'][$requestParams['groupSno']] = $selected['pageNum'][$requestParams['pageNum']] = 'selected="selected"';

        return $selected;
    }

    /**
     * 체크박스 UI 체크 처리 함수
     *
     * @param $requestParams
     *
     * @return array
     */
    public function setChecked($requestParams)
    {
        $checked = [];
        $checked['guideSend'][$requestParams['guideSend'][0]] = $checked['guideSend'][$requestParams['guideSend'][1]] = $checked['depositCheckFl'][$requestParams['depositCheckFl']] = $checked['removeMethodFl'][$requestParams['removeMethodFl']] = $checked['targetMemberFl'][$requestParams['targetMemberFl']] = $checked['mode'][$requestParams['mode']] = $checked['depositCheckFl'][$requestParams['depositCheckFl']] = $checked['regDtPeriod'][$requestParams['regDtPeriod']] = 'checked="checked"';

        return $checked;
    }

    /**
     * 예치금 지급/차감 내역 리스트 조회
     *
     * @param $requestParams
     *
     * @return array|object
     */
    public function getDepositList(array $requestParams)
    {
        $arrBind = $arrWhere = [];
        $fieldTypes = array_merge(DBTableField::getFieldTypes('tableMember'), DBTableField::getFieldTypes('tableMemberDeposit'));

        //@formatter:off
        $this->db->bindKeywordByTables(self::COMBINE_SEARCH, $requestParams, $arrBind, $arrWhere, ['tableMemberDeposit', 'tableMember'], ['md', 'mb']);
        //@formatter:on
        $this->db->bindParameter('reasonCd', $requestParams, $arrBind, $arrWhere, 'tableMemberDeposit', 'md');
        $this->db->bindParameter('groupSno', $requestParams, $arrBind, $arrWhere, 'tableMember', 'mb');
        $this->db->bindParameterByRange('deposit', $requestParams, $arrBind, $arrWhere, 'tableMemberDeposit', 'md');

        // 지급/차감 구분
        if ($requestParams['mode'] == 'add') {
            $arrWhere[] = 'md.deposit >= 0';
        } else if ($requestParams['mode'] == 'remove') {
            $arrWhere[] = 'md.deposit <= 0';
        }

        // 사유 기타일 경우 사유 내용 확인
        $requestParams['contents'] = gd_isset($requestParams['contents']);
        if ($requestParams['reasonCd'] == Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_ETC && $requestParams['contents'] != '') {
            $arrWhere[] = 'md.contents' . ' LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($arrBind, $fieldTypes['contents'], $requestParams['contents']);
        }
        // 지급/차감일
        $this->db->bindParameterByDateTimeRange('regDt', $requestParams, $arrBind, $arrWhere, 'tableMemberDeposit', 'md');

        // 검색제한(회원등급평가(수동))
        if (isset($requestParams['groupValidDt']) === true) {
            $arrWhere[] = 'groupValidDt < now()';
        }

        $this->db->bindParameter('handleMode', $requestParams, $arrBind, $arrWhere, 'tableMemberDeposit');
        $this->db->bindParameter('handleCd', $requestParams, $arrBind, $arrWhere, 'tableMemberDeposit');
        $this->db->bindParameter('handleNo', $requestParams, $arrBind, $arrWhere, 'tableMemberDeposit');
        $this->db->bindParameter('deleteFl', $requestParams, $arrBind, $arrWhere, 'tableMemberDeposit');
        $this->db->bindParameter('memNo', $requestParams, $arrBind, $arrWhere, 'tableMemberDeposit', 'md');

        // --- 페이지 설정
        $page = \App::load('Component\\Page\\Page', $requestParams['page'], 0, 0, $requestParams['pageNum']);
        $join = [];
        $join[] = ' LEFT JOIN ' . DB_MEMBER . ' as mb ON md.memNo= mb.memNo';
        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' as ma ON ma.sno= md.managerNo';

        $funcSelectLists = function ($arrBind, $arrWhere) use ($requestParams, $join) {
            $memberField = implode(', ', DBTableField::setTableField('tableMember', explode(',', 'memNo,memId,groupSno,memNm'), null, 'mb'));
            $depositField = implode(', ', DBTableField::setTableField('tableMemberDeposit', explode(',', 'memNo,managerId,deposit,afterDeposit,contents,handleMode,handleCd,handleNo,reasonCd,deleteFl,deleteScheduleDt,deleteDt,handleSno'), null, 'md'));
            $sort = gd_isset($requestParams['sort'], 'md.regDt desc');

            $db = \App::getInstance('DB');
            $db->strField = $memberField . ', ' . $depositField . ', md.sno, md.regDt,isDelete';
            $db->strJoin = implode('', $join);
            $db->strWhere = implode(' AND ', $arrWhere);
            $db->strOrder = $sort;
            $db->strLimit = '?,?';
            $db->bind_param_push($arrBind, 'i', ($requestParams['page'] - 1) * $requestParams['pageNum']);
            $db->bind_param_push($arrBind, 'i', $requestParams['pageNum']);

            $query = $db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_DEPOSIT . ' as md ' . implode(' ', $query);
            $resultSet = $db->query_fetch($strSQL, $arrBind);

            return $resultSet;
        };
        $data = $funcSelectLists($arrBind, $arrWhere);
        if (count($data)) {
            Manager::displayListData($data);
        }
        $funcCountLists = function ($arrBind, $arrWhere) use ($requestParams, $join) {
            $db = \App::getInstance('DB');
            $db->strField = 'COUNT(*) AS cnt';
            $db->strJoin = implode(' ', $join);
            $db->strWhere = implode(' AND ', $arrWhere);
            $query = $db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_DEPOSIT . ' as md ' . implode(' ', $query);
            $resultSet = $db->query_fetch($strSQL, $arrBind, false);
            StringUtils::strIsSet($resultSet['cnt'], 0);

            return $resultSet['cnt'];
        };
        $cnt = $funcCountLists($arrBind, $arrWhere);
        unset($arrBind);
        $page->recode['total'] = $cnt; // 검색 레코드 수

        return $data;
    }

    /**
     * 회원예치금 일괄 지급 처리
     *
     * @param $arrData
     *
     * @return bool
     * @throws Exception
     */
    public function addDeposit($arrData)
    {
        $this->validateBatchDeposit($arrData);

        $arrBind = $search = $arrWhere = [];

        $arrWhere[] = "find_in_set(memNo,?)";
        if (is_array($arrData['chk'])) {
            $this->db->bind_param_push($arrBind, 's', implode(',', $arrData['chk']));
        } else {
            $this->db->bind_param_push($arrBind, 's', $arrData['chk']);
        }

        return $this->getResultByAddDeposit($arrData, $arrWhere, $arrBind);
    }

    /**
     * 회원예치금 가감 처리
     *
     * @param int|string $memNo
     * @param int        $deposit    처리할 예치금
     * @param            $reasonCd
     * @param string     $handleMode 처리 모드 (m - 회원, o - 주문, b - 게시판, r - 추천인, c - 쿠폰)
     * @param string     $handleCd   처리 코드 (주문 번호, 게시판 코드)
     * @param string     $handleNo   처리 번호 (상품 번호, 게시물 번호)
     * @param string     $contents   적립 내용 ($handleType 처리 타입이 null 인경우 필수)
     * @param int        $handleSno   주문 취소 테이블 Sno

     * @return bool
     * @throws Exception
     */
    public function setMemberDeposit($memNo, $deposit, $reasonCd, $handleMode, $handleCd = null, $handleNo = null, $contents = null, $handleSno = null)
    {
        $arrBind = [];
        // 회원 번호 체크
        if (empty($memNo) === true) {
            return false;
        }

        // 지급 예치금 체크
        if (empty($deposit) === true) {
            return false;
        }

        $beforeMember = MemberDAO::getInstance()->selectMemberByOne($memNo);

        // 회원 존재여부 체크
        if ($this->db->num_rows() == 0) {
            return false;
        }

        // 예치금 처리
        $afterDeposit = $beforeMember['deposit'] + $deposit;

        // 저장할 내용 처리
        $tableField = DBTableField::tableMemberDeposit();
        $arrData = [];
        foreach ($tableField as $key => $val) {
            $arrData[$val['val']] = gd_isset(${$val['val']});
        }
        $arrData['beforeDeposit'] = $beforeMember['deposit'];
        $arrData['afterDeposit'] = $afterDeposit;
        $arrData['deposit'] = $deposit;
        $arrData['handleMode'] = $handleMode;
        $arrData['handleCd'] = $handleCd;
        $arrData['handleNo'] = $handleNo;
        $arrData['handleSno'] = $handleSno;

        //적립 사유 직접입력(01006006)이 아닐 경우 코드관리의 내용을 입력
        if ($reasonCd === Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_ETC) {
            $arrData['contents'] = $contents;
        } else if($reasonCd === Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_GOODS_BUY && is_null($contents) === false) {
            $arrData['contents'] = $contents;
        } else if($reasonCd === Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_GIFT_COUPON && is_null($contents) === false) {
            $arrData['contents'] = $contents;
        } else {
            $mileageReasons = gd_code(Deposit::REASON_CODE_GROUP);
            $arrData['contents'] = $mileageReasons[$arrData['reasonCd']];
        }

        $this->validateDeposit($arrData);

        // 관리자가 처리하는 경우
        if (Session::has('manager.managerId')) {
            $arrData['managerId'] = Session::get('manager.managerId');
            $arrData['managerNo'] = Session::get('manager.sno');
        }
        $arrData['regIp'] = Request::getRemoteAddress();

        $arrData['sno'] = $this->depositDAO->insertDeposit($arrData);
        $this->depositDAO->updateMemberDeposit($memNo, $afterDeposit);

        if ($this->allowSendSms) {
            $aBasicInfo = gd_policy('basic.info');
            $groupInfo = \Component\Member\Group\Util::getGroupName('sno=' . $beforeMember['groupSno']);
            $smsAuto = \App::load('Component\\Sms\\SmsAuto');
            $smsAuto->setSmsType(SmsAutoCode::MEMBER);
            $smsAuto->setSmsAutoCodeType($deposit < 0 ? Code::DEPOSIT_MINUS : Code::DEPOSIT_PLUS);
            $smsAuto->setReceiver($beforeMember);
            $smsAuto->setReplaceArguments(
                [
                    'name'       => $beforeMember['memNm'],
                    'memNm'      => $beforeMember['memNm'],
                    'memId'      => $beforeMember['memId'],
                    'mileage'    => $beforeMember['mileage'],
                    'deposit'    => $beforeMember['deposit'],
                    'groupNm'    => $groupInfo[$beforeMember['groupSno']],
                    'rc_deposit' => $deposit,
                    'rc_mallNm'  => Globals::get('gMall.mallNm'),
                    'shopUrl'    => $aBasicInfo['mallDomain'],
                ]
            );
            if ($this->smsReserveTime != null) {
                $smsAuto->setSmsAutoSendDate($this->smsReserveTime);
            }
            $smsAuto->autoSend();
        }

        return true;
    }

    /**
     * 다건의 예치금을 지급/차감 함수
     *
     * @param array $deposit
     *
     * @return array
     * @throws Exception
     */
    public function saveDeposit(array $deposit)
    {
        $this->validateDeposit($deposit);
        $deposit['sno'] = $this->depositDAO->insertDeposit($deposit);

        return $deposit;
    }

    /**
     * 예치금 지급/차감 검증 함수
     *
     * @param array $deposit
     *
     * @throws Exception
     */
    public function validateDeposit(array $deposit)
    {
        $validator = new Validator();
        $validator->add('memNo', 'number', true);
        // __('{예치금}')
        $validator->add('deposit', 'signDouble', true, '{예치금}');
        $validator->add('reasonCd', 'number', true);
        $validator->add('handleMode', 'alphaNum', true);
        $validator->add('handleCd', 'alphaNum', true);
        $validator->add('handleNo', 'alphaNum');
        $validator->add('contents', '', true);
        $validator->add('afterDeposit', 'signDouble', true);
        $validator->add('beforeDeposit', 'signDouble', true);

        if ($validator->act($deposit, true) === false) {
            throw new Exception(implode("\n", $validator->errors));
        }
    }

    /**
     * 예치금 차감 함수
     *
     * @param $arrData
     *
     * @return bool
     * @throws Exception
     */
    public function removeDeposit($arrData)
    {
        $this->validateBatchDeposit($arrData);

        $arrBind = $search = $arrWhere = [];

        $arrWhere[] = "find_in_set(memNo,?)";
        if (is_array($arrData['chk'])) {
            $this->db->bind_param_push($arrBind, 's', implode(',', $arrData['chk']));
        } else {
            $this->db->bind_param_push($arrBind, 's', $arrData['chk']);
        }

        return $this->getResultByRemoveDeposit($arrData, $arrWhere, $arrBind);
    }

    /**
     * 검색회원 전체 지급 함수
     *
     * @param $arrData
     * @param $searchJson
     *
     * @return bool
     * @throws Exception
     */
    public function addDepositAll($arrData, $searchJson)
    {
        /** @var \Bundle\Component\Member\MemberAdmin $memberAdmin */
        $memberAdmin = App::load('\\Component\\Member\\MemberAdmin');

        $this->validateBatchDeposit($arrData);

        $searchJson = json_decode($searchJson);
        $searchJson = ArrayUtils::objectToArray($searchJson);
        $tmp = $memberAdmin->searchMemberWhere($searchJson);
        $arrBind = $tmp['arrBind'];
        $arrWhere = $tmp['arrWhere'];

        return $this->getResultByAddDeposit($arrData, $arrWhere, $arrBind);
    }

    /**
     * 검색회원 전체 차감 함수
     *
     * @param $arrData
     * @param $searchJson
     *
     * @return bool
     * @throws Exception
     */
    public function removeDepositAll($arrData, $searchJson)
    {
        /** @var \Bundle\Component\Member\MemberAdmin $memberAdmin */
        $memberAdmin = App::load('\\Component\\Member\\MemberAdmin');

        $this->validateBatchDeposit($arrData);

        $searchJson = json_decode($searchJson);
        $searchJson = ArrayUtils::objectToArray($searchJson);
        $tmp = $memberAdmin->searchMemberWhere($searchJson);
        $arrBind = $tmp['arrBind'];
        $arrWhere = $tmp['arrWhere'];

        return $this->getResultByRemoveDeposit($arrData, $arrWhere, $arrBind);
    }

    /**
     * 세션 데이터를 기준으로 예치금 리스트를 조회
     *
     * @param array $searchDt
     * @param int   $offset
     * @param int   $limit
     *
     * @return array
     */
    public function listBySession(array $searchDt = [], $offset = 1, $limit = 10)
    {
        $session = \App::getInstance('session');
        $this->arrWhere = $this->arrBind = [];

        $join[] = ' ';

        $this->arrWhere[] = 'a.memNo=' . $session->get('member.memNo');
        $search = ['a.regDt' => $searchDt];
        $this->db->bindParameterByDateTimeRange('a.regDt', $search, $this->arrBind, $this->arrWhere, $this->tableFunctionName);
        $this->db->strField = 'a.*, substr(a.regDt, 1, 10) AS regdate, oh.handleReason, oh.handleDetailReasonShowFl';
        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER_HANDLE . ' as oh ON oh.sno = a.handleSno';
        $this->db->strWhere = implode(' AND ', $this->arrWhere);
        $this->db->strOrder = 'a.regDt DESC';

        if (is_null($offset) === false && is_null($limit) === false) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT /* 회원 예치금 */' . array_shift($query) . ' FROM ' . DB_MEMBER_DEPOSIT . ' AS a ' . implode(' ', $query);
        $list = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($query);

        return $list;
    }

    /**
     * 세션 기준 예치금 리스트 검색결과 카운트 함수
     * \Component\Deposit\Deposit::listBySession 함수가 실행되어야 검색조건을 참조한다.
     *
     * @return int
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function foundRowsByListSession()
    {
        $db = \App::getInstance('DB');
        $db->strField = 'COUNT(*) AS cnt';
        $db->strWhere = implode(' AND ', $this->arrWhere);
        $query = $db->query_complete();
        $strSQL = 'SELECT /* 회원 예치금 */' . array_shift($query) . ' FROM ' . DB_MEMBER_DEPOSIT . ' AS a ' . implode(' ', $query);
        $resultSet = $db->query_fetch($strSQL, $this->arrBind, false);
        StringUtils::strIsSet($resultSet['cnt'], 0);

        return $resultSet['cnt'];
    }

    /**
     * @param string $smsReserveTime
     */
    public function setSmsReserveTime(string $smsReserveTime)
    {
        $this->smsReserveTime = $smsReserveTime;
    }

    /**
     * 재정의 랩핑 함수
     *
     */
    protected function initResultStorage()
    {
        $this->_initResultStorage();
    }

    /**
     * 재정의 랩핑 함수
     *
     * @param $arrData
     *
     * @throws Exception
     */
    protected function validateBatchDeposit($arrData)
    {
        $this->_validateBatchDeposit($arrData);
    }

    /**
     * 재정의 랩핑 함수
     *
     * @param $arrData
     * @param $arrWhere
     * @param $arrBind
     *
     * @return bool
     */
    protected function getResultByAddDeposit($arrData, $arrWhere, $arrBind)
    {
        return $this->_getResultByAddDeposit($arrData, $arrWhere, $arrBind);
    }

    /**
     * 재정의 랩핑 함수
     *
     * @param $arrData
     * @param $arrWhere
     * @param $arrBind
     *
     * @return bool
     */
    protected function getResultByRemoveDeposit($arrData, $arrWhere, $arrBind)
    {
        return $this->_getResultByRemoveDeposit($arrData, $arrWhere, $arrBind);
    }

    /**
     * 예치금 지급/차감 결과 Storage 초기화 함수
     */
    private function _initResultStorage()
    {
        $this->resultStorage = new SimpleStorage();
        $this->resultStorage->set('totalCount', 0);
        $this->resultStorage->set('successCount', 0);
        $this->resultStorage->set('failCount', 0);
        $this->resultStorage->set('excludeCount', 0);
        $this->resultStorage->set('minusCount', 0);
    }

    /**
     * 예치금 일괄 지급/차감 검증
     *
     * @param $arrData
     *
     * @throws Exception
     */
    private function _validateBatchDeposit($arrData)
    {
        $v = new Validator();
        $v->init();
        // __('{지급/차감여부}')
        // __('{예치금 부족 시 차감방법}')
        // __('{지급 예치금}')
        $v->add('depositCheckFl', 'pattern', true, '{지급/차감여부}', '/^(add|remove)$/');
        $v->add('removeMethodFl', 'pattern', true, '{예치금 부족 시 차감방법}', '/^(minus|exclude)$/');
        $v->add('depositValue', 'pattern', true, '{지급 예치금}', '/^[0-9]*$/');
        $v->add('reasonCd', 'number', true);
        $v->add('contents', '', true, '지급/차감 사유를 입력해주세요.');

        if ($v->act($arrData, true) === false) {
            throw new Exception(implode("\n", $v->errors));
        }
    }

    /**
     * 예치금 지급 및 결과를 반환하는 함수
     *
     * @param $arrData
     * @param $arrWhere
     * @param $arrBind
     *
     * @return bool
     */
    private function _getResultByAddDeposit($arrData, $arrWhere, $arrBind)
    {
        $where = (count($arrWhere) ? ' WHERE ' . implode(' and ', $arrWhere) : '');
        $strSQL = 'SELECT memNo, memId, memNm, deposit, cellPhone, email, maillingFl, smsFl FROM ' . DB_MEMBER . ' as m ' . $where;
        $data = $this->db->query_fetch($strSQL, (empty($arrBind) === false ? $arrBind : null));
        \Logger::debug(__METHOD__, $data);
        $depositValue = $arrData['depositValue'];
        $reasonCd = $arrData['reasonCd'];

        if (isset($data) && is_array($data)) {
            $isGuideEmail = isset($arrData['guideSend']) && in_array('email', $arrData['guideSend']);
            $this->allowSendSms = in_array('sms', $arrData['guideSend']);
            foreach ($data as $val) {
                $result = $this->setMemberDeposit($val['memNo'], $depositValue, $reasonCd, 'm', null, null, $arrData['contents']);

                // 회원의 메일링 수신동의와 자동발송 설정, 회원안내가 참일 경우에만 메일을 발송
                if ($val['maillingFl'] === 'y' && $result && $isGuideEmail) {
                    $mailArgs['memId'] = $val['memId'];
                    $mailArgs['memNm'] = $val['memNm'];
                    $mailArgs['email'] = $val['email'];
                    $mailArgs['deposit'] = $depositValue;
                    $mailArgs['totalDeposit'] = $val['deposit'] + $depositValue;

                    $this->mailMimeAuto->init(MailMimeAuto::ADD_DEPOSIT, $mailArgs)->autoSend();
                }
                /* 2017-02-13 yjwee sms 발송은 setMemberDeposit 에서 처리하며 관리자에서 일괄 지급/차감 시 발송 안함 설정 상태를 제외하고는 모두 발송입니다. */
            }

            return true;
        }

        return false;
    }

    /**
     * 예치금 차감 및 결과 반환 함수
     *
     * @param $arrData
     * @param $arrWhere
     * @param $arrBind
     *
     * @return bool
     */
    private function _getResultByRemoveDeposit($arrData, $arrWhere, $arrBind)
    {
        $where = (count($arrWhere) ? ' WHERE ' . implode(' and ', $arrWhere) : '');
        $strSQL = 'SELECT * FROM ' . DB_MEMBER . ' as m ' . $where;
        $data = $this->db->query_fetch($strSQL, (empty($arrBind) === false ? $arrBind : null));


        $depositValue = $arrData['depositValue'];
        $removeMethodFl = $arrData['removeMethodFl'];
        $reasonCd = $arrData['reasonCd'];

        $depositValue = abs($depositValue) * -1;

        if (isset($data) && is_array($data)) {
            $this->initResultStorage();
            $this->resultStorage->set('totalCount', count($data));
            $isGuideEmail = isset($arrData['guideSend']) && in_array('email', $arrData['guideSend']);
            $this->allowSendSms = in_array('sms', $arrData['guideSend']);
            foreach ($data as $val) {
                $setDepositValue = $depositValue;
                $currentDeposit = $val['deposit'];
                $isLess = ($currentDeposit + $depositValue) < 0;
                if (($removeMethodFl === 'exclude') && $isLess) {
                    $this->resultStorage->increase('excludeCount');
                    continue;   // 예치금 부족 시 제외
                }
                if (($removeMethodFl === 'minus') && $isLess) {
                    $this->resultStorage->increase('minusCount');
                    $setDepositValue = abs($currentDeposit) * -1;  // 남은 예치금만 차감 처리
                }
                $result = $this->setMemberDeposit($val['memNo'], $setDepositValue, $reasonCd, 'm', null, null, $arrData['contents']);

                if ($result) {
                    $this->resultStorage->increase('successCount');
                } else {
                    $this->resultStorage->increase('failCount');
                }

                // 회원의 메일링 수신동의와 자동발송 설정이 참일 경우에만 메일을 발송
                if ($val['maillingFl'] === 'y' && $result && $isGuideEmail) {
                    $mailArgs['memId'] = $val['memId'];
                    $mailArgs['memNm'] = $val['memNm'];
                    $mailArgs['email'] = $val['email'];
                    $mailArgs['deposit'] = $setDepositValue;
                    $mailArgs['totalDeposit'] = $val['deposit'] + $setDepositValue;
                    $this->mailMimeAuto->init(MailMimeAuto::REMOVE_DEPOSIT, $mailArgs)->autoSend();
                }
                /* 2017-02-13 yjwee sms 발송은 setMemberMileage 에서 처리하며 관리자에서 일괄 지급/차감 시 발송 안함 설정 상태를 제외하고는 모두 발송입니다. */
            }

            return true;
        }

        return false;
    }

    /**
     * 예치금 사유코드 수정
     *
     * @param $arrData
     *
     * @return bool
     * @throws Exception
     */
    public function reasonCdModifyDeposit($arrData)
    {
        if (empty($arrData['sno']) || empty($arrData['contents']) || empty($arrData['reasonModiyCode'])) {
            return true;
        }
        $db = App::load('DB');
        $depositReasons = gd_code(Deposit::REASON_CODE_GROUP);
        $sno = $arrData['sno'];

        if($arrData['reasonModiyCode'] == Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_ETC) {
            $depositData['reasonCd'] = Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_ETC;
            $depositData['contents'] = $arrData['contents'];
        }else{
            $depositData['reasonCd'] = $arrData['reasonModiyCode'];
            $depositData['contents'] = $depositReasons[$arrData['reasonModiyCode']];
        }
        $compareField = array_keys($depositData);
        $arrBind = $this->db->get_binding(DBTableField::tableMemberDeposit(), $depositData, 'update', $compareField);
        $db->bind_param_push($arrBind['bind'], 'i',  $sno);
        $rs = $db->set_update_db(DB_MEMBER_DEPOSIT, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        unset($arrBind, $depositData);
        return $rs;
    }
}
