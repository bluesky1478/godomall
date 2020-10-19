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

use Component\Sms\SmsSender;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;
use SplObserver;

/**
 * Class SMS 자동 발송
 * 기존 SMS::smsAutoSend 함수를 대신 사용하는 클래스
 * 기존 smsAutoSend 함수 호출 시 전달하던 파라미터는 setter 함수를 통해 전역변수로 설정하면 된다.
 *
 * @package Bundle\Component\Sms
 * @author  yjwee
 */
class SmsAuto implements \SplSubject
{
    /** @var \Framework\Database\DBTool $db */
    protected $db;
    /** @var  \Bundle\Component\Sms\Sms $sms */
    protected $sms;
    /** @var  \Bundle\Component\Sms\SmsAutoCode $smsAutoCode */
    protected $smsAutoCode;
    /** @var  string $smsType SMS 자동 발송 종류 ('order', 'member', 'promotion', 'board') */
    protected $smsType;
    /** @var  string $smsAutoCodeType SMS 자동 발송 코드 (ORDER, INCASH, ACCOUNT, DELIVERY .......) */
    protected $smsAutoCodeType;
    /** @var  mixed $receiver 수신정보
     * (1. 전화번호만 변수로 전송,
     * 2. scmNo, memNo, memNm, smsFl, cellPhone 키값으로 1차 배열,
     * 3. 0 => [scmNo, memNo, memNm, smsFl, cellPhone] 이와 같은 2차 배열, scmNo 는 배열로)
     * EX) cellPhone 는 (비)회원 전송폰번호
     * */
    protected $receiver;
    /** @var array $replaceArguments 전송할 데이터 (치환코드 대입용) */
    protected $replaceArguments = null;
    /** @var array $smsAutoType 강제 대상지정 (member or admin or provider) ex)게시판 */
    protected $smsAutoType = null;
    protected $smsAutoConfig;
    /** @var array $smsContentWheres SMS 발송내용 조회 조건절 */
    protected $smsContentWheres = [];
    /** @var string $receiverType 수신자 정보 타입
     * phone : 전화번호
     * array : 배열
     * multi : 다중배열
     */
    protected $receiverType = 'phone';
    /** @var array $receiversScmNo SMS 수신받을 공급사 번호 */
    protected $receiversScmNo = [];
    /** @var string $smsContents SMS 발송내용 */
    protected $smsContents = '';
    /** @var array $checkModes 회원수신, 야간발송, 최근 주문일 체크 배열 */
    protected $checkModes = [];
    /** @var null|string 자동 SMS 발송 시간 */
    protected $smsAutoSendDate = null;
    /** @var string 자동 SMS 발송 방식 */
    protected $smsAutoTranType = 'send';
    /** @var \SplObserver[] $smsAutoObservers 자동 SMS 발송 대기 리스트 */
    protected $smsAutoObservers = [];
    /** @var bool sms 지연발송 여부 */
    protected $useObserver = false;

    /** @var array 카카오알림설정 */
    protected $kakaoAlrimAutoConfig;
    /** @var array 카카오알림설정(루나) */
    protected $kakaoAlrimLunaAutoConfig;
    /** @var array 카카오설정 무시 플래그 */
    protected $kakaoAlrimIgnoreFlag = 'n';
    /** @var array 발송내용 중 로그 출력 시 마스킹 정보 */
    protected $contentsMask = [];
    /** @var array 발송내용 출력 시 마스킹 처리될 치환코드 */
    protected $maskArguments = ['rc_certificationCode'];

    /** @var 마이앱 사용유무 */
    protected $useMyapp;

    /** @var 비밀번호 확인 유무 */
    protected $passwordCheckFl = true;

    public function __construct(array $config = [])
    {
        if (isset($config['db']) && \is_object($config['db'])) {
            $this->db = $config['db'];
        } else {
            $this->db = \App::load('DB');
        }
        // 카카오알림설정값만 기본셋팅처리
        $this->kakaoAlrimAutoConfig = $this->getKakaoAlrimAutoPolicy();
        // 카카오알림설정값(루나)
        $this->kakaoAlrimLunaAutoConfig = $this->getKakaoAlrimLunaAutoPolicy();

        // 마이앱 사용유무
        $this->useMyapp = gd_policy('myapp.config')['useMyapp'];
    }

