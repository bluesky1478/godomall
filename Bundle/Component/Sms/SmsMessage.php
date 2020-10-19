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
 * 단문발송내용
 * @package Bundle\Component\Sms
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class SmsMessage extends \Component\Sms\AbstractMessage
{
    /**
     * SMS 발송내용 반환
     *
     * @param null $contents
     *
     * @return array
     */
    public function getMessages($contents = null)
    {
        $this->messages = [];
        if ($contents === null) {
            $contents = $this->contents;
        }
        if ($this->exceedSmsLength()) {
            $arrTmp = StringUtils::strCutToArray($contents, Sms::SMS_STRING_LIMIT);
            $this->messages[] = $arrTmp[0];
        } else {
            $this->messages[] = $contents;
        }

        return $this->messages;
    }

    /**
     * 발송로그 내용 반환
     *
     * @return string
     */
    public function getContentsByLog()
    {
        if ($this->exceedSmsLength()) {
            return StringUtils::strCutToArray($this->contents, Sms::SMS_STRING_LIMIT)[0];
        }

        return $this->contents;
    }
}
