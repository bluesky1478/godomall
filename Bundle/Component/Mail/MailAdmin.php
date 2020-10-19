<?php
/* @codingStandardsIgnoreFile */
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 * Do not edit or add to this file if you wish to upgrade Smart to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\Mail;

use App;
use Component\Member\MemberDAO;
use Component\Member\MemberVO;
use Component\Validator\Validator;
use Core\View\Template_\Template_ as Engine;
use Encryptor;
use Exception;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\DateTimeUtils;
use Logger;
use UserFilePath;

/**
 * Class 관리자 메일 관리
 * @package Bundle\Component\Mail
 * @author  yjwee
 */
class MailAdmin extends \Component\AbstractComponent
{
    // __('=통합검색=')
    // __('발송자')
    // __('메일제목')
    const CRM_KEYS = [
        'all'     => '=통합검색=',
        'sender'  => '발송자',
        'subject' => '메일제목',
    ];

    // __('발송일')
    // __('메일제목')
    const CRM_SORTS = [
        'regDt DESC'   => '발송일&darr;',
        'regDt ASC'    => '발송일&uarr;',
        'subject DESC' => '메일제목&darr;',
        'subject ASC'  => '메일제목&uarr;',
    ];

    /** @var \Bundle\Component\Member\MemberDAO $memberDao */
    protected $memberDao;
    /** @var \Bundle\Component\Mail\MailLog $mailLog */
    private $mailLog;
    /** @var \Bundle\Component\Member\MemberAdmin $memberAdmin */
    private $memberAdmin;
    /** @var \Bundle\Component\Member\Member $member */
    private $member;
    /** @var \Bundle\Component\Mail\MailMime $mailMime */
    private $mailMime;

    public function __construct(array $config = [])
    {
        parent::__construct();
        $this->mailLog = App::load('\\Component\\Mail\\MailLog');
        $this->memberAdmin = App::load('\\Component\\Member\\MemberAdmin');
        $this->member = App::load('\\Component\\Member\\Member');
        $this->mailMime = App::load('\\Component\\Mail\\MailMime');
        $this->memberDao = is_object($config['memberDao']) ? $config['memberDao'] : new MemberDAO();
    }

    public function getConfig($mode)
    {
        $configKey = sprintf('mall.%s', $mode);
        $mailConf = gd_policy($configKey);

        return $mailConf;
    }

    /**
     * setConfig
     *
     * @param $req
     *
     * @throws Exception
     */
    public function setConfig(&$req)
    {
        $mode = $req['mode'];
        switch ($mode) {
            case 'configPmail':
                if (!gd_isset($req['userNm']) || !gd_isset($req['email']) || !gd_isset($req['tel']) || !gd_isset($req['mobile'])) {
                    throw new Exception(__('메일 설정이 잘못되었습니다.'));
                }
                $conf = [
                    'userNm' => $req['userNm'],
                    'email'  => $req['email'],
                    'tel'    => implode('-', $req['tel']),
                    'mobile' => implode('-', $req['mobile']),
                ];

                gd_set_policy('mail.' . $mode, $conf, false);
                break;
        }
    }