    /**
     * SMS 자동발송 함수
     *
     * @param string $kakaoAlrimIgnoreFlag 카카오알림톡 설정 무시 (기본 n:무시안함)
     *
     * @return array|bool 성공, 실패 Count
     */
    public function autoSend($kakaoAlrimIgnoreFlag = 'n')
    {
        //카카오알림톡설정 무시 값으로 넘어오면처리
        if ($kakaoAlrimIgnoreFlag == 'y') {
            $this->kakaoAlrimIgnoreFlag = 'y';
        }

        $logger = \App::getInstance('logger');
        $session = \App::getInstance('session');
        $result = [];
        try {
            if ($session->has(SESSION_GLOBAL_MALL)) {
                throw new \Exception('Not support sending SMS in overseas shops');
            }

            if (gd_is_plus_shop(PLUSSHOP_CODE_KAKAOALRIMLUNA) === true && $this->kakaoAlrimIgnoreFlag == 'n' && $this->kakaoAlrimLunaAutoConfig['useFlag'] == 'y' && $this->kakaoAlrimLunaAutoConfig[strtolower($this->smsType) . 'UseFlag'] == 'y') {

                $this->checkSmsTypeAndCodeWithReceiverKakao('luna');
                $this->checkSmsAutoTypeAndSmsContentWheresKakao('luna');
                $this->checkDefaultSmsAutoCodeAndReceiverKakao('luna');
                if ($this->smsType == \Component\Sms\SmsAutoCode::MEMBER && $this->smsAutoCodeType == \Component\Sms\Code::JOIN) {
                    if ($this->kakaoAlrimLunaAutoConfig[$this->smsType][$this->smsAutoCodeType]['smsDisapproval'] != 'y' && $this->replaceArguments['appFl'] == 'n') {
                        throw new \Exception('Disapproval member');
                    }
                }
                $smsContents = $this->getSmsContentsKakaoLuna($this->smsContentWheres);
                $logger->info(sprintf('Count sms contents.[%d]', count($smsContents)));

                // 카카오알림톡 자동 발송 예약시간 설정
                if ($this->smsType == SmsAutoCode::MEMBER && array_key_exists($this->smsAutoCodeType, Sms::KAKAO_AUTO_RESERVATION_DEFAULT_TIME)) {
                    $this->setSmsAutoSendDate($this->getKakaoAlrimAutoReserveTime($this->smsAutoCodeType,'luna'));
                }
            }
            //기본값들 셋팅 카카오알림 설정에 따른 분기
            //카카오알림톡설정무시가 아니면 & 카카오 알림톡 사용설정일때 & 카카오 알림톡 smsType영역 설정이 사용설정일때
            else if ($this->kakaoAlrimIgnoreFlag == 'n' && $this->kakaoAlrimAutoConfig['useFlag'] == 'y' && $this->kakaoAlrimAutoConfig[strtolower($this->smsType) . 'UseFlag'] == 'y') {
                $this->checkSmsTypeAndCodeWithReceiverKakao();
                $this->checkSmsAutoTypeAndSmsContentWheresKakao();
                $this->checkDefaultSmsAutoCodeAndReceiverKakao();
                if ($this->smsType == \Component\Sms\SmsAutoCode::MEMBER && $this->smsAutoCodeType == \Component\Sms\Code::JOIN) {
                    if ($this->kakaoAlrimAutoConfig[$this->smsType][$this->smsAutoCodeType]['smsDisapproval'] != 'y' && $this->replaceArguments['appFl'] == 'n') {
                        throw new \Exception('Disapproval member');
                    }
                }
                $smsContents = $this->getSmsContentsKakao($this->smsContentWheres);
                $logger->info(sprintf('Count sms contents.[%d]', count($smsContents)));
                // 카카오알림톡 자동 발송 예약시간 설정
                if ($this->smsType == SmsAutoCode::MEMBER && array_key_exists($this->smsAutoCodeType, Sms::KAKAO_AUTO_RESERVATION_DEFAULT_TIME)) {
                    $this->setSmsAutoSendDate($this->getKakaoAlrimAutoReserveTime($this->smsAutoCodeType));
                }
            } else {
                $this->checkSmsTypeAndCodeWithReceiver();
                $this->checkSmsAutoTypeAndSmsContentWheres();
                $this->checkDefaultSmsAutoCodeAndReceiver();
                if ($this->smsType == \Component\Sms\SmsAutoCode::MEMBER && $this->smsAutoCodeType == \Component\Sms\Code::JOIN) {
                    if ($this->smsAutoConfig[$this->smsType][$this->smsAutoCodeType]['smsDisapproval'] != 'y' && $this->replaceArguments['appFl'] == 'n') {
                        throw new \Exception('Disapproval member');
                    }
                }

                $smsContents = $this->getSmsContents($this->smsContentWheres);
                $logger->info(sprintf('Count sms contents.[%d]', count($smsContents)));
            }

            foreach ($smsContents as $index => $item) {
                // 전송가능여부
                $isPossibleSend = true;
                // 발송자
                $receiverData = [];
                // 저장 로그 데이터
                $logData = [];
                $logData['receiver']['smsAutoType'] = $item['smsAutoType'];
                // 발송 문자 내용 replace
                $contents = $item['contents'];
                if (gd_is_plus_shop(PLUSSHOP_CODE_KAKAOALRIMLUNA) === true && $this->kakaoAlrimIgnoreFlag == 'n' && $this->kakaoAlrimLunaAutoConfig['useFlag'] == 'y' && $this->kakaoAlrimLunaAutoConfig[strtolower($this->smsType) . 'UseFlag'] == 'y') {
                    $contents = $this->replaceContentsKakao($contents);
                }
                else if ($this->kakaoAlrimIgnoreFlag == 'n' && $this->kakaoAlrimAutoConfig['useFlag'] == 'y' && $this->kakaoAlrimAutoConfig[strtolower($this->smsType) . 'UseFlag'] == 'y') {
                    $contents = $this->replaceContentsKakao($contents);
                    // 주문배송관련 알림톡 중 페이지링크에 송장번호 치환코드 있는 경우
                    if (empty($item['button']) === false) {
                        $buttons = json_decode($item['button'], true);
                        $replaceCodeButtonFl = false;
                        foreach ($buttons as $key => &$val) {
                            $buttonData[$key]['name'] = $val['name'];
                            $buttonData[$key]['type'] = $val['linkType'];
                            if ($val['linkType'] === 'WL') {
                                if (preg_match("/#{invoiceNo}/", $val['linkMo'])) {
                                    $replaceCodeButtonFl = true;
                                    $val['linkMo'] = $this->replaceContentsKakao($val['linkMo']);
                                }
                                $buttonData[$key]['url_mobile'] = $val['linkMo'];
                            }

                        }
                        if (is_array($buttonData) && count($buttonData) > 0) {
                            $buttonData = json_encode($buttonData, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
                        }
                    }
                } else {
                    $contents = $this->replaceContents($contents);
                }
                if (empty($contents)) {
                    $logger->info(sprintf('Impossible send sms index[%d] empty contents.', $index));
                    $isPossibleSend = false;
                }
                // 수신자 가져오기 (회원)
                if ($item['smsAutoType'] == 'member' && $isPossibleSend) {
                    // 수신정보가 일반배열인 경우
                    if ($this->receiverType === 'array') {
                        $receiverData[0]['memNo'] = StringUtils::strIsSet($this->receiver['memNo']);
                        $receiverData[0]['memNm'] = StringUtils::strIsSet($this->receiver['memNm']);
                        $receiverData[0]['smsFl'] = StringUtils::strIsSet($this->receiver['smsFl'], 'n');
                        $receiverData[0]['cellPhone'] = StringUtils::strIsSet($this->receiver['cellPhone']);
                        // 수신동의 회원만 발송
                        if ($this->checkModes['agreeCheck'] === 'y' && $this->receiver['smsFl'] === 'n') {
                            $logger->info(__METHOD__ . ' Receive Reject');
                            $isPossibleSend = false;
                        }
                        $logData['receiver']['type'] = 'each';
                        $logData['receiver']['scmNo'] = StringUtils::strIsSet($this->receiver['scmNo']);
                    } elseif ($this->receiverType === 'multi') {    // 수신정보가 이차배열인 경우
                        $multipleReceiver = $this->getMultipleReceiver();
                        $receiverData = $multipleReceiver['receivers'];
                        // 수신 동의 회원만 발송이여서 정보가 없거나. 다른 이유로 정보가 없는 경우 return
                        if (empty($receiverData)) {
                            $logger->info(__METHOD__ . ' Empty ReceiverData - ' . $item['smsAutoType']);
                            $isPossibleSend = false;
                        }
                        // 로그 내용
                        $logData['receiver']['type'] = 'group';
                        $logData['receiver']['scmNo'] = $multipleReceiver['scmNo'];
                    } elseif ($this->receiverType === 'phone') {
                        $receiverData[0]['memNo'] = '0';
                        $receiverData[0]['memNm'] = '';
                        $receiverData[0]['smsFl'] = 'y';
                        $receiverData[0]['cellPhone'] = $this->receiver;
                        $logData['receiver']['type'] = 'each';
                        // 수신 동의 회원만 발송 관련 로직은 없음 그냥 다 보냄
                    } else {
                        $logger->info(sprintf('Not found receiver type.[%s]', $this->receiverType));
                    }
                }
                // 수신자 가져오기 (운영자 / 공급사)
                if (($item['smsAutoType'] === 'admin' || ($item['smsAutoType'] === 'provider' && empty($this->receiversScmNo) === false)) && $isPossibleSend) {
                    if ($this->smsAutoCodeType == 'SETTLE_BANK') {
                        $receiverData[]['cellPhone'] = $this->receiver;
                    } else {
                        $receiverData = $this->getManagerReceiver($item['smsAutoType']);
                    }
                    $logData['receiver']['type'] = 'group';
                    $logData['receiver']['scmNo'] = $this->receiversScmNo;
                    $logData['receiver']['dbTable'] = 'manager';
                }

                // 마이앱 푸시 대체 발송 타입 (사용안함 : NONE / 대체발송 : ALTERNATE / 함께발송 : TOGETHER)
                if ($this->useMyapp) {
                    $myappAlternativePush = gd_policy('myapp.config')['alternative_push'];
                    $isMyappPushSend = false;

                    $myapp = \App::load('Component\\Myapp\\Myapp');
                    $member = \App::load('\\Component\\Member\\Member');

                    foreach ($receiverData as $k => $v) {
                        $memInfo = $member->getMemberId($receiverData[$k]['memNo']);
                        $myappParams['memNo'] = $receiverData[$k]['memNo'];
                        $myappPushUser['users'][] = $memInfo ? $memInfo['memId'] : '비회원';
                        $appDeviceInfo = $myapp->getAppDeviceInfo($myappParams);

                        // 알림 허용 여부 체크
                        if ($appDeviceInfo[0]['pushEnabled'] === 1) {
                            switch ($myappAlternativePush['alternativeType']) {
                                case 'none': // 자동 SMS or 카카오알림톡 발송
                                    $isMyappPushSend = false;
                                    break;
                                case 'push': // 푸시 대체 발송
                                    $isMyappPushSend = true;
                                    $isPossibleSend = false;
                                    break;
                                case 'all': // 자동 SMS or 카카오 알림톡 && 푸시 대체 발송
                                    $isMyappPushSend = true;
                                    break;
                            }

                            // 마이앱 푸시 대체 발송
                            if ($isMyappPushSend === true) {
                                $myapp->sendAppPush($appDeviceInfo[0]['pushToken'], $item['subject'], $contents, $myappPushUser['users']);
                            }
                        } else {
                            $isMyappPushSend = false;
                        }
                    }
                }

                // 전송 정보가 없는 경우
                if (empty($receiverData) === true) {
                    $logger->error('Empty ReceiverData - ' . $item['smsAutoType']);
                    $isPossibleSend = false;
                }
                // 전송 가능한경우 SMS 발송
                if ($isPossibleSend) {
                    $logData['smsAutoCode'] = $this->smsAutoCodeType;
                    if ($this->smsAutoSendDate != null) {
                        $logData['reserve']['mode'] = 'reserve';
                        $logData['reserve']['date'] = $this->smsAutoSendDate;
                        $logData['reserve']['dbTable'] = 'member';
                        $logData['reserve']['smsAutoCode'] = $this->smsAutoCodeType;
                        if ($this->smsAutoCodeType == 'ACCOUNT') {
                            $logData['reserve']['orderNo'] = $this->replaceArguments['orderNo'];
                        }
                    }

                    // 공구우먼 확인하기 위해서 SNO가져오기
                    $globals = \App::getInstance('globals');
                    $shopSno = $globals->get('gLicense.godosno');
                    $aOrderSmsTypeCheck = array('ORDER', 'INVOICE_CODE', 'INCASH', 'ACCOUNT', 'REPAY', 'REPAYPART');
                    $aMemberSmsTypeCheck = array('JOIN', 'SLEEP_INFO_TODAY');
                    if( gd_is_plus_shop(PLUSSHOP_CODE_KAKAOALRIMLUNA) === true && ($this->kakaoAlrimIgnoreFlag == 'n' && $this->kakaoAlrimLunaAutoConfig['useFlag'] == 'y' && $this->kakaoAlrimLunaAutoConfig[strtolower($this->smsType) . 'UseFlag'] == 'y') ) {
                        //카카오 알림톡(루나) 발송
                        $oKakao = new \Component\Member\KakaoAlrimLuna;

                        $smsUtil = \App::load('Component\\Sms\\SmsUtil');
                        $aSender = $smsUtil->getSender();
                        $aSmsLog = [
                            'sendFl'            => 'kakaoLuna',
                            'smsType'           => $item['smsType'],
                            'smsDetailType'     => $logData['smsAutoCode'],
                            'sendType'          => 'send',
                            //'subject'           => $item['name'],
                            'contents'          => $item['contents'],
                            'receiverCnt'       => count($receiverData),
                            'replaceCodeType'   => '',
                            'sendDt'            => date('Y-m-d H:i:s'),
                            'smsSendKey'        => '',
                            'smsAutoSendOverFl' => 'none',
                            'code'              => $item['code'],
                            'useParam'              => $item['useParam'],
                        ];

                        // 위에서 예약발송으로 셋팅되었으면
                        if ($this->smsAutoSendDate != null) {
                            $tranDTime = $this->smsAutoSendDate;
                            $aSmsLog['sendType'] = 'res_send';
                            $aSmsLog['reserveDt'] = $tranDTime;
                        }
                        $aLogData = $logData;
                        // 카카오 알림톡의 경우 알림톡 발송 실패시 sms로 재발송처리 여부를 확인하기 위해 smsFl값을 기본n으로 하여 sms재발송을 처리할경우 y로 변경해 중복발송을 방지한다
                        foreach ($receiverData as $k => $v) {
                            $receiverData[$k]['smsFl'] = 'n';
                        }
                        $receiverForSaveSmsSendList = $receiverData;
                        //$lunaSendCode = $this->smsType.'||'.$this->smsAutoCodeType.'||'.$item['smsAutoType'];
                        $oKakao->sendKakaoAlrimLuna($aSmsLog, $aSender, $aLogData, $receiverForSaveSmsSendList, $this->replaceArguments, $contents );

                        $result[] = [
                            'success' => 1,
                            'fail'    => 0,
                        ];
                    }
                    // 공구우먼이면 임시로 kakao콘텐츠로 변경
                    else if (($this->kakaoAlrimIgnoreFlag == 'n' && $this->kakaoAlrimAutoConfig['useFlag'] == 'y' && $this->kakaoAlrimAutoConfig[strtolower($this->smsType) . 'UseFlag'] == 'y') ||
                        ($shopSno == '419067' && (($this->smsType == 'order' && in_array($this->smsAutoCodeType, $aOrderSmsTypeCheck)) || ($this->smsType == 'member' && in_array($this->smsAutoCodeType, $aMemberSmsTypeCheck)) || $this->smsType == 'board'))) {
                        //카카오 알림톡 발송
                        $oKakao = new \Component\Member\KakaoAlrim;

                        $smsUtil = \App::load('Component\\Sms\\SmsUtil');
                        $aSender = $smsUtil->getSender();
                        $aSmsLog = [
                            'sendFl'            => 'kakao',
                            'smsType'           => $item['smsType'],
                            'smsDetailType'     => $logData['smsAutoCode'],
                            'sendType'          => 'send',
                            'subject'           => $item['name'],
                            'contents'          => $item['contents'],
                            'receiverCnt'       => count($receiverData),
                            'replaceCodeType'   => '',
                            'sendDt'            => date('Y-m-d H:i:s'),
                            'smsSendKey'        => '',
                            'smsAutoSendOverFl' => 'none',
                            'code'              => $item['code'],
                        ];
                        // 위에서 예약발송으로 셋팅되었으면
                        if ($this->smsAutoSendDate != null) {
                            $tranDTime = $this->smsAutoSendDate;
                            $aSmsLog['sendType'] = 'res_send';
                            $aSmsLog['reserveDt'] = $tranDTime;
                        }
                        $aLogData = $logData;
                        // 카카오 알림톡의 경우 알림톡 발송 실패시 sms로 재발송처리 여부를 확인하기 위해 smsFl값을 기본n으로 하여 sms재발송을 처리할경우 y로 변경해 중복발송을 방지한다
                        foreach ($receiverData as $k => $v) {
                            $receiverData[$k]['smsFl'] = 'n';
                        }
                        $receiverForSaveSmsSendList = $receiverData;

                        // 특정 sms발송이면 루나소프트로 보내도록 처리
                        // $this->smsType 탭 order주문 member회원 board게시판
                        // $this->smsAutoCodeType order ORDER주문접수 INVOICE_CODE송장번호안내 INCASH입금확인 ACCOUNT입금요청 REPAY환불완료
                        // $this->smsAutoCodeType member JOIN회원가입
                        // $this->smsAutoCodeType board board아래는 다..
                        if ($shopSno == '419067' && (($this->smsType == 'order' && in_array($this->smsAutoCodeType, $aOrderSmsTypeCheck)) || ($this->smsType == 'member' && in_array($this->smsAutoCodeType, $aMemberSmsTypeCheck)) || $this->smsType == 'board')) {
                            $config = $this->kakaoAlrimAutoConfig[$this->smsType][$this->smsAutoCodeType];
                            if ($config['memberSend'] == 'y') {
                                //카카오 알림톡 발송
                                $oKakao->sendKakaoAlrimLunar($aSmsLog, $aLogData, $receiverForSaveSmsSendList, $this->replaceArguments, $this->smsType.'||'.$this->smsAutoCodeType);
                            }
                        } else {
                            if (empty($item['button']) === false) {
                                $aSmsLog['templateButton'] = $item['button'];
                            }
                            if (empty($buttonData) === false) {
                                $aSmsLog['buttonData'] = $buttonData;
                                unset($buttonData);
                            }
                            $oKakao->sendKakaoAlrim($aSmsLog, $aSender, $aLogData, $receiverForSaveSmsSendList, $this->replaceArguments, $contents);
                        }

                        $result[] = [
                            'success' => 1,
                            'fail'    => 0,
                        ];
                    } else {
                        $message = new \Component\Sms\SmsMessage($contents);
                        if ($message->exceedSmsLength()) {
                            if ($this->smsAutoConfig['smsAutoSendOver'] == 'lms') {
                                $message = new \Component\Sms\LmsMessage($contents);
                            } elseif ($this->smsAutoConfig['smsAutoSendOver'] == 'division') {
                                $message = new \Component\Sms\DivisionSmsMessage($contents);
                            }
                        }
                        $transport = \App::load(SmsSender::class);
                        if ($this->isAutoSend() || !$this->passwordCheckFl) {
                            $transport->validPassword(\App::load(\Component\Sms\SmsUtil::class)->getPassword());
                        }
                        $transport->setSmsPoint(Sms::getPoint());
                        $transport->setMessage($message);
                        $transport->setSmsType($item['smsType']);
                        $transport->setReceiver($receiverData);
                        $transport->setSendDate($this->smsAutoSendDate);
                        $transport->setTranType($this->smsAutoTranType);
                        $transport->setLogData($logData);
                        $transport->setContentsMask($this->contentsMask);
                        $result[] = $transport->send();
                    }

                    unset($receiverData);
                }
                unset($logData);
            }

        } catch (\Exception $e) {
            $exceptionMessage = __METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage();
            $logger->warning($exceptionMessage);
            \Logger::channel('exception')->info($exceptionMessage, $e->getTrace());

            return false;
        }
        $logger->info(__METHOD__ . ' Send Sms Result', $result);

        return $result;
    }

    /**
     * 시스템에 의해 발송되는 SMS 의 경우 true, 관리자에 의해 발송되는 경우 false
     *
     * @return bool
     */
    protected function isAutoSend()
    {
        return $this->smsAutoCodeType !== Code::COUPON_MANUAL;
    }

    /**
     * 자동발송 내용에 치환될 데이터를 치환하는 함수
     *
     * @param $contents
     *
     * @return mixed
     */
    public function replaceContents($contents)
    {
        $result = $contents;
        if (ArrayUtils::isEmpty($this->replaceArguments) === false) {
            $patterns = $values = [];
            foreach ($this->replaceArguments as $index => $argument) {
                $patterns[] = '{' . $index . '}';
                $values[] = $argument;
            }
            $result = str_replace($patterns, $values, $contents);
            unset($patterns, $values);
        }

        return $result;
    }

    /**
     * 자동발송 내용에 치환될 데이터를 치환하는 함수 - 카카오 알림톡
     *
     * @param $contents
     *
     * @return mixed
     */
    public function replaceContentsKakao($contents)
    {
        $result = $contents;
        $orgArguments = [
            'rc_mallNm'            => 'rc_mallNm',
            'shopUrl'              => 'shopUrl',
            'orderNo'              => 'orderNo',
            'orderName'            => 'orderName',
            'settlePrice'          => 'settlePrice',
            'bankAccount'          => 'bankAccount',
            'orderDate'            => 'orderDate',
            'deliveryName'         => 'deliveryName',
            'invoiceNo'            => 'invoiceNo',
            'goodsNm'              => 'goodsNm',
            'userExchangeStatus'   => 'userExchangeStatus',
            'memId'                => 'memId',
            'memNm'                => 'memNm',
            'sleepScheduleDt'      => 'sleepScheduleDt',
            'smsAgreementFl'       => 'smsAgreementFl',
            'smsAgreementDt'       => 'smsAgreementDt',
            'mailAgreementFl'      => 'mailAgreementFl',
            'mailAgreementDt'      => 'mailAgreementDt',
            'groupNm'              => 'groupNm',
            'mileage'              => 'mileage',
            'rc_mileage'           => 'rc_mileage',
            'deleteScheduleDt'     => 'deleteScheduleDt',
            'rc_deleteScheduleDt'  => 'rc_deleteScheduleDt',
            'deposit'              => 'deposit',
            'rc_deposit'           => 'rc_deposit',
            'rc_certificationCode' => 'rc_certificationCode',
            'wriNm'                => 'wriNm',
        ];
        if (ArrayUtils::isEmpty($this->replaceArguments) === false) {
            $patterns = $values = [];
            foreach ($this->replaceArguments as $index => $argument) {
                if ($this->smsType == 'board') { // 게시판은 다른곳이랑 다르게 상점명코드로 작성자명을 대처해서...
                    $patterns[] = '#{' . $index . '}';
                    if ($index == 'rc_mallNm') {
                        $values[] = $this->replaceArguments['wriNm'];
                    } elseif ($index == 'wriNm') {
                        $values[] = $this->replaceArguments['rc_mallNm'];
                    } else {
                        $values[] = $argument;
                    }
                } else {
                    $patterns[] = '#{' . $index . '}';
                    $values[] = $argument;
                }
                unset($orgArguments[$index]);
            }

            foreach ($orgArguments as $index => $argument) {
                $patterns[] = '#{' . $index . '}';
                $values[] = '';
            }

            $result = str_replace($patterns, $values, $contents);
            unset($patterns, $values);
        }

        return $result;
    }

    /**
     * SMS 발송 내용을 반한화는 함수
     *
     * @param array $wheres SMS 수신대상 분류
     *
     * @return array|object
     * @throws \Exception
     */
    public function getSmsContents(array $wheres)
    {
        $logger = \App::getInstance('logger');
        $binds = [];
        $this->db->strField = 'smsType, smsAutoType, contents, subject';
        $this->db->strJoin = DB_SMS_CONTENTS;
        $this->db->strWhere = 'smsType= ? AND smsAutoCode = ? AND smsAutoType IN (\'' . implode('\', \'', $wheres) . '\') AND contents != \'\' AND contents IS NOT NULL';
        $this->db->bind_param_push($binds, 's', $this->smsType);
        $this->db->bind_param_push($binds, 's', $this->smsAutoCodeType);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . array_shift($query) . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $binds);
        if (ArrayUtils::isEmpty($getData)) {
            $logger->info(sprintf('Not found sms contents query is [%s]', $strSQL), $binds);
            throw new \Exception(__('자동발송 내용을 찾을 수 없습니다.'));
        }
        $getData = StringUtils::htmlSpecialCharsStripSlashes($getData);

        return $getData;
    }

