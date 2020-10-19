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

namespace Bundle\Component\Member\Sleep;

use Component\Member\Member;
use Component\Database\DBTableField;
use Component\Mail\MailMimeAuto;
use Component\Sms\Code;
use Component\Sms\Sms;
use Component\Sms\SmsAuto;
use Component\Sms\SmsAutoCode;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;

/**
 * 휴면회원
 *
 * @package Bundle\Component\Member\Sleep
 * @author  yjwee
 */
class SleepService
{
    /** 휴면회원 기간 */
    const SLEEP_PERIOD = 365;
    /** 휴면회원 메일 발송 기간 */
    const MAIL_PERIOD = 335;
    /** @var SleepDAO $sleepDAO 휴면호원 DAO 클래스 */
    private $sleepDAO;
    /** @var Sms $sms Sms 발송 클래스 */
    private $sms;
    /** @var array $sleepResult 휴면회원 처리 결과 */
    private $sleepResult = [
        'total'   => 0,
        'success' => 0,
    ];
    /** @var array $sleepMailResult 휴면회원 메일 발송 결과 */
    private $sleepMailResult = [
        'total' => 0,
    ];
    /** @var array $sleepSmsResult 휴면회원 Sms 발송 결과 */
    private $sleepSmsResult = [
        'total' => 0,
    ];
    /** @var array $expirationFlags 개인정보처리 기간 */
    private $expirationFlags = [
        5,
        3,
        1,
    ];
    /**
     * @var array 휴면회원전환시 암호화 대상 필드명
     */
    //@formatter:off
    private $sleepEncryptField = ['groupSno', 'groupModDt', 'groupValidDt', 'memNm', 'nickNm', 'memPw', 'appFl'
        , 'memberFl', 'entryBenefitOfferDt', 'sexFl', 'birthDt', 'calendarFl', 'email', 'zipcode', 'zonecode', 'address', 'addressSub'
        , 'phone', 'cellPhone', 'fax', 'company', 'service', 'item', 'busiNo', 'ceo', 'comZipcode', 'comZonecode', 'comAddress', 'comAddressSub'
        , 'mileage', 'deposit', 'maillingFl', 'smsFl', 'marriFl', 'marriDate', 'job', 'interest', 'reEntryFl', 'entryDt', 'entryPath'
        , 'lastLoginIp', 'lastSaleDt', 'loginCnt', 'saleCnt', 'saleAmt', 'memo', 'recommId', 'recommFl', 'ex1', 'ex2', 'ex3', 'ex4', 'ex5', 'ex6'
        , 'privateApprovalFl', 'privateApprovalOptionFl', 'privateOfferFl', 'privateConsignFl', 'foreigner', 'dupeinfo', 'adultFl', 'adultConfirmDt'
        , 'pakey', 'rncheck', 'adminMemo', 'sleepMailFl', 'expirationFl'];
    //@formatter:on

    /**
     * @inheritDoc
     */
    public function __construct(SleepDAO $sleepDAO = null, Sms $sms = null)
    {
        if ($sleepDAO === null) {
            $sleepDAO = new SleepDAO();
        }
        $this->sleepDAO = $sleepDAO;
        if ($sms === null) {
            $sms = new Sms();
        }
        $this->sms = $sms;
    }

