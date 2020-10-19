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

/**
 * 장문발송내용
 * @package Bundle\Component\Sms
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class LmsMessage extends \Component\Sms\AbstractMessage
{
    public function __construct($contents, $replaceType = null)
    {
        parent::__construct($contents, $replaceType);
        if ($this->exceedLmsLength()) {
            throw new \Exception('LMS ' . __('전송은 최대') . ' ' . number_format(Sms::LMS_STRING_LIMIT) . ' Byte ' . __('까지 가능합니다.'));
        }
    }

    /**
     * 장문발송내용반환
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
        $this->messages[] = $contents;

        return $this->messages;
    }
}
