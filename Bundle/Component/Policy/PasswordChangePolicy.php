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
 * @link http://www.godo.co.kr
 */

namespace Bundle\Component\Policy;

use Component\Policy\Storage\StorageInterface;
use Component\Validator\Validator;
use DateTime;
use Framework\Utility\ArrayUtils;

/**
 * Class PasswordChangePolicy
 * @package Bundle\Component\Policy
 * @author  yjwee
 */
class PasswordChangePolicy extends \Component\Policy\Policy
{
    protected $policy;

    /**
     * @inheritDoc
     */
    public function __construct(StorageInterface $storage = null)
    {
        parent::__construct($storage);
        $this->policy = $this->getDefaultValue('member.passwordChange');
    }

    public function saveMemberPasswordChange($data)
    {
        $validator = new Validator();
        // __('관리자 사용여부')
        // __('쇼핑몰 사용여부')
        // __('비밀번호 변경 안내 주기')
        // __('비밀번호 변경 안내 주기 항목')
        // __('비밀번호 변경 재안내 주기')
        // __('비밀번호 변경 재안내 주기 항목')
        $validator->add('managerFl', 'yn', true, '{관리자 사용여부}');
        $validator->add('memberFl', 'yn', true, '{쇼핑몰 사용여부}');
        if ($data['managerFl'] == 'y' || $data['memberFl'] == 'y') {
            $validator->add('guidePeriod', 'number', true, '{비밀번호 변경 안내 주기}');
            $validator->add('guidePeriodItem', 'pattern', true, '{비밀번호 변경 안내 주기 항목}', '/^(month|day)$/');
            $validator->add('reGuidePeriod', 'number', true, '{비밀번호 변경 재안내 주기}');
            $validator->add('reGuidePeriodItem', 'pattern', true, '{비밀번호 변경 재안내 주기 항목}', '/^(month|day)$/');
        }
        if ($validator->act($data, true) === false) {
            throw new \Exception(implode("\n", $validator->errors), 500);
        }
        $getValue = ArrayUtils::removeEmpty($data);

        return $this->setValue('member.passwordChange', $getValue);
    }

    public function useManager()
    {
        return $this->policy['managerFl'] === 'y';
    }

    public function useUser()
    {
        return $this->policy['memberFl'] === 'y';
    }

    public function isNotificationDate(\DateTime $dateTime, $period)
    {
        $currentDateTime = new \DateTime();

        $dateTime->setTime(0, 0, 0);
        $currentDateTime->setTime(0, 0, 0);

        $dateTime->modify($period);

        $diff = $currentDateTime->diff($dateTime);

        return (($diff->invert === 1) && ($diff->days >= 0)) || ($diff->invert === 0 && $diff->days === 0);
    }

    public function isGuideDate(\DateTime $lastChangeDateTime)
    {
        $period = $this->policy['guidePeriod'] . ' ' . $this->policy['guidePeriodItem'];

        return $this->isNotificationDate($lastChangeDateTime, $period);
    }

    public function isReGuideDate(\DateTime $lastGuideDateTime)
    {
        $period = $this->policy['reGuidePeriod'] . ' ' . $this->policy['reGuidePeriodItem'];

        return $this->isNotificationDate($lastGuideDateTime, $period);
    }

    public function getPolicy()
    {
        return $this->policy;
    }

    public function checkGateway(array $session)
    {
        gd_isset($session['changePasswordDt'], $session['mRegDt']);
        gd_isset($session['guidePasswordDt'], $session['mRegDt']);

        if ($session['changePasswordDt'] == '0000-00-00 00:00:00') {
            $session['changePasswordDt'] = $session['mRegDt'];
        }
        if ($session['guidePasswordDt'] == '0000-00-00 00:00:00') {
            $session['guidePasswordDt'] = $session['mRegDt'];
        }

        $lastChangeDateTime = new DateTime($session['changePasswordDt']);
        $lastGuideDateTime = new DateTime($session['guidePasswordDt']);

        return $this->isGuideDate($lastChangeDateTime) && $this->isReGuideDate($lastGuideDateTime);
    }

    /**
     * 접속한 운영자의 비밀번호 변경 이력이 기준일보다 오래 되었는지 체크
     * @param array $session
     *
     * @return bool
     */
    public function checkCompulsionGateway(array $session)
    {
        // 운영자 비밀변호 변경일 - 없으면 운영자 등록일 입력
        gd_isset($session['changePasswordDt'], '0000-00-00');
        if ($session['changePasswordDt'] == '0000-00-00') {
            $session['changePasswordDt'] = $session['mRegDt'];
        }
        // 비밀변호 변경 기준일 - 2018-07-01부터 변경이력이 없는 운영자
        gd_isset($compulsionDate, '2018-07-01');

        $lastChangeDateTime = new DateTime($session['changePasswordDt']);
        $compulsionDateTime = new DateTime($compulsionDate);

        $diff = $compulsionDateTime->diff($lastChangeDateTime);
        $diffCheck = $diff->format('%R');
        $return = false;
        if ($diffCheck == '+') {
            $return = false;
        } else if ($diffCheck == '-') {
            $return = true;
        }

        return $return;
    }
}
