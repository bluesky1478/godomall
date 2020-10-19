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

namespace Bundle\Component\Member\Group;


use Component\Mail\MailUtil;
use Component\Validator\Validator;

/**
 * Class AppraisalMailSender
 * @package Bundle\Component\Member\Group
 * @author  yjwee
 */
class AppraisalMailSender
{
    /** @var array $receiver 메일 수신대상 */
    private $receiver = [];
    /** @var \Bundle\Component\Mail\MailMimeAuto $mailMimeAuto */
    private $mailMimeAuto;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->mailMimeAuto = \App::load('\\Component\\Mail\\MailMimeAuto');
    }

    /**
     * 등급평가 후 메일을 받을 대상을 추가하는 함수
     *
     * @param array $storage 회원정보
     * @param array $group   등급정보
     */
    public function addReceiver(array $storage, array $group)
    {
        if (Validator::email(gd_isset($storage['email'], ''))) {
            $this->receiver[$storage['memNo']] = [
                $storage,
                $group,
            ];
        } else {
            \App::getInstance('logger')->warning(__METHOD__ . ', 메일 검증 오류로 인해 메일발송 대상에서 제외됩니다. memNo[' . $storage['memNo'] . '], email[' . $storage['email'] . ']');
        }
    }

    public function send()
    {
        foreach ($this->receiver as $index => $item) {
            $groupVo = new \Component\Member\Group\GroupDomain($item[1]);
            if (!($groupVo instanceof GroupDomain)) {
                \App::getInstance('logger')->error('그룹정보 객체 오류', $item);
                continue;
            }
            MailUtil::sendMemberGroupChangeMail($item[0], $groupVo);
        }
    }

    public function count()
    {
        return count($this->receiver);
    }
}
