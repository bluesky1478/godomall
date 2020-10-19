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

namespace Bundle\Component\Policy;

/**
 * 회원 > 회원 관리 > 휴면 회원 정책
 *
 * @package Bundle\Component\Policy
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class MemberSleepPolicy extends \Component\Policy\Policy
{
    /** 휴면회원 정책 코드+그룹코드 */
    const KEY = 'member.sleep';

    /**
     * 휴면회원 정책 반환
     * 기준 상점이 아닌 경우 특정 설정 값은 사용안함으로 처리
     *
     * @param $mallSno
     *
     * @return array
     */
    public function getPolicy($mallSno = DEFAULT_MALL_NUMBER)
    {
        $policy = $this->getValue(self::KEY, $mallSno);
        if ($mallSno != DEFAULT_MALL_NUMBER) {
            $policy['authSms'] = 'n';
            $policy['authIpin'] = 'n';
            $policy['authRealName'] = 'n';
            $policy['authCellPhone'] = 'n';
        }

        return $policy;
    }

    /**
     * 휴면회원 전환 시 보유 마일리지 초기화
     *
     * @return bool     true 전환 시 보유 마일리지 초기화, false 해제 시 소멸 처리(기본 설정)
     */
    public function isExpireMileageSleepMember(): bool
    {
        $policy = $this->getValue(self::KEY);

        return $policy['initMileage'] == 'sleep';
    }

    /**
     * 휴면회원 해제 시 소멸 마일리지 처리
     *
     * @return bool     true 해제 시 소멸 대상 마일리지 소멸 처리, false 전환 시 보유 마일리지 초기화
     */
    public function isExpireMileageWakeMember(): bool
    {
        return !$this->isExpireMileageSleepMember();
    }

    /**
     * 회원등급 초기화 설정
     *
     * @return bool     true 휴면회원 해제 시 기본회원으로 등급변경, false 사용안함
     */
    public function useResetGroup(): bool
    {
        $policy = $this->getValue(self::KEY);

        return $policy['initMemberGroup'] === 'y';
    }
}
