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

use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;

/**
 * Class Sms080ConfigController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class Sms080ConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('member', 'sms', 'sms080Config');
        $policy = ComponentUtils::getPolicy('sms.sms080');
        StringUtils::strIsSet($policy['status'], '');
        StringUtils::strIsSet($policy['rejectNumber'], '000-0000-0000');
        StringUtils::strIsSet($policy['use'], 'n');
        StringUtils::strIsSet($policy['date'], '0000-00-00 00:00:00');
        if ($policy['status'] != 'O') { // 개통상태가 아니면 사용안함 처리
            $policy['use'] = 'n';
        }
        $checked['use'][$policy['use']] = 'checked="checked"';
        $this->setData('checked', $checked);
        $this->setData('policy', $policy);
    }
}
