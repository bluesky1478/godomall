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

namespace Bundle\Component\Mail;

use Component\Design\ReplaceCode;
use Component\Member\MemberDAO;
use Component\Validator\Validator;
use Core\View\Template_\Template_ as Engine;
use Exception;
use Framework\Application\Bootstrap\Log;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use SplObserver;

/**
 * Class MailMimeAuto
 * @package Bundle\Component\Mail
 * @author  yjwee
 */
class MailMimeAuto implements \SplSubject
{
    /**
     * 주문/배송관련
     */
    const ORDER_DETAIL = 'ORDER';
    const ORDER_INCASH = 'INCASH';
    const GOODS_DELIVERY = 'DELIVERY';
    /**
     * 가입/탈퇴/문의관련
     */
    const FIND_PASSWORD = 'FINDPASSWORD';
    const BOARD_QNA = 'QNA';
    const MEMBER_JOIN = 'JOIN';
    const MEMBER_APPROVAL = 'APPROVAL';
    const MEMBER_HACKOUT = 'HACKOUT';
    /**
     * 회원정보관련
     */
    const WAKE_MEMBER = 'WAKE';
    const CHANGE_PASSWORD = 'CHANGEPASSWORD';
    const SLEEP_NOTICE = 'SLEEPNOTICE';
    const AGREEMENT = 'AGREEMENT';
    const AGREEMENT2YPERIOD = 'AGREEMENT2YPERIOD';
    const REJECTEMAIL = 'REJECTEMAIL';
    const GROUPCHANGE = 'GROUPCHANGE';
    /**
     * 마일리지/예치금관련
     */
    const REMOVE_DEPOSIT = 'REMOVEDEPOSIT';
    const ADD_DEPOSIT = 'ADDDEPOSIT';
    const REMOVE_MILEAGE = 'REMOVEMILEAGE';
    const ADD_MILEAGE = 'ADDMILEAGE';
    const DELETE_MILEAGE = 'DELETEMILEAGE';
    /**
     * 관리자보안관련
     */
    const ADMIN_SECURITY = 'ADMINSECURITY';


    /** @var \SplObserver[]|MailAutoObserver $observers */
    protected $observers = [];
    /** @var bool $useObserver */
    protected $useObserver = false;
    /** @var integer $mallSno 상점번호 */
    protected $mallSno;
    /** @var array $contentsMask 내용 출력 시 마스킹 정보 */
    protected $contentsMask = [];
    protected $maskArguments = ['certificationCode',];
    /** @var \Component\Member\MemberDAO $memberDao */
    protected $memberDao;
    /** @var  \Bundle\Component\Design\ReplaceCode $replaceCode */
    private $replaceCode;
    /** @var  MailMime $mail */
    private $mail;
    /** @var  MailLog $mailLog */
    private $mailLog;
    /** @var \Framework\Log\Logger */
    private $logger;
    /** @var  array 쇼핑몰기본정책 */
    private $baseInfo;
    /** @var  integer 메일발송내역번호 */
    private $mailLogSno;
    /** @var  array 메일 수신자 정보 */
    private $replaceInfo;
    /** @var  array $config 자동메일 발송 정책 */
    private $config;
    /** @var  bool $isAutoSend 자동메일 발송 여부 */
    private $isAutoSend = false;
    /** @var  string 메일 발송 타입 */
    private $type;
    /** @var  array 메일 발송에 사용될 치환 데이터 */
    private $replaceData;
    /** @var  string 메일 발송 제목 템플릿 파일 */
    private $subjectTemplateFile;
    /** @var  string 메일 발송 본문 템플릿 파일 */
    private $bodyTemplateFile;
    private $subject;
    private $body;
    private $subjectTemplatePrefix = 'subject_';
    private $bodyTemplatePrefix = 'body_';
    private $templateExt = '.php';
    private $isSendResult = false;

    /**
     * MailMimeAuto constructor.
     *
     * @param array|object $config
     */
    public function __construct($config = [])
    {
        if (\is_object($config)) {
            $this->mail = $config;
        } else {
            if (isset($config['mailMime']) && \is_object($config['mailMime'])) {
                $this->mail = $config['mailMime'];
            } else {
                $this->mail = new \Component\Mail\MailMime();
            }

            if (isset($config['mailLog']) && \is_object($config['mailLog'])) {
                $this->mailLog = $config['mailLog'];
            } else {
                $this->mailLog = new \Component\Mail\MailLog();
            }

            if (isset($config['replaceCode']) && \is_object($config['replaceCode'])) {
                $this->replaceCode = $config['replaceCode'];
            } else {
                $this->replaceCode = \App::load(ReplaceCode::class);
            }

            if (isset($config['memberDao']) && \is_object($config['memberDao'])) {
                $this->memberDao = $config['memberDao'];
            } else {
                $this->memberDao = \App::load(MemberDAO::class);
            }
        }
        $this->logger = \App::getInstance('logger')->channel(Log::CHANNEL_DEFAULT_MAIL);
        $this->baseInfo = ComponentUtils::getPolicy('basic.info');
    }