    /**
     * SMS 발송 내용을 반한화는 함수 - 카카오알림톡
     *
     * @param array $wheres SMS 수신대상 분류
     *
     * @return array|object
     * @throws \Exception
     */
    public function getSmsContentsKakao(array $wheres)
    {
        $logger = \App::getInstance('logger');

        $config = $this->kakaoAlrimAutoConfig[$this->smsType][$this->smsAutoCodeType];
        $aReturn = [];

        foreach ($wheres as $v) {
            $this->db->strField = '*';
            $this->db->strJoin = DB_KAKAO_MESSAGE_TEMPLATE;
            $this->db->strWhere = 'templateCode = ?';
            $this->db->bind_param_push($binds, 's', $config[$v . 'TemplateCode']);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . array_shift($query) . ' ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $binds);
            if (ArrayUtils::isEmpty($getData)) {
                $logger->info(sprintf('Not found sms contents query is [%s]', $strSQL), $binds);
                throw new \Exception(__('자동발송 내용을 찾을 수 없습니다.'));
            }
            $getData = StringUtils::htmlSpecialCharsStripSlashes($getData[0]);
            $aReturn[] = [
                'smsType'     => $this->smsType,
                'smsAutoType' => $v,
                'name'        => $getData['templateName'],
                'contents'    => $getData['templateContent'],
                'code'        => $getData['templateCode'],
                'button'      => $getData['templateButton'],
            ];
            unset($binds);
        }

        return $aReturn;
    }

