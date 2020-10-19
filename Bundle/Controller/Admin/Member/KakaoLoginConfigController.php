<?php
/**
 * Created by PhpStorm.
 * User: godo
 * Date: 2018-08-10
 * Time: 오후 5:21
 */

namespace Bundle\Controller\Admin\Member;

use Bundle\Component\Policy\KakaoLoginPolicy;
use Component\Mall\Mall;
use Component\Policy\JoinItemPolicy;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Component\Policy\Policy;

/**
 * 카카오 아이디 로그인 설정
 * Class KakaoLoginConfigController
 * @package Bundle\Controller\Admin\Member
 */
class KakaoLoginConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            $this->callMenu('member', 'sns', 'kakaoLoginConfig');

            $mallSno = gd_isset(\Request::get()->get('mallSno'), 1);
            $this->setData('mallInputDisp', $mallSno == 1 ? false : true);
            $policy = gd_policy(KakaoLoginPolicy::KEY);

            gd_isset($policy['useFl'], 'n');
            gd_isset($policy['simpleLoginFl'],'y');
            gd_isset($policy['baseInfo'],'y');
            gd_isset($policy['businessInfo'],'n');
            gd_isset($policy['supplementInfo'],'n');
            gd_isset($policy['additionalInfo'], 'n');
            $checked['useFl'][$policy['useFl']] = 'checked="checked"';
            $checked['simpleLoginFl'][$policy['simpleLoginFl']] = 'checked="checked"';

            //회원가입항목정보
            $policyService = new JoinItemPolicy();
            $joinItemPolicy = $policyService->getJoinPolicyDisplay($mallSno);
            $policy['items'] = $joinItemPolicy;

            //도메인 정보
            $policyInfo = new Policy();
            $getPolicy = $policyInfo->getValue('basic.info', $mallSno);
            $policy['mallDomain'] = $getPolicy['mallDomain'];

            $this->setData('checked', $checked);
            $this->setData('data', $policy);
        }catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }

}