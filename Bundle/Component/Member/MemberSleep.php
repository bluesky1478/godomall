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

use App;
use Component\Database\DBTableField;
use Component\Mail\MailMimeAuto;
use Component\Member\HackOut\HackOutDAO;
use Component\Member\Sleep\SleepDAO;
use Component\Member\Sleep\SleepService;
use Component\Mileage\MileageUtil;
use Component\Validator\Validator;
use Encryptor;
use Exception;
use Framework\Database\DBTool;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Logger;
use Message;
use Request;
use Session;

/**
 * Class MemberSleep 휴면회원
 * @package Component\Member
 * @author  yjwee
 */
class MemberSleep extends \Component\AbstractComponent
{
    /** 휴면회원 통합검색 항목 */
    const COMBINE_SEARCH = [
        'memId'     => '아이디',
        //__('아이디')
        'memNm'     => '이름',
        //__('이름')
//        'email'     => '이메일',
        //__('이메일')
//        'cellPhone' => '휴대폰',
        //__('휴대폰')
//        'phone'     => '전화번호',
        //__('전화번호')
    ];

    /** 휴면회원 해제 정보 세션키 */
    const SESSION_WAKE_INFO = 'SESSION_WAKE_INFO';

    /** 메일 인증번호 세션키  */
    const SESSION_AUTH_NUMBER_MAIL = 'SESSION_AUTH_NUMBER_MAIL';

    /** 메일 인증번호 길이 */
    const AUTH_NUMBER_LENGTH = 8;
    /**
     * @var string es_memberSleep테이블필드
     */
    protected $fieldTypes;
    /**
     * @var string es_member테이블필드
     */
    protected $memberFieldTypes;
    /** @var SleepDAO $dao */
    private $dao;
    /** @var HackOutDAO $hackOutDao */
    private $hackOutDao;

    /**
     * @var array 휴면회원전환시 암호화 대상 필드명
     */
    //@formatter:off
    private $sleepEncryptField = ['groupSno', 'groupModDt', 'groupValidDt', 'memNm', 'nickNm', 'memPw', 'appFl', 'memberFl', 'entryBenefitOfferDt', 'sexFl', 'birthDt', 'calendarFl', 'email', 'zipcode', 'zonecode', 'address', 'addressSub', 'phone', 'cellPhone', 'fax', 'company', 'service', 'item', 'busiNo', 'ceo', 'comZipcode', 'comZonecode', 'comAddress', 'comAddressSub', 'mileage', 'deposit', 'maillingFl', 'smsFl', 'marriFl', 'marriDate', 'job', 'interest', 'reEntryFl', 'entryDt', 'entryPath', 'lastLoginIp', 'lastSaleDt', 'loginCnt', 'saleCnt', 'saleAmt', 'memo', 'recommId', 'recommFl', 'ex1', 'ex2', 'ex3', 'ex4', 'ex5', 'ex6', 'privateApprovalFl', 'privateApprovalOptionFl', 'privateOfferFl', 'privateConsignFl', 'foreigner', 'dupeinfo', 'adultFl', 'adultConfirmDt', 'pakey', 'rncheck', 'adminMemo', 'sleepMailFl', 'expirationFl'];
    //@formatter:on

    public function __construct(DBTool $db = null)
    {
        parent::__construct($db);
        $this->fieldTypes = DBTableField::getFieldTypes('tableMemberSleep');
        $this->memberFieldTypes = DBTableField::getFieldTypes('tableMember');
        $this->dao = new SleepDAO($db);
        $this->hackOutDao = new HackOutDAO($db);
    }

