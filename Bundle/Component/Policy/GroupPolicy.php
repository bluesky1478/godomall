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

use Component\Validator\Validator;
use Framework\Utility\DateTimeUtils;

/**
 * Class GroupPolicy
 * @package Bundle\Component\Policy
 * @author  yjwee
 */
class GroupPolicy extends \Component\Policy\Policy
{
    const KEY = 'member.group';
    protected $currentPolicy;

    /**
     * @inheritDoc
     */
    public function __construct($storage = null)
    {
        parent::__construct($storage);
        $this->currentPolicy = $this->getValue(self::KEY);
    }

    /**
     * 등급 쇼핑몰페이지 노출이름 저장
     *
     * @throws \Exception 저장 실패
     */
    public function saveGroupLabel()
    {
        $policy = $this->getValue(self::KEY);
        $policy['grpLabel'] = \Request::post()->get('grpLabel', '등급');
        if ($this->setValue(self::KEY, $policy) !== true) {
            throw new \Exception(__('등급 라벨 저장에 실패하였습니다.'));
        }
    }

    /**
     * 등급 쇼핑몰페이지 노출이름 저장
     *
     * @throws \Exception 저장 실패
     */
    public function saveCouponCondition()
    {

        $policyTmp['couponConditionComplete'] = \Request::post()->get('couponConditionComplete', '');
        $policyTmp['couponConditionCompleteChange'] = \Request::post()->get('couponConditionCompleteChange', '');
        $policyTmp['couponConditionManual'] = \Request::post()->get('couponConditionManual', '');
        $policyTmp['couponConditionExcel'] = \Request::post()->get('couponConditionExcel', '');
        $policyTmp['couponConditionExcelChange'] = \Request::post()->get('couponConditionExcelChange', '');

        foreach($policyTmp as $key => $value){
            if($value == 'true'){
                $policyMerge[$key] = 'y';
            }else{
                $policyMerge[$key] = '';
            }
        }

        $policy = $this->getValue(self::KEY);
        $policy = array_merge($policy, $policyMerge);

        if ($this->setValue(self::KEY, $policy) !== true) {
            throw new \Exception(__('등급 쿠폰 발급 설정 저장에 실패하였습니다.'));
        }
    }

    /**
     * 회원등급 자동평가 후 평가일 저장
     *
     * @param memberGroup 회원 등급 기본 설정
     * @throws \Exception 저장 실패
     */
    public function saveAutoAppraisalDateTime($memberGroup = null)
    {
        $logger = \App::getInstance('logger'); // 로그 추가
        $logger->channel('memberGroup')->info(__METHOD__ . ' memberGroup: ' , [$memberGroup]);
        if (empty($memberGroup)) {
            // gd_policy로 처리
            $policy = gd_policy('member.group');
            $policy['autoAppraisalDateTime'] = DateTimeUtils::dateFormat('Y-m-d', 'now');
            if ($this->setValue(self::KEY, $policy) !== true) {
                $logger->channel('memberGroup')->error(__METHOD__ . ' SAVE FAILED. -1: ' , [$policy]);
                throw new \Exception(__('자동등급 평가일 저장에 실패하였습니다.'));
            }
            $logger->channel('memberGroup')->info(__METHOD__ . ' SAVE SUCCESSFULLY: ' , [$policy]);
        } else {
            $memberGroup['autoAppraisalDateTime'] = DateTimeUtils::dateFormat('Y-m-d', 'now');
            if ($this->setValue(self::KEY, $memberGroup) !== true) {
                $logger->channel('memberGroup')->error(__METHOD__ . ' SAVE FAILED. -2: ' , [$memberGroup]);
                throw new \Exception(__('자동등급 평가일 저장에 실패하였습니다.'));
            }
            $logger->channel('memberGroup')->error(__METHOD__ . '  DATA(memberGroup) IS NULL: ' , [$memberGroup]);
        }
    }

