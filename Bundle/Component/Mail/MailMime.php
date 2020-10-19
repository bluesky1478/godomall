<?php
/**
 *
 *  This is commercial software, only users who have purchased a valid license
 *  and accept to the terms of the License Agreement can install and use this
 *  program.
 *
 *  Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 *  versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 *
 */

namespace Bundle\Component\Mail;

use Component\Validator\Validator;
use Exception;
use Framework\Application\Bootstrap\Log;
use Framework\Utility\StringUtils;

/**
 * Class MailMime
 * @package Bundle\Component\Mail
 * @author  yjwee
 */
class MailMime
{
    protected $mallSno;
    private $from;
    private $to;
    private $subject;
    private $header = [];
    private $textBody;
    private $htmlBody;
    private $isFileTextBody = false;
    private $isFileHtmlBody = false;
    private $crlf;
    /** @var string sendmail path */
    private $sendmail_path = '/usr/sbin/sendmail';
    /** @var string sendmail options (기존 메일 클래스 옵션을 그대로 적용함) */
    private $sendmail_args = '-t -i';


    /**
     * @inheritDoc
     */
    public function __construct(array $array = null)
    {
        if ($array !== null) {
            foreach ($array as $index => $item) {
                $this->$index = $item;
            }
        }
    }

    private function _setLocalPathWithArgs()
    {
        $this->sendmail_path = 'C:\wamp64\bin\sendmail\sendmail.exe';
        $this->sendmail_args .= ' -v';
    }

    /**
     * 이메일 호스트 체크
     * checkdnsrr와 같이 기본적으로 제공하는 함수들은 timeout이 없어
     * DNS를 찾지 못하는 경우 딜레이가 심해 DB 에러가 발생한다.
     * 따라서 curl로 timeout 2초로 설정 후 해당 host 체크하도록 변경 처리 함
     *
     * @param $host
     *
     * @return bool
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     *
     * @deprecated
     */
    public function checkEmailHost($host)
    {
        $logger = \App::getInstance('logger')->channel(Log::CHANNEL_DEFAULT_MAIL);
        $ch = curl_init($host);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); //timeout in seconds
        if (curl_exec($ch) === false) {
            $logger->error('Invalidate check email [' . curl_error($ch) . '].');
        }
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (200 == $retcode) {
            return true;
        }
        $logger->warning(sprintf('Invalid email host!! [%s], return code[%s]', $host, $retcode));