    /**
     * MemberSleepPsController::delete_sleep_member_all
     * 전체 휴면회원 탈퇴처리
     */
    public function deleteSleepMemberAll()
    {
        /** @var \Bundle\Component\Member\HackOut\HackOutService $hackOutService */
        $hackOutService = App::load('\\Component\\Member\\HackOut\\HackOutService');

        $data = $this->lists([], null, null);

        try {
            \DB::begin_tran();
            $policy = gd_policy('member.join');
            $rejoinFl = ($policy['rejoinFl'] === 'n') ? 'y' : 'n';
            $managerId = Session::get('manager.managerId');
            $managerNo = Session::get('manager.sno');
            $managerIp = Request::getRemoteAddress();
            foreach ($data as $index => $item) {
                $member = $this->hackOutDao->getMember(['memNo' => $item['memNo']]);
                $params = [];
                $params['rejoinFl'] = $rejoinFl;
                $params['memNo'] = $item['memNo'];
                $params['memId'] = $member['memId'];
                $params['dupeInfo'] = $member['dupeInfo'];
                $params['hackType'] = 'directManager';
                $params['managerId'] = $managerId;
                $params['managerNo'] = $managerNo;
                $params['managerIp'] = $managerIp;
                $params['regIp'] = $managerIp;
                $params['hackDt'] = date('Y-m-d H:i:s');

                $v = new Validator();
                $v->add('hackType', '', true, '{' . __('탈퇴구분') . '}');
                $v->add('memNo', 'number', true, '{' . __('회원번호') . '}');
                $v->add('memId', 'userId', true, '{' . __('회원아이디') . '}');
                $v->add('dupeInfo', '');
                $v->add('reasonCd', '');
                $v->add('reasonDesc', '');
                $v->add('managerId', '', true, '{' . __('관리자ID') . '}');
                $v->add('managerNo', '', true, '{' . __('관리자NO') . '}');
                $v->add('managerIp', '', true, '{' . __('관리자IP') . '}');
                $v->add('hackDt', '', true, '{' . __('탈퇴일') . '}');
                $v->add('regIp', '', true);
                $v->add('rejoinFl', 'yn', true, '{' . __('재가입여부') . '}');

                if ($v->act($params, true) === false) {
                    throw new Exception(implode("\n", $v->errors));
                }
                $this->hackOutDao->setParams($params)->insertHackOutWithDeleteMemberByParams();
            }
            $this->dao->deleteAllSleep();
            if ($this->isTran) {
                \DB::commit();
            }
        } catch (Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    public function lists(array $params, $offset = 1, $limit = 10)
    {
        $params['offset'] = $offset;
        $params['limit'] = $limit;

        return $this->dao->selectListBySearch($params);
    }

    /**
     * 전체 휴면 회원 탈퇴 처리전 예치금, 마일리지, 쿠폰 체크
     *
     * @return array
     */
    public function getDeleteSleepMemberAllAvailable(){
        $arrField[] = 'COUNT(IF ( ms.deposit > 0, 1, NULL) ) AS deposit';
        $arrField[] = 'COUNT(IF ( ms.mileage > 0, 1, NULL) ) AS mileage';
        $arrField[] = 'COUNT(IF(mc.memberCouponState="y" AND mc.memberCouponStartDate <= NOW() AND mc.memberCouponEndDate >= NOW(), 1, NULL)) AS countMemberCoupon';
        $this->db->strField = implode(', ', $arrField);
        $this->db->strJoin = ' LEFT JOIN es_memberCoupon AS mc ON mc.memNo = ms.memNo ';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_SLEEP . ' AS ms ' . implode(' ', $query);
        $result = $this->db->query_fetch($strSQL, null)[0];
        return $result;
    }

    /**
     * 회원 탈퇴 / 삭제관리 페이지 접근시 최초 1회 마이그레이션 진행 member/hackout_list.php
     * @todo 나중에 삭제 되어도 되는 코드 (구버전 포함)
     * @todo HackoutListController.php 에서 호출하는 부분 함께 삭제
     * @todo es_config member.hackout 함께 삭제
     */
    public function deleteSleepMemberMigrations(){
        $arrBind = [];
        $this->db->strField = 'm.memNo';
        $arrWhere[] = 'm.sleepFl = ?';
        $arrWhere[] = 'ms.memNo IS NULL';
        $this->db->bind_param_push($arrBind, 's', 'y');
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strJoin = ' LEFT JOIN es_memberSleep AS ms ON ms.memNo = m.memNo ';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' AS m ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        if(count($data) <= 0) {
            gd_set_policy('member.hackout', ['isMigrations'=>'y']);
        } else {
            try {
                \DB::begin_tran();
                $policy = gd_policy('member.join');
                $rejoinFl = ($policy['rejoinFl'] === 'n') ? 'y' : 'n';
                $managerId = Session::get('manager.managerId');
                $managerNo = Session::get('manager.sno');
                $managerIp = '-';
                foreach ($data as $index => $item) {
                    $member = $this->hackOutDao->getMember(['memNo' => $item['memNo']]);
                    $params = [];
                    $params['rejoinFl'] = $rejoinFl;
                    $params['memNo'] = $item['memNo'];
                    $params['memId'] = $member['memId'];
                    $params['dupeInfo'] = $member['dupeInfo'];
                    $params['hackType'] = 'directManager';
                    $params['managerId'] = $managerId;
                    $params['managerNo'] = $managerNo;
                    $params['managerIp'] = $managerIp;
                    $params['regIp'] = $managerIp;
                    $params['hackDt'] = date('Y-m-d H:i:s');

                    $v = new Validator();
                    $v->add('hackType', '', true, '{' . __('탈퇴구분') . '}');
                    $v->add('memNo', 'number', true, '{' . __('회원번호') . '}');
                    $v->add('memId', 'userId', true, '{' . __('회원아이디') . '}');
                    $v->add('dupeInfo', '');
                    $v->add('reasonCd', '');
                    $v->add('reasonDesc', '');
                    $v->add('managerId', '', true, '{' . __('관리자ID') . '}');
                    $v->add('managerNo', '', true, '{' . __('관리자NO') . '}');
                    $v->add('managerIp', '', true, '{' . __('관리자IP') . '}');
                    $v->add('hackDt', '', true, '{' . __('탈퇴일') . '}');
                    $v->add('regIp', '', true);
                    $v->add('rejoinFl', 'yn', true, '{' . __('재가입여부') . '}');

                    if ($v->act($params, true) === false) {
                        throw new Exception(implode("\n", $v->errors));
                    }
                    $this->hackOutDao->setParams($params)->insertHackOutWithDeleteMemberByParams();
                    $this->dao->deleteSleep($item['memNo'], 'memNo');
                }
                if ($this->isTran) {
                    \DB::commit();
                }
                gd_set_policy('member.hackout', ['isMigrations'=>'y']);
            } catch (Exception $e) {
                \DB::rollback();
                gd_set_policy('member.hackout', ['isMigrations'=>'n']);
            }
        }
    }

    /**
     * 휴면회원 전환
     *
     * @param int|array $memNo 회원 번호
     *
     * @return array
     * @throws Exception
     */
    public function sleep($memNo)
    {
        $memberInfo = $this->getSleepMemberByMemNo($memNo);
        if (count($memberInfo) <= 0) {
            throw new Exception(__('휴면회원 대상이 아닙니다.'));
        }
        $mileage = \App::load('Component\\Mileage\\Mileage');
        $sleepPolicy = \App::load('Component\\Policy\\MemberSleepPolicy');
        $isExpireMileageSleepMember = $sleepPolicy->isExpireMileageSleepMember();

        $return = [];
        foreach ($memberInfo as $info) {
            try {
                // 휴면회원 정책에 따라 마일리지 초기화 처리 및 내역 저장 후 휴면 처리
                if ($info['mileage'] != 0 && $isExpireMileageSleepMember) {
                    $adjustMileage = ($info['mileage'] * -1);
                    $mileage->setMemberMileage($info['memNo'], $adjustMileage, \Component\Mileage\Mileage::REASON_CODE_GROUP . \Component\Mileage\Mileage::REASON_CODE_MEMBER_SLEEP, 'm', null, null, \Component\Mileage\Mileage::REASON_TEXT_MEMBER_SLEEP);
                    $info['mileage'] = $info['mileage'] + $adjustMileage;
                }
                // 휴면회원 정보 설정 및 데이터 바인딩
                $sleepInfo = DBTableField::tableModel('tableMemberSleep');
                $sleepInfo = array_intersect_key($info, $sleepInfo);
                $sleepInfo['encryptData'] = $this->_encryptInfo($info);
                $sleepInfo['sleepDt'] = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');

                \DB::begin_tran();
                $this->dao->insertSleep($sleepInfo);
                $sleepNo = $this->dao->insertId();

                if ($sleepNo <= 0) {
                    throw new Exception(__('휴면회원 처리 중 실패 하였습니다.'));
                }

                $this->dao->updateMemberByEncrypt($sleepInfo, $this->sleepEncryptField);
                if ($this->isTran) {
                    \DB::commit();
                }
                $return[] = $sleepInfo;
            } catch (Exception $e) {
                \DB::rollback();
                throw $e;
            }
        }

        return $return;
    }

    /**
     * 휴면 대상 회원정보 조회
     *
     * @param null   $memNo 회원번호
     * @param string $type  휴면(memeber|null), 메일(email) 대상 구분
     *
     * @return mixed
     */
    public function getSleepMemberByMemNo($memNo = null, $type = 'member')
    {
        $arrBind['where'] = [];

        if ($memNo !== null) {
            if (is_array($memNo)) {
                array_push($arrBind['where'], 'memNo IN(' . implode(',', array_fill(0, count($memNo), '?')) . ')');
                foreach ($memNo as $no) {
                    $this->db->bind_param_push($arrBind['bind'], 'i', $no);
                }
            } else {
                array_push($arrBind['where'], 'memNo = ?');
                $this->db->bind_param_push($arrBind['bind'], 'i', $memNo);
            }
        }

        array_push($arrBind['where'], 'DATE_FORMAT(lastLoginDt,\'%Y%m%d\') <= ?');
        array_push($arrBind['where'], 'sleepFl != \'y\'');

        $this->db->strField = implode(',', DBTableField::setTableField('tableMember'));
        $this->db->strWhere = implode(' AND ', $arrBind['where']);

        $this->db->bind_param_push($arrBind['bind'], $this->memberFieldTypes['lastLoginDt'], $this->getTimestamp($type));
        $query = $this->db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' ' . implode(' ', $query);
        $result = $this->db->query_fetch($strSQL, $arrBind['bind']);

        return $result;
    }

    /**
     * 오늘을 기준으로 휴면회원 전환 기준일, 휴면회원 메일발송 기준일 반환 함수
     *
     * @param null|string $type
     *
     * @return string format('Y-m-d')
     */
    private function getTimestamp($type = null)
    {
        $timestamp = DateTimeUtils::dateFormat('Y-m-d', '-' . SleepService::SLEEP_PERIOD . ' day');
        if ($type == 'email') {
            $timestamp = DateTimeUtils::dateFormat('Y-m-d', '-' . SleepService::MAIL_PERIOD . ' day');
        }

        return $timestamp;
    }

    /**
     * 회원정보 암호화
     *
     * @param array $memberInfo 회원정보
     *
     * @return mixed 직렬화 및 암호화
     */
    private function _encryptInfo($memberInfo)
    {
        $encryptInfo = array_intersect_key($memberInfo, array_fill_keys($this->sleepEncryptField, null));
        $encryptInfo = serialize($encryptInfo);

        return Encryptor::mysqlAesEncrypt($encryptInfo);
    }

    /**
     * MemberSleepPsController::delete_sleep_member
     * 선택 탈퇴처리 요청 처리 함수
     *
     * @param int|array $sleepNo
     *
     * @throws Exception
     */
    public function deleteSleepMember($sleepNo)
    {
        $data = $this->dao->select($sleepNo);
        try {
            \DB::begin_tran();
            $policy = gd_policy('member.join');
            $rejoinFl = ($policy['rejoinFl'] === 'n') ? 'y' : 'n';
            $managerId = Session::get('manager.managerId');
            $managerNo = Session::get('manager.sno');
            $managerIp = Request::getRemoteAddress();
            foreach ($data as $index => $item) {
                if (gd_isset($item['memNo'], '') == '') {
                    throw new Exception(__('회원번호는 필수 입니다.'));
                }
                if ($managerId == '') {
                    throw new Exception(__('관리자 아이디가 없습니다.'));
                }
                if ($managerIp == '') {
                    throw new Exception(__('처리자 정보가 없습니다.'));
                }

                $member = $this->hackOutDao->getMember(['memNo' => $item['memNo']]);
                $params = [];
                $params['rejoinFl'] = $rejoinFl;
                $params['memNo'] = $item['memNo'];
                $params['memId'] = $member['memId'];
                $params['dupeInfo'] = $member['dupeInfo'];
                $params['hackType'] = 'directManager';
                $params['managerId'] = $managerId;
                $params['managerNo'] = $managerNo;
                $params['managerIp'] = $managerIp;
                $params['regIp'] = $managerIp;
                $params['hackDt'] = date('Y-m-d H:i:s');

                $v = new Validator();
                $v->add('hackType', '', true, '{' . __('탈퇴구분') . '}');
                $v->add('memNo', 'number', true, '{' . __('회원번호') . '}');
                $v->add('memId', 'userId', true, '{' . __('회원아이디') . '}');
                $v->add('dupeInfo', '');
                $v->add('reasonCd', '');
                $v->add('reasonDesc', '');
                $v->add('managerId', '', true, '{' . __('관리자ID') . '}');
                $v->add('managerNo', '', true, '{' . __('관리자NO') . '}');
                $v->add('managerIp', '', true, '{' . __('관리자IP') . '}');
                $v->add('hackDt', '', true, '{' . __('탈퇴일') . '}');
                $v->add('regIp', '', true);
                $v->add('rejoinFl', 'yn', true, '{' . __('재가입여부') . '}');

                if ($v->act($params, true) === false) {
                    throw new Exception(implode("\n", $v->errors));
                }
                $this->hackOutDao->setParams($params)->insertHackOutWithDeleteMemberByParams();
                $this->dao->deleteSleep($item['memNo'], 'memNo');
            }
            if ($this->isTran) {
                \DB::commit();
            }
        } catch (Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * wakeUp
     *
     * @param $wakeData
     *
     * @throws Exception
     * @deprecated
     * @uses wake
     */
    public function wakeUp($wakeData)
    {
        if (ArrayUtils::isEmpty($wakeData) === true) {
            throw new Exception(__('휴면회원 정보가 비어있습니다.'));
        }
        if (Validator::required($wakeData['sleepNo']) === false) {
            throw new Exception(__('휴면회원번호가 없습니다.'));
        }
        try {
            \DB::begin_tran();
            $this->dao->updateMemberByWake($wakeData);
            $this->dao->deleteSleepBySleepNo($wakeData['sleepNo']);
            if ($this->isTran) {
                \DB::commit();
            }
        } catch (Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * 휴면회원정보 조회 및 복호화
     *
     * @param array $sleepData
     *
     * @return mixed 휴면회원정보 + 복호화정보
     * @throws Exception
     */
    public function getSleepInfoByMemberIdWithDecrypt(array $sleepData)
    {
        $decryptInfo = Encryptor::mysqlAesDecrypt($sleepData['encryptData']);
        $decryptInfo = unserialize($decryptInfo);
        $result = array_merge($sleepData, $decryptInfo);
        unset($sleepData);
        unset($decryptInfo);

        return $result;
    }

    /**
     * 회원아이디로 단건의 휴면회원 정보를 조회하는 함수
     *
     * @param $memberId
     *
     * @return array|object
     * @throws Exception
     */
    public function getSleepInfoByMemberId($memberId)
    {
        if (Validator::userid($memberId, true, false) === false) {
            throw new Exception(__('회원아이디가 필요합니다'));
        }

        return $this->db->getData(DB_MEMBER_SLEEP, $memberId, 'memId');
    }

    /**
     * 휴면회원 전환 안내 메일발송
     *
     * @param null $memNo 회원번호
     *
     * @return int
     * @throws Exception
     */
    public function sendSleepMail($memNo = null)
    {
        $sendMemNo = [];
        $memberInfo = $this->getSleepMemberByMemNo($memNo, 'email');

        if (count($memberInfo) <= 0) {
            throw new Exception(__('휴면회원 대상이 아닙니다.'));
        }

        $failCount = 0;
        foreach ($memberInfo as $info) {
            try {
                //휴면전환예정일
                $expirationDay = $info['expirationFl'] * 365;
                $info['sleepScheduleDt'] = DateTimeUtils::dateFormatByParameter('Y-m-d', $info['lastLoginDt'], '+' . $expirationDay . ' day');

                /** @var \Bundle\Component\Mail\MailMimeAuto $mailMimeAuto */
                $mailMimeAuto = App::load('\\Component\\Mail\\MailMimeAuto');
                $send = $mailMimeAuto->init(MailMimeAuto::SLEEP_NOTICE, $info)->autoSend();
                if ($send === true) {
                    debug(111);
                    $sendMemNo[] = $info['memNo'];
                } else {
                    debug(222);
                    $failCount++;
                }
            } catch (Exception $e) {
                Logger::error($e->getMessage(), $e->getTrace());
            }
        }
        if (count($sendMemNo) > 0) {
            $this->dao->updateSleepMailFlag($sendMemNo);
        }

        return $failCount;
    }

    /**
     * 휴면해제 휴대전화번호 체크인증
     *
     * @param $cellPhone
     *
     * @return mixed
     */
    public function checkPhone($cellPhone)
    {
        $this->db->strField = 'sleepNo, cellPhone';
        $arrBind['where'] = 'replace(cellPhone, \'-\', \'\')' . ' = ?';
        $this->db->bind_param_push($arrBind['bind'], 's', str_replace('-', '', $cellPhone));
        $this->db->strWhere = $arrBind['where'];

        $query = $this->db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_SLEEP . ' ' . implode(' ', $query);
        $result = $this->db->query_fetch($strSQL, $arrBind['bind']);

        return $result;
    }

    /**
     * 휴면해제 이메일 체크인증
     *
     * @param $email
     *
     * @return mixed
     */
    public function checkEmail($email)
    {
        $this->db->strField = 'sleepNo, email';
        $arrBind['where'] = 'email = ?';
        $this->db->bind_param_push($arrBind['bind'], 's', $email);
        $this->db->strWhere = $arrBind['where'];

        $query = $this->db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_SLEEP . ' ' . implode(' ', $query);
        $result = $this->db->query_fetch($strSQL, $arrBind['bind']);

        return $result;
    }

    /**
     * wake 휴면회원 해제
     *
     * @param array  $data  조회파라미터
     * @param string $field 조회필드
     *
     * @return array
     * @throws Exception
     */
    public function wake($data, $field = 'sleepNo')
    {
        $logger = \App::getInstance('logger');
        $db = \App::getInstance('DB');
        $encryptor = \App::getInstance('encryptor');
        $sleepInfo = $this->dao->select($data, $field);
        $sleepPolicy = \App::load('Component\\Policy\\MemberSleepPolicy');

        $groups = \Component\Member\Group\Util::getGroupName();
        $return = [];
        foreach ($sleepInfo as $info) {
            try {
                if ($info['encryptData'] == '') {
                    $logger->error('not found sleep member encrypt data.', $info);
                    throw new \Exception(__('휴면회원의 회원데이터가 없습니다.'));
                }
                $decryptInfo = $encryptor->mysqlAesDecrypt($info['encryptData']);
                $decryptInfo = unserialize($decryptInfo);
                if ($decryptInfo === false) {
                    $logger->error(__METHOD__ . ', unserialize false');
                    throw new \Exception(__('휴면회원 정보 해제 중 오류가 발생하였습니다.'));
                }

                if ($decryptInfo['groupSno'] > 1) {
                    if (array_key_exists($decryptInfo['groupSno'], $groups) == false || $sleepPolicy->useResetGroup()) {
                        $decryptInfo['groupSno'] = \Component\Member\Group\Util::getDefaultGroupSno();
                        $logger->info(sprintf('use init member group. current default group sno [%d]', $decryptInfo['groupSno']));
                    }
                }

                $db->begin_tran();
                $this->dao->updateMemberByDecrypt($decryptInfo, $info['memNo']);
                $this->dao->deleteSleep($data, $field);
                if ($this->isTran) {
                    $db->commit();
                }
                if ($sleepPolicy->isExpireMileageWakeMember()) {
                    $this->expireMileageByWakeMember($info['memNo']);
                }
            } catch (\Exception $e) {
                $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
                $db->rollback();
                throw $e;
            }
            $return[] = $decryptInfo;
        }

        return $return;
    }

    /**
     * expireMileageByWakeMember
     *
     * @param int|string $memNo
     */
    protected function expireMileageByWakeMember($memNo)
    {
        $logger = \App::getInstance('logger');
        $mileageDAO = \App::load('Component\\Mileage\\MileageDAO');
        $mileage = \App::load('Component\\Mileage\\Mileage');
        $sleepPolicy = \App::load('Component\\Policy\\MemberSleepPolicy');
        if ($sleepPolicy->isExpireMileageWakeMember()) {
            $expire = $mileageDAO->selectExpireMileageByMemberNo($memNo, DateTimeUtils::dateFormat('Y-m-d 23:59:59', 'now'));
            if ($expire['mileage'] > 0) {
                $logger->info(sprintf('wake member and expire mileage. memNo[%d], expire mileage[%d]', $expire['memNo'], $expire['mileage']));
                $expire['mileage'] = MileageUtil::removeUseHistory($expire['mileage'], $expire['useHistory']);
                $expire['mileage'] = ($expire['mileage'] * -1);
                $result = $mileage->setMemberMileage($expire['memNo'], $expire['mileage'], \Component\Mileage\Mileage::REASON_CODE_GROUP . \Component\Mileage\Mileage::REASON_CODE_MEMBER_WAKE, 'm', null, null, \Component\Mileage\Mileage::REASON_TEXT_MEMBER_WAKE);
                if ($result) {
                    $logger->info(sprintf('expire member mileage complete. memNo[%d], expire mileage[%d]', $expire['memNo'], $expire['mileage']));
                } else {
                    $logger->info(sprintf('expire member mileage fail. memNo[%d], expire mileage[%d]', $expire['memNo'], $expire['mileage']));
                }
            } else {
                $logger->info(sprintf('expire member mileage validate fail. memNo[%d], expire mileage[%d]', $expire['memNo'], $expire['mileage']));
            }
        }
    }

    /**
     * 휴면전환대상 또는 휴면메일대상 체크
     *
     * @param        $memNo
     * @param string $type
     *
     * @return bool
     */
    public function isTarget($memNo, $type = 'member')
    {
        $this->db->strField = implode(',', DBTableField::setTableField('tableMember'));
        $this->db->strWhere = 'memNo = ? AND DATE_FORMAT(lastLoginDt,\'%Y%m%d\') <= ?';

        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $this->db->bind_param_push($arrBind, $this->memberFieldTypes['lastLoginDt'], $this->getTimestamp($type));
        $query = $this->db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' ' . implode(' ', $query);
        $queryResult = $this->db->query_fetch($strSQL, $arrBind, false);
        $result = $queryResult['memNo'] == $memNo;

        return $result;
    }

    public function getCounBySearch(array $params)
    {
        return $this->dao->selectCountListBySearch($params);
    }

    /**
     * @return SleepDAO
     */
    public function getDao()
    {
        return $this->dao;
    }

    /**
     * @param SleepDAO $dao
     */
    public function setDao($dao)
    {
        $this->dao = $dao;
    }

    /**
     * @return HackOutDAO
     */
    public function getHackOutDao()
    {
        return $this->hackOutDao;
    }

    /**
     * @param HackOutDAO $hackOutDao
     */
    public function setHackOutDao($hackOutDao)
    {
        $this->hackOutDao = $hackOutDao;
    }
}
