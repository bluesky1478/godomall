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
 * 분할발송내용
 *
 * @package Bundle\Component\Sms
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class DivisionSmsMessage extends \Component\Sms\AbstractMessage
{
    /**
     * 분할발송내용 반환
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
        $arrTmp = StringUtils::strCutToArray($contents, Sms::SMS_STRING_LIMIT);
        $this->messages = $arrTmp;
        unset($arrTmp);

        return $this->messages;
    }

    /**
     * 분할 발송될 메시지 갯수 반환
     *
     * @return int
     */
    public function count()
    {
        return count(StringUtils::strCutToArray($this->contents, Sms::SMS_STRING_LIMIT));
    }

}