    public function getSmsContentsKakaoLuna(array $wheres)
    {
        $logger = \App::getInstance('logger');

        $config = $this->kakaoAlrimLunaAutoConfig[$this->smsType][$this->smsAutoCodeType];
        $aReturn = [];

        foreach ($wheres as $v) {
            $this->db->strField = '*';
            $this->db->strJoin = DB_KAKAO_LUNA_MESSAGE_TEMPLATE;
            $this->db->strWhere = 'templateCode = ?';
            $this->db->bind_param_push($binds, 's', $config[$v . 'TemplateCode']);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . array_shift($query) . ' ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $binds);
            if (ArrayUtils::isEmpty($getData)) {
                $logger->info(sprintf('Not found sms contents query is [%s]', $strSQL), $binds);
                throw new \Exception(__('자동발송 내용을 찾을 수 없습니다.'));
            }
            $getData = StringUtils::htmlSpecialCharsStripSlashes($getData[0]);
            $aReturn[] = [
                'smsType'     => $this->smsType,
                'smsAutoType' => $v,
                //'name'        => $getData['templateName'],
                'contents'    => $getData['templateContent'],
                'code'        => $getData['templateCode'],
                'useParam'        => $getData['useParam'],
            ];
            unset($binds);
        }

        return $aReturn;
    }