    /**
     * 휴면회원 전환 안내 SMS 발송
     * @deprecated 2017-02-06 yjwee MySQL 연결시간 초과 이슈 수정을 위해 사용하지 않습니다.
     *
     * @throws \Exception
     */
    public function sendSleepSms()
    {
        $globals = \App::getInstance('globals');
        $member = \App::load('\\Component\\Member\\Member');
        $receivers = $this->getSleepSmsReceivers();
        $aMemInfo = $member->getMemberId($receivers['memNo']);
        $countReceivers = count($receivers);
        $this->sleepSmsResult['total'] = $countReceivers;
        $aBasicInfo = gd_policy('basic.info');
        if ($countReceivers > 0) {
            foreach ($receivers as $receiver) {
                $replace = [
                    'name'               => $receiver['memNm'],
                    'rc_sleepScheduleDt' => $receiver['sleepScheduleDt'],
                    'memNm'              => $receiver['memNm'],
                    'memId'              => $receiver['memId'],
                    'groupNm'            => $aMemInfo['groupNm'],
                    'mileage'            => $receiver['mileage'],
                    'deposit'            => $receiver['deposit'],
                    'sleepScheduleDt'    => $receiver['sleepScheduleDt'],
                    'rc_mallNm'          => $globals->get('gMall.mallNm'),
                    'shopUrl'            => $aBasicInfo['mallDomain'],
                ];
                $result = $this->sms->smsAutoSend('member', 'SLEEP_INFO', $receiver, $replace, 'member');
                if ($result[0]['success'] > 0) {
                    $this->sleepSmsResult['success'][] = $receiver['memNo'];
                }
            }
        }

        return $this->sleepSmsResult;
    }

    /**
     * 휴면회원 전환 안내 SMS 수신 대상 조회 및 전환 예정일 설정
     *
     * @return array
     */
    public function getSleepSmsReceivers()
    {
        $result = [];
        foreach ($this->expirationFlags as $expirationFlag) {
            $receivers = $this->sleepDAO->selectSleepSmsReceiver($expirationFlag);
            $period = '+' . (SleepService::SLEEP_PERIOD * $expirationFlag) . ' day';
            foreach ($receivers as $receiver) {
                $baseDate = StringUtils::strIsSet($receiver['lastLoginDt'], '0000-00-00 00:00:00') == '0000-00-00 00:00:00' ? $receiver['entryDt'] : $receiver['lastLoginDt'];
                $receiver['sleepScheduleDt'] = DateTimeUtils::dateFormatByParameter('Y-m-d', $baseDate, $period);
                $result[] = $receiver;
            }
        }

        return $result;
    }

    /**
     * 휴면회원 전환 안내 메일 발송
     * @deprecated 2017-07-04 yjwee 휴면회원 메일 발송 결과 업데이트 이슈 해결을 위해 해당 함수를 사용하지 않습니다.
     *
     * @return array ['total'=>'발송대상자수', 'success'=>['회원번호']]
     */
    public function sendSleepMail()
    {
        $receivers = $this->_getSleepMailReceivers();
        $countReceivers = count($receivers);
        $this->sleepMailResult['total'] = $countReceivers;
        if ($countReceivers > 0) {
            /** @var \Bundle\Component\Mail\MailMimeAuto $mailMimeAuto */
            $mailMimeAuto = \App::load('\\Component\\Mail\\MailMimeAuto');
            foreach ($receivers as $receiver) {
                $autoSend = $mailMimeAuto->init(MailMimeAuto::SLEEP_NOTICE, $receiver)->autoSend();
                if ($autoSend) {
                    $this->sleepMailResult['success'][] = $receiver['memNo'];
                }
            }
        }

        return $this->sleepMailResult;
    }

    /**
     * 휴면회원 전환 안내 메일 수신 대상 조회 및 전환 예정일 설정
     * @deprecated 2017-07-04 yjwee 휴면회원 메일 발송 결과 업데이트 이슈 해결을 위해 해당 함수를 사용하지 않습니다.
     *
     * @return array
     */
    private function _getSleepMailReceivers()
    {
        $result = [];
        foreach ($this->expirationFlags as $expirationFlag) {
            $receivers = $this->sleepDAO->selectSleepMailReceiver($expirationFlag);
            $period = '+' . (SleepService::SLEEP_PERIOD * $expirationFlag) . ' day';
            foreach ($receivers as $receiver) {
                $baseDate = StringUtils::strIsSet($receiver['lastLoginDt'], '0000-00-00 00:00:00') == '0000-00-00 00:00:00' ? $receiver['entryDt'] : $receiver['lastLoginDt'];
                $receiver['sleepScheduleDt'] = DateTimeUtils::dateFormatByParameter('Y-m-d', $baseDate, $period);
                $result[] = $receiver;
            }
        }

        return $result;
    }

