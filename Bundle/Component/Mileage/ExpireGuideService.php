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

namespace Bundle\Component\Mileage;

use Component\Mail\MailMimeAuto;
use Component\Sms\Code;
use Component\Sms\Sms;
use Component\Sms\SmsAutoCode;
use Framework\Utility\UrlUtils;
use Globals;

/**
 * Class ExpireGuideService
 * @package Bundle\Component\Mileage
 * @author  yjwee
 */
class ExpireGuideService
{
    private $dao;
    private $mailAuto;
    private $sms;
    private $expireDate;

    /**
     * @inheritDoc
     */
    public function __construct(MileageDAO $dao = null, MailMimeAuto $mailAuto = null, Sms $sms = null, $date = null)
    {
        if ($dao === null) {
            $dao = new MileageDAO();
        }
        $this->dao = $dao;

        if ($mailAuto === null) {
            $mailAuto = new MailMimeAuto();
        }
        $this->mailAuto = $mailAuto;

        if ($sms === null) {
            $sms = new Sms();
        }
        $this->sms = $sms;

        if ($date) {
            $date = new \DateTime($date);
        }
        $this->expireDate = MileageUtil::getExpireBeforeDate($date);
    }

    public function getListsByGuide()
    {
        $lists = $this->dao->getListsByExpireGuideDate($this->expireDate);
        $targets = [];
        foreach ($lists as $index => $list) {
            $list['mileage'] = MileageUtil::removeUseHistory($list['mileage'], $list['useHistory']);
            $targets[] = $list;
        }
        \App::getInstance('logger')->info(sprintf('Mileage expire guide sms list count[%d], target count[%d]', count($lists), count($targets)));

        return $targets;
    }

    public function sendMail(array $targets)
    {
        $results = [];
        foreach ($targets as $target) {
            if ($target['maillingFl'] != 'y') {
                continue;
            }
            $target['mileage'] = gd_money_format($target['mileage']);
            $target['totalMileage'] = gd_money_format($target['totalMileage']);
            // 마일리지 소멸예정일을 저장된 데이터가 아닌 전날로 변경하여 노출되도록 수정
            $target['deleteScheduleDt'] = MileageUtil::changeDeleteScheduleDt($target['deleteScheduleDt']);
            $result = $this->mailAuto->init(MailMimeAuto::DELETE_MILEAGE, $target)->autoSend();
            $results[] = [
                $target['email'],
                $result,
            ];
        }

        return $results;
    }

    public function sendSms(array $targets)
    {
        $results = [];
        $aBasicInfo = gd_policy('basic.info');
        foreach ($targets as $target) {
            $target['mileage'] = gd_money_format($target['mileage']);
            $target['totalMileage'] = gd_money_format($target['totalMileage']);
            // 마일리지 소멸예정일을 저장된 데이터가 아닌 전날로 변경하여 노출되도록 수정
            $target['deleteScheduleDt'] = MileageUtil::changeDeleteScheduleDt($target['deleteScheduleDt']);
            $groupInfo = \Component\Member\Group\Util::getGroupName('sno=' . $target['groupSno']);
            $result = $this->sms->smsAutoSend(
                SmsAutoCode::MEMBER, Code::MILEAGE_EXPIRE, $target['cellPhone'], [
                    'rc_deleteScheduleDt' => $target['deleteScheduleDt'],
                    'rc_mileage'          => $target['mileage'],
                    'memNm'      => $target['memNm'],
                    'memId'      => $target['memId'],
                    'mileage'      => $target['totalMileage'],
                    'deposit'      => $target['deposit'],
                    'groupNm'      => $groupInfo[$target['groupSno']],
                    'rc_mallNm' => $aBasicInfo['mallNm'],
                    'shopUrl' => $aBasicInfo['mallDomain']
                ]
            );
            $results[] = [
                $target['cellPhone'],
                $result,
            ];
        }

        return $results;
    }
}
