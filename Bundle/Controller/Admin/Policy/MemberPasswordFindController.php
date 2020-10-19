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

namespace Bundle\Controller\Admin\Policy;

use Component\Member\Member;

/**
 * Class 비밀번호 찾기 설정
 * @package Bundle\Controller\Admin\Policy
 * @author yjwee
 */
class MemberPasswordFindController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('policy', 'management', 'passwordFind');

        $data = gd_policy('member.passwordFind');
        gd_isset($data['emailFl'], 'n');
        gd_isset($data['smsFl'], 'n');

        $checked['emailFl'][$data['emailFl']] = $checked['smsFl'][$data['smsFl']] = 'checked="checked"';

        $this->setData('data', $data);
        $this->setData('checked', $checked);
        $this->addScript(['member.js']);
    }
}