    /**
     * 리스트 데이터를 기준으로 개별/전체메일발송 처리
     *
     * @param $list
     * @param $subject
     * @param $contents
     * @param $agreeReceiveWordsFl
     * @param $agreeReceiveWords
     * @param $rejectReceiveWordsFl
     * @param $sendType
     *
     * @param $senderEmail
     *
     * @return array
     * @throws Exception
     */
    public function sendMailByMemberVO($list, $subject, $contents, $agreeReceiveWordsFl, $rejectReceiveWordsFl, $sendType, $senderEmail)
    {
        if (!MailUtil::hasMallDomain()) {
            throw new Exception(__('대표도메인이 설정되지 않았습니다.'), 200);
        }
        set_time_limit(RUN_TIME_LIMIT);

        $searchCount = count($list);

        $this->checkFreeSendMail($searchCount);

        $receiver = '';
        $rcverCondition = '';
        $getData = [];
        $emptyCount = 0;
        /**
         * @var \Bundle\Component\Member\MemberVO $item
         */
        foreach ($list as $index => $item) {
            if (StringUtils::strIsSet($item->getEmail(), '') === '') {
                $emptyCount++;
                continue;
            }
            $getData[] = [
                'memNo' => $item->getMemNo(),
                'memId' => $item->getMemId(),
                'memNm' => $item->getMemNm(),
                'email' => $item->getEmail(),
            ];
            $receiver .= $item->getEmail();
        }

        // ## 이메일 발송 로그 저장

        $log = [
            'subject'           => $subject,
            'contents'          => $contents,
            'receiver'          => $receiver,
            'receiverCnt'       => $searchCount,
            'receiverCondition' => $rcverCondition,
            'sendType'          => $sendType,
        ];
        $this->mailLog->insertMailLogByArray($log);
        $policy = gd_policy('basic.info');
        $cnt = count($getData);
        $isAppendReceiveWithReject = false;
        for ($i = $success = $process = $fail = 0; $i < $cnt; $i++) {
            $data = $getData[$i];

            $this->mailLog->insertMailSendList($data);
            $headers['From'] = $senderEmail;
            $headers['Name'] = $policy['mallNm'];
            $headers['Subject'] = $subject;
            list($headers, $contents) = $this->replaceHeaderWithBody($contents, $data, $headers);
            $contentsWithReject = $this->appendReceiveWithReject($contents, $agreeReceiveWordsFl, $rejectReceiveWordsFl, $isAppendReceiveWithReject, $data['email']);
            $sendResult = $this->mailMime->setFrom($senderEmail, $policy['mallNm'])->setTo($data['email'], $data['memNm'])->setSubject($subject)->setHtmlBody($contentsWithReject)->send();
            if ($sendResult) {
                $this->mailLog->updateSendList($data['email']);
                $success++;
            } else {
                $fail++;
            }

            $tmp = floor(($i + 1) / $cnt * 100);
            if ($process != $tmp) {
                $process = $tmp;
            }
        }
        unset($getData);

        // 저장
        $this->mailLog->updateMailLog($success);

        $result = [
            'total'   => $searchCount,
            'success' => $success,
            'fail'    => $fail + $emptyCount,
        ];
        $logger = \App::getInstance('logger');
        $logger->info('Send mail by member value object', $result);
        $this->_decreaseFreePoint($success);

        return $result;
    }

    /**
     * 메일발송 잔여 포인트 확인
     *
     * @param $recvCount
     *
     * @throws Exception
     */
    private function checkFreeSendMail($recvCount)
    {
        $config = $this->getMailConfig();
        $freePoint = $config['freePoint'];

        // ## 발송건수 체크
        $logger = \App::getInstance('logger');
        $logger->info(sprintf('Check free send mail point. receiver count[%s], free point[%s]', $recvCount, $freePoint));
        if ($recvCount > $freePoint) {
            throw new Exception(__("잔여 무료 발송포인트가 부족합니다."));
        }
    }

    /**
     * 첫번째 무료 충전 여부 확인
     *
     * @return bool
     *
     * @deprecated 2016-12-16 yjwee 제거될 함수 입니다.
     */
    private function _isFirstFreePointCharge()
    {
        $config = $this->getMailConfig();
        if ($config['freePointChargeCount'] === 0) {
            return true;
        }

        return false;
    }

    /**
     * mail.config 설정 값을 반환
     *
     * @return array
     */
    public function getMailConfig()
    {
        $config = gd_policy('mail.config');

        return $config;
    }

    /**
     * 메일발송 무료 포인트 충전
     * @deprecated 2016-12-16 yjwee 제거될 함수 입니다.
     */
    private function _chargeFreePoint()
    {
        $config = $this->getMailConfig();
        $config['freePointChargeCount']++;
        $config['freePoint'] = 3000;
        ComponentUtils::setPolicy('mail.config', $config, true);
    }

    /**
     * 수신동의 문구 html 반환
     *
     * @param $agreeReceiveWords
     *
     * @return string
     */
    public function agreeReceiveWordsToHtml($agreeReceiveWords)
    {
        $html = [];
        $html[] = '<p style="margin-top:30px;">';
        $html[] = $agreeReceiveWords;
        $html[] = '</p>';

        return join($html, '');
    }

