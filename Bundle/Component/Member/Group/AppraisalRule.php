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


use App;
use Component\AbstractComponent;
use Exception;
use Framework\Object\SimpleStorage;

/**
 * 회원등급 평가방법 설정
 * @package Bundle\Component\Member\Group
 * @author  yjwee
 * @deprecated
 */
class AppraisalRule extends \Component\AbstractComponent
{
    /** @var  \Bundle\Component\Policy\Policy */
    private $policy;

    /** @var \Bundle\Component\Member\MemberGroup */
    private $memberGroup;

    /** @var  SimpleStorage */
    private $requestStorage;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->policy = App::load('\\Component\\Policy\\Policy');
        $this->memberGroup = App::load('\\Component\\Member\\MemberGroup');
    }

    /**
     * 회원등급 평가 규칙 데이터 설정
     *
     * @param SimpleStorage $storage
     *
     * @deprecated
     */
    public function setRequestStorage(SimpleStorage $storage)
    {
        $this->requestStorage = $storage;
    }

    /**
     * 회원등급 평가방법 설정 레이어 저장 함수
     * 실적수치제의 등급별 기준치를 제외한 정보 저장한다.
     * @throws Exception
     * @deprecated
     * @uses \Bundle\Component\Policy\GroupPolicy
     */
    public function savePolicyRule()
    {
        $group = gd_policy('member.group');
        $group['automaticFl'] = $this->requestStorage->get('automaticFl', 'n');
        $group['apprSystem'] = $this->requestStorage->get('apprSystem');
        $group['apprPointTitle'] = $this->requestStorage->get('apprPointTitle');
        $group['apprPointLabel'] = $this->requestStorage->get('apprPointLabel');
        // 실점 점수제 공통 설정
        if ($group['apprSystem'] == 'point') {
            $group['appraisalPointOrderPriceFl'] = $this->requestStorage->get('appraisalPointOrderPriceFl', 'n');
            $group['appraisalPointOrderRepeatFl'] = $this->requestStorage->get('appraisalPointOrderRepeatFl', 'n');
            $group['appraisalPointReviewRepeatFl'] = $this->requestStorage->get('appraisalPointReviewRepeatFl', 'n');
            $group['appraisalPointLoginRepeatFl'] = $this->requestStorage->get('appraisalPointLoginRepeatFl', 'n');
            $group['apprPointOrderPriceUnit'] = $this->requestStorage->get('apprPointOrderPriceUnit');
            $group['apprPointOrderPricePoint'] = $this->requestStorage->get('apprPointOrderPricePoint');
            $group['apprPointOrderRepeatPoint'] = $this->requestStorage->get('apprPointOrderRepeatPoint');
            $group['apprPointReviewRepeatPoint'] = $this->requestStorage->get('apprPointReviewRepeatPoint');
            $group['apprPointLoginRepeatPoint'] = $this->requestStorage->get('apprPointLoginRepeatPoint');
            $group['apprPointOrderPriceUnitMobile'] = $this->requestStorage->get('apprPointOrderPriceUnitMobile');
            $group['apprPointOrderPricePointMobile'] = $this->requestStorage->get('apprPointOrderPricePointMobile');
            $group['apprPointOrderRepeatPointMobile'] = $this->requestStorage->get('apprPointOrderRepeatPointMobile');
            $group['apprPointReviewRepeatPointMobile'] = $this->requestStorage->get('apprPointReviewRepeatPointMobile');
            $group['apprPointLoginRepeatPointMobile'] = $this->requestStorage->get('apprPointLoginRepeatPointMobile');
        }
        $group['calcPeriodFl'] = $this->requestStorage->get('calcPeriodFl');
        $group['calcPeriodBegin'] = $this->requestStorage->get('calcPeriodBegin');
        $group['calcPeriodMonth'] = $this->requestStorage->get('calcPeriodMonth');
        $group['calcCycleMonth'] = $this->requestStorage->get('calcCycleMonth');
        $group['calcCycleDay'] = $this->requestStorage->get('calcCycleDay');
        $group['calcKeep'] = $this->requestStorage->get('calcKeep');
        $this->policy->saveMemberGroup($group);
    }

    /**
     * 실적 수치제의 등급별 기준치를 저장하는 함수
     *
     * @param $params
     *
     * @throws Exception
     * @deprecated
     * @see \Bundle\Component\Member\Group\GroupService::saveGroupStandard
     */
    public function saveRule($params)
    {
        $arrSno = $params['sno'];
        if (!(isset($arrSno) && is_array($arrSno))) {
            throw new Exception(__('회원등급별 평가기준 정보가 없습니다.'));
        }
        $groups = [];
        foreach ($arrSno as $index => $sno) {
            $domain = new GroupDomain();
            $domain->setSno($sno);

            $domain->setApprFigureOrderPriceFl(gd_isset($params['apprFigureOrderPriceFl'][$sno]));
            $domain->setApprFigureOrderRepeatFl(gd_isset($params['apprFigureOrderRepeatFl'][$sno]));
            $domain->setApprFigureReviewRepeatFl(gd_isset($params['apprFigureReviewRepeatFl'][$sno]));

            $domain->setApprFigureOrderPriceMore(gd_isset($params['apprFigureOrderPriceMore'][$sno]));
            $domain->setApprFigureOrderPriceBelow(gd_isset($params['apprFigureOrderPriceBelow'][$sno]));
            $domain->setApprFigureOrderRepeat(gd_isset($params['apprFigureOrderRepeat'][$sno]));
            $domain->setApprFigureReviewRepeat(gd_isset($params['apprFigureReviewRepeat'][$sno]));
            $domain->setApprPointMore(gd_isset($params['apprPointMore'][$sno]));
            $domain->setApprPointBelow(gd_isset($params['apprPointBelow'][$sno]));

            $domain->setApprFigureOrderPriceMoreMobile(gd_isset($params['apprFigureOrderPriceMoreMobile'][$sno]));
            $domain->setApprFigureOrderPriceBelowMobile(gd_isset($params['apprFigureOrderPriceBelowMobile'][$sno]));
            $domain->setApprFigureOrderRepeatMobile(gd_isset($params['apprFigureOrderRepeatMobile'][$sno]));
            $domain->setApprFigureReviewRepeatMobile(gd_isset($params['apprFigureReviewRepeatMobile'][$sno]));
            $domain->setApprPointMoreMobile(gd_isset($params['apprPointMoreMobile'][$sno]));
            $domain->setApprPointBelowMobile(gd_isset($params['apprPointBelowMobile'][$sno]));

            $groups[] = $domain;
        }

        /** @var \Bundle\Component\Member\Group\GroupValidation $validation */
        $validation = App::load('\\Component\\Member\\Group\\GroupValidation');
        $validation->setTargetGroups($groups);
        foreach ($groups as $group) {
            $this->memberGroup->setAppraisalFl(gd_isset($params['apprSystem']));
            $validation->setDomain($group);
            $validation->validateStandard();
            $this->memberGroup->modifyGroupByAppraisalRule($group);
        }
    }
}