    /**
     * @param mixed $smsType
     */
    public function setSmsType($smsType)
    {
        $this->smsType = $smsType;
        \App::getInstance('logger')->info(__METHOD__, [$this->smsType]);
    }

    /**
     * @param mixed $smsAutoCodeType
     */
    public function setSmsAutoCodeType($smsAutoCodeType)
    {
        $this->smsAutoCodeType = $smsAutoCodeType;
        \App::getInstance('logger')->info(__METHOD__, [$this->smsAutoCodeType]);
    }

    /**
     * @param mixed $receiver
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
        \App::getInstance('logger')->info(__METHOD__, [$this->receiver]);

    }

    /**
     * 자동 SMS 치환코드 정보 설정
     *
     * @param mixed $replaceArguments
     */
    public function setReplaceArguments($replaceArguments)
    {
        if ($this->smsAutoCodeType == Code::SOLD_OUT && key_exists('goodsNm', $replaceArguments) && is_array($replaceArguments['goodsNm'])) {
            $replaceArguments['goodsNm'] = $replaceArguments['goodsNm'][0];
        }
        $this->addContentsMaskByMaskArguments($replaceArguments);
        $this->replaceArguments = $replaceArguments;
        \App::getInstance('logger')->debug(__METHOD__, [$this->replaceArguments]);
    }

    /**
     * @param mixed $smsAutoType
     */
    public function setSmsAutoType($smsAutoType)
    {
        $this->smsAutoType = $smsAutoType;
        \App::getInstance('logger')->info(__METHOD__, [$this->smsAutoType]);
    }

    /**
     * 자동 SMS 예약발송 시 예약발송 시간 설정
     *
     * @param string $smsAutoSendDate
     */
    public function setSmsAutoSendDate(string $smsAutoSendDate)
    {
        $this->smsAutoSendDate = $smsAutoSendDate;
        \App::getInstance('logger')->info(__METHOD__, [$this->smsAutoSendDate]);
    }

    /**
     * 자동 SMS 예약발송 시 res_send 로 설정
     *
     * @param string $smsAutoTranType
     */
    public function setSmsAutoTranType($smsAutoTranType)
    {
        $this->smsAutoTranType = $smsAutoTranType;
        \App::getInstance('logger')->info(__METHOD__, [$this->smsAutoTranType]);
    }

    /**
     * attach
     *
     * @param SplObserver $observer
     */
    public function attach(SplObserver $observer)
    {
        $this->smsAutoObservers[] = $observer;
    }

    /**
     * detach
     *
     * @param SplObserver $observer
     */
    public function detach(SplObserver $observer)
    {
        foreach ($this->smsAutoObservers as $index => $smsAutoObserver) {
            if ($observer === $smsAutoObserver) {
                unset($this->smsAutoObservers[$index]);
            }
        }
    }

    /**
     * notify
     *
     */
    public function notify()
    {
        $logger = \App::getInstance('logger');
        $logger->info(sprintf('Start auto sms notify. observers count[%s]', count($this->smsAutoObservers)));
        foreach ($this->smsAutoObservers as $observer) {
            // 에러가 있는 경우 중단 되는 케이스가 있어 아래와 같이 처리
            try {
                $observer->update($this);
            } catch (\Throwable $e) {
                $logger->error($e->getMessage(), $e->getTrace());
            }
        }
    }

    public function reserveNotify($reserveSmsTime)
    {
        $logger = \App::getInstance('logger');
        $logger->info(sprintf('Start auto sms reserve notify. reserve sms time[%s]. observers count[%s]', $reserveSmsTime, count($this->smsAutoObservers)));
        $this->setSmsAutoSendDate($reserveSmsTime);
        foreach ($this->smsAutoObservers as $observer) {
            // 에러가 있는 경우 중단 되는 케이스가 있어 아래와 같이 처리
            try {
                $observer->update($this);
            } catch (\Throwable $e) {
                $logger->error($e->getMessage(), $e->getTrace());
            }
        }
    }

    /**
     * wrapping 함수
     *
     * @return int
     */
    public function getSmsLogSno()
    {
        return \App::load('Component\\Sms\\SmsSender')->getSmsLogSno();
    }

    /**
     * sms 발송을 지연발송과 즉시발송 동시에 사용하는 경우
     * 해당 값을 참조하여 attach 여부를 결정하도록 한다.
     *
     * @param bool $useObserver
     */
    public function setUseObserver(bool $useObserver)
    {
        $this->useObserver = $useObserver;
    }

    /**
     * @return bool false 인 경우 observer 를 attach 하지 않아야 한다.
     */
    public function useObserver(): bool
    {
        return $this->useObserver;
    }

