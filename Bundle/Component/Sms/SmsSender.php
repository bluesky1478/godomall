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

namespace Bundle\Component\Sms;


use Component\Godo\GodoSmsServerApi;
use Component\Sms\Exception\PasswordException;
use Component\Sms\SmsLog;
use Component\Sms\SmsUtil;
use Component\Validator\Validator;
use Framework\Security\Otp;
use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;
use UserFilePath;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use FileHandler;

/**
 * SMS 발송, 로그, api
 * @package Bundle\Component\Sms
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class SmsSender
{
    /** @var string sms 전송 타입 (order, member, promotion, board, user) */
    protected $smsType = 'user';
    /** @var array 수신자 정보 */
    protected $receiver;
    /** @var null 발송자 정보 */
    protected $sender;
    /** @var null 발송시간 */
    protected $sendDate;
    /** @var string api 전송 타입 (send : 발송, res_send : 예약 발송, res_update : 예약 수정, res_delete : 예약 삭제) */
    protected $tranType = 'res_send';
    /** @var null 전송 및 발송내역 저장 시 추가적으로 저장될 정보 */
    protected $logData;
    /** @var int sms 포인트 */
    protected $smsPoint;
    /** @var int sms 전송내역 일련번호 */
    protected $smsLogSno;
    /** @var array sms api 호출 시 사용될 정보 */
    protected $sendSms = [];
    /** @var array sms 전송내역 기록 시 사용될 정보 */
    protected $smsLog = [];
    /** @var \Bundle\Component\Sms\AbstractMessage */
    protected $message;
    /** @var array sms 전송결과 */
    protected $transportResult = [
        'success' => 0,
        'fail'    => 0,
    ];
    /** @var array $contentsMask 발송내용 출력 시 마스킹 처리될 정보 */
    protected $contentsMask = [];
    /** @var int */
    protected $password;
    /** @var bool SMS 발송 시 Exception 을 전달할 경우 사용 */
    protected $isThrowPasswordException = false;
    /** @var string SMS 대량발송 파일 경로 */
    private $_smsLargeFilePath;
    /** @var object SMS 대량발송 TXT 파일 생성 로거 */
    private $_smsLargeLogger;
    /** @var string SMS 대량발송 TXT 압축 파일명 */
    const SMS_LARGE_TXT_ZIP_NAME = 'SmsLargeTXT.zip';
    /** @var string SMS 대량발송 TXT 파일명 */
    const SMS_LARGE_TXT_FILE_NAME = 'SmsLarge.txt';
    /** @var int SMS 대량발송 기준 (501명 이상) */
    const SMS_LARGE_NUMS = 501;

    public function __construct()
    {
        $session = \App::getInstance('session');
        if ($session->has(SESSION_GLOBAL_MALL)) {
            throw new \Exception('Not support sending SMS in overseas shops');
        }
    }

    /**
     * 발송 함수
     *
     * @return array
     */
    public function send()
    {
        $logger = \App::getInstance('logger');
        try {
            // 전송일자가 없는 경우
            if (empty($this->sendDate) === true) {
                $this->sendDate = date('Y-m-d H:i:s', time());
            }
            if ($this->message === null) {
                throw new \Exception('message variable must not be null');
            }
            if (empty($this->receiver) === true) {
                $this->initResult();
                throw new \Exception('Empty Sms Receiver');
            }

            /* 2016-11-02 yjwee
            pg 결제 시 referer 가 변경되는 경우가 발생하여 주석처리
            스마트 원본 소스 부터 있던 로직
            if ($this->smsUtil->checkReferer() == false) { $logger->info(__METHOD__ . ' Check Host Referer', [Request::getHost(), Request::getReferer(),]); return $failReturn; }*/
            $smsUtil = \App::load(SmsUtil::class);
            $this->sender = $smsUtil->getSender($this->sender);
            if ($this->tranType === 'res_update' || $this->tranType === 'res_delete') {
                $this->message = new \Component\Sms\SmsMessage('');
            }
            $logger->info('Current message class is ' . \get_class($this->message));
            $this->removeDash();
            $this->validatePoint();
            $this->sendSmsByReceiver();
            $this->saveFailLog();
        } catch (\Throwable $e) {
            $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            if ($this->isThrowPasswordException) {
                throw $e;
            }
        }
        $logger->info('finish sms result!!!');

        return $this->transportResult;
    }

    /**
     * 발송결과 초기화
     *
     */
    protected function initResult()
    {
        $this->transportResult = [
            'success' => 0,
            'fail'    => 0,
        ];
    }

    /**
     * 전화번호의 `-` 제거
     *
     */
    protected function removeDash()
    {
        foreach ($this->receiver as $i => $item) {
            $this->receiver[$i]['cellPhone'] = str_replace('-', '', $this->receiver[$i]['cellPhone']);      // - 제거
        }
    }

    /**
     * 보유 포인트 검증
     *
     * @throws \Exception
     */
    protected function validatePoint()
    {
        if ($this->smsPoint < $this->getSendPoint() * $this->message->count()) {
            $logger = \App::getInstance('logger');
            $logger->info(sprintf('current sms point[%d], send count[%d], message count[%d]', $this->smsPoint, $this->getSendPoint(), $this->message->count()));
            throw new \Exception(__('SMS 잔여 포인트가 부족 합니다. SMS 포인트 충전하기를 통해 충전 후 발송을 하시기 바랍니다.'));
        }
    }

    /**
     * 발송 시 사용될 포인트 계산 함수
     * tranType 이 res_delete 인 경우 복구될 포인트를 계산한다.
     *
     * @param null $receiverCount
     * @return int
     */
    protected function getSendPoint($receiverCount = null)
    {
        if (is_null($receiverCount)) {
            $receiverCount = \count($this->receiver);
        }
        if ($this->_isLms()) {
            $point = $receiverCount * Sms::LMS_POINT;
        } else {
            $point = $receiverCount * 1;
        }
        if ($this->tranType === 'res_delete') {
            $point *= -1;
        }

        return $point;
    }

    /**
     * isLms
     *
     * @return bool
     */
    private function _isLms(): bool
    {
        return $this->message instanceof \Bundle\Component\Sms\LmsMessage;
    }

    /**
     * receiver 에 설정된 수신자 정보를 기준으로 전송내역, 발송내역, sms api 호출 을 처리하는 함수
     *
     * @throws \Exception
     */
    protected function sendSmsByReceiver()
    {
        $smsLog = \App::load('Component\\Sms\\SmsLog');
        $this->initResult();
        $receiverList = $this->getReceiverGroup();
        if (empty($receiverList)) {
            throw new \Exception('Empty receiver list');
        }
        $this->initSendSms();
        $this->initSmsLog();
        // 대량발송 데이터 파일 저장
        $isLargeData = $this->createLargeDataFile();
        // 발송내용 저장
        $this->smsLogSno = $smsLog->insertSmsLogByArray($this->smsLog, $this->sender, $this->logData);
        // 전송 리스트 저장
        $receiverForSaveSmsSendList = $this->receiver;
        if ($this->message instanceof \Bundle\Component\Sms\DivisionSmsMessage) {
            /*
             * 2017-07-19 yjwee 분할발송 수신결과 확인 시 메시지는 n 건 발송되므로 es_smsLog 에 발송 건수가 저장되지만
             * 실제 수신자는 1명이기 때문에 es_smsSendList 에는 1개의 데이터만 저장되는 문제를 해결하기 위해
             * 분할 발송시에는 es_smsSendList 에 메시지 갯수 만큼 저장하도록 처리하기 위해 수신자 정보를 메시지 갯수만큼 늘린다.
             */
            for ($i = 1; $i < $this->message->count(); $i++) {
                $receiverForSaveSmsSendList[$i] = $receiverForSaveSmsSendList[0];
            }
        }
        $smsLog->saveBulkSmsSendList($this->smsLogSno, $receiverForSaveSmsSendList, $this->logData, $this->message->getReplaceType());
        if ($this->message->hasReplaceCode()) { // 발송메시지에 치환코드 있는 경우
            $this->callApiByReceiver();
        } else if ($isLargeData === true) { // 발송메시지에 치환코드 없고, 대량발송인 경우
            $this->callApiByLarge();
            $this->removeLargeDataFile();
        } else { // 발송메시지에 치환코드 없고, 소량발송인 경우
            foreach ($receiverList as $item) {
                if (empty($item)) {
                    continue;
                }
                // 전송 파라미터 추가 - 휴대폰 번호
                $this->sendSms['hp'] = $item;
                $this->callApiByMessages();
            }
        }
    }

    /**
     * receiver 에 설정된 휴대폰번호를 30개씩 묶어서 반환하는 함수
     * 동일한 발송내용일 경우 사용된다.
     *
     * @return array
     */
    protected function getReceiverGroup()
    {
        $logger = \App::getInstance('logger');
        // SMS 번호를 30개씩 배열로 처리함
        $receiverListCnt = 0;
        $receiverList = [];
        $receiverList[] = '';
        $receiverCount = count($this->receiver);
        for ($i = 0; $i < $receiverCount; $i++) {
            $receiverList[$receiverListCnt] .= $this->receiver[$i]['cellPhone'] . ((($i + 1) % 30 == 0 || $i == $receiverCount - 1) ? '' : ',');
            if (($i + 1) % 30 == 0 && $i != $receiverCount - 1) {
                $receiverListCnt++;
                $receiverList[] = '';
            }
        }
        $logger->info(sprintf('receiver count[%s], send list count[%s], receiver list count(group 30)[%s]', count($this->receiver), $receiverCount, count($receiverList)));

        return $receiverList;
    }

    /**
     * sms api 시 사용되는 값 초기화 함수
     *
     */
    protected function initSendSms()
    {
        // 전송 파라미터 (순서 변경되면 전송 안됨)
        // 2017-04-04 yjwee 전송파라미터는 재설정하기때문에 순서는 상관없는 것으로 확인됨
        //@formatter:off
        $this->sendSms = [
            'type'       => $this->tranType,
            'sno'        => ''  ,   // api 전송 시 sno 값을 설정하게끔 수정
            'pass'       => $this->password,
            'callback'   => $this->sender['recall'],
            'hp'         => '',
            'res_date'   => $this->sendDate,
            'res_etc'    => gd_date_format('Ymd', $this->sendDate),
            'smsSendKey' => time() . Otp::getOtp(4),
            '__head__'   => '__body__',
            'msg'        => '',
        ];
        //@formatter:on
        if ($this->_isLms()) {
            $this->sendSms['subject'] = '';
        }
    }

    /**
     * sms 전송내역 정보 초기화 함수
     *
     */
    protected function initSmsLog()
    {
        $smsAutoPolicy = ComponentUtils::getPolicy('sms.smsAuto');
        // 전송전 로그 저장
        $this->smsLog = [
            'sendFl'            => 'sms',
            'smsType'           => $this->smsType,
            'smsDetailType'     => $this->logData['smsAutoCode'],
            'sendType'          => $this->tranType,
            'subject'           => '',
            'contents'          => $this->message->getContentsByLog(),
            'contentsMask'      => implode(STR_DIVISION, $this->contentsMask),
            'receiverCnt'       => (count($this->receiver) * $this->message->count()),
            'replaceCodeType'   => $this->message->getReplaceType(),
            'sendDt'            => $this->sendDate,
            'smsSendKey'        => $this->sendSms['smsSendKey'],
            'smsAutoSendOverFl' => StringUtils::strIsSet($smsAutoPolicy['smsAutoSendOver'], 'none'),
        ];
        if ($this->_isLms()) {
            $this->smsLog['sendFl'] = 'lms';
        }
    }

    /**
     * sms api 를 수신자 정보 기준으로 발송하는 함수
     * 수신자 정보 기준으로 발송 시에는 분할 발송을 지원하지 않는다.
     */
    protected function callApiByReceiver()
    {
        $logger = \App::getInstance('logger');
        $logger->info(__METHOD__ . ', receiver count[' . count($this->receiver) . '], start point[' . $this->smsPoint . ']');
        if ($this->message instanceof \Bundle\Component\Sms\DivisionSmsMessage) {
            throw new \Exception('Not support division sms message');
        }
        foreach ($this->receiver as $item) {
            $this->sendSms['hp'] = $item['cellPhone'];
            $replaceCode = $item['replaceCode'];
            if (count($replaceCode) < 1) {
                $replaceCode = $item;   // 치환코드 데이터가 없는 경우 수신자 정보 자체를 치환코드로 사용한다.
            }
            $replace = \App::load('Component\\Design\\ReplaceCode');
            $this->message = $this->message->toSmsMessage();    // 치환 결과에 따라 변수의 클래스가 lms 일 수 있기 때문에 sms 로 변환
            $defaultReplaceCode = array_fill_keys($this->message->getReplaceCodes(), '');
            $replaceCode = array_merge($defaultReplaceCode, $replaceCode);
            $replaceContents = $replace->appendBracesAndReplace($this->message->getContents(), $replaceCode);
            $hasPoint = $this->smsPoint > 1;
            if ($this->message->exceedSmsLength($replaceContents)) {    // 치환 후 sms 길이를 초과하는 경우 lms 로 변환
                $this->message = $this->message->toLmsMessage();
                $replaceContents = $replace->appendBracesAndReplace($this->message->getContents(), $replaceCode);
                $hasPoint = $this->smsPoint > Sms::LMS_POINT;
                if ($this->message->exceedLmsLength($replaceContents)) {
                    $logger->error('Message [' . $replaceContents . '] is exceed lms length');
                    $this->saveSendResultByFail($this->sendSms['hp'], '1002');
                    $this->transportResult['fail']++;
                    continue;
                }
            }
            if ($hasPoint) {
                $messages = $this->message->getMessages($replaceContents);
                $message = $messages[0];    // 치환코드 적용 시 개별 메시지로 적용되기 때문에 분할 발송이 없다.
                $this->sendSms['msg'] = $message;
                $res = $this->callApi();
                $this->smsPoint -= ($this->_isLms() ? Sms::LMS_POINT : 1);
                $this->callApiPostProcess($res, 1);
            } else {
                $this->saveSendResultByFail($this->sendSms['hp'], '1001');
                $this->transportResult['fail']++;
            }
        }
        $logger->info(__METHOD__ . ', end point[' . $this->smsPoint . ']');
        Sms::saveSmsPoint($this->smsPoint);
    }

    /**
     * sms api 호출 실패 시 호출되는 함수
     * 실패 정보 기록에 필요한 값 설정 및 기록 함수를 호출한다.
     *
     * @param      $phoneNumber
     * @param null $failCode
     */
    protected function saveSendResultByFail($phoneNumber, $failCode = null)
    {
        $logger = \App::getInstance('logger');
        $smsLog = \App::load('Component\\Sms\\SmsLog');
        $log['smsLogSno'] = $this->smsLogSno;
        $log['receiverCellPhone'] = $phoneNumber;
        $log['sendCheckFl'] = 'n';
        $log['failCode'] = $failCode;
        $logger->info(__METHOD__, $log);
        $smsLog->updateSmsSendResult($log);
    }

    /**
     * sms api 실행 함수
     *
     * @param string $type
     * @return string OK = 성공, ERROR|사유 = 유효성검사 에러, BLOCK|사유 = 발신번호차단
     */
    protected function callApi($type = 'basic'): string
    {
        $api = new GodoSmsServerApi();
        if ($this->_isLms()) {
            $this->sendSms['tranType'] = 'lms';
        } else {
            unset($this->sendSms['subject']);
            $this->sendSms['tranType'] = 'sms';
        }
        if ($this->password === null) {
            return 'BLOCK|INVALID_PASSWORD_SKIP_SEND_SMS_API_G5_MESSAGE';
        }
        if ($type == 'large') { // 대량발송
            $res = $api->sendSmsLargeApi($this->sendSms);
        } else {
            $res = $api->sendSmsApi($this->sendSms);
        }
        $this->_writeCallApiLog($res);
        $this->authenticatePassword($res);

        return $res;
    }

    /**
     * api 호출 로그
     *
     * @param $response
     */
    private function _writeCallApiLog($response)
    {
        $logger = \App::getInstance('logger')->channel(\Framework\Application\Bootstrap\Log::CHANNEL_SMS);
        $log = [
            'smsLogSno'  => $this->smsLogSno,
            'smsSendKey' => $this->sendSms['smsSendKey'],
            'tranType'   => $this->sendSms['tranType'],
            'point'      => $this->smsPoint,
        ];
        $logger->info($response, $log);
    }

    protected function authenticatePassword($response)
    {
        if (strpos($response, 'NOT_VALID_PASSWORD') !== false) {
            $this->password = null;
            $this->transportResult['fail'] += (\count($this->receiver) - $this->transportResult['success']);
            throw new PasswordException(PasswordException::INVALID_PASSWORD);
        }
    }

    /**
     * sms api 호출 후 처리 함수
     * api 호출 결과에 따라 발송내역을 갱신한다.
     *
     * @param $response
     * @param $count
     */
    protected function callApiPostProcess($response, $count)
    {
        $smsLog = \App::load('Component\\Sms\\SmsLog');
        // 결과값 처리 OK = 성공, ERROR|사유 = 유효성검사 에러, BLOCK|사유 = 발신번호차단
        if ($response == 'OK') {
            // 접수상태 update
            $smsLog->updateSmsSendList($this->smsLogSno, $this->sendSms['hp']);
            $this->transportResult['success'] += $count;
        } else {
            //접수상태 실패로 저장
            $arrayFailPhone = explode(',', $this->sendSms['hp']);
            foreach ($arrayFailPhone as $failPhone) {
                $this->saveSendResultByFail($failPhone);
            }
            $this->transportResult['fail'] += $count;
        }
    }

    /**
     * sms api 를 발송 메시지 기준으로 호출하는 함수
     * 기존 호출 방식이며 분할 발송 시 해당 함수로 발송해야한다.
     *
     */
    protected function callApiByMessages()
    {
        $logger = \App::getInstance('logger');
        // 분할 발송일경우 내용만큼 보냄
        $messages = $this->message->getMessages();
        $hpCount = count(explode(',', $this->sendSms['hp']));
        // 분할 전송시 메시지 순서가 섞이는 경우가 발생하여 딜레이(1초씩) 시켜줌
        $delayTime = 1;
        foreach ($messages as $index => $message) {
            $this->sendSms['msg'] = $message;
            $tmpDate = strtotime($this->sendSms['res_date']);
            $this->sendSms['res_date'] = date('Y-m-d H:i:s', $tmpDate + $delayTime * $index);
            $res = $this->callApi();
            $logger->info(__METHOD__ . ' Sms API Result=[' . $res . ']');
            $this->callApiPostProcess($res, $hpCount);
        }
        // 그룹을 2개 이상 발송할 경우 sms포인트가 중복 차감 처리 되어 해당 부분 수신인으로 카운팅 처리
        $receiverCount = null;
        if ($this->getReceiverGroup() > 1) {
            $receiverCount = count(explode(',', $this->sendSms['hp']));
        }
        $this->smsPoint -= ($this->getSendPoint($receiverCount) * $this->message->count());
        Sms::saveSmsPoint($this->smsPoint);
    }

    /**
     * 대량발송 sms api 호출 후 처리 함수
     * api 호출 결과에 따라 발송내역을 갱신한다.
     *
     * @param $response
     * @param $count
     */
    protected function callApiByLargePostProcess($response, $count)
    {
        $smsLog = \App::load('Component\\Sms\\SmsLog');
        // 결과값 처리 success = 성공, 그외 = 실패코드
        if ($response == 'OK') {
            // 접수상태 update
            $smsLog->updateSmsSendListAll($this->smsLogSno);
            $this->transportResult['success'] += $count;
        } else {
            // 접수상태 실패로 저장
            $log = [];
            $log['smsLogSno'] = $this->smsLogSno;
            $log['sendCheckFl'] = 'n';
            $log['failCode'] = null;
            $smsLog->updateSmsSendResultAll($log);
            $this->transportResult['fail'] += $count;
        }
    }

    /**
     * sms api 를 대량발송 메시지 기준으로 호출하는 함수
     */
    protected function callApiByLarge()
    {
        $logger = \App::getInstance('logger');

        $res = $this->callApi('large');
        $logger->info(__METHOD__ . ' Sms API Result=[' . $res . ']');
        $hpCount = count($this->receiver);
        $this->callApiByLargePostProcess($res, $hpCount);

        // 그룹을 2개 이상 발송할 경우 sms포인트가 중복 차감 처리 되어 해당 부분 수신인으로 카운팅 처리
        $this->smsPoint -= ($this->getSendPoint() * $this->message->count());
        Sms::saveSmsPoint($this->smsPoint);
    }

    /**
     * 전송내역 실패 시 호출되는 함수
     *
     */
    protected function saveFailLog()
    {
        $smsLog = \App::load(SmsLog::class);
        if ($this->transportResult['success'] === 0 && $this->transportResult['fail'] > 0) {
            //전송내역 실패건 체크
            $smsLogFailData['sno'] = $this->smsLogSno;
            $smsLogFailData['sendStatus'] = 'n';
            $smsLogFailData['sendFailCnt'] = $this->transportResult['fail'];
            $smsLog->updateSmsLog($smsLogFailData);
        }
    }

    /**
     * @param string $smsType
     */
    public function setSmsType(string $smsType)
    {
        $this->smsType = $smsType;
    }

    /**
     * setMessage
     *
     * @param AbstractMessage $message
     */
    public function setMessage(AbstractMessage $message)
    {
        $this->message = $message;
    }

    /**
     * @param mixed $receiver
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * @param null $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     * @param null $sendDate
     */
    public function setSendDate($sendDate)
    {
        $this->sendDate = $sendDate;
    }

    /**
     * @param string $tranType
     */
    public function setTranType(string $tranType)
    {
        if ($tranType == 'send') {
            $tranType = 'res_send';
        }
        $this->tranType = $tranType;
    }

    /**
     * @param null $logData
     */
    public function setLogData($logData)
    {
        $this->logData = $logData;
    }

    /**
     * @param mixed $smsPoint
     */
    public function setSmsPoint($smsPoint)
    {
        $this->smsPoint = $smsPoint;
    }

    /**
     * @param array $contentsMask
     */
    public function setContentsMask(array $contentsMask)
    {
        $this->contentsMask = $contentsMask;
    }

    /**
     * @return int
     */
    public function getSmsLogSno()
    {
        return $this->smsLogSno;
    }

    /**
     * @param bool $isThrowPasswordException
     */
    public function setIsThrowPasswordException(bool $isThrowPasswordException)
    {
        $this->isThrowPasswordException = $isThrowPasswordException;
    }

    /**
     * validPassword
     *
     * @param      $smsPassword
     *
     * @param bool $isVerify
     *
     * @throws PasswordException
     */
    public function validPassword($smsPassword, $isVerify = true)
    {
        if ($smsPassword !== '1111') {
            if (Validator::password($smsPassword, true) === false) {
                throw new PasswordException(PasswordException::INVALID_PASSWORD);
            }
            if ($isVerify) {
                $smsUtil = \App::load(\Component\Sms\SmsUtil::class);
                if ($smsUtil->getPassword() !== $smsPassword) {
                    throw new PasswordException(PasswordException::WRONG_PASSWORD);
                }
                if ($smsUtil->isAuthenticationLimit()) {
                    throw new PasswordException(PasswordException::LIMIT_PASSWORD);
                }
            }
        }
        $this->password = $smsPassword;
    }

    /**
     * 대량발송 데이터 파일 저장 (txt 저장 > zip 압축)
     * 발송메시지에 치환코드 없고 (치환코드 있으면 메시지 길이 달라져 개별 발송) , 발송 대상 수가 501명 이상인 경우 대량 발송 프로세스로 전환
     *
     * @return bool
     * @throws \Exception
     */
    protected function createLargeDataFile()
    {
        $logger = \App::getInstance('logger');

        // 대량발송 조건 체크
        if ($this->message->hasReplaceCode() === true) { // 발송메시지에 치환코드 있는 경우 false 리턴
            return false;
        }
        if (count($this->receiver) < self::SMS_LARGE_NUMS) { // 발송 대상 수가 소량(501명 이상 대량발송)인 경우 false 리턴
            return false;
        }

        // TXT 파일 저장
        $this->_smsLargeFilePath = UserFilePath::temporary('sms_large')->getRealPath() . DS;
        $tmpFilePath = $this->_smsLargeFilePath . self::SMS_LARGE_TXT_FILE_NAME;
        $this->initSmsLargeLogger($tmpFilePath, false);
        $this->saveSmsLargeTXT();

        // TXT 파일 체크
        if (\FileHandler::isExists($tmpFilePath) === false) {
            throw new \Exception(__('대량발송 데이터 파일 저장시 오류가 발생되었습니다.'));
        }
        if (\FileHandler::getSize($tmpFilePath) === false || \FileHandler::getSize($tmpFilePath) == 0) {
            throw new \Exception(__('대량발송 데이터 파일 저장시 오류가 발생되었습니다.'));
        }

        // TXT 로그
        $tmpFileTime = date('Y-m-d H:i:s', \FileHandler::getModifiedTime($tmpFilePath));
        $tmpFileSize = \FileHandler::getSize($tmpFilePath);
        $logger->info(__METHOD__ . ', TXT Name[' . basename($tmpFilePath) . '], ModifiedTime[' . $tmpFileTime . '], Size[' . $tmpFileSize . ']');

        // 압축 파일명
        $compressFileName = $this->_smsLargeFilePath . self::SMS_LARGE_TXT_ZIP_NAME;

        // 기존 압축 파일 존재시 삭제
        if (\FileHandler::isExists($compressFileName) === true) {
            \FileHandler::delete($compressFileName, true);
        }

        // 압축 대상
        $compressTarget = self::SMS_LARGE_TXT_FILE_NAME;

        // 파일 압축
        exec('cd ' . $this->_smsLargeFilePath . ' && zip -r "' . $compressFileName . '" ' . $compressTarget);

        // 압축파일 체크
        if (\FileHandler::isExists($compressFileName) === false) {
            throw new \Exception(__('대량발송 데이터 압축시 오류가 발생되었습니다.'));
        }
        if (\FileHandler::getSize($compressFileName) === false || \FileHandler::getSize($compressFileName) == 0) {
            throw new \Exception(__('대량발송 데이터 압축시 오류가 발생되었습니다.'));
        }

        // 압축파일 로그
        $compressFileTime = date('Y-m-d H:i:s', \FileHandler::getModifiedTime($compressFileName));
        $compressFileSize = \FileHandler::getSize($compressFileName);
        $logger->info(__METHOD__ . ', ZIP Name[' . basename($compressFileName) . '], ModifiedTime[' . $compressFileTime . '], Size[' . $compressFileSize . ']');

        // 퍼미션
        \FileHandler::chmod($this->_smsLargeFilePath, 0707);
        \FileHandler::chmod($compressFileName, 0707);

        $this->sendSms['dataFile'] = $compressFileName; // 압축파일 리얼경로

        return true;
    }

    /**
     * 대량발송 TXT 파일 저장
     */
    protected function saveSmsLargeTXT()
    {
        $globals = \App::getInstance('globals');
        $godoSno = $globals->get('gLicense.godosno');
        $ecCode = $globals->get('gLicense.ecCode');
        $transSystem = 'dreamline';//sms기본모듈은 드림라인으로 설정한다.
        if ($globals->get('gLicense.smsModule') === 'l') {
            $transSystem = 'lgdacom';//sms모듈
        } else if ($globals->get('gLicense.smsModule') === 'i') {
            $transSystem = 'infobank';//sms모듈
        } else if ($globals->get('gLicense.smsModule') === 'd') {
            $transSystem = 'dreamline';//sms모듈
        }
        $smsConf = gd_policy('sms.config');

        $headParams = [];
        $headParams['svcKind'] = 'echost'; // 서비스 구분
        $headParams['svcKey'] = $godoSno; // 서비스 key (쇼핑몰 sno)
        $headParams['fromPhoneNumber'] = str_replace('-', '', $smsConf['smsCallNum']); // 발신번호
        $headParams['tranId'] = $godoSno; //전송 ID
        $headParams['tranDTime'] = $this->sendSms['res_date']; // 발송시간 : 전송될 시간(즉시발송 = now(), 예약발송 = 시간입력)
        $headParams['tranSystem'] = $transSystem; // 발송모듈
        $headParams['svcVer'] = 'godo5'; // 서비스 버전
        $headParams['pass'] = $this->sendSms['pass']; // SMS 인증 패스워드 : 비밀번호
        $headParams['smsSendKey'] = $this->sendSms['smsSendKey']; // SMS 발송 건 고유번호 : sms 발송키
        $headParams['serviceType'] = $ecCode; // 상점 구분코드 : 서비스 타입
        $headParams['sendData'] = []; // 발송 데이터
        $jsonParams = json_encode($headParams, JSON_UNESCAPED_UNICODE);
        $jsonParams = preg_replace('/"sendData":\[\]\}$/', '"sendData":[', $jsonParams);
        $this->writeSmsLargeLogger($jsonParams);

        // 분할 발송일경우 내용만큼 보냄
        $messages = $this->message->getMessages();
        $delayTime = 1; // 분할 전송시 메시지 순서가 섞이는 경우가 발생하여 딜레이(1초씩) 시켜줌
        $tmpDate = strtotime($this->sendSms['res_date']);
        $msgCount = count($messages);
        $receiverCount = count($this->receiver);
        foreach ($messages as $mIndex => $message) {
            foreach ($this->receiver as $rIndex => $receiver) {
                $res_date = date('Y-m-d H:i:s', $tmpDate + $delayTime * $mIndex);
                $sendData = [
                    'toPhoneNumber' => $receiver['cellPhone'], // 수신번호
                    'msg' => $message, // 전송내용
                    'tranDTime' => $res_date // 발송시간 : 전송될 시간(즉시발송 = now(), 예약발송 = 시간입력)
                ];

                if ($this->_isLms()) {
                    $sendData['subject'] = $this->sendSms['subject']; // lms 제목
                }

                $jsonParams = json_encode($sendData, JSON_UNESCAPED_UNICODE);
                if ($msgCount > ($mIndex + 1) || $receiverCount > ($rIndex + 1)) {
                    $jsonParams .= ',';
                }

                $this->writeSmsLargeLogger($jsonParams);
            }
        }

        $this->writeSmsLargeLogger(']}');
    }

    /**
     * 파일 작성
     *
     * @param $contents
     */
    protected function writeSmsLargeLogger($contents)
    {
        $this->_smsLargeLogger->info($contents);
    }

    /**
     * 파일 생성 로거
     *
     * @param $loggerPath string 파일 생성 경로
     * @param $appendFl bool  이어쓰기 여부
     * @throws
     */
    protected function initSmsLargeLogger($loggerPath, $appendFl = true)
    {
        if ($appendFl === false) {
            unlink($loggerPath);
        }
        $handler = new StreamHandler($loggerPath, Logger::INFO, false, 0707);
        $formatter = new LineFormatter("%message%\r\n");
        $formatter->allowInlineLineBreaks(false);
        $formatter->ignoreEmptyContextAndExtra(false);
        $handler->setFormatter($formatter);
        $this->_smsLargeLogger = new Logger('smsLarge');
        $this->_smsLargeLogger->pushHandler($handler);
    }

    /**
     * 대량발송 데이터 파일 삭제 (txt, zip)
     */
    protected function removeLargeDataFile()
    {
        $this->_smsLargeFilePath = UserFilePath::temporary('sms_large')->getRealPath() . DS;
        $tmpFilePath = $this->_smsLargeFilePath . self::SMS_LARGE_TXT_FILE_NAME;
        $compressFileName = $this->_smsLargeFilePath . self::SMS_LARGE_TXT_ZIP_NAME;

        // TXT 파일 정리
        if (\FileHandler::isExists($tmpFilePath) === true) {
            \FileHandler::delete($tmpFilePath, true);
        }

        // 압축 파일 정리
        if (\FileHandler::isExists($compressFileName) === true) {
            \FileHandler::delete($compressFileName, true);
        }
    }
}