    /**
     * 스케줄러에서 사용하며 조건에 맞는 회원 모두 휴면회원으로 전환
     *
     * @throws \Exception
     */
    public function sleepMember()
    {
        $logger = \App::getInstance('logger');
        $members = $this->_getSleepMemberTarget();
        $count = count($members);
        $this->sleepResult['total'] = $count;
        $mileage = \App::load('Component\\Mileage\\Mileage');
        $sleepPolicy = \App::load('Component\\Policy\\MemberSleepPolicy');
        $isExpireMileageSleepMember = $sleepPolicy->isExpireMileageSleepMember();

        $aBasicInfo = gd_policy('basic.info');
        $smsAuto = new SmsAuto();
        $oMember = new Member();

        if ($count > 0) {
            $db = \App::getInstance('DB');
            foreach ($members as $member) {
                try {
                    // 휴면회원 정책에 따라 마일리지 초기화 처리 및 내역 저장 후 휴면 처리
                    if ($member['mileage'] != 0 && $isExpireMileageSleepMember) {
                        $adjustMileage = ($member['mileage'] * -1);
                        $mileage->setMemberMileage($member['memNo'], $adjustMileage, \Component\Mileage\Mileage::REASON_CODE_GROUP . \Component\Mileage\Mileage::REASON_CODE_MEMBER_SLEEP, 'm', null, null, \Component\Mileage\Mileage::REASON_TEXT_MEMBER_SLEEP);
                        $member['mileage'] = $member['mileage'] + $adjustMileage;
                    }

                    // 휴면회원 정보 설정 및 데이터 바인딩
                    $sleepInfo = $this->getIntersectMemberWithDefaultSleep($member);

                    $db->begin_tran();
                    $this->sleepDAO->insertSleep($sleepInfo);
                    $sleepNo = $this->sleepDAO->insertId();

                    if ($sleepNo < 1) {
                        $logger->error(sprintf('fail member sleep process. memNo[%d] rollback and continue.', $member['memNo']));
                        $db->rollback();
                        continue;
                    }

                    $this->sleepDAO->updateMemberByEncrypt($sleepInfo, $this->sleepEncryptField);
                    $db->commit();
                    $this->sleepResult['success']++;

                    // 추가로 당일 안내 SMS발송
                    $aMemInfo = $oMember->getMemberId($member['memNo']);
                    $replace = [
                        'name'                  => $member['memNm'],
                        'memNm'                 => $member['memNm'],
                        'memId'                 => $member['memId'],
                        'groupNm'               => $aMemInfo['groupNm'],
                        'mileage'               => $member['mileage'],
                        'deposit'               => $member['deposit'],
                        'rc_sleepScheduleDt'    => date('Y-m-d'),
                        'sleepScheduleDt'       => date('Y-m-d'),
                        'rc_mallNm'             => $aBasicInfo['mallNm'],
                        'shopUrl'               => $aBasicInfo['mallDomain']
                    ];

                    $smsAuto->setSmsType(SmsAutoCode::MEMBER);
                    $smsAuto->setSmsAutoCodeType(Code::SLEEP_INFO_TODAY);
                    $smsAuto->setReceiver($member);
                    $smsAuto->setReplaceArguments($replace);
                    $smsAuto->setSmsAutoType('member');
                    $smsAuto->setSmsAutoSendDate($smsAuto->getSmsAutoReserveTime(Code::SLEEP_INFO_TODAY));
                    $result = $smsAuto->autoSend();
                    if ($result[0]['success'] < 1) {
                        $logger->info(sprintf('fail member sleep sms process. memNo[%d]', $member['memNo']));
                    }
                } catch (\Exception $e) {
                    $logger->error($e->getMessage(), $e->getTrace());
                    $db->rollback();
                    throw $e;
                }
            }
        }
    }