    /**
     * 본사/공급사 SMS 수신대상 조회 함수
     *
     * @param $smsAutoType
     *
     * @return array|string
     */
    protected function getManagerReceiver($smsAutoType)
    {
        $strWhere = '';
        if ($smsAutoType === 'admin') {
            $strWhere = 'scmNo = 1 AND ';
        }
        if ($smsAutoType === 'provider') {
            if (is_array($this->receiversScmNo)) {
                $strWhere = 'scmNo IN (' . implode(', ', $this->receiversScmNo) . ') AND scmNo != ' . DEFAULT_CODE_SCMNO . ' AND ';
            } else {
                $strWhere = 'scmNo  = ' . $this->receiversScmNo . ' AND scmNo != ' . DEFAULT_CODE_SCMNO . ' AND ';
            }
        }
        $this->db->strField = 'sno as memNo, managerNm as memNm, \'y\' as smsFl, cellPhone';
        $this->db->strWhere = $strWhere . 'cellPhone !=\'\' AND cellPhone IS NOT NULL';
        $this->db->strWhere .= ' AND isDelete=\'n\'';
        $this->db->strWhere .= ' AND smsAutoReceive LIKE \'%smsAuto' . ucwords($this->smsType) . '%\'';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGER . implode(' ', $query);
        $receiverData = StringUtils::htmlSpecialCharsStripSlashes($this->db->query_fetch($strSQL));

        return $receiverData;
    }

    /**
     * 수신자 정보가 다중배열 형태인 경우 수신자의 정보를 재가공하여 반환하는 함수
     *
     * @return array ['receivers'=>수신자정보, 'scmNo'=>공급사번호]
     */
    protected function getMultipleReceiver()
    {
        $logger = \App::getInstance('logger');
        $result = [];
        $key = 0;
        $tmpScmNo = [];
        $logger->info(sprintf('Multiple receiver count[%d]', count($this->receiver)));
        foreach ($this->receiver as $index => $item) {
            $logger->debug($index, $item);
            $result[$key]['memNo'] = StringUtils::strIsSet($this->receiver['memNo']);
            $result[$key]['memNm'] = StringUtils::strIsSet($this->receiver['memNm']);
            $result[$key]['smsFl'] = StringUtils::strIsSet($this->receiver['smsFl'], 'n');
            $result[$key]['cellPhone'] = StringUtils::strIsSet($this->receiver['cellPhone']);
            // 수신동의 회원만 발송
            if ($this->checkModes['agreeCheck'] === 'y' && $this->receiver['smsFl'] === 'n') {
                $logger->info(sprintf('Check receive agreement. agreeCheck[%s], smsFl[%s]', $this->checkModes['agreeCheck'], $this->receiver['smsFl']));
                unset($result[$key]);
            }
            $tmpScmNo = array_merge($tmpScmNo, $item['scmNo']);
            $key++;
        }

        return [
            'receivers' => $result,
            'scmNo'     => array_unique($tmpScmNo),
        ];
    }

    /**
     * 야간발송 여부, 최근 주문 n일 여부 체크, 수신정보 분석, 수신동의 회원 체크, 공급사 정보 확인 함수
     *
     * @throws \Exception
     */
    protected function checkDefaultSmsAutoCodeAndReceiver()
    {
        $logger = \App::getInstance('logger');
        $this->smsAutoCode = \App::load('\\Component\\Sms\\SmsAutoCode');
        $defaultAutoCode = $this->smsAutoCode->getCodes();
        foreach ($defaultAutoCode[$this->smsType] as $item) {
            if ($item['code'] == $this->smsAutoCodeType) {
                $this->checkModes['orderCheck'] = $item['orderCheck'];
                $this->checkModes['nightCheck'] = $item['nightCheck'];
                $this->checkModes['agreeCheck'] = $item['agreeCheck'];
            }
        }
        $config = $this->smsAutoConfig[$this->smsType][$this->smsAutoCodeType];
        if ($this->checkModes['orderCheck'] === 'y') {
            $smsOrderDate = StringUtils::strIsSet($config['smsOrderDate'], 15);
            if (isset($this->replaceArguments['orderNo'])) {
                $tmpDate = '20' . substr($this->replaceArguments['orderNo'], 0, 12);    // 주문번호에서 날짜 추출
                $compareDate = strtotime('+' . $smsOrderDate . ' day', strtotime($tmpDate));
                if ($compareDate < time()) {
                    $logger->warning(__METHOD__ . ', smsOrderDate=[' . $smsOrderDate . '], dateByOrderNo=[' . $tmpDate . '], compareDate=[' . $compareDate . ']');
                    throw new \Exception(__('최근') . ' ' . $smsOrderDate . __('일 주문건만 전송이 가능합니다.'));
                }
            }
        }
        if ($this->checkModes['nightCheck'] === 'y') {
            $smsNightSend = StringUtils::strIsSet($config['smsNightSend'], 'n');
            if ($smsNightSend === 'n') {
                $thisHour = date('G', time());
                if (in_array($thisHour, Sms::SMS_FORBID_TIME)) {
                    throw new \Exception(__('야간 발송 금지인 SMS 입니다.'));
                }
            }
        }

        if (is_array($this->receiver)) {
            if (array_key_exists('cellPhone', $this->receiver)) {
                $this->receiverType = 'array';  // 일반 배열
            } else {
                $this->receiverType = 'multi';  // 이차 배열
            }
        } else {
            $this->receiverType = 'phone'; // 전화번호
        }
        $logger->info(sprintf('Check receiver type[%s]', $this->receiverType));
        // 수신정보에서 공급사 정보만 추출
        if ($this->receiverType === 'array') {
            $isCheck = true;
            // 수신동의 회원발송
            if ($this->checkModes['agreeCheck'] === 'y' && StringUtils::strIsSet($this->receiver['smsFl'], 'n') === 'n') {
                $isCheck = false;
                $logger->info(sprintf('Receiver type array. check agreeCheck[%s], smsFl[%s]', $this->checkModes['agreeCheck'], $this->receiver['smsFl']));
            }
            // 공급사 정보 확인
            if (empty($this->receiver['scmNo'])) {
                $logger->info('Receiver type array. check empty scmNo.');
                $isCheck = false;
            }
            // 체크 후 정상인 경우
            if ($isCheck) {
                $this->receiversScmNo = $this->receiver['scmNo'];
                $logger->info('Receiver type array.', [$this->receiversScmNo]);
            }
        }
        if ($this->receiverType === 'multi') {
            $tmpScmNo = [];
            foreach ($this->receiver as $index => $item) {
                $isCheck = true;
                // 수신동의 회원발송
                if ($this->checkModes['agreeCheck'] === 'y' && StringUtils::strIsSet($item['smsFl'], 'n') === 'n') {
                    $isCheck = false;
                }
                // 공급사 정보 확인
                if (empty($item['scmNo'])) {
                    $isCheck = false;
                }
                // 체크 후 정상인 경우
                if ($isCheck) {
                    $tmpScmNo = array_merge($tmpScmNo, $item['scmNo']);
                }
            }
            // 공급사 정보
            $this->receiversScmNo = array_unique($tmpScmNo);
        }
    }

