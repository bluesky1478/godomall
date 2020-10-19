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

namespace Bundle\Component\Mileage;

use App;
use Component\Database\DBTableField;
use Component\Mail\MailMimeAuto;
use Component\Member\MemberVO;
use Component\Sms\Code;
use Component\Sms\SmsAutoCode;
use Component\Validator\Validator;
use Component\Mileage\MileageUtil;
use Exception;
use Framework\Object\SimpleStorage;
use Framework\Utility\ArrayUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\UrlUtils;
use Globals;

/**
 * 마일리지 관련 기능 담당 클래스
 * @package Bundle\Component\Mileage
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class Mileage extends \Component\AbstractComponent
{
    /** 마일리지 지급/차감사유 그룹 코드 */
    const REASON_CODE_GROUP = '01005';
    /** 마일리지 지급/차감사유 상품구매 시 마일리지 사용 */
    const REASON_CODE_USE_GOODS_BUY = '001';
    /** 마일리지 지급/차감사유 상품구매 시 마일리지 지급 */
    const REASON_CODE_ADD_GOODS_BUY = '002';
    /** 마일리지 지급/차감사유 환불 시 사용 마일리지 환불 */
    const REASON_CODE_USE_GOODS_BUY_RESTORE = '003';
    /** 마일리지 지급/차감사유 환불 시 적립 마일리지 차감 */
    const REASON_CODE_ADD_GOODS_BUY_RESTORE = '004';
    /** 마일리지 지급/차감사유 신규회원 가입 */
    const REASON_CODE_JOIN_MEMBER = '005';
    /** 마일리지 지급/차감사유 가입 시 추천인 등록 */
    const REASON_CODE_REGISTER_RECOMMEND = '006';
    /** 마일리지 지급/차감사유 신규회원에 의한 추천 */
    const REASON_CODE_RECEIVE_RECOMMEND = '007';
    /** 마일리지 지급/차감사유 환불 시 기타환불 처리 */
    const REASON_CODE_ETC_REFUND = '008';
    /** 마일리지 지급/차감사유 게시글등록 시 마일리지 지급 */
    const REASON_CODE_WRITE_BOARD = '009';
    /** 마일리지 지급/차감사유 게시글삭제 시 마일리지 차감 */
    const REASON_CODE_REMOVE_BOARD = '010';
    /** 마일리지 지급/차감사유 기타 */
    const REASON_CODE_ETC = '011';
    /** 마일리지 지급/차감사유 휴면회원 해제 시 소멸 대상 마일리지 소멸 */
    const REASON_CODE_MEMBER_WAKE = '9997';
    /** 마일리지 지급/차감사유 휴면회원 전환으로 마일리지 소멸 */
    const REASON_CODE_MEMBER_SLEEP = '9998';
    /** 마일리지 지급/차감사유 소멸 */
    const REASON_CODE_EXPIRE = '9999';
    /** 마일리지 지급/차감사유 취소 시 사용 마일리지 환불 */
    const REASON_CODE_ADD_BUY_CANCEL = '501';
    /** 마일리지 지급/차감사유 회원정보 수정 이벤트 참여 */
    const REASON_CODE_MEMBER_MODIFY_EVENT = '502';
    /** 마일리지 지급/차감사유 쿠폰 마일리지 적립 */
    const REASON_CODE_MILEAGE_SAVE_COUPON = '504';
    /** 마일리지 지급/차감사유 기프트쿠폰 적립 */
    const REASON_CODE_GIFT_COUPON = '505';
    /** 마일리지 지급/차감사유 모바일앱 혜택 */
    const REASON_CODE_MILEAGE_MOBILE_APP = '9996';

    /** 마일리지 지급/차감사유 게시글등록 시 마일리지 지급 */
    const REASON_TEXT_MEMBER_WAKE = '휴면해제로 유효기간 만료 마일리지 소멸'; //__('휴면해제로 유효기간 만료 마일리지 소멸')
    const REASON_TEXT_MEMBER_SLEEP = '휴면전환으로 마일리지 소멸'; //__('휴면전환으로 마일리지 소멸')
    const REASON_TEXT_EXPIRE = '유효기간 만료로 마일리지 소멸'; //__('유효기간 만료로 마일리지 소멸')

    const COMBINE_SEARCH = [
        'all'       => '=통합검색=',
        //__('=통합검색=')
        'memNm'     => '이름',
        //__('이름')
        'memId'     => '아이디',
        //__('아이디')
        'nickNm'    => '닉네임',
        //__('닉네임')
        'managerId' => '처리자',
        //__('처리자')
    ];

    /** @var  \Bundle\Component\Member\History $historyService 회원정보변경내역 클래스 */
    protected $historyService;
    /** @var  \Bundle\Component\Mileage\MileageDAO $mileageDAO 마일리지 데이터베이스 클래스 */
    protected $mileageDAO;
    /** @var  \Bundle\Component\Member\MemberDAO $memberDAO 회원 데이터베이스 클래스 */
    protected $memberDAO;
    /** @var  \Bundle\Component\Member\MemberAdmin $memberAdmin 관리자 회원 클래스 */
    protected $memberAdmin;
    /** @var \Bundle\Component\Member\Member $memberService 회원 클래스 */
    protected $memberService;
    /** @var  \Bundle\Component\Sms\SmsAuto $smsAuto 자동 SMS 클래스 */
    protected $smsAuto;

    /** @var  SimpleStorage $resultStorage 마일리지 지급 결과 저장소 */
    protected $resultStorage;
    /** @var array $fieldTypes 마일리지 테이블 필드 타입 */
    protected $fieldTypes;
    /** @var array $mileageMail 메일발송 정보 */
    protected $mileageMail = [];
    /** @var bool $isMailingFlag 수신동의 및 설정 여부 */
    protected $isMailingFlag = false;
    /**
     * @var bool $isSmsFlag 수신동의 및 설정 여부
     * @deprecated 2017-02-09 yjwee 마일리지 sms 는 회원 수신여부와 무관함.
     */
    protected $isSmsFlag = false;
    /** @var bool $allowSendSms 마일리지 지급/차감 SMS 발송 여부 상태 값. 관리자 일괄 지급/차감 에서 발송 설정을 하지 않은 경우를 제외하고 모두 발송 */
    protected $allowSendSms = true;
    /** @var string $smsReserveTime SMS 의 예약시간 */
    protected $smsReserveTime;

    /**
     * 2016-10-06 yjwee 기존 생성자 파라미터로 받던 MileageDAO, History 는 func_get_args 함수를 통해 처리되도록 수정
     */
    private $arrWhere;

    private $arrBind;

    public function __construct()
    {
        parent::__construct();
        $args = func_get_args();
        $this->tableFunctionName = 'tableMemberMileage';
        $this->fieldTypes = DBTableField::getFieldTypes($this->tableFunctionName);
        $this->memberAdmin = App::load(\Component\Member\MemberAdmin::class);
        $this->memberService = App::load(\Component\Member\Member::class);
        // 기존 생성자는 1번째 파라미터는 MileageDAO 클래스 이기때문에 배열로 넘어온 경우는 변경된 후에 해당 클래스를 사용 한 것으로 판단함.
        if (is_array($args[0]) || empty($args)) {
            $config = $args[0];
            $this->mileageDAO = is_object($config['mileageDAO']) ? $config['mileageDAO'] : new \Component\Mileage\MileageDAO();
            $this->memberDAO = is_object($config['memberDAO']) ? $config['memberDAO'] : new \Component\Member\MemberDAO();
            $this->historyService = is_object($config['historyService']) ? $config['historyService'] : new \Component\Member\History();
            $this->smsAuto = is_object($config['smsAuto']) ? $config['smsAuto'] : new \Component\Sms\SmsAuto();
            $this->memberAdmin = is_object($config['memberAdmin']) ? $config['memberAdmin'] : App::load(\Component\Member\MemberAdmin::class);
            $this->memberService = is_object($config['memberService']) ? $config['memberService'] : App::load(\Component\Member\Member::class);
        } else {
            // 기존 생성자를 사용하여 클래스 객체를 넘긴 경우에 대한 대비
            if (get_class($args[0]) === \Component\Mileage\MileageDAO::class) {
                $this->mileageDAO = $args[0];
            }
            if (get_class($args[1]) === \Component\Member\History::class) {
                $this->historyService = $args[1];
            }
        }
        $this->tableFunctionName = 'tableMemberMileage';
        $this->memberAdmin = App::load('\\Component\\Member\\MemberAdmin');
        $this->memberService = App::load('\\Component\\Member\\Member');
        $this->fieldTypes = DBTableField::getFieldTypes($this->tableFunctionName);
    }

    /**
     * 마일리지 테이블 필드 타입 반환
     *
     * @return array
     */
    public function getFieldTypes()
    {
        return $this->fieldTypes;
    }

    /**
     * 회원 세션의 회원번호를 이용하여 해당 회원의 마일리지 내역을 조회한다.
     *
     * @param array $searchDt
     * @param int   $offset
     * @param int   $limit
     *
     * @return mixed
     */
    public function listBySession(array $searchDt = [], $offset = 1, $limit = 10)
    {
        $this->arrWhere = $this->arrBind = [];
        $session = \App::getInstance('session');
        $this->arrWhere[] = 'memNo=' . $session->get('member.memNo');
        $search = ['regDt' => $searchDt];
        $this->db->bindParameterByDateTimeRange('regDt', $search, $this->arrBind, $this->arrWhere, $this->tableFunctionName);
        $this->db->strWhere = implode(' AND ', $this->arrWhere);
        $this->db->strField = '*, substr(regDt, 1, 10) AS regdate';
        $this->db->strOrder = 'sno DESC';

        if (is_null($offset) === false && is_null($limit) === false) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT /* 회원 적립금 */' . array_shift($query) . ' FROM ' . DB_MEMBER_MILEAGE . ' ' . implode(' ', $query);
        $list = $this->db->query_fetch($strSQL, $this->arrBind);

        unset($query);

        return $list;
    }

    /**
     * 세션 기준 마일리지 리스트 검색결과 카운트 함수
     * \Component\Mileage\Mileage::listBySession 함수가 실행되어야 검색조건을 참조한다.
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
        $strSQL = 'SELECT /* 회원 적립금 */' . array_shift($query) . ' FROM ' . DB_MEMBER_MILEAGE . ' AS a ' . implode(' ', $query);
        $resultSet = $db->query_fetch($strSQL, $this->arrBind, false);
        StringUtils::strIsSet($resultSet['cnt'], 0);

        return $resultSet['cnt'];
    }

    /**
     * 회원 마일리지 내역
     *
     * @param int $memNo   회원 번호
     * @param int $page    페이지 번호
     * @param int $perPage 페이지당 리스트수
     *
     * @return mixed
     *
     * @deprecated 2018-05-18 사용하는 곳이 없는 함수이므로 제거 될 수 있습니다. 사용하지 마시기 바랍니다.
     */
    public function getMemberMileageList($memNo, $page = 1, $perPage = 10)
    {
        $arrBind = [];
        $arrWhere = null;
        $funcLists = function () use ($memNo, $perPage) {
            $start = (gd_isset($page, 1) - 1) * $perPage;
            $this->db->strField = "*, substr(regDt, 1, 10) AS regdate";
            $this->db->strWhere = " memNo=? ";
            $this->db->strLimit = "?,?";
            $this->db->strOrder = "sno DESC";
            $this->db->bind_param_push($arrBind, 'i', $memNo);
            $this->db->bind_param_push($arrBind, 'i', $start);
            $this->db->bind_param_push($arrBind, 'i', $perPage);

            $query = $this->db->query_complete();
            $strSQL = "SELECT " . array_shift($query) . " FROM " . DB_MEMBER_MILEAGE . " " . implode(" ", $query);
            $resultSet = $this->db->query_fetch($strSQL, $arrBind);

            return $resultSet;
        };
        $funcFoundRows = function () use ($memNo) {
            $db = \App::getInstance('DB');
            $db->strField = 'COUNT(*) AS cnt';
            $db->strWhere = 'memNo=?';
            $db->bind_param_push($arrBind, 'i', $memNo);
            $query = $this->db->query_complete();
            $strSQL = "SELECT " . array_shift($query) . " FROM " . DB_MEMBER_MILEAGE . " " . implode(" ", $query);
            $resultSet = $this->db->query_fetch($strSQL, $arrBind, false);
            StringUtils::strIsSet($resultSet['cnt'], 0);

            return $resultSet['cnt'];
        };

        $getData['list'] = gd_htmlspecialchars_stripslashes($funcLists());
        $getData['searchCnt'] = $funcFoundRows();

        return $getData;
    }

    /**
     * 회원마일리지 일괄 차감 처리
     *
     * @param $arrData
     *
     * @return bool
     * @throws Exception
     */
    public function removeMileage($arrData)
    {
        $db = App::load('DB');

        $this->validateBatchMileage($arrData);

        $arrBind = $search = $arrWhere = [];

        $arrWhere[] = "find_in_set(memNo,?)";
        if (is_array($arrData['chk'])) {
            $db->bind_param_push($arrBind, 's', implode(',', $arrData['chk']));
        } else {
            $db->bind_param_push($arrBind, 's', $arrData['chk']);
        }

        return $this->getResultByRemoveMileage($arrData, $arrWhere, $arrBind);
    }

    /**
     * 일괄 처리 결과를 반환하는 함수
     *
     * @return SimpleStorage
     */
    public function getResultStorage()
    {
        return $this->resultStorage;
    }

    /**
     * 회원 마일리지 가감 처리
     *
     * @param      $memNo            int 회원 번호
     * @param      $targetMileage    int 처리할 마일리지
     * @param      $reasonCd         string 지급 사유 코드
     * @param      $handleMode       string 처리 모드 (m - 회원, o - 주문, b - 게시판, r - 추천인, c - 쿠폰)
     * @param      $handleCd         string 처리 코드 (주문 번호, 게시판 코드, 추천한 사람 ID)
     * @param null $handleNo         string 처리 번호 (상품 번호, 게시물 번호)
     * @param null $contents         string 사유(reasonCd가 기타가 아니면 입력할 필요 없는 항목)
     *
     * @return bool
     */
    public function setMemberMileage($memNo, $targetMileage, $reasonCd, $handleMode, $handleCd, $handleNo = null, $contents = null, $frontPage = false)
    {
        $logger = \App::getInstance('logger');
        $logger->info('Start setMemberMileage.');
        StringUtils::strIsSet($memNo, '');
        $member = $this->getDataByTable(DB_MEMBER, $memNo, 'memNo', '*');
        $memberVO = new MemberVO($member);
        if ($this->db->num_rows() == 0) {
            $logger->warning(__METHOD__ . ', 마일리지 지급 대상을 찾기 못하였습니다.', func_get_args());

            return false;
        }

        $domain = new MileageDomain();   // 지급/차감 처리할 마일리지 정보
        $domain->setMemNo($memNo);
        $domain->setMileage($targetMileage);
        $domain->setReasonCd($reasonCd);
        $domain->setHandleMode($handleMode);
        $domain->setHandleCd($handleCd);
        $domain->setHandleNo($handleNo);
        $domain->setContents($contents);
        $domain->setBeforeMileage($memberVO->getMileage());
        $domain->setAfterMileage($memberVO->getMileage() + $targetMileage);

        try {
            $this->validateMileage($domain->toArray());
            /*
             * 마일리지 유효기간 설정
             * 2017-02-03 yjwee 마일리지 지급 시에만 설정하게끔 수정
             */
            if ($domain->getMileage() > 0) {
                $domain->setDeleteScheduleDt($domain->getMileage() > 0 ? MileageUtil::getDeleteScheduleDate() : '0000-00-00 00:00:00');
                $logger->info(sprintf('Set add mileage delete schedule datetime[%s]', $domain->getDeleteScheduleDt()));
            }
            /*
             * 소멸 마일리지 여부
             * 2017-02-03 yjwee 소멸 마일리지 체크하는 로직 수정 날짜로는 체크가 안되어서 사유 코드로 체크하게끔 함
             */
            if ($reasonCd == self::REASON_CODE_GROUP . self::REASON_CODE_EXPIRE) {
                $domain->setDeleteFl('y');
                $logger->info(sprintf('Set delete mileage flag[%s]', $domain->getDeleteFl()));
            }

            //유저페이지에서 마일리지가 지급되는 경우 관리자 아이디가 저장되는 경우가 있어서 저장안되게 처리
            if($frontPage === true){
                $domain->setManagerNo(0);
                $domain->setManagerId('');
            }

            $sno = $this->mileageDAO->insertMileage($domain);
            $logger->info(sprintf('Mileage have been added. sno[%s], memNo[%s]', $sno, $memNo));
            $domain->setSno($sno);
            // 적립/차감 내용 저장
            if ($domain->getMileage() < 0 && $domain->getSno() > 0) {
                try {
                    $dao = new MileageDAO();
                    $history = new MileageHistory($dao, $this);
                    $history->setMileageDomain($domain);
                    $history->saveUseHistory();
                    $logger->info('Saved usage mileage history.');
                } catch (\Throwable $e) {
                    $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());

                    return false;
                }
            }
            // 회원 마일리지 가감 처리
            $this->memberService->update($domain->getMemNo(), 'memNo', ['mileage'], [$domain->getAfterMileage()]);
        } catch (\Throwable $e) {
            $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());

            return false;
        }
        StringUtils::strIsSet($member['sleepFl'], '');
        if ($member['sleepFl'] === 'y' || $reasonCd == self::REASON_CODE_GROUP . self::REASON_CODE_MEMBER_SLEEP || $reasonCd == self::REASON_CODE_GROUP . self::REASON_CODE_MEMBER_WAKE) {
            $this->allowSendSms = false;
        }
        // 마일리지 지급/차감 SMS 는 수신동의 상관없이 설정값에 따라 발송이 되므로 관리자 일괄 지급/차감에서 sms 미발송으로 설정한 경우에만 발송하지 않도록 한다.
        if ($this->allowSendSms) {
            $aBasicInfo = gd_policy('basic.info');
            $groupInfo = \Component\Member\Group\Util::getGroupName('sno=' . $memberVO->getGroupSno());
            $smsAuto = \App::load(\Component\Sms\SmsAuto::class);
            $smsAuto->setSmsType(SmsAutoCode::MEMBER);
            $smsAuto->setSmsAutoCodeType($targetMileage < 0 ? Code::MILEAGE_MINUS : Code::MILEAGE_PLUS);
            $smsAuto->setReceiver($memberVO->getCellPhone());
            $smsAuto->setReplaceArguments(
                [
                    'name'       => $memberVO->getMemNm(),
                    'memNm'      => $memberVO->getMemNm(),
                    'memId'      => $memberVO->getMemId(),
                    'deposit'    => $memberVO->getDeposit(),
                    'mileage'    => $memberVO->getMileage(),
                    'groupNm'    => $groupInfo[$memberVO->getGroupSno()],
                    'rc_mileage' => $targetMileage,
                    'rc_mallNm'  => $aBasicInfo['mallNm'],
                    'shopUrl'    => $aBasicInfo['mallDomain'],
                ]
            );
            if ($this->smsReserveTime != null) {
                $smsAuto->setSmsAutoSendDate($this->smsReserveTime);
            }
            $smsAuto->autoSend();
        } else {
            $logger->info(sprintf('%s, disAllow send sms.', __METHOD__));
        }

        return true;
    }

    /**
     * 엑셀 마일리지에서 사용하는 함수
     *
     * @param MileageDomain $domain
     *
     * @return MileageDomain
     * @throws Exception
     */
    public function saveMileage(MileageDomain $domain)
    {
        $this->validateMileage($domain->toArray());
        $domain->setDeleteScheduleDt(MileageUtil::getDeleteScheduleDate());
        if ($this->isDeleteMileage($domain->getDeleteScheduleDt())) {
            $domain->setDeleteFl('y');
        }

        $domain->setSno($this->mileageDAO->insertMileage($domain));

        return $domain;
    }

    /**
     * 마일리지 사용내역 저장 함수
     *
     * @param MileageDomain $domain
     */
    public function saveMileageUseHistory(MileageDomain $domain)
    {
        if ($domain->getMileage() < 0 && $domain->getSno() > 0) {
            $history = new MileageHistory($this->mileageDAO, $this);
            $history->setMileageDomain($domain);
            $history->saveUseHistory();
        }
    }

    /**
     * 마일리지 지급/차감 검증 함수
     *
     * @param array $mileage
     *
     * @throws Exception
     */
    public function validateMileage(array $mileage)
    {
        $validator = new Validator();
        $validator->add('memNo', 'number', true);
        $validator->add('mileage', 'signDouble', true, '{' . __('마일리지') . '}');
        $validator->add('reasonCd', 'number', true);
        $validator->add('handleMode', 'alphaNum', true);
        $validator->add('handleCd', '', true);
        $validator->add('handleNo', '');
        $validator->add('contents', '', true);
        $validator->add('beforeMileage', 'signDouble', true);
        $validator->add('afterMileage', 'signDouble', true);

        if ($validator->act($mileage, true) === false) {
            \App::getInstance('logger')->info(__METHOD__, $validator->errors);
            throw new Exception(implode("\n", $validator->errors));
        }
    }

    /**
     * 검색 대상 회원 차감 함수
     *
     * @param $arrData
     * @param $searchJson
     *
     * @return bool
     * @throws Exception
     */
    public function allRemoveMileage($arrData, $searchJson)
    {
        $this->validateBatchMileage($arrData);

        $searchJson = json_decode($searchJson);
        $searchJson = ArrayUtils::objectToArray($searchJson);
        $tmp = $this->memberAdmin->searchMemberWhere($searchJson);
        $arrBind = $tmp['arrBind'];
        $arrWhere = $tmp['arrWhere'];

        return $this->getResultByRemoveMileage($arrData, $arrWhere, $arrBind);
    }

    /**
     * 검색 대상 회원 지급 함수
     *
     * @param $arrData
     * @param $searchJson
     *
     * @return bool
     * @throws Exception
     */
    public function allAddMileage($arrData, $searchJson)
    {
        $this->validateBatchMileage($arrData);

        $searchJson = json_decode($searchJson);
        $searchJson = ArrayUtils::objectToArray($searchJson);
        $tmp = $this->memberAdmin->searchMemberWhere($searchJson);
        $arrBind = $tmp['arrBind'];
        $arrWhere = $tmp['arrWhere'];

        return $this->getResultByAddMileage($arrData, $arrWhere, $arrBind);
    }

    /**
     * 회원마일리지 일괄 지급 처리
     *
     * @param $arrData
     *
     * @return bool
     * @throws Exception
     */
    public function addMileage($arrData)
    {
        $this->validateBatchMileage($arrData);

        $arrBind = $search = $arrWhere = [];

        $arrWhere[] = "find_in_set(memNo,?)";
        if (is_array($arrData['chk'])) {
            $this->db->bind_param_push($arrBind, 's', implode(',', $arrData['chk']));
        } else {
            $this->db->bind_param_push($arrBind, 's', $arrData['chk']);
        }

        return $this->getResultByAddMileage($arrData, $arrWhere, $arrBind);
    }

    /**
     * @deprecated 해당함수는 사용하지 않는 것을 권장합니다.
     *
     * @param       $data
     * @param       $where
     * @param array $arrField
     * @param array $arrData
     */
    public function update($data, $where, array $arrField, array $arrData)
    {
        $arrBind = $arrUpdate = [];

        foreach ($arrField as $key => $value) {
            $arrUpdate[] = $value . '= ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes[$value], $arrData[$key]);
        }

        if (is_array($data) === true && is_array($where) === true && count($data) === count($where)) {
            $this->db->strWhere = implode(' AND', $where);
            foreach ($where as $idx => $val) {
                $fieldType = $this->fieldTypes[$val];
                $this->db->bind_param_push($arrBind, $fieldType, $data[$idx]);
            }
        } else {
            $this->db->strWhere = $where;

            if ($data !== null) {
                $this->db->strWhere = $where . ' = ?';
                $this->db->bind_param_push($arrBind, $this->fieldTypes[$where], $data);
            }
        }

        $this->db->set_update_db(DB_MEMBER_MILEAGE, $arrUpdate, $this->db->strWhere, $arrBind);
        $this->db->query_reset();
    }

    /**
     * sms 예약 시간
     *
     * @param $smsReserveTime
     */
    public function setSmsReserveTime($smsReserveTime)
    {
        $this->smsReserveTime = $smsReserveTime;
        $logger = \App::getInstance('logger');
        $logger->info('set smsReserveTime - ' . $smsReserveTime);
    }

    public function setAllowSendSms($flag)
    {
        $this->allowSendSms = $flag;
    }

    /**
     * 재정의 랩핑 함수
     *
     * @param array $arrData
     *
     * @throws Exception
     */
    protected function validateBatchMileage(array $arrData)
    {
        $this->_validateBatchMileage($arrData);
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
     * @param $arrWhere
     * @param $arrBind
     *
     * @return bool
     */
    protected function getResultByRemoveMileage($arrData, $arrWhere, $arrBind)
    {
        return $this->_getResultByRemoveMileage($arrData, $arrWhere, $arrBind);
    }

    /**
     * 재정의 랩핑 함수
     *
     * @param $deleteScheduleDateTime
     *
     * @return bool
     */
    protected function isDeleteMileage($deleteScheduleDateTime)
    {
        return $this->_isDeleteMileage($deleteScheduleDateTime);
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
    protected function getResultByAddMileage($arrData, $arrWhere, $arrBind)
    {
        return $this->_getResultByAddMileage($arrData, $arrWhere, $arrBind);
    }

    /**
     * 일괄 처리 시 값 검증하는 함수
     *
     * @param array $arrData 검증 데이터
     *
     * @throws Exception
     */
    private function _validateBatchMileage(array $arrData)
    {
        $v = new Validator();
        $v->init();
        $v->add('mileageCheckFl', 'pattern', true, '{' . __('지급/차감여부') . '}', '/^(add|remove)$/');
        $v->add('removeMethodFl', 'pattern', true, '{' . __('마일리지 부족 시 차감방법') . '}', '/^(minus|exclude)$/');
        $v->add('mileageValue', 'pattern', true, '{' . __('지급 마일리지') . '}', '/^[0-9]*$/');
        $v->add('reasonCd', 'number', true);
        $v->add('contents', '', true, '지급/차감 사유를 입력해주세요.');

        if ($v->act($arrData, true) === false) {
            throw new Exception(implode("\n", $v->errors));
        }
    }

    /**
     * 일괄 처리 결과를 담는 객체를 초기화하는 함수
     */
    private function _initResultStorage()
    {
        $this->resultStorage = new SimpleStorage();
        $this->resultStorage->set('totalCount', 0);
        $this->resultStorage->set('successCount', 0);
        $this->resultStorage->set('failCount', 0);
        $this->resultStorage->set('excludeCount', 0);
    }

    /**
     * 차감 일괄 처리 및 결과를 반환하는 함수
     *
     * @param array $arrData  데이터
     * @param array $arrWhere 조건절
     * @param array $arrBind  바인딩
     *
     * @return bool 결과
     * @throws Exception
     */
    private function _getResultByRemoveMileage($arrData, $arrWhere, $arrBind)
    {
        $logger = \App::getInstance('logger');
        $where = (count($arrWhere) ? ' WHERE ' . implode(' and ', $arrWhere) : '');
        $strSQL = 'SELECT memNo, memId, memNm, mileage, cellPhone, email, maillingFl, smsFl FROM ' . DB_MEMBER . ' as m ' . $where;
        $data = $this->db->query_fetch($strSQL, (empty($arrBind) === false ? $arrBind : null));

        $mileageValue = $arrData['mileageValue'];
        $removeMethodFl = $arrData['removeMethodFl'];
        $reasonCd = $arrData['reasonCd'];

        $mileageValue = abs($mileageValue) * -1;

        if (isset($data) && is_array($data)) {
            $this->allowSendSms = in_array('sms', $arrData['guideSend']);
            $this->_initResultStorage();
            $this->resultStorage->set('totalCount', count($data));

            foreach ($data as $val) {
                $currentMileage = $val['mileage'];
                // 마일리지 부족 시 차감 제외
                if ($removeMethodFl == 'exclude' && ($currentMileage + ($mileageValue) < 0)) {
                    $this->resultStorage->increase('excludeCount');
                    continue;
                }
                $result = $this->setMemberMileage($val['memNo'], $mileageValue, $reasonCd, 'm', null, null, $arrData['contents']);
                $logger->debug($result);
                if ($result) {
                    $this->resultStorage->increase('successCount');
                } else {
                    $this->resultStorage->increase('failCount');
                }

                // 회원의 메일링 수신동의와 자동발송 설정이 참일 경우에만 메일을 발송
                $isSendMail = $arrData['guideSend'] != null && in_array('email', $arrData['guideSend']);
                $this->isMailingFlag = $isSendMail && $result && ($val['maillingFl'] === 'y');
                if ($this->isMailingFlag) {
                    $this->mileageMail = [];

                    $this->mileageMail['memId'] = $val['memId'];
                    $this->mileageMail['memNm'] = $val['memNm'];
                    $this->mileageMail['email'] = $val['email'];
                    $this->mileageMail['mileage'] = $mileageValue;
                    $this->mileageMail['totalMileage'] = $val['mileage'] + $mileageValue;
                    /** @var \Bundle\Component\Mail\MailMimeAuto $mailMimeAuto */
                    $mailMimeAuto = App::load('\\Component\\Mail\\MailMimeAuto');
                    $mailMimeAuto->init(MailMimeAuto::REMOVE_MILEAGE, $this->mileageMail)->autoSend();
                }
                /* 2017-02-09 yjwee sms 발송은 setMemberMileage 에서 처리하며 관리자에서 일괄 지급/차감 시 발송 안함 설정 상태를 제외하고는 모두 발송입니다. */
            }

            return true;
        }

        return false;
    }

    /**
     * 삭제 마일리지 여부 체크
     *
     * @param $deleteScheduleDateTime
     *
     * @return bool
     */
    private function _isDeleteMileage($deleteScheduleDateTime)
    {
        return DateTimeUtils::intervalDay($deleteScheduleDateTime, DateTimeUtils::dateFormat('Y-m-d G:i:s', 'today'), 'sec') >= 0;
    }

    /**
     * 관리자 마일리지 일괄 지급 처리 함수
     *
     * @param $arrData
     * @param $arrWhere
     * @param $arrBind
     *
     * @return bool
     * @throws Exception
     */
    private function _getResultByAddMileage($arrData, $arrWhere, $arrBind)
    {
        $logger = \App::getInstance('logger');
        $where = (count($arrWhere) ? ' WHERE ' . implode(' and ', $arrWhere) : '');
        $strSQL = 'SELECT memId, memNo, memNm, mileage, cellPhone, email, maillingFl, smsFl FROM ' . DB_MEMBER . ' as m ' . $where;
        $data = $this->db->query_fetch($strSQL, (empty($arrBind) === false ? $arrBind : null));

        $mileageValue = $arrData['mileageValue'];
        $reasonCd = $arrData['reasonCd'];

        if (isset($data) && is_array($data)) {
            $this->allowSendSms = in_array('sms', $arrData['guideSend']);
            $logger->info(sprintf('%s, add mileage member count[%d]', count($data)));
            foreach ($data as $val) {
                $result = $this->setMemberMileage($val['memNo'], $mileageValue, $reasonCd, 'm', null, null, $arrData['contents']);

                $this->isMailingFlag = ($val['maillingFl'] === 'y') && $result && ($arrData['guideSend'] != null && in_array('email', $arrData['guideSend']));
                // 회원의 메일링 수신동의와 자동발송 설정이 참일 경우에만 메일을 발송
                if ($this->isMailingFlag) {
                    $this->mileageMail = [];
                    $this->mileageMail['memId'] = $val['memId'];
                    $this->mileageMail['memNm'] = $val['memNm'];
                    $this->mileageMail['email'] = $val['email'];
                    $this->mileageMail['mileage'] = $mileageValue;
                    $this->mileageMail['totalMileage'] = $val['mileage'] + $mileageValue;
                    // 마일리지 소멸예정일을 저장된 데이터가 아닌 전날의 23:59:59 로 변경하여 노출되도록 수정
                    $this->mileageMail['deleteScheduleDt'] = MileageUtil::changeDeleteScheduleDt(MileageUtil::getDeleteScheduleDate(), true);
                    /** @var \Bundle\Component\Mail\MailMimeAuto $mailMimeAuto */
                    $mailMimeAuto = App::load('\\Component\\Mail\\MailMimeAuto');
                    $mailMimeAuto->init(MailMimeAuto::ADD_MILEAGE, $this->mileageMail)->autoSend();
                }
                /* 2017-02-09 yjwee sms 발송은 setMemberMileage 에서 처리하며 관리자에서 일괄 지급/차감 시 발송 안함 설정 상태를 제외하고는 모두 발송입니다. */
            }

            return true;
        }

        return false;
    }

    /**
     * 마일리지 사유코드 수정
     *
     * @param $arrData
     *
     * @return bool
     * @throws Exception
     */
    public function reasonCdModifyMileage($arrData)
    {
        if (empty($arrData['sno']) || empty($arrData['contents']) || empty($arrData['reasonModiyCode'])) {
            return true;
        }
        $db = App::load('DB');
        $mileageReasons = MileageUtil::getReasons();
        $sno = $arrData['sno'];

        if($arrData['reasonModiyCode'] == self::REASON_CODE_GROUP . self::REASON_CODE_ETC) {
            $milageData['reasonCd'] = self::REASON_CODE_GROUP . self::REASON_CODE_ETC;
            $milageData['contents'] = $arrData['contents'];
        }else{
            $milageData['reasonCd'] = $arrData['reasonModiyCode'];
            $milageData['contents'] = $mileageReasons[$arrData['reasonModiyCode']];
        }
        $compareField = array_keys($milageData);
        $arrBind = $this->db->get_binding(DBTableField::tableMemberMileage(), $milageData, 'update', $compareField);
        $db->bind_param_push($arrBind['bind'], 'i',  $sno);
        $rs = $db->set_update_db(DB_MEMBER_MILEAGE, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        unset($arrBind, $milageData);
        return $rs;
    }


}
