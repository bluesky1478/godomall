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
namespace Bundle\Controller\Admin\Member;

/**
 * Class 회원가입항목설정
 * @package Controller\Admin\Policy
 * @author  yjwee
 */
class MemberJoinitemController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $this->callMenu('member', 'member', 'joinItem');

        $request = \App::getInstance('request');
        $mallSno = $request->get()->get('mallSno', 1);

        $policyService = new \Component\Policy\JoinItemPolicy();
        $policy = $policyService->getPolicy($mallSno);

        // 체크처리 항목 정의 (단, 추가 정보(ex)는 번외 checked 처리함)
        $policy['items'] = array_filter($policy, function ($v, $k) {
            if (preg_match('/^ex[1-6]+$/', $k)) return false;
            else if ($k == 'mode' || $k == 'mallSno') return false;
            return true;
        }, ARRAY_FILTER_USE_BOTH);
        $policy['items'] = json_encode($policy['items']);

        /** @var \Bundle\Component\Code\Code $code */
        $code = \App::load('\\Component\\Code\\Code',$mallSno);
        $policy['interestCnt'] = $code->codeFetch('getCodeCount', '01001');
        $policy['jobCnt'] = $code->codeFetch('getCodeCount', '01002');

        /** set view data */
        $this->setData('data', $policy);
        $this->setData('mall', ['mallSno' => $mallSno]);
    }
}