    /**
     * 자동메일 발송
     *
     * @return bool
     */
    public function autoSend()
    {
        $this->logger->info('Start auto send.');
        if ($this->isAutoSend) {
            try {
                $receiverCondition = '';
                $this->setTemplateWithReplaceCode();
                $this->insertSendMailLog($receiverCondition);
                $this->mailLog->insertMailSendList($this->replaceInfo);
                $this->isSendResult = $this->mail->setFrom($this->getSender(), $this->baseInfo['mallNm'])
                    ->setSubject($this->subject)
                    ->setHtmlBody($this->body)->send();
                $this->updateSendMailLog();
            } catch (Exception $e) {
                if ($e->getCode() == 200) {
                    $this->logger->warning('Exception auto send. code=[' . $e->getCode() . '], message=[' . $e->getMessage() . ']' . ', ' . $e->getFile() . ', ' . $e->getLine());
                } else {
                    $this->logger->error('Exception auto send. code=[' . $e->getCode() . '], message=[' . $e->getMessage() . ']', $e->getTrace());
                }

                return false;
            }
        } else {
            $this->logger->info('Automatic mail settings are not.', $this->config);
        }
        $this->logger->info(sprintf('End auto send. send result is [%s]', $this->isSendResult));

        return $this->isSendResult;
    }

    /**
     * 랩핑 함수
     *
     */
    protected function setTemplateWithReplaceCode()
    {
        $this->_setTemplateWithReplaceCode();
    }

    /**
     * 메일 템플릿 로드 및 치환코드 적용
     */
    private function _setTemplateWithReplaceCode()
    {
        $this->logger->info('Assign code to template.');
        $tpl = new Engine();
        $userFilePath = \App::getInstance('user.path');
        $tpl->template_dir = $userFilePath->data('mail');
        $tpl->compile_dir = $userFilePath->data('mail', 'compiles');
        if ($this->mallSno > DEFAULT_MALL_NUMBER) {
            $tpl->template_dir = $userFilePath->data('mail', $this->mallSno);
            $tpl->compile_dir = $userFilePath->data('mail', 'compiles', $this->mallSno);
        }
        $this->logger->info(sprintf('Template path %s, Compile path %s', $tpl->template_dir, $tpl->compile_dir));
        $this->logger->info(sprintf('Subject file %s, Body file %s', $this->subjectTemplateFile, $this->bodyTemplateFile));
        $tpl->define('subject', $this->subjectTemplateFile);
        $tpl->assign($this->replaceData);
        $this->subject = $tpl->fetch('subject');

        $tpl->define('body', $this->bodyTemplateFile);
        $tpl->assign($this->replaceData);
        $this->body = $tpl->fetch('body');

        $this->body = $this->replaceCode->replace($this->body);
        $this->logger->info('Assign complete.');
        unset($tpl);
    }

    /**
     * 랩핑 함수
     *
     * @param $receiverCondition
     */
    protected function insertSendMailLog($receiverCondition)
    {
        $this->_insertSendMailLog($receiverCondition);
    }

    /**
     * 메일 로그 저장 함수
     *
     * @param $receiverCondition
     */
    private function _insertSendMailLog($receiverCondition)
    {
        $this->logger->info('Record send mail log.');
        $log = [
            'subject'           => $this->subject,
            'contents'          => $this->body,
            'receiverCnt'       => 1,
            'receiverCondition' => $receiverCondition,
            'sendType'          => 'auto',
            'mailType'          => $this->type,
            'category'          => 'getCategoryByMailType',
            'contentsMask'      => implode(STR_DIVISION, $this->contentsMask),
        ];
        if ($this->type === self::MEMBER_HACKOUT) {
            $this->logger->info('member hack out log.');
            $memberData = $this->memberDao->selectMemberByOne($this->replaceInfo['email'], 'email');
            $log['receiver'] = $memberData['memId'];
            $this->logger->info(sprintf('receiver mail address %s, member id %s', $memberData['email'], $memberData['memId']));
            $this->mailLogSno = $this->mailLog->insertMailLogByArray($log);
        } else {
            $log['receiver'] = $this->replaceInfo['email'];
            $this->mailLogSno = $this->mailLog->insertMailLogByArray($log);
        }
        $this->logger->info('Record complete.');
    }