        return false;
    }

    /**
     * 코드 관리에 저장된 도메인 여부 확인
     *
     * @param string $domain
     *
     * @return bool
     *
     * @deprecated
     */
    public function checkHostRequired($domain)
    {
        $hostCodes = $this->getHostCodes();

        return in_array($domain, $hostCodes) === false;
    }

    /**
     * getHostCodes
     *
     * @return array
     *
     * @deprecated
     */
    protected function getHostCodes()
    {
        $code = \App::load('Component\\Code\\Code');

        return $code::getGroupItems('01004');
    }

    /**
     * send
     *
     * @return bool
     */
    public function send()
    {
        $logger = \App::getInstance('logger')->channel(Log::CHANNEL_DEFAULT_MAIL);

        error_reporting(0);
        $defaultDomain = gd_policy('basic.info');
        $defaultDomain = 'http://' . $defaultDomain['mallDomain'];
        $logger->info(sprintf('Representative domain is %s', $defaultDomain));

        $mime = new \Mail_mime(['eol' => $this->crlf]);
        if ($this->textBody !== null) {
            $mime->setTXTBody($this->textBody, $this->isFileTextBody);
        }
        if ($this->htmlBody !== null) {
            // html 일 경우 실행
            $this->htmlBody = stripslashes($this->htmlBody);
            $util = new MailUtil();
            $this->htmlBody = $util->relativeToAbsolute($this->htmlBody, $defaultDomain, $this->mallSno);
            $mime->setHTMLBody($this->htmlBody, $this->isFileHtmlBody);
        }

        $body = $mime->get();

        // 헤더 설정
        $mime->setContentType('text/html', ['charset' => SET_CHARSET]);
        $headers = $mime->headers($this->header);

        if (@is_file($this->sendmail_path)) {
            $logger->info(__METHOD__ . ' mail factory use sendmail');
            $params['sendmail_path'] = $this->sendmail_path;
            $params['sendmail_args'] = $this->sendmail_args;
            $mail = &\Mail::factory('sendmail', $params);
        } else {
            $logger->info(__METHOD__ . ' main factory use mail');
            $mail = &\Mail::factory('mail');
        }

        if ($mail instanceof \PEAR_Error) {
            $logger->error($mail->getMessage(), $mail->getBacktrace());

            return false;
        }

        try {
            $logger->info(__METHOD__ . ', header info', $headers);
            $result = $mail->send($this->to, $headers, $body);
            $logger->info(__METHOD__ . ', send result=[' . $result . ']');
        } catch (\Throwable $e) {
            $logger->error($e->getTraceAsString());

            return false;
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param mixed $from
     *
     * @param null  $name
     *
     * @return MailMime
     */
    public function setFrom($from, $name = null)
    {
        $this->from = $from;
        $this->header['From'] = $from;
        if ($name !== null || empty($name) === false) {
            $name = StringUtils::htmlSpecialCharsDecode($name);
            $this->header['From'] = '"=?' . SET_CHARSET . '?B?' . base64_encode($name) . '?="<' . $from . '>';
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCrlf()
    {
        return $this->crlf;
    }

    /**
     * @param mixed $crlf
     *
     * @return MailMime
     */
    public function setCrlf($crlf)
    {
        $this->crlf = $crlf;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param mixed $to
     *
     * @param null  $name
     *
     * @return MailMime
     * @throws Exception
     */
    public function setTo($to, $name = null)
    {
        if (!Validator::required($to)) {
            throw new Exception(__('받는 사람 메일 주소는 필수 입니다.'), 200);
        }
        $this->to = $to;
        $this->header['To'] = $to;
        if ($name !== null || empty($name) === false) {
            $this->header['To'] = '"=?' . SET_CHARSET . '?B?' . base64_encode($name) . '?="<' . $to . '>';
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     *
     * @return MailMime
     */
    public function setSubject($subject)
    {
        $subject = StringUtils::htmlSpecialCharsDecode($subject);
        $this->header['Subject'] = '=?' . SET_CHARSET . '?B?' . base64_encode($subject) . '?=';

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTextBody()
    {
        return $this->textBody;
    }

    /**
     * @param mixed   $textBody
     *
     * @param boolean $isFileTextBody
     *
     * @return MailMime
     */
    public function setTextBody($textBody, $isFileTextBody)
    {
        $this->textBody = $textBody;
        $this->isFileTextBody = $isFileTextBody;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHtmlBody()
    {
        return $this->htmlBody;
    }

    /**
     * @param mixed $htmlBody
     *
     * @return MailMime
     */
    public function setHtmlBody($htmlBody)
    {
        $this->htmlBody = $htmlBody;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsFileTextBody()
    {
        return $this->isFileTextBody;
    }

    /**
     * @param boolean $isFileTextBody
     *
     * @return MailMime
     */
    public function setIsFileTextBody($isFileTextBody)
    {
        $this->isFileTextBody = $isFileTextBody;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsFileHtmlBody()
    {
        return $this->isFileHtmlBody;
    }

    /**
     * @param boolean $isFileHtmlBody
     *
     * @return MailMime
     */
    public function setIsFileHtmlBody($isFileHtmlBody)
    {
        $this->isFileHtmlBody = $isFileHtmlBody;

        return $this;
    }

    /**
     * @param mixed $mallSno
     */
    public function setMallSno($mallSno)
    {
        $this->mallSno = $mallSno;
    }
}