    /**
     * 회원등급 평가방법 설정 저장
     *
     * @throws \Exception 저장 실패
     */
    public function saveAppraisal()
    {
        $policy = $this->getValue(self::KEY);
        if (\Request::post()->get('apprSystem') === 'figure') {
            $this->setPostByPointPolicy('appraisalPointOrderPriceFl', $policy);
            $this->setPostByPointPolicy('appraisalPointOrderRepeatFl', $policy);
            $this->setPostByPointPolicy('appraisalPointReviewRepeatFl', $policy);
            $this->setPostByPointPolicy('appraisalPointLoginRepeatFl', $policy);
            $this->setPostByPointPolicy('apprPointOrderPriceUnit', $policy);
            $this->setPostByPointPolicy('apprPointOrderPricePoint', $policy);
            $this->setPostByPointPolicy('apprPointOrderRepeatPoint', $policy);
            $this->setPostByPointPolicy('apprPointReviewRepeatPoint', $policy);
            $this->setPostByPointPolicy('apprPointLoginRepeatPoint', $policy);
            $this->setPostByPointPolicy('apprPointOrderPriceUnitMobile', $policy);
            $this->setPostByPointPolicy('apprPointOrderPricePointMobile', $policy);
            $this->setPostByPointPolicy('apprPointOrderRepeatPointMobile', $policy);
            $this->setPostByPointPolicy('apprPointReviewRepeatPointMobile', $policy);
            $this->setPostByPointPolicy('apprPointLoginRepeatPointMobile', $policy);
        } elseif (\Request::post()->get('apprSystem') === 'point') {
            if (\Request::post()->has('appraisalPointOrderPriceFl') === false) {
                $policy['appraisalPointOrderPriceFl'] = 'n';
                $this->setPostByPointPolicy('apprPointOrderPriceUnit', $policy);
                $this->setPostByPointPolicy('apprPointOrderPricePoint', $policy);
                $this->setPostByPointPolicy('apprPointOrderPriceUnitMobile', $policy);
                $this->setPostByPointPolicy('apprPointOrderPricePointMobile', $policy);
            }
            if (\Request::post()->has('appraisalPointOrderRepeatFl') === false) {
                $policy['appraisalPointOrderRepeatFl'] = 'n';
                $this->setPostByPointPolicy('apprPointOrderRepeatPoint', $policy);
                $this->setPostByPointPolicy('apprPointOrderRepeatPointMobile', $policy);
            }
            if (\Request::post()->has('appraisalPointReviewRepeatFl') === false) {
                $policy['appraisalPointReviewRepeatFl'] = 'n';
                $this->setPostByPointPolicy('apprPointReviewRepeatPoint', $policy);
                $this->setPostByPointPolicy('apprPointReviewRepeatPointMobile', $policy);
            }
            if (\Request::post()->has('appraisalPointLoginRepeatFl') === false) {
                $policy['appraisalPointLoginRepeatFl'] = 'n';
                $this->setPostByPointPolicy('apprPointLoginRepeatPoint', $policy);
                $this->setPostByPointPolicy('apprPointLoginRepeatPointMobile', $policy);
            }
        }
        $policy = array_merge($policy, \Request::post()->all());
        $this->validate($policy);
        if ($this->setValue(self::KEY, $policy) !== true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * post 배열에 정책 데이터를 설정
     *
     * @param string $name   post attribute name
     * @param array  $policy group policy
     */
    protected function setPostByPointPolicy($name, $policy)
    {
        \Request::post()->set($name, $policy[$name]);
    }

    /**
     * 회원등급 평가방법 설정 검증
     *
     * @param array $policy
     *
     * @throws \Exception 검증 실패
     */
    protected function validate(array &$policy)
    {
        $validator = new Validator();
        $validator->setIgnoreIssetByAct(true);
        $validator->init();
        $validator->add('grpLabel', '');
        $validator->add('automaticFl', 'yn');
        $validator->add('apprSystem', '');
        $validator->add('appraisalPointOrderPriceFl', 'yn');
        $validator->add('appraisalPointOrderRepeatFl', 'yn');
        $validator->add('appraisalPointReviewRepeatFl', 'yn');
        $validator->add('appraisalPointLoginRepeatFl', 'yn');
        $validator->add('apprPointOrderPriceUnit', '');
        $validator->add('apprPointOrderPricePoint', 'number');
        $validator->add('apprPointOrderRepeatPoint', 'number');
        $validator->add('apprPointReviewRepeatPoint', 'number');
        $validator->add('apprPointLoginRepeatPoint', 'number');
        $validator->add('apprPointOrderPriceUnitMobile', '');
        $validator->add('apprPointOrderPricePointMobile', '');
        $validator->add('apprPointOrderRepeatPointMobile', 'number');
        $validator->add('apprPointReviewRepeatPointMobile', 'number');
        $validator->add('apprPointLoginRepeatPointMobile', 'number');
        $validator->add('calcPeriodFl', 'yn');
        $validator->add('calcPeriodBegin', '');
        $validator->add('calcPeriodMonth', '');
        $validator->add('calcCycleMonth', '');
        $validator->add('calcCycleDay', '');
        $validator->add('calcKeep', '');
        $validator->add('autoAppraisalDateTime', 'date');
        $validator->add('couponConditionComplete', 'yn');
        $validator->add('couponConditionCompleteChange', 'yn');
        $validator->add('couponConditionManual', 'yn');
        $validator->add('couponConditionExcel', 'yn');
        $validator->add('couponConditionExcelChange', 'yn');
        $validator->add('downwardAdjustment', 'yn');
        if ($validator->act($policy, true) === false) {
            throw new \Exception(implode("\n", $validator->errors));
        }

        if ($policy['appraisalPointOrderPriceFl'] == 'y') {
            if (!($policy['apprPointOrderPriceUnit'] > 0 && $policy['apprPointOrderPricePoint'] > 0)) {
                throw new \Exception(__('주문금액 실적금액 1원 이상, 실적점수 1점 이상이어야 합니다.'));
            }
        }
        if ($policy['appraisalPointOrderRepeatFl'] == 'y' && $policy['apprPointOrderRepeatPoint'] < 1) {
            throw new \Exception(__('상품주문건수 실적점수를 1점 이상이어야 합니다.'));
        }
        if ($policy['appraisalPointReviewRepeatFl'] == 'y' && $policy['apprPointReviewRepeatPoint'] < 1) {
            throw new \Exception(__('주문상품후기 실적점수를 1점 이상이어야 합니다.'));
        }
        if ($policy['appraisalPointLoginRepeatFl'] == 'y' && $policy['apprPointLoginRepeatPoint'] < 1) {
            throw new \Exception(__('로그인 횟수 실적점수를 1점 이상이어야 합니다.'));
        }
    }

}