    /**
     * 무료 포인트 차감처리
     *
     * @param int $decreasePoint
     */
    private function _decreaseFreePoint($decreasePoint = 1)
    {
        $config = $this->getMailConfig();
        $before = $config['freePoint'];
        $config['freePoint'] = $before - $decreasePoint;
        $logger = \App::getInstance('logger');
        $logger->info(sprintf('Decrease free mail point. before [%s], after [%s], point[%s]', $before, $config['freePoint'], $decreasePoint));

        // 메일 무료 포인트 체크 할 대상 솔루션
        if (in_array(\Globals::get('gLicense.ecKind'), ['standard', 'pro']) === true) {
            ComponentUtils::setPolicy('mail.config', $config, true);
        }
    }

    /**
     * 대상회원 선택의 검색결과 전체 추가
     *
     * @param array $params
     *
     * @return mixed
     */
    public function getAddMemberListBySearchResult($params)
    {
        unset($params['offset'], $params['limit']);
        $memberLists = $this->memberDao->selectListBySearch($params);

        $groups = \Component\Member\Group\Util::getGroupName();

        foreach ($memberLists as $key => &$value) {
            $value['groupNm'] = $groups[$value['groupSno']];
            if (is_null($value['cellPhone']) === true) {
                $value['cellPhone'] = '';
            }
        }

        return $memberLists;
    }
    /**
     * 수신 동의, 수신 거부 템플릿 저장
     *
     * @param     $data
     * @param     $fileName
     */
    protected function saveFooterMailTemplate($data, $fileName)
    {
        $data = StringUtils::htmlSpecialCharsStripSlashes($data);
        $fname = \UserFilePath::data('mail', $fileName);
        $fp = fopen($fname, 'w');
        fwrite($fp, $data);
        fclose($fp);
        @chmod($fname, 0707);
    }

    /**
     * Bundle/Controller/Admin/Member/MailPsController.php
     * case mailSend
     *
     * @param $requestParams
     *
     * @return array
     * @throws Exception
     */
    public function mailSend($requestParams)
    {
        $logger = \App::getInstance('logger');
        $logger->info('Send manager email.');
        $subject = $requestParams['subject'];
        $contents = $requestParams['contents'];
        $agreeReceiveWordsFl = $requestParams['agreeReceiveWordsFl'];
        $rejectReceiveWordsFl = $requestParams['rejectReceiveWordsFl'];
        $agreeReceiveWords = $requestParams['agreeReceiveWords'];
        $rejectReceiveWords  = $requestParams['rejectReceiveWords'];
        $sendType = $requestParams['sendType'];
        $senderEmail = $requestParams['senderEmail'];
        $selectTargetFl = $requestParams['selectTargetFl'];

        $v = new Validator();
        $v->init();
        if (!$v->required($subject)) {
            throw new Exception(sprintf(__('%s은(는) 필수 항목 입니다.'), __('제목')));
        }
        if (!$v->required($contents)) {
            throw new Exception(sprintf(__('%s은(는) 필수 항목 입니다.'), __('내용')));
        }

        if (!$v->required($senderEmail)) {
            throw new Exception(sprintf(__('%s은(는) 필수 항목 입니다.'), __('발송자')));
        }
        $logger->info(sprintf('Send target type [%s]', $selectTargetFl));

        /*
         * 수신 동의, 수신 거부 템플릿 저장
         * */
        $this->saveFooterMailTemplate($agreeReceiveWords, 'footer_RECEIVE.html');
        $this->saveFooterMailTemplate($rejectReceiveWords, 'footer_REJECT.html');

        if ($selectTargetFl === 'group') {
            /*
             * 등급선택의 경우 수신거부 대상자 + 그룹번호 체크
             */
            $memberSearchParams = ['groupSno' => $requestParams['groupSno'],];
            if ($requestParams['sendAgreeGroupFl'] === 'y') {
                $memberSearchParams['maillingFl'] = 'y';
            }
            $lists = $this->member->lists($memberSearchParams, null, null, 'memNo,memNm,memId,email');
        } else if ($selectTargetFl === 'all') {
            /*
             * 전체회원선택의 경우 수신거부 대상자 발송 여부만 체크
             */
            $memberSearchParams = [];
            if ($requestParams['sendAgreeAllFl'] === 'y') {
                $memberSearchParams = ['maillingFl' => 'y',];
            }
            $lists = $this->member->lists($memberSearchParams, null, null, 'memNo,memNm,memId,email');
        } else {
            $receiveList = $requestParams['rcverList'];
            $receiveList = json_decode($receiveList, true);
            $lists = [];
            $logger->debug('Selected member', $receiveList);

            $memberDao = \Component\Member\MemberDAO::getInstance();
            foreach ($receiveList as $index => $item) {
                $selectMemberByOne = $memberDao->selectMemberByOne($item['memNo']);
                ArrayUtils::unsetDiff($selectMemberByOne, explode(',', 'memNo,memNm,memId,email'));
                $lists[] = new MemberVO($selectMemberByOne);
            }
            $logger->debug('Selected member', $lists);
        }

        return $this->sendMailByMemberVO($lists, $subject, $contents, $agreeReceiveWordsFl, $rejectReceiveWordsFl, $sendType, $senderEmail);
    }

