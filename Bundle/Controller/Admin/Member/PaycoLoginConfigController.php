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

namespace Bundle\Controller\Admin\Member;


use Component\Policy\PaycoLoginPolicy;
use Component\Mall\Mall;
use Component\Policy\JoinItemPolicy;
use Exception;
use Framework\Debug\Exception\AlertBackException;

/**
 * Class PaycoLoginConfigController
 * @package Bundle\Controller\Admin\Payco
 * @author  yjwee
 */
class PaycoLoginConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        try {
            $this->callMenu('member', 'sns', 'loginConfig');

            $mallSno = gd_isset(\Request::get()->get('mallSno'), 1);
            $this->setData('mallInputDisp', $mallSno == 1 ? false : true);
            $mall = new Mall();

            $mallList = $mall->getListByUseMall();
            if (count($mallList) > 1) {
                $this->setData('mallCnt', count($mallList));
                $this->setData('mallList', $mallList);
                $this->setData('mallSno', $mallSno);
                if ($mallSno > 1) {
                    $defaultData = gd_policy('basic.info', DEFAULT_MALL_NUMBER);
                    foreach ($defaultData as $key => $value) {
                        if (in_array($key, Mall::GLOBAL_MALL_BASE_INFO) === true) $data[$key] = $value;
                    }

                    $disabled = ' disabled = "disabled"';
                    $readonly = ' readonly = "readonly"';
                    $this->setData('disabled', $disabled);
                    $this->setData('readonly', $readonly);
                }
            }

            if($mallSno > 1){
                $policy = gd_policy(PaycoLoginPolicy::KEY, $mallSno);
            }else {
                $policy = gd_policy(PaycoLoginPolicy::KEY);
            }

            gd_isset($policy['useFl'], 'n');
            gd_isset($policy['simpleLoginFl'],'y');
            gd_isset($policy['baseInfo'],'y');
            gd_isset($policy['supplementInfo'],'n');
            gd_isset($policy['additionalInfo'], 'n');
            $checked['useFl'][$policy['useFl']] = 'checked="checked"';
            $checked['simpleLoginFl'][$policy['simpleLoginFl']] = 'checked="checked"';

            //회원가입항목정보
            $policyService = new JoinItemPolicy();
            $joinItemPolicy = $policyService->getJoinPolicyDisplay($mallSno);
            $policy['items'] = $joinItemPolicy;

            $this->setData('checked', $checked);
            $this->setData('data', $policy);
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
