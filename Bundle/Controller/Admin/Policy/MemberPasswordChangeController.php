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

/**
 * Class 관리자-기본설정-회원정책-비밀번호 변경안내 설정 컨트롤러
 * @package Bundle\Controller\Admin\Policy
 * @author  yjwee
 */
class MemberPasswordChangeController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('policy', 'management', 'passwordChange');

        $data = gd_policy('member.passwordChange');
        gd_isset($data['managerFl'], 'y');
        gd_isset($data['memberFl'], 'y');
        gd_isset($data['guidePeriod'], '6');
        gd_isset($data['guidePeriodItem'], 'month');
        gd_isset($data['reGuidePeriod'], '1');
        gd_isset($data['reGuidePeriodItem'], 'month');

        $checked['managerFl'][$data['managerFl']] = $checked['memberFl'][$data['memberFl']] = 'checked="checked"';
        $selected['guidePeriodItem'][$data['guidePeriodItem']] = $selected['reGuidePeriodItem'][$data['reGuidePeriodItem']] = 'selected="selected"';

        $this->setData('data', $data);
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);

//        debug($data);
    }
}