    /**
     * 야간발송 여부, 최근 주문 n일 여부 체크, 수신정보 분석, 수신동의 회원 체크, 공급사 정보 확인 함수 - 카카오알림톡용
     *
     * @throws \Exception
     */
    protected function checkDefaultSmsAutoCodeAndReceiverKakao($kind = '')
    {
        $logger = \App::getInstance('logger');
        $this->smsAutoCode = \App::load('\\Component\\Sms\\SmsAutoCode');
        $defaultAutoCode = $this->smsAutoCode->getCodes();
        foreach ($defaultAutoCode[$this->smsType] as $item) {
            if ($item['code'] == $this->smsAutoCodeType) {
                $this->checkModes['orderCheck'] = $item['orderCheck'];
                $this->checkModes['nightCheck'] = $item['nightCheck'];
                $this->checkModes['agreeCheck'] = $item['agreeCheck'];
            }
        }
        if($kind == 'luna'){
            $config = $this->kakaoAlrimLunaAutoConfig[$this->smsType][$this->smsAutoCodeType];
        }else{
            $config = $this->kakaoAlrimAutoConfig[$this->smsType][$this->smsAutoCodeType];
        }

        if ($this->checkModes['orderCheck'] === 'y') {
            $smsOrderDate = StringUtils::strIsSet($config['smsOrderDate'], 15);
            if (isset($this->replaceArguments['orderNo'])) {
                $tmpDate = '20' . substr($this->replaceArguments['orderNo'], 0, 12);    // 주문번호에서 날짜 추출
                $compareDate = strtotime('+' . $smsOrderDate . ' day', strtotime($tmpDate));
                if ($compareDate < time()) {
                    $logger->warning(__METHOD__ . ', smsOrderDate=[' . $smsOrderDate . '], dateByOrderNo=[' . $tmpDate . '], compareDate=[' . $compareDate . ']');
                    throw new \Exception(__('최근') . ' ' . $smsOrderDate . __('일 주문건만 전송이 가능합니다.'));
                }
            }
        }
        if ($this->checkModes['nightCheck'] === 'y') {
            $smsNightSend = StringUtils::strIsSet($config['smsNightSend'], 'n');
            if ($smsNightSend === 'n') {
                $thisHour = date('G', time());
                if (in_array($thisHour, Sms::SMS_FORBID_TIME)) {
                    throw new \Exception(__('야간 발송 금지인 SMS 입니다.'));
                }
            }
        }

        if (is_array($this->receiver)) {
            if (array_key_exists('cellPhone', $this->receiver)) {
                $this->receiverType = 'array';  // 일반 배열
            } else {
                $this->receiverType = 'multi';  // 이차 배열
            }
        } else {
            $this->receiverType = 'phone'; // 전화번호
        }
        $logger->info(sprintf('Check receiver type[%s]', $this->receiverType));

        // 수신정보에서 공급사 정보만 추출
        if ($this->receiverType === 'array') {
            $isCheck = true;
            // 수신동의 회원발송
            if ($this->checkModes['agreeCheck'] === 'y' && StringUtils::strIsSet($this->receiver['smsFl'], 'n') === 'n') {
                $isCheck = false;
                $logger->info(sprintf('Receiver type array. check agreeCheck[%s], smsFl[%s]', $this->checkModes['agreeCheck'], $this->receiver['smsFl']));
            }
            // 공급사 정보 확인
            if (empty($this->receiver['scmNo'])) {
                $logger->info('Receiver type array. check empty scmNo.');
                $isCheck = false;
            }
            // 체크 후 정상인 경우
            if ($isCheck) {
                $this->receiversScmNo = $this->receiver['scmNo'];
                $logger->info('Receiver type array.', [$this->receiversScmNo]);
            }
        }
        if ($this->receiverType === 'multi') {
            $tmpScmNo = [];
            foreach ($this->receiver as $index => $item) {
                $isCheck = true;
                // 수신동의 회원발송
                if ($this->checkModes['agreeCheck'] === 'y' && StringUtils::strIsSet($item['smsFl'], 'n') === 'n') {
                    $isCheck = false;
                }
                // 공급사 정보 확인
                if (empty($item['scmNo'])) {
                    $isCheck = false;
                }
                // 체크 후 정상인 경우
                if ($isCheck) {
                    $tmpScmNo = array_merge($tmpScmNo, $item['scmNo']);
                }
            }
            // 공급사 정보
            $this->receiversScmNo = array_unique($tmpScmNo);
        }
    }

    /**
     * SMS 발송대상 체크 및 SMS 발송 내용
     *
     * @throws \Exception
     */
    protected function checkSmsAutoTypeAndSmsContentWheres()
    {
        $logger = \App::getInstance('logger');
        $this->smsContentWheres = [];
        $emptyReceiver = true;
        $config = $this->smsAutoConfig[$this->smsType][$this->smsAutoCodeType];
        if ($this->smsAutoType === null) {
            StringUtils::strIsSet($config['memberSend'], 'n');
            if ($config['memberSend'] == 'y') {
                $this->smsContentWheres[] = 'member';
                $emptyReceiver = false;
            }
            StringUtils::strIsSet($config['adminSend'], 'n');
            if ($config['adminSend'] == 'y') {
                $this->smsContentWheres[] = 'admin';
                $emptyReceiver = false;
            }
            StringUtils::strIsSet($config['providerSend'], 'n');
            if ($config['providerSend'] == 'y') {
                $this->smsContentWheres[] = 'provider';
                $emptyReceiver = false;
            }
        } else {
            $defaultSmsAutoType = [
                'member',
                'admin',
                'provider',
            ];
            if (in_array($this->smsAutoType, $defaultSmsAutoType) === false) {
                $logger->info(__METHOD__ . ', smsAutoType=[' . $this->smsAutoType . '], defaultSmsAutoType=>', $defaultSmsAutoType);
                throw new \Exception(__('잘못된 SMS 발송대상입니다.'));
            }
            StringUtils::strIsSet($config[$this->smsAutoType . 'Send'], 'n');
            if ($config[$this->smsAutoType . 'Send'] == 'y') {
                $this->smsContentWheres[] = $this->smsAutoType;
                $emptyReceiver = false;
            }
        }
        if ($emptyReceiver) {
            throw new \Exception(__('지정한 SMS 발송대상이 없습니다.'));
        }
    }

    /**
     * SMS 발송대상 체크 및 SMS 발송 내용 - 카카오알림톡용
     *
     * @throws \Exception
     */
    protected function checkSmsAutoTypeAndSmsContentWheresKakao($kind = '')
    {
        $logger = \App::getInstance('logger');
        $this->smsContentWheres = [];
        $emptyReceiver = true;
        if($kind == 'luna'){
            $config = $this->kakaoAlrimLunaAutoConfig[$this->smsType][$this->smsAutoCodeType];
        }else{
            $config = $this->kakaoAlrimAutoConfig[$this->smsType][$this->smsAutoCodeType];
        }

        if ($this->smsAutoType === null) {
            StringUtils::strIsSet($config['memberSend'], 'n');
            if ($config['memberSend'] == 'y') {
                $this->smsContentWheres[] = 'member';
                $emptyReceiver = false;
            }
            StringUtils::strIsSet($config['adminSend'], 'n');
            if ($config['adminSend'] == 'y') {
                $this->smsContentWheres[] = 'admin';
                $emptyReceiver = false;
            }
            StringUtils::strIsSet($config['providerSend'], 'n');
            if ($config['providerSend'] == 'y') {
                $this->smsContentWheres[] = 'provider';
                $emptyReceiver = false;
            }
        } else {
            $defaultSmsAutoType = [
                'member',
                'admin',
                'provider',
            ];
            if (in_array($this->smsAutoType, $defaultSmsAutoType) === false) {
                $logger->info(__METHOD__ . ', smsAutoType=[' . $this->smsAutoType . '], defaultSmsAutoType=>', $defaultSmsAutoType);
                throw new \Exception(__('잘못된 SMS 발송대상입니다.'));
            }
            StringUtils::strIsSet($config[$this->smsAutoType . 'Send'], 'n');
            if ($config[$this->smsAutoType . 'Send'] == 'y') {
                $this->smsContentWheres[] = $this->smsAutoType;
                $emptyReceiver = false;
            }
        }

        if ($emptyReceiver) {
            throw new \Exception(__('지정한 SMS 발송대상이 없습니다.'));
        }
    }

