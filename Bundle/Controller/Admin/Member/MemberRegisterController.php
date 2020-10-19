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

use Component\Member\Group\Util as GroupUtil;
use Component\Member\MemberVO;
use Component\Member\Util\MemberUtil;

/**
 * Class 회원 등록
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MemberRegisterController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     */
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */

        $this->callMenu('member', 'member', 'view');

        $joinField = MemberUtil::getJoinField();

        /**
         * 회원가입 기본 설정
         */
        $vo = new MemberVO();
        $vo->setMemberFl('personal');
        $vo->setAppFl('y');
        $vo->setExpirationFl('1');
        $vo->setGroupSno(GroupUtil::getDefaultGroupSno());
        $member = $vo->toArray();
        $member['cellPhoneCountryCode'] = 'kr';
        $member['phoneCountryCode'] = 'kr';

        $this->setData('mode', 'register');
        $this->setData('joinField', $joinField);
        $this->setData('htmlExtra', MemberUtil::makeExtraField());
        $this->setData('data', $member);
        $this->setData('checked', $vo->toChecked());
    }
}
