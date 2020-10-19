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

use Component\Deposit\Deposit;
use Component\Member\Member;
use Component\Member\Util\MemberUtil;
use Component\Page\Page;
use Framework\Utility\ArrayUtils;
use Framework\Utility\StringUtils;

/**
 * Class MemberBatchDepositController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MemberBatchDepositController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');

        /** @var \Bundle\Controller\Admin\Controller $this */
        $this->callMenu('member', 'point', 'deposit');
        $request->get()->set('mallSno', DEFAULT_MALL_NUMBER);
        if (!$request->get()->has('page')) {
            $request->get()->set('page', 1);
        }
        if (!$request->get()->has('pageNum')) {
            $request->get()->set('pageNum', 10);
        }
        /** @var \Bundle\Component\Deposit\Deposit $deposit */
        $deposit = \App::load(Deposit::class);
        $memberService = \App::load(Member::class);
        $funcSkipOverTime = function () use ($memberService, $request) {
            $getAll = $request->get()->all();
            $page = $request->get()->get('page');
            $pageNum = $request->get()->get('pageNum');

            return $memberService->listsWithCoupon($getAll, $page, $pageNum);
        };
        $funcCondition = function () use ($request) {
            return \count($request->get()->all()) === 3
                && $request->get()->get('mallSno') === DEFAULT_MALL_NUMBER
                && $request->get()->get('page') === 1
                && $request->get()->get('pageNum') === 10;
        };
        $getData = $this->skipOverTime($funcSkipOverTime, $funcCondition, [], $isSkip);
        // 지급/차감여부
        $request->get()->set('depositCheckFl', $request->get()->get('depositCheckFl', 'add'));
        // 마일리지 부족 시 차감방법
        $request->get()->set('removeMethodFl', $request->get()->get('removeMethodFl', 'minus'));
        // 대상회원 선택
        $request->get()->set('targetMemberFl', $request->get()->get('targetMemberFl', 'search'));

        /** @var \Bundle\Component\Page\Page $pageObject */
        $getParams = $request->get()->all();
        $pageTotal = \count($getData);
        if ($pageTotal > 0) {
            $pageTotal = $memberService->foundRowsByListsWithCoupon($getParams);
        }
        $pageAmount = $memberService->getCount(DB_MEMBER, 'memNo', 'WHERE sleepFl=\'n\'');
        $pageObject = new Page($request->get()->get('page'), $pageTotal, $pageAmount, $request->get()->get('pageNum'));
        $pageObject->setPage();
        $pageObject->setUrl($request->getQueryString());

        $checked = array_merge($deposit->setChecked($getParams), MemberUtil::checkedByMemberListSearch($getParams));
        $selected = array_merge($deposit->setSelected($getParams), MemberUtil::selectedByMemberListSearch($getParams));

        $searchItem = ArrayUtils::removeEmpty($getParams);
        if (empty($searchItem) === false) {
            $searchJson = htmlspecialchars(json_encode($searchItem));
        }

        $this->setData('isSkip', $isSkip);
        $this->setData('page', $pageObject);
        $this->setData('data', $getData);
        $this->setData('search', $getParams);
        $this->setData('groups', \Component\Member\Group\Util::getGroupName());
        $this->setData('combineSearch', \Component\Member\Member::getCombineSearchSelectBox());
        $this->setData('combineSearch', \Component\Member\Member::COMBINE_SEARCH);
        $this->setData('searchJson', StringUtils::strIsSet($searchJson, ''));
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
        $this->setData('disableGlobalSearch', true);

        $this->addScript(['member.js']);
    }
}