    /**
     * 발송종류, 발송코드, 수신정보 데이터 체크 및 정책 정보를 체크하는 함수
     *
     * @throws \Exception
     */
    protected function checkSmsTypeAndCodeWithReceiver()
    {
        if (empty($this->smsType)) {
            throw new \Exception('SMS ' . __('발송종류 정보가 없습니다.'));
        }
        if (empty($this->smsAutoCodeType)) {
            throw new \Exception('SMS ' . __('발송코드 정보가 없습니다.'));
        }
        if (empty($this->receiver)) {
            throw new \Exception(__('수신정보가 없습니다.'));
        }
        $this->smsAutoConfig = $this->getSmsAutoPolicy();

        if (empty($this->smsAutoConfig[$this->smsType][$this->smsAutoCodeType])) {
            throw new \Exception(__('발송종류') . '[' . $this->smsType . '] , ' . __('발송코드') . '[' . $this->smsAutoCodeType . ']' . __('에 맞는 자동발송 정보가 없습니다.'));
        }
    }

    /**
     * 발송종류, 발송코드, 수신정보 데이터 체크 및 정책 정보를 체크하는 함수 - 카카오알림톡용
     *
     * @throws \Exception
     */
    protected function checkSmsTypeAndCodeWithReceiverKakao($kind = '')
    {
        if (empty($this->smsType)) {
            throw new \Exception('SMS ' . __('발송종류 정보가 없습니다.'));
        }
        if (empty($this->smsAutoCodeType)) {
            throw new \Exception('SMS ' . __('발송코드 정보가 없습니다.'));
        }
        if (empty($this->receiver)) {
            throw new \Exception(__('수신정보가 없습니다.'));
        }
        if($kind == 'luna'){
            if (empty($this->kakaoAlrimLunaAutoConfig[$this->smsType][$this->smsAutoCodeType])) {
                throw new \Exception(__('발송종류') . '[' . $this->smsType . '] , ' . __('발송코드') . '[' . $this->smsAutoCodeType . ']' . __('에 맞는 자동발송 정보가 없습니다.'));
            }
        }else{
            if (empty($this->kakaoAlrimAutoConfig[$this->smsType][$this->smsAutoCodeType])) {
                throw new \Exception(__('발송종류') . '[' . $this->smsType . '] , ' . __('발송코드') . '[' . $this->smsAutoCodeType . ']' . __('에 맞는 자동발송 정보가 없습니다.'));
            }
        }

    }

    /**
     * 치환코드 중 마스킹 처리될 내용 추가
     *
     * @param mixed $replaceArguments
     */
    protected function addContentsMaskByMaskArguments($replaceArguments)
    {
        foreach ($this->maskArguments as $maskArgument) {
            if (key_exists($maskArgument, $replaceArguments)) {
                $this->addContentsMask($replaceArguments[$maskArgument]);
            }
        }
    }

    /**
     * 발송 내용 출력 시 마스킹 될 데이터 추가
     *
     * @param $value
     */
    protected function addContentsMask($value)
    {
        $this->contentsMask[] = $value;
    }

    /**
     * `es_config` 에 저장된 자동SMS 정책을 반환하는 함수
     *
     * @return array
     */
    protected function getSmsAutoPolicy()
    {
        return ComponentUtils::getPolicy('sms.smsAuto');
    }

    /**
     * `es_config` 에 저장된 자동SMS 정책을 반환하는 함수
     *
     * @return array
     */
    protected function getKakaoAlrimAutoPolicy()
    {
        return ComponentUtils::getPolicy('kakaoAlrim.kakaoAuto');
    }
    /**
     * `es_config` 에 저장된 자동SMS 정책을 반환하는 함수
     *
     * @return array
     */
    protected function getKakaoAlrimLunaAutoPolicy()
    {
        return ComponentUtils::getPolicy('kakaoAlrimLuna.kakaoAuto');
    }

    /**
     * 자동 SMS 발송 예약 시간 설정
     *
     * @param string $autoSmsType
     *
     * @return string 예약시간
     */
    public function getSmsAutoReserveTime($autoSmsType)
    {
        $smsAuto = $this->getSmsAutoPolicy();
        switch ($autoSmsType) {
            case Code::AGREEMENT2YPERIOD:
            case Code::GROUP_CHANGE:
            case Code::MILEAGE_EXPIRE:
            case Code::SLEEP_INFO:
            case Code::SLEEP_INFO_TODAY:
                $smsAutoContents = SmsAutoCode::MEMBER;
                break;
            case Code::COUPON_BIRTH:
            case Code::COUPON_WARNING:
            case Code::BIRTH:
                $smsAutoContents = SmsAutoCode::PROMOTION;
                break;
            default:
                $smsAutoContents = null;
        }

        if (empty($smsAutoContents) === false) {
            $reserveHour = gd_isset($smsAuto[$smsAutoContents][$autoSmsType]['reserveHour'], Sms::SMS_AUTO_RESERVATION_DEFAULT_TIME[$autoSmsType]);
            $convertHour = date('H:i:s', strtotime($reserveHour . ':00:00'));
        } else {
            return false;
        }

        return date('Y-m-d ' . $convertHour, strtotime('now'));
    }

    /**
     * 자동 kakao 발송 예약 시간 설정
     *
     * @param string $autoKakaoAlrimType
     *
     * @return string 예약시간
     */
    public function getKakaoAlrimAutoReserveTime($autoKakaoAlrimType,$kind = '')
    {
        if($kind == 'luna'){
            $kakaoAuto = $this->getKakaoAlrimLunaAutoPolicy();
        }else{
            $kakaoAuto = $this->getKakaoAlrimAutoPolicy();
        }

        switch ($autoKakaoAlrimType) {
            case Code::AGREEMENT2YPERIOD:
            case Code::GROUP_CHANGE:
            case Code::MILEAGE_EXPIRE:
            case Code::SLEEP_INFO:
            case Code::SLEEP_INFO_TODAY:
                $kakaoAutoContents = SmsAutoCode::MEMBER;
                break;
            default:
                $kakaoAutoContents = null;
        }

        if (empty($kakaoAutoContents) === false) {
            $reserveHour = gd_isset($kakaoAuto[$kakaoAutoContents][$autoKakaoAlrimType]['reserveHour'], Sms::KAKAO_AUTO_RESERVATION_DEFAULT_TIME[$autoKakaoAlrimType]);
            $convertHour = date('H:i:s', strtotime($reserveHour . ':00:00'));
        } else {
            return false;
        }

        return date('Y-m-d ' . $convertHour, strtotime('now'));
    }

    public function setPasswordCheckFl($passwordCheckFl)
    {
        $this->passwordCheckFl = $passwordCheckFl;
    }
}
