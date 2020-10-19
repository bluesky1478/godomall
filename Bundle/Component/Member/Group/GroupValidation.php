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


/**
 * Class GroupValidation
 * @package Bundle\Component\Member\Group
 * @author  yjwee
 */
class GroupValidation extends \Component\AbstractComponent
{
    /** @var  \Component\Member\Group\GroupDomain $domain 검증할 등급 */
    private $domain;
    /** @var  \Component\Member\Group\GroupDomain $targetGroup 검증할 등급과 개별 비교할 등급 */
    private $targetGroup;
    /** @var  array[\Component\Member\Group\GroupDomain] $targetGroups 검증할 등급과 비교할 등급 */
    private $targetGroups = [];

    /**
     * 검증할 등급 설정
     *
     * @param \Component\Member\Group\GroupDomain $domain
     */
    public function setDomain(\Component\Member\Group\GroupDomain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * $domain 에 설정된 등급을 검증
     *
     */
    public function validateStandard()
    {
        $this->validateFigure();
        $this->validatePoint();

        if (count($this->targetGroups) < 1) {
            $this->db->strOrder = 'apprFigureOrderPriceMore DESC, apprFigureOrderRepeat DESC';
            $this->targetGroups = $this->getDataByTable(DB_MEMBER_GROUP, null, 'sno!=1', '*', true);
        }

        $this->validateOverlapStandard();
    }

    /**
     * 등급평가 기준구간 중첩여부 확인
     *
     */
    public function validateOverlapStandard()
    {
        foreach ($this->targetGroups as $index => $group) {
            $this->targetGroup = $group;
            if (is_array($group)) {
                $this->targetGroup = new \Component\Member\Group\GroupDomain($group);
            }

            if ($this->domain->getSno() == $this->targetGroup->getSno()) {
                continue;
            }
            $this->checkOverlapStandard();
        }
    }

    /**
     * validateFigure
     *
     */
    public function validateFigure()
    {
        if ($this->domain->isApprFigureOrderPriceFl()) {
            $this->_validateOrderPrice();
        }
        if ($this->domain->isApprFigureOrderRepeatFl()) {
            $this->_validateOrderRepeat();
        }
        if ($this->domain->isApprFigureReviewRepeatFl()) {
            $this->_validateReviewRepeat();
        }
    }

    private function _validateOrderPrice()
    {
        if ((!$this->domain->isApprFigureOrderPriceMore() || !$this->domain->isApprFigureOrderPriceBelow())) {
            throw new \Exception(__('주문금액은 1만원 이상이어야 합니다.'));
        }
        if (!$this->domain->greaterThanFigureOrderPrice()) {
            throw new \Exception(__('쇼핑몰 전체실적 주문금액 범위를 다시 입력해 주세요.'));
        }
        if ($this->domain->isApprFigureOrderPriceMoreMobile() || $this->domain->isApprFigureOrderPriceBelowMobile()) {
            if (!$this->domain->greaterThanFigureOrderPriceMobile()) {
                throw new \Exception(__('모바일샵 추가실적 주문금액 범위를 다시 입력해 주세요.'));
            }
        }
    }

    private function _validateOrderRepeat()
    {
        if (!$this->domain->isApprFigureOrderRepeat()) {
            throw new \Exception(__('상품주문건수는 1회 이상이어야 합니다.'));
        }
    }

    private function _validateReviewRepeat()
    {
        if (!$this->domain->isApprFigureReviewRepeat()) {
            throw new \Exception(__('주문상품후기는 1회 이상이어야 합니다.'));
        }
    }

    /**
     * validatePoint
     *
     * @throws \Exception
     */
    public function validatePoint()
    {
        if ($this->domain->isDefaultGroup()) {
            if ($this->domain->isApprPointMore() || $this->domain->isApprPointBelow()) {
                throw new \Exception(__('기준등급 실적 점수는 0점 이어야 합니다.'));
            }
            if ($this->domain->isApprPointMoreMobile() || $this->domain->isApprPointBelowMobile()) {
                throw new \Exception(__('기준등급 모바일샵 추가실적 점수는 0점 이어야 합니다.'));
            }
        } else {
            if (!$this->domain->isApprPointMore() || !$this->domain->isApprPointBelow()) {
                throw new \Exception(__('실적 점수는 1점 이상이어야 합니다.'));
            }
            if (!$this->domain->greaterThanPoint()) {
                throw new \Exception(__('쇼핑몰 전체실적 점수 범위를 다시 입력해 주세요.'));
            }
            if ($this->domain->isApprPointMoreMobile() || $this->domain->isApprPointBelowMobile()) {
                if (!$this->domain->greaterThanPointMobile()) {
                    throw new \Exception(__('모바일샵 추가실적 점수 범위를 다시 입력해 주세요.'));
                }
            }
        }
    }

    public function checkOverlapStandard()
    {
        $exceptionMessage = [];
        $isOrderPrice = $isOrderRepeat = $isReviewRepeat = $isPoint = true;
        if ($this->domain->isApprFigureOrderPriceFl()) {
            if (!$this->checkOrderPriceRange()) {
                $isOrderPrice = false;
                $exceptionMessage[] = __('주문금액 기준이 중복입니다.');
            }
        }
        if ($this->domain->isApprFigureOrderRepeatFl()) {
            if (!$this->checkOrderRepeat()) {
                $isOrderRepeat = false;
                $exceptionMessage [] = __('상품주문건수 기준이 중복입니다.');
            }
        }
        if ($this->domain->isApprFigureReviewRepeatFl()) {
            if (!$this->checkReviewRepeat()) {
                $isPoint = false;
                $exceptionMessage [] = __('주문상품후기 기준이 중복입니다.');
            }
        }
        if ($this->domain->isApprPointMore() || $this->domain->isApprPointBelow()) {
            if (!$this->checkPointRange()) {
                $isReviewRepeat = false;
                $exceptionMessage [] = __('실적점수제 기준이 중복입니다.');
            }
        }

        if (!($isOrderPrice && $isOrderRepeat && $isReviewRepeat && $isPoint)) {
            throw new \Exception(implode('\n', $exceptionMessage));
        }
    }

    public function checkOrderPriceRange()
    {
        $targetMore = $this->targetGroup->getApprFigureOrderPriceMore();
        $targetBelow = $this->targetGroup->getApprFigureOrderPriceBelow();
        $domainMore = $this->domain->getApprFigureOrderPriceMore();
        $domainBelow = $this->domain->getApprFigureOrderPriceBelow();

        $isMore = ($domainMore < $targetMore) || ($domainMore >= $targetBelow);
        $isBelow = ($domainBelow <= $targetMore) || ($domainBelow > $targetBelow);

        return $isMore && $isBelow;
    }

    public function checkPointRange()
    {
        $targetMore = $this->targetGroup->getApprPointMore();
        $targetBelow = $this->targetGroup->getApprPointBelow();
        $domainMore = $this->domain->getApprPointMore();
        $domainBelow = $this->domain->getApprPointBelow();

        $isMore = ($domainMore < $targetMore) || ($domainMore >= $targetBelow);
        $isBelow = ($domainBelow <= $targetMore) || ($domainBelow > $targetBelow);

        return $isMore && $isBelow;
    }

    public function checkOrderRepeat()
    {
        return $this->targetGroup->getApprFigureOrderRepeat() != $this->domain->getApprFigureOrderRepeat();
    }

    public function checkReviewRepeat()
    {
        return $this->targetGroup->getApprFigureReviewRepeat() != $this->domain->getApprFigureReviewRepeat();
    }

    public function setTargetGroup($targetGroup)
    {
        $this->targetGroup = $targetGroup;
    }

    public function setTargetGroups($targetGroups)
    {
        $this->targetGroups = $targetGroups;
    }
}