    /**
     * 메일 내용에 수신거부와 동의기준일 추가
     *
     * @param         $contents
     * @param string  $agreeReceiveWordsFl
     * @param string  $agreeReceiveWords
     * @param string  $rejectReceiveWordsFl
     * @param string  $isAppendReceiveWithReject
     * @param null    $email 받는 사람 메일 주소
     *
     * @return string
     */
    public function appendReceiveWithReject($contents, $agreeReceiveWordsFl, $rejectReceiveWordsFl, &$isAppendReceiveWithReject, $email = null)
    {
        $userFilePath = \App::getInstance('user.path');
        $isAppendReceiveWithReject = true;
        $tpl = new Engine();
        $tpl->template_dir = $userFilePath->data('mail');
        $tpl->compile_dir = $userFilePath->data('mail', 'compiles');
        if ($agreeReceiveWordsFl === 'y') {
            $tpl->define('footer_receive', 'footer_RECEIVE.html');
            $args['rc_today'] = DateTimeUtils::dateFormat('Y년 m월 d일', 'today');
            $tpl->assign($args);
            $contents .= $tpl->fetch('footer_receive');
        }
        if ($rejectReceiveWordsFl === 'y') {
            $encryptor = \App::getInstance('encryptor');
            $tpl->define('footer_reject', 'footer_REJECT.html');
            $encEmail = $encryptor->encrypt($email);     // 수신거부 처리를 위한 수신자 이메일 주소 암호화
            $encEmail = rtrim(strtr(base64_encode($encEmail), '+/', '-_'), '=');
            $rejectLink = '/member/reject_mailing.php?email=' . $encEmail;
            $args['rc_refusalKo'] = '<a href="'.$rejectLink.'">수신거부</a>';
            $args['rc_refusalEn'] = '<a href="'.$rejectLink.'">click here</a>';
            $tpl->assign($args);
            $contents .= $tpl->fetch('footer_reject');

            return $contents;
        }

        return $contents;
    }

    /**
     * replaceHeaderWithBody
     *
     * @param $contents
     * @param $data
     * @param $headers
     *
     * @return array
     */
    public function replaceHeaderWithBody($contents, $data, $headers)
    {
        $headers['To']['Name'] = $data['memNm'];
        $headers['To']['email'] = $data['email'];

        $subjectSearch = [
            '{memId}',
            '{memNm}',
        ];
        $subjectReplace = [
            $data['memId'],
            $data['memNm'],
        ];
        $headers['Subject'] = str_replace($subjectSearch, $subjectReplace, $headers['Subject']);
        $contentsSearch = [
            '{memId}',
            '{memNm}',
        ];
        $contentsReplace = [
            $data['memId'],
            $data['memNm'],
        ];
        $contents = str_replace($contentsSearch, $contentsReplace, $contents);

        return [
            $headers,
            $contents,
        ];
    }
}
