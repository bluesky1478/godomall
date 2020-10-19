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

use Component\Policy\WonderLoginPolicy;
use Exception;
use Framework\Debug\Exception\AlertBackException;

/**
 * Class WonderLoginConfigController
 * @package Bundle\Controller\Admin\Wonder
 */
class WonderLoginConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            $this->callMenu('member', 'sns', 'wonderLoginConfig');

            $policy = gd_policy(WonderLoginPolicy::KEY);
            $policy['useFl'] = strtolower($policy['useFl']);
            gd_isset($policy['useFl'], 'f'); // first: 아무것도 설정 안한 상태(최초)
            $checked['useFl'][$policy['useFl']] = 'checked="checked"';

            if($policy['useFl'] == 'f') {
                $checked['useFl']['n'] = 'checked="checked"';
            }

            $this->setData('checked', $checked);
            $this->setData('data', $policy);

        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
