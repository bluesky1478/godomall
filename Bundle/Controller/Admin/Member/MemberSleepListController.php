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

use Framework\Utility\DateTimeUtils;
use Framework\Utility\SkinUtils;
/**
 * Class 관리자-회원-휴면회원 관리리스트 조회 컨트롤러
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MemberSleepListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('member', 'member', 'sleep');
        $request = \App::getInstance('request');
        $globals = \App::getInstance('globals');
        if (!$request->get()->has('sleepDt')) {
            $request->get()->set('sleepDt', DateTimeUtils::getBetweenDateString('-6days'));
        }
        if (!$request->get()->has('mallSno')) {
            $request->get()->set('mallSno', '');
        }
        if (!$request->get()->has('page')) {
            $request->get()->set('page', 1);
        }
        if (!$request->get()->has('pageNum')) {
            $request->get()->set('pageNum', 10);
        }

        // ISMS 인증관련 추가
        if (array_search($request->get()->get('pageNum'), SkinUtils::getPageViewCount()) === false) {
            $request->get()->set('pageNum', 10);
        }

        $memberSleep = \App::getInstance('MemberSleep');
        if (!is_object($memberSleep)) {
            $memberSleep = new \Component\Member\MemberSleep();
        }
        $getData = $memberSleep->lists($request->get()->all(), $request->get()->get('page'), $request->get()->get('pageNum'));

        $pageObject = new \Component\Page\Page($request->get()->get('page'),0,0,$request->get()->get('pageNum'));
        $pageObject->setCache(true);
        if($pageObject->hasRecodeCache('total') === false) {
            $total = $memberSleep->getCounBySearch($request->get()->all());
            $pageObject->setTotal($total);
        }
        if($pageObject->hasRecodeCache('amount') === false) {
            $amount = $memberSleep->getCount(DB_MEMBER_SLEEP);
            $pageObject->setAmount($amount);
        }

        $pageObject->setUrl($request->getQueryString());
        $pageObject->setPage();
        $checked['mallSno'][$request->get()->get('mallSno')] = 'checked="checked"';
        $this->setData('checked', $checked);
        $this->setData('page', $pageObject);
        $this->setData('data', $getData);
        $this->setData('search', $request->get()->all());
        $this->setData('combineSearch', \Component\Member\MemberSleep::COMBINE_SEARCH);
        $this->setData('groups', \Component\Member\Group\Util::getGroupName());
        $this->addScript(['member.js']);
    }
}
