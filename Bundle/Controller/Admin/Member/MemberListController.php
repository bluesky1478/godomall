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

use Component\Member\Member;
use Framework\Utility\SkinUtils;

/**
 * Class 회원리스트
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MemberListController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        $this->callMenu('member', 'member', 'list');
        $request = \App::getInstance('request');

        // 회원 아이디 검증 (영문, 숫자, 특수문자(-),(_),(.),(@)만 가능함)
        if ($request->get()->get('key') === 'memId' && !preg_match('/^[a-zA-Z0-9\.\-\_\@]*$/', $request->get()->get('keyword'))) {
            $request->get()->set('keyword', preg_replace("/([^a-zA-Z0-9\.\-\_\@])/", "", $request->get()->get('keyword')));
        } else {
            $request->get()->set('keyword', preg_replace("!<script(.*?)<\/script>!is", "", $request->get()->get('keyword')));
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

        $memberService = \App::load(Member::class);
        $funcSkipOverTime = function () use ($memberService, $request) {
            $getAll = $request->get()->all();
            $page = $request->get()->get('page');
            $pageNum = $request->get()->get('pageNum');

            return $memberService->listsWithCoupon($getAll, $page, $pageNum);
        };
        $funcCondition = function () use ($request) {
            return \count($request->get()->all()) === 3
                && $request->get()->get('mallSno') === ''
                && $request->get()->get('page') === 1
                && $request->get()->get('pageNum') === 10;
        };
        $getData = $this->skipOverTime($funcSkipOverTime, $funcCondition, [], $isSkip);

        $pageObject = new \Component\Page\Page($request->get()->get('page'), 0, 0, $request->get()->get('pageNum'));
        $pageTotal = \count($getData);
        $pageObject->setTotal($pageTotal);
        $pageObject->setCache(true);
        if ($pageTotal > 0 && $pageObject->hasRecodeCache('total') === false) {
            $total = $memberService->foundRowsByListsWithCoupon($request->get()->all());
            $pageObject->setTotal($total);
        }
        if ($pageObject->hasRecodeCache('amount') === false) {
            $amount = $memberService->getCount(DB_MEMBER, 'memNo', 'WHERE sleepFl=\'n\'');
            $pageObject->setAmount($amount);
        }

        $pageObject->setUrl($request->getQueryString());
        $pageObject->setPage();
        $checked = \Component\Member\Util\MemberUtil::checkedByMemberListSearch($request->get()->all());
        $selected = \Component\Member\Util\MemberUtil::selectedByMemberListSearch($request->get()->all());
        $this->setData('isSkip', $isSkip);
        $this->setData('page', $pageObject);
        $this->setData('data', $getData);
        $this->setData('search', $request->get()->all());
        $this->setData('groups', \Component\Member\Group\Util::getGroupName());
        $this->setData('combineSearch', \Component\Member\Member::getCombineSearchSelectBox());
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
        $this->addScript(
            [
                'member.js',
                'sms.js',
            ]
        );
    }
}
