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

namespace Bundle\Controller\Admin\Share;

use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;


/**
 * Class 관리자 회원 추가 팝업
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class PopupAddMemberController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $request = \App::getInstance('request');
        $globals = \App::getInstance('globals');
        // sendMode 정보 (혹시라도 없다면 기본값은 mail)
        if (!$request->get()->has('sendMode')) {
            $request->get()->set('sendMode', 'mail');
        }
        $sendMode = htmlspecialchars($request->get()->get('sendMode'));
        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_blank_noiframe.php');
        $this->addScript(['member.js']);
        $this->setData('sendMode', $sendMode);
        if (!$request->get()->has('mallSno')) {
            $request->get()->set('mallSno', '');
        }
        if ($request->get()->has('maillingFl') === false) {
            $request->get()->set('maillingFl', 'y');
        }
        if ($request->get()->has('smsFl') === false) {
            $request->get()->set('smsFl', 'y');
        }

        $getParams = $request->get()->all();
        $getParams['sendMode'] = htmlspecialchars($getParams['sendMode'], ENT_QUOTES, 'UTF-8');
        $checked = \Component\Member\Util\MemberUtil::checkedByMemberListSearch($getParams);
        $selected = \Component\Member\Util\MemberUtil::selectedByMemberListSearch($getParams);
        $addSearchResultFl = $request->get()->get('addSearchResultFl', 'n');
        $groups = \Component\Member\Group\Util::getGroupName();
        $globalByGlobals = $globals->get('gGlobal');
        // 글로벌 설정 지원용 스킨으로 변경함.
        $this->getView()->setPageName('share/popup_add_member2');
        $this->getView()->setDefine('layout', 'layout_blank.php');
        $this->setData('search', $getParams);
        $this->setData('groups', $groups);
        $this->setData('combineSearch', \Component\Member\Member::getCombineSearchSelectBox());
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
        $this->setData('addSearchResultFl', $addSearchResultFl);
        $this->setData('sendMode', $request->get()->get('sendMode', 'mail'));
        StringUtils::strIsSet($groups, []);
        $this->setData('groupNames', json_encode($groups));
        StringUtils::strIsSet($globalByGlobals['mallList'], []);
        $this->setData('mallList', json_encode($globalByGlobals['mallList']));
        StringUtils::strIsSet($globalByGlobals['isUse'], false);
        $this->setData('useGlobal', $globalByGlobals['isUse'] ? 'use' : 'none');
        $this->setData('paycoIcon', SkinUtils::getThirdPartyIconWebPath('payco'));
        $this->setData('facebookIcon', SkinUtils::getThirdPartyIconWebPath('facebook'));
        $this->addScript(['member.js']);
    }
}
