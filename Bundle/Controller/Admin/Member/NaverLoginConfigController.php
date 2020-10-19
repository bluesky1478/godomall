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


use Component\Policy\NaverLoginPolicy;
use Component\Policy\JoinItemPolicy;;
use Exception;
use Framework\Debug\Exception\AlertBackException;

/**
 * Class NaverLoginConfigController
 * @package Bundle\Controller\Admin\Naver
 */
class NaverLoginConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        try {
            $this->callMenu('member', 'sns', 'naverLoginConfig');

            $policy = gd_policy(NaverLoginPolicy::KEY);
            gd_isset($policy['useFl'], 'f'); // first: 아무것도 안한 상태(최초)

            $checked['useFl'][$policy['useFl']] = 'checked="checked"';
            $checked['simpleLoginFl'][$policy['simpleLoginFl']] = 'checked="checked"';

            if($policy['useFl'] == 'f') {
                $checked['useFl']['n'] = 'checked="checked"';
            }

            // 카테고리 관련
            $naverPolicy = new NaverLoginPolicy();
            $categoryArr = $naverPolicy->getCategory();
            $this->setData('category', $categoryArr);

            // 이미지 관련
            $imageURL = '';
            if($policy['useFl'] != 'f') {
                $imageURL = $policy['imageURL'] ? $policy['imageURL'] : '/admin/gd_share/img/skin_noimg.jpg';
                $imageURLPrint = '<img src="' . $policy['imageURL'] . '" class="service-image" />';
            }

            $this->setData('imageURL', $imageURL);
            $this->setData('imageURLPrint', $imageURLPrint);

            //일반 회원가입 선택시 회원가입 항목값 호출위한 mallSno, joinItemPolicy 설정
            $policyService = new JoinItemPolicy();
            $joinItemPolicy = $policyService->getJoinPolicyDisplay(gd_isset(\Request::get()->get('mallSno'), 1));
            $policy['items'] = $joinItemPolicy;

            $this->setData('checked', $checked);
            $this->setData('data', $policy);

        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