    /**
     * 휴면회원 대상 조회
     *
     * @return array
     */
    private function _getSleepMemberTarget()
    {
        $total = [];
        foreach ($this->expirationFlags as $expirationFlag) {
            $receivers = $this->sleepDAO->selectSleepMember($expirationFlag);
            foreach ($receivers as $receiver) {
                $total[] = $receiver;
            }
        }

        return $total;
    }

    /**
     * 휴면회원 정보 설정
     *
     * @param $member
     *
     * @return array
     */
    public function getIntersectMemberWithDefaultSleep($member)
    {
        $sleepInfo = DBTableField::tableModel('tableMemberSleep');
        $sleepInfo = array_intersect_key($member, $sleepInfo);
        $sleepInfo['encryptData'] = $this->_encryptMemberBySleepTargetField($member);
        $sleepInfo['sleepDt'] = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');

        return $sleepInfo;
    }

    /**
     * 휴면회원 대상 필드 암호화
     *
     * @param $member
     *
     * @return string
     */
    private function _encryptMemberBySleepTargetField($member)
    {
        $encryptor = \App::getInstance('encryptor');
        $encryptInfo = array_intersect_key($member, array_fill_keys($this->sleepEncryptField, null));
        $encryptInfo = serialize($encryptInfo);

        return $encryptor->mysqlAesEncrypt($encryptInfo);
    }

    /**
     * 휴면회원 전환
     *
     * @param string|integer $memberNo 회원번호
     *
     * @throws \Exception
     */
    public function sleepMemberByMemberNo($memberNo)
    {
        $member = $this->sleepDAO->selectSleepMemberByMemberNo($memberNo, SleepService::SLEEP_PERIOD);
        if (count($member) <= 0) {
            throw new \Exception(__('휴면회원 대상이 아닙니다.'));
        }
        try {
            $sleepInfo = $this->getIntersectMemberWithDefaultSleep($member);

            \DB::begin_tran();
            $this->sleepDAO->insertSleep($sleepInfo);
            $sleepNo = $this->sleepDAO->insertId();

            if ($sleepNo <= 0) {
                throw new \Exception(__('휴면회원 처리 중 실패 하였습니다.'));
            }

            $this->sleepDAO->updateMemberByEncrypt($sleepInfo, $this->sleepEncryptField);
            \DB::commit();
            $return[] = $sleepInfo;
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * 휴면회원 sms 발송함 저장
     * @deprecated 2017-02-06 yjwee MySQL 연결시간 초과 이슈 수정을 위해 사용하지 않습니다.
     *
     * @param $memberNo
     *
     * @return bool
     */
    public function saveSleepSmsSendFlag($memberNo)
    {
        $flag = ['sleepSmsFl' => 'y'];
        $result = $this->sleepDAO->updateMemberSleepGuideFlag($memberNo, $flag);

        return $result;
    }

    /**
     * 휴면회원 mail 발송함 저장
     * @deprecated 2017-02-06 yjwee MySQL 연결시간 초과 이슈 수정을 위해 사용하지 않습니다.
     *
     * @param $memberNo
     *
     * @return bool
     */
    public function saveSleepMailSendFlag($memberNo)
    {
        $flag = ['sleepMailFl' => 'y'];
        $result = $this->sleepDAO->updateMemberSleepGuideFlag($memberNo, $flag);

        return $result;
    }

    /**
     * @return mixed 휴면회원 대상 필드
     */
    public function getSleepEncryptField()
    {
        return $this->sleepEncryptField;
    }

    /**
     * @return array
     */
    public function getSleepResult()
    {
        return $this->sleepResult;
    }
}
