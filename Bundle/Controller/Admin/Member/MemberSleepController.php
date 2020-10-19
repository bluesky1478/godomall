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

/**
 * Class MemberSleepController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MemberSleepController extends \Controller\Admin\Controller
{
    /**
     * index 휴면회원 정책
     *
     */
    public function index()
    {
        /**
         *   page navigation
         */
        $this->callMenu('member', 'member', 'sleepConfig');

        $data = gd_policy('member.sleep');
        $phone = gd_get_auth_cellphone_info();
        $ipin = gd_policy('member.ipin');

        gd_isset($phone['useFl'], 'n');
        gd_isset($ipin['useFl'], 'n');
        gd_isset($data['checkPhone'], 'n');
        gd_isset($data['checkEmail'], 'n');
        gd_isset($data['authSms'], 'n');
        gd_isset($data['authEmail'], 'n');
        gd_isset($data['authIpin'], 'n');
        gd_isset($data['authRealName'], 'n');
        gd_isset($data['wakeType'], 'normal');
        gd_isset($data['phoneUseFl'], $phone['useFl']);
        gd_isset($data['ipinUseFl'], $ipin['useFl']);
        gd_isset($data['initMemberGroup'], 'n');
        gd_isset($data['initMileage'], 'wake');

        /**
         *   set checkbox, select property
         */
        $checked['initMileage'][$data['initMileage']] = $checked['checkPhone'][$data['checkPhone']] = $checked['checkEmail'][$data['checkEmail']] = $checked['authSms'][$data['authSms']] = $checked['authEmail'][$data['authEmail']] = $checked['authIpin'][$data['authIpin']] = $checked['authRealName'][$data['authRealName']] = $checked['wakeType'][$data['wakeType']] = $checked['initMemberGroup'][$data['initMemberGroup']] = 'checked="checked"';
        $disabled['phoneUseFl'][$data['phoneUseFl']] = $disabled['ipinUseFl'][$data['ipinUseFl']] = 'disabled="true"';

        /**
         *   set view data
         */
        $this->setData('data', $data);
        $this->setData('checked', $checked);
        $this->setData('disabled', $disabled);
    }
}
