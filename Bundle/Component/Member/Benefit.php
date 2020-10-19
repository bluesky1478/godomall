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

namespace Bundle\Component\Member;

use App;
use Component\Mileage\Mileage;
use Component\Validator\Validator;
use Exception;
use Framework\Object\SimpleStorage;
use Framework\Utility\StringUtils;

/**
 * Class Benefit
 * @package Bundle\Component\Member
 * @author  yjwee
 */
class Benefit extends \Component\AbstractComponent
{
    /** @var SimpleStorage */
    private $mileageGive;
    /** @var  MemberVO */
    private $benefitMember;
    /** @var \Bundle\Component\Mileage\Mileage $mileage */
    private $mileage;

    public function __construct()
    {
        parent::__construct();
        $this->mileageGive = new SimpleStorage(gd_policy('member.mileageGive'));
    }

    /**
     * 가입혜택을 받을 회원 정보 설정
     *
     * @param MemberVO $benefitMember
     */
    public function setBenefitMember(MemberVO $benefitMember)
    {
        $this->benefitMember = $benefitMember;
    }

    /**
     * 가입 시 가입 혜택 지급
     *
     */
    public function entryBenefitOffer()
    {
        $this->validateBenefitMember();
        $this->benefitOffer();
    }

    /**
     * 승인 시 가입 혜택 지급
     *
     */
    public function approvalBenefitOffer()
    {
        $this->validateBenefitMember();
        $db = \App::getInstance('DB');
        $db->bind_param_push($bind, 'i', $this->benefitMember->getMemNo());
        $countBenefitMember = $db->query_fetch('SELECT COUNT(*) AS cnt FROM ' . DB_MEMBER . ' WHERE memNo=? AND (entryBenefitOfferDt IS NULL OR entryBenefitOfferDt = \'0000-00-00 00:00:00\')', $bind, false);
        if ($countBenefitMember['cnt'] > 0) {
            $this->benefitOffer();
        } else {
            $logger = \App::getInstance('logger');
            $logger->info(sprintf('This member[%s] has already been entry benefit. ', $this->benefitMember->getMemNo()));
        }
    }

    /**
     * 회원가입 시 메일수신 동의 혜택 지급
     *
     */
    public function benefitMileageMailingFlag()
    {
        $emailAmount = $this->mileageGive->get('emailAmount', 0);
        $isEmailFl = $this->mileageGive->get('emailFl', 'n') == 'y';
        $isMailingFl = $this->benefitMember->getMaillingFl() == 'y';
        if ($isEmailFl && $isMailingFl && Validator::email($this->benefitMember->getEmail(), true)) {
            $this->mileage->setMemberMileage($this->benefitMember->getMemNo(), $emailAmount, Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_JOIN_MEMBER, 'm', $this->benefitMember->getMemId(), 'mailingFl');
        }
    }

    /**
     * 회원가입 시 문자수신 동의 혜택 지급
     *
     */
    public function benefitMileageSmsFlag()
    {
        $smsAmount = $this->mileageGive->get('smsAmount', 0);
        $isSmsFl = $this->mileageGive->get('smsFl', 'n') == 'y';
        $isMemberSmsFl = $this->benefitMember->getSmsFl() == 'y';
        if ($isSmsFl && $isMemberSmsFl && Validator::phone($this->benefitMember->getCellPhone(), true)) {
            $this->mileage->setMemberMileage($this->benefitMember->getMemNo(), $smsAmount, Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_JOIN_MEMBER, 'm', $this->benefitMember->getMemId(), 'smsFl');
        }
    }

    /**
     * 회원가입 마일리지 지급
     *
     */
    public function benefitMileageJoin()
    {
        $joinAmount = $this->mileageGive->get('joinAmount', 0);
        if ($joinAmount > 0) {
            $this->mileage->setMemberMileage($this->benefitMember->getMemNo(), $joinAmount, Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_JOIN_MEMBER, 'm', $this->benefitMember->getMemId());
        }
    }

    /**
     * 회원가입 추천인 혜택 지급
     *
     * @param string $mode
     *
     */
    public function benefitRecommender($mode = null)
    {
        $recommJoinAmount = $this->mileageGive->get('recommJoinAmount', 0);
        if ($recommJoinAmount > 0) {
            if ($mode == 'modify') {
                $this->mileage->setMemberMileage($this->benefitMember->getMemNo(), $recommJoinAmount, Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_REGISTER_RECOMMEND, 'm', $this->benefitMember->getRecommId(), null, '수정 시 추천인 등록');
            } else {
                $this->mileage->setMemberMileage($this->benefitMember->getMemNo(), $recommJoinAmount, Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_REGISTER_RECOMMEND, 'm', $this->benefitMember->getRecommId());
            }
        }
    }

