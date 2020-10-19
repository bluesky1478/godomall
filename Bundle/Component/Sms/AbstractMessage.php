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

use Framework\Utility\StringUtils;

/**
 * SMS 발송 메시지 클래스
 * @package Bundle\Component\Sms
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
abstract class AbstractMessage
{
    /** @var null 메시지 치환코드 타입 정의 */
    protected $replaceType;
    /** @var array 메시지 치환코드 */
    protected $replaceCode = [];
    /** @var string 문자 발송 시 사용될 문자 메시지 */
    protected $contents;
    /** @var array 문자 발송을 위한 통신에서 사용되는 메시지 */
    protected $messages;

    //@formatter:off
    protected $replaceCodes = [
        'order' => ['orderNo', 'orderName', 'settlePrice', 'bankAccount', 'deliveryName', 'invoiceNo', 'bankInfo', 'orderDate'],
        'member' => ['memId', 'memNm', 'sleepScheduleDt', 'smsAgreementDt', 'mailAgreementDt', 'groupNm', 'mileage', 'deposit'],
        'promotion' => ['memNm', 'eventName', 'eventDt', 'eventUrl'],
        'goods' => ['restockName', 'restockGoodsNm', 'restockOptionName', 'restockGoodsUrl'],
        'excel' => ['name']
    ];
    //@formatter:on

    /**
     * 기본상점 치환코드 치환
     *
     * @param string $contents    발송할 메시지
     * @param null   $replaceType 치환코드 타입
     */
    public function __construct($contents, $replaceType = null)
    {
        $this->replaceType = $replaceType;
        $replaceCode = \App::load('\\Component\\Design\\ReplaceCode');
        $replaceContents = $replaceCode->replace(trim($contents));
        $replaceContents = trim($replaceContents);
        $replaceContents = str_replace("\r", "", $replaceContents);
        $this->contents = $replaceContents;
    }

    /**
     * lms 길이 체크
     *
     * @param null $contents
     *
     * @return bool
     */
    public function exceedLmsLength($contents = null)
    {
        return StringUtils::strLength($contents ?? $this->contents) > Sms::LMS_STRING_LIMIT;
    }

    /**
     * sms 길이 체크
     *
     * @param null $contents
     *
     * @return bool
     */
    public function exceedSmsLength($contents = null)
    {
        return StringUtils::strLength($contents ?? $this->contents) > Sms::SMS_STRING_LIMIT;
    }

    /**
     * 발송메시지에 치환코드가 포함되었는지 확인하는 함수
     *
     * @return bool
     */
    public function hasReplaceCode()
    {
        $logger = \App::getInstance('logger');
        if (!key_exists($this->replaceType, $this->replaceCodes)) {
            $logger->info('Not found replace code. ' . $this->replaceType);

            return false;
        }
        $pattern = '/\{[a-zA-Z]*\}/';
        preg_match_all($pattern, $this->contents, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $index => $match) {
            $key = substr($match[0], 1, strlen($match[0]) - 2);
            if (in_array($key, $this->replaceCodes[$this->replaceType])) {
                return true;
            }
        }

        return false;
    }

    /**
     * contents 의 내용을 배열로 반환하는 함수
     *
     * @param bool $useReplaceContents
     *
     * @return array
     */
    abstract public function getMessages($useReplaceContents = false);

    /**
     * @return string
     */
    public function getContents(): string
    {
        return $this->contents;
    }

    /**
     * getContentsByLog
     *
     * @return string
     */
    public function getContentsByLog()
    {
        return $this->contents;
    }

    /**
     * 배열로 변환된 발송 메시지 갯수 체크
     *
     * @return int
     */
    public function count()
    {
        return 1;
    }

    /**
     * 현재 contents, replaceType 을 이용하여 LmsMessage 클래스를 생성하여 반환
     *
     * @return LmsMessage
     */
    public function toLmsMessage()
    {
        return new LmsMessage($this->contents, $this->replaceType);
    }

    /**
     * 현재 contents, replaceType 을 이용하여 SmsMessage 클래스를 생성하여 반환
     *
     * @return SmsMessage
     */
    public function toSmsMessage()
    {
        return new SmsMessage($this->contents, $this->replaceType);
    }

    /**
     * @return null
     */
    public function getReplaceType()
    {
        return $this->replaceType;
    }

    public function getReplaceCodes()
    {
        if (!key_exists($this->replaceType, $this->replaceCodes)) {
            return [];
        }

        return $this->replaceCodes[$this->replaceType];
    }
}