    protected function getSender()
    {
        return StringUtils::strIsSet($this->config['senderMail'], $this->baseInfo['email']);
    }

    /**
     * 랩핑 함수
     *
     */
    protected function updateSendMailLog()
    {
        $this->_updateSendMailLog();
    }

    /**
     * 메일로그 수정
     *
     */
    private function _updateSendMailLog()
    {
        if ($this->isSendResult) {
            $this->mailLog->updateSendList($this->replaceInfo['email']);
            $this->mailLog->updateMailLog(1);
            $this->logger->info('Update send mail log complete.');
        }
    }

    /**
     * 자동메일 발송 정보 설정
     *
     * @param              $type
     * @param              $replaceInfo
     * @param null|integer $mallSno
     *
     * @return $this
     */
    public function init($type, $replaceInfo, $mallSno = null)
    {
        $this->logger->info(sprintf('Automatic email initialize start. email type is %s, mall sno %s', $type, $mallSno));
        $session = \App::getInstance('session');
        if ($mallSno === null) {
            if ($session->has(SESSION_GLOBAL_MALL)) {
                $mallSno = \Component\Mall\Mall::getSession('sno');
                $this->logger->info(sprintf('has global mall session. mall sno %s', $mallSno));
            } else {
                $member = $this->memberDao->selectMemberByOne($replaceInfo['email'], 'email');
                $mallSno = $member['mallSno'];
                $this->logger->info(sprintf('has not global mall session select mall sno by email address %s , member mall sno %s', $replaceInfo['email'], $mallSno));
                unset($member);
            }
        }
        $this->mail->setMallSno($mallSno);
        $this->mallSno = $mallSno;
        $this->config = ComponentUtils::getPolicy('mail.configAuto', $mallSno);
        try {
            $this->type = $type;
            $this->replaceInfo = $replaceInfo;
            $this->replaceCode->init();
            $this->mail->setTo($this->replaceInfo['email'], $this->replaceInfo['memNm']);
            if($this->mallSno > 1 && $this->mallSno != $session->has(SESSION_GLOBAL_MALL)) {
                $globalBasicInfo = ComponentUtils::getPolicy('basic.info', $this->mallSno);
                $this->replaceCode->setReplaceCodeByGlobalMall($globalBasicInfo);
            }
            switch ($this->type) {
                /**
                 * 주문/배송관련
                 */
                case self::ORDER_DETAIL:
                    $this->config = $this->config['order']['order'];
                    $this->replaceCode->setReplaceCodeByOrder($replaceInfo);
                    $this->mail->setTo($this->replaceInfo['email'], $this->replaceInfo['orderNm']);
                    break;
                case self::ORDER_INCASH:
                    if ($this->mallSno == DEFAULT_MALL_NUMBER) {
                        $this->config = $this->config['order']['incash'];
                        $this->replaceCode->setReplaceCodeByIncash($replaceInfo);
                        $this->mail->setTo($this->replaceInfo['email'], $this->replaceInfo['orderNm']);
                    } else {
                        $this->logger->warning('Incash only default store the reference can be sent.');
                        throw new \Exception('Automatic mail initialize fail');
                    }
                    break;
                case self::GOODS_DELIVERY:
                    if (count($replaceInfo['goods']) > 0) {
                        $this->config = $this->config['order']['delivery'];
                        $this->replaceCode->setReplaceCodeByDelivery($replaceInfo);
                        $this->mail->setTo($this->replaceInfo['email'], $this->replaceInfo['orderNm']);
                    } else {
                        $this->logger->error('empty goods information');
                        throw new Exception(__('상품 정보가 없습니다.'));
                    }
                    break;
                /**
                 * 가입/탈퇴/문의관련
                 */
                case self::FIND_PASSWORD:
                    $this->addContentsMaskByMaskArguments($this->replaceInfo);
                    $this->config = $this->config['join']['findpassword'];
                    $this->replaceCode->setReplaceCodeByCertification($replaceInfo);
                    break;
                case self::BOARD_QNA:
                    $this->config = $this->config['join']['qna'];
                    $boardArray = [];
                    foreach ($this->config['boardInfo'] as $key => $val) {
                        $boardArray[$val['sno']] = $val;
                    }
                    if ($this->checkBoard($replaceInfo, $boardArray) === false) {
                        $this->logger->error('Automatic email not allowed board. boardSno=>' . $replaceInfo['boardSno'], $boardArray);
                        throw new \Exception(__('발송이 허용된 게시판이 아닙니다.'), 403);
                    }
                    $replaceInfo['boardName'] = $boardArray[$replaceInfo['boardSno']]['bdNm'];

                    $this->replaceCode->setReplaceCodeByQna($replaceInfo);
                    break;
                case self::MEMBER_JOIN:
                    if ($this->config['join']['join']['mailDisapproval'] != 'y' && $replaceInfo['appFl'] == 'n') {
                        throw new \Exception(__('승인대기 회원은 발송할 수 없습니다.'));
                    }
                    $this->config = $this->config['join']['join'];
                    StringUtils::strIsSet($replaceInfo['regDt'], DateTimeUtils::dateFormat('Y-m-d', 'now'));
                    $this->replaceCode->setReplaceCodeByJoin($replaceInfo);
                    break;
                case self::MEMBER_APPROVAL:
                    $this->config = $this->config['join']['approval'];
                    StringUtils::strIsSet($replaceInfo['regDt'], DateTimeUtils::dateFormat('Y-m-d', 'now'));
                    $this->replaceCode->setReplaceCodeByApproval($replaceInfo);
                    break;
                case self::MEMBER_HACKOUT:
                    $this->config = $this->config['join']['hackout'];
                    $this->replaceCode->setReplaceCodeByHackOut($replaceInfo);
                    break;
                /**
                 * 회원정보관련
                 */
                case self::WAKE_MEMBER:
                    $this->addContentsMaskByMaskArguments($this->replaceInfo);
                    $this->config = $this->config['member']['wake'];
                    $this->replaceCode->setReplaceCodeByCertification($replaceInfo);
                    break;
                case self::CHANGE_PASSWORD:
                    $this->config = $this->config['member']['changepassword'];
                    $this->replaceCode->setReplaceCodeByChangePassword($replaceInfo);
                    break;
                case self::SLEEP_NOTICE:
                    $this->config = $this->config['member']['sleepnotice'];
                    $this->replaceCode->setReplaceCodeBySleepNotice($replaceInfo);
                    break;
                case self::AGREEMENT:
                    $this->config = $this->config['member']['agreement'];
                    $this->replaceCode->setReplaceCodeByAgreement($replaceInfo);
                    break;
                case self::AGREEMENT2YPERIOD:
                    $this->config = $this->config['member']['agreement2yperiod'];
                    $this->replaceCode->setReplaceCodeByAgreement($replaceInfo);
                    break;
                case self::REJECTEMAIL:
                    $this->config = $this->config['member']['rejectemail'];
                    $this->replaceCode->setReplaceCodeByRejectEmail($replaceInfo);
                    break;
                case self::GROUPCHANGE:
                    $this->config = $this->config['member']['groupchange'];
                    $this->replaceCode->setReplaceCodeByGroupChange($replaceInfo);
                    break;
                /**
                 * 마일리지/예치금관련
                 */
                case self::REMOVE_DEPOSIT:
                    $this->config = $this->config['point']['removedeposit'];
                    $this->replaceCode->setReplaceCodeByRemoveDeposit($replaceInfo);
                    break;
                case self::ADD_DEPOSIT:
                    $this->config = $this->config['point']['adddeposit'];
                    $this->replaceCode->setReplaceCodeByAddDeposit($replaceInfo);
                    break;
                case self::REMOVE_MILEAGE:
                    $this->config = $this->config['point']['removemileage'];
                    $this->replaceCode->setReplaceCodeByRemoveMileage($replaceInfo);
                    break;
                case self::ADD_MILEAGE:
                    $this->config = $this->config['point']['addmileage'];
                    $this->replaceCode->setReplaceCodeByAddMileage($replaceInfo);
                    break;
                case self::DELETE_MILEAGE:
                    $this->config = $this->config['point']['deletemileage'];
                    $this->replaceCode->setReplaceCodeByDeleteMileage($replaceInfo);
                    break;
                /**
                 * 관리자 보안관련
                 */
                case self::ADMIN_SECURITY:
                    $this->addContentsMaskByMaskArguments($this->replaceInfo);
                    $this->config = $this->config['admin']['adminsecurity'];
                    $this->replaceCode->setReplaceCodeByAdminSecurity($replaceInfo);
                    break;
                default:
                    $this->logger->error(sprintf('Automatic sending mail type is required. not found type %s', $type));
                    throw new \Exception(__('자동발송할 메일 종류는 필수 입니다.'));
                    break;
            }
            // 자동메일 템플릿 파일 설정
            $this->subjectTemplateFile = $this->subjectTemplatePrefix . $this->type . $this->templateExt;
            $this->bodyTemplateFile = $this->bodyTemplatePrefix . $this->type . $this->templateExt;

            // 자동메일 발송 허용 여부 체크
            $this->isAutoSend = $this->checkAutoSendFlag();

            // 비밀번호 찾기 관리자 보안 인증번호는 항상 발송 설정
            if ($this->type === self::FIND_PASSWORD || $this->type === self::ADMIN_SECURITY) {
                $this->isAutoSend = true;
            }

            // 발송 기간 제한 설정이 있는 경우 추가적으로 기간 체크 기간정보는 orderDt 키로 한정
            if ($this->isAutoSend && gd_isset($this->config['sendTarget'], '') !== '') {
                $this->checkSendTarget($replaceInfo);
            }
        } catch (Exception $e) {
            $this->isAutoSend = false;
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
        $this->replaceData = $this->replaceCode->getReplaceCode();
        $this->logger->info('Initialize end.');

        return $this;
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
     * checkBoard
     *
     * @param $replaceInfo
     * @param $boardArray
     *
     * @return bool
     */
    protected function checkBoard($replaceInfo, $boardArray)
    {
        $isMailBoard = array_key_exists($replaceInfo['boardSno'], $boardArray);

        return $isMailBoard;
    }

    /**
     * checkAutoSendFlag
     *
     * @return bool
     */
    protected function checkAutoSendFlag()
    {
        return $this->config['autoSendFl'] == 'y';
    }

    /**
     * checkSendTarget
     *
     * @param $replaceInfo
     */
    protected function checkSendTarget($replaceInfo)
    {
        $currentDateTime = DateTimeUtils::dateFormat('Y-m-d G:i:s', 'now');
        $this->logger->info(sprintf('check send target by order date. order datetime is %s, current datetime is %s', $replaceInfo['orderDt'], $currentDateTime));
        $intervalDay = DateTimeUtils::intervalDay($replaceInfo['orderDt'], $currentDateTime);
        $this->isAutoSend = $intervalDay <= $this->config['sendTarget'];
    }

    public function getMailLogSno()
    {
        return $this->mailLogSno;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return array
     */
    public function getReplaceInfo()
    {
        return $this->replaceInfo;
    }

    /**
     * @return \Bundle\Component\Design\ReplaceCode
     */
    public function getReplaceCode()
    {
        return $this->replaceCode;
    }

    public function checkRequiredValue()
    {
        if (!MailUtil::hasMallDomain()) {
            $this->logger->error('Representative domain is not set up.');
            throw new Exception(__('대표도메인이 설정되지 않았습니다.'), 300);
        }
        if (!Validator::required(str_replace('@', '', $this->config['senderMail']))) {
            $this->logger->error('Sender email address is required.');
            throw new Exception(__('보내는 사람 메일 주소는 필수 입니다.'), 200);
        }
    }

    /**
     * @param \SplObserver[] $observers
     */
    public function setObservers(array $observers)
    {
        $this->observers = $observers;
    }

    /**
     * @return bool
     */
    public function isUseObserver(): bool
    {
        return $this->useObserver;
    }

    /**
     * @param bool $useObserver
     */
    public function setUseObserver(bool $useObserver)
    {
        $this->useObserver = $useObserver;
    }

    /**
     * Attach an SplObserver
     * @link  http://php.net/manual/en/splsubject.attach.php
     *
     * @param SplObserver $observer <p>
     *                              The <b>SplObserver</b> to attach.
     *                              </p>
     *
     * @return void
     * @since 5.1.0
     */
    public function attach(SplObserver $observer)
    {
        $this->observers[] = $observer;
    }

    /**
     * Detach an observer
     * @link  http://php.net/manual/en/splsubject.detach.php
     *
     * @param SplObserver $observer <p>
     *                              The <b>SplObserver</b> to detach.
     *                              </p>
     *
     * @return void
     * @since 5.1.0
     */
    public function detach(SplObserver $observer)
    {
        foreach ($this->observers as $index => $observer) {
            if ($observer === $observer) {
                unset($this->observers[$index]);
            }
        }
    }

    /**
     * Notify an observer
     * @link  http://php.net/manual/en/splsubject.notify.php
     * @return void
     * @since 5.1.0
     */
    public function notify()
    {
        $logger = \App::getInstance('logger');
        $logger->info(sprintf('Start auto mail notify. observers count[%s]', count($this->observers)));
        foreach ($this->observers as $observer) {
            // 에러가 있는 경우 중단 되는 케이스가 있어 아래와 같이 처리
            try {
                $observer->update($this);
            } catch (\Throwable $e) {
                $logger->error($e->getMessage(), $e->getTrace());
            }
        }
    }
}