    /**
     * 회원가입 피추천인 혜택 지급
     *
     * @param string $mode
     *
     */
    public function benefitRecommendee($mode = null)
    {
        $recommAmount = $this->mileageGive->get('recommAmount', 0);
        $recommCount = $this->mileageGive->get('recommCount', 0);
        if ($recommAmount > 0) {
            $recommendVo = new \Component\Member\MemberVO($this->getDataByTable(DB_MEMBER, $this->benefitMember->getRecommId(), 'memId'));
            $countWhere = 'WHERE recommId=\'' . $this->benefitMember->getRecommId() . '\'';
            $countWhere .= ' AND memNo!=' . $this->benefitMember->getMemNo();
            $recommendeeCount = $this->getCount(DB_MEMBER, '1', $countWhere);
            if ($this->mileageGive->get('recommCountFl', 'n') === 'y' && $recommendeeCount >= $recommCount) {
                $msg = sprintf('%s 회원의 추천을 받았으나 적립횟수[%s]가 초과되어 적립안됨', $this->benefitMember->getMemId(), number_format($recommCount));
                $this->mileage->setMemberMileage($recommendVo->getMemNo(), 0, Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ETC, 'm', $this->benefitMember->getRecommId(), null, $msg);
            } else {
                if ($mode == 'modify') {
                    $this->mileage->setMemberMileage($recommendVo->getMemNo(), $recommAmount, Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_RECEIVE_RECOMMEND, 'm', $this->benefitMember->getMemId(), null, ' (수정 시)');
                } else {
                    $this->mileage->setMemberMileage($recommendVo->getMemNo(), $recommAmount, Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_RECEIVE_RECOMMEND, 'm', $this->benefitMember->getMemId(), null, ' (가입 시)');
                }
            }
        }
    }

    /**
     * 추천인 혜택 지급
     *
     * @param string $mode
     *
     */
    public function benefitRegisterRecommender($mode = null)
    {
        if (empty($this->benefitMember->getRecommId()) === false) {
            $this->benefitRecommender($mode);
            $this->benefitRecommendee($mode);
            $this->updateRecommendFlag();
        }
    }

    public function updateRecommendFlag()
    {
        /** @var \Bundle\Component\Member\Member $member */
        $member = App::load('\\Component\\Member\\Member');
        $member->updateMemberByMemberNo($this->benefitMember->getMemNo(), ['recommFl'], ['y']);
    }

    /**
     * 회원가입 쿠폰 지급
     *
     */
    public function saveAutoCouponByJoin()
    {
        $session = \App::getInstance('session');
        $logger = \App::getInstance('logger');
        $logger->info(__METHOD__);
        $policy = gd_policy('member.group');
        $defaultGroupSno = $policy['defaultGroupSno'];
        /** @var \Bundle\Component\Coupon\Coupon $coupon */
        $coupon = \App::load('Component\\Coupon\\Coupon');

        // 회원정보
        $coupon->setAutoCouponMemberSave('join', $this->benefitMember->getMemNo(), intval($defaultGroupSno));
        foreach ($coupon->getResultStorage()->get('smsReceivers', []) as $index => $item) {
            $logger->info('save join coupon result', $item);
            // 2017-02-08 yjwee 기존 회원가입 쿠폰 SMS 발송 로직은 \Component\Coupon\Coupon 클래스에서 처리합니다.
        }
        if($session->has('simpleJoin')) {
            $joinEventOrder = gd_policy('member.joinEventOrder');
            if($joinEventOrder['couponNo']) {
                $coupon->setAutoCouponMemberSave('joinEvent', $this->benefitMember->getMemNo(), intval($defaultGroupSno), $joinEventOrder['couponNo']);
            }
        }
    }

    public function updateEntryBenefitOfferDateTime()
    {
        $arrUpdate[] = 'entryBenefitOfferDt = now()';
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $this->benefitMember->getMemNo());
        $this->db->set_update_db(DB_MEMBER, $arrUpdate, 'memNo = ?', $arrBind);
    }

    /**
     * 가입 및 승인 혜택 받는 회원 정보 확인
     *
     * @throws Exception
     */
    protected function validateBenefitMember()
    {
        if (!isset($this->benefitMember)) {
            throw new Exception(__('회원가입 혜택 지급을 위한 정보가 없습니다.'));
        }
    }

    /**
     * 가입 및 승인 시 마일리지, 쿠폰 혜택 지급
     *
     */
    protected function benefitOffer()
    {
        $appFl = $this->benefitMember->getAppFl();
        StringUtils::strIsSet($appFl, 'n');
        if ($appFl == 'y') {
            if ($this->mileageGive->get('giveFl', 'n') == 'y') {
                $mileage = \App::load('Component\\Mileage\\Mileage');
                $this->mileage = $mileage;
                $this->mileage->setIsTran(false);
                $this->benefitMileageJoin();
                $this->benefitMileageMailingFlag();
                $this->benefitMileageSmsFlag();
                $this->benefitRegisterRecommender();
            }

            $this->saveAutoCouponByJoin();
            $this->updateEntryBenefitOfferDateTime();
        } else {
            $logger = \App::getInstance('logger');
            $logger->info('This member is disapproval status. You are not eligible for join benefits.');
        }
    }

    /**
     * 회원 정보 수정에서 추천인 등록시 혜택 지급
     *
     * @param array $member 회원 정보
     *
     * @throws Exception
     */
    public function benefitMoidfyRecommender($member)
    {
        if (!isset($this->benefitMember)) {
            if (empty($member) || is_array($member) == false) {
                throw new Exception(__('혜택 지급을 위한 회원정보가 없습니다.'));
            }
            $vo = new \Component\Member\MemberVO($member);
            $this->setBenefitMember($vo);
        }

        if (!isset($this->mileage)) {
            $mileage = \App::load('Component\\Mileage\\Mileage');
            $this->mileage = $mileage;
            $this->mileage->setIsTran(false);
        }

        if ($this->mileageGive->get('giveFl', 'n') == 'y') {
            $this->benefitRegisterRecommender('modify');
        }
    }
}
