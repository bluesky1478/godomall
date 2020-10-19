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

use Component\Member\Member;
use Component\Member\Util\MemberUtil;
use Component\Page\Page;

/**
 * Class 관리자 회원 추가 팝업 회원 검색 리스트
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class IfrmeAddMemberSearchController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        if (!$request->get()->has('mallSno')) {
            $request->get()->set('mallSno', '');
        }
        if (!$request->get()->has('page')) {
            $request->get()->set('page', 1);
        }
        if (!$request->get()->has('pageNum')) {
            $request->get()->set('pageNum', 10);
        }

        if ($request->get()->has('maillingFl') === false) {
            $request->get()->set('maillingFl', 'y');
        }
        if ($request->get()->has('smsFl') === false) {
            $request->get()->set('smsFl', 'y');
        }

        $requestParams = $request->get()->all();
        $requestParams['sendMode'] = htmlspecialchars($requestParams['sendMode'], ENT_QUOTES, 'UTF-8');

        $memberService = \App::load(Member::class);
        if ($request->get()->get('sendMode', '') !== 'mail') {
            $requestParams['mallSno'] = DEFAULT_MALL_NUMBER;
        }

        $isSkip = false;
        if ($request->get()->get('searchMode', '') === 'addList') {
            $getData = $memberService->listsWithCoupon($requestParams, null, null);
        } else {
            $funcSkipOverTime = function () {
                $memberService = \App::load(Member::class);
                $request = \App::getInstance('request');
                $page = $request->get()->get('page');
                $pageNum = $request->get()->get('pageNum');

                return $memberService->listsWithCoupon($request->get()->all(), $page, $pageNum);
            };
            $funcCondition = function () {
                $request = \App::getInstance('request');
                return $request->get()->has('initSearch');
            };
            $getData = $this->skipOverTime($funcSkipOverTime, $funcCondition, [], $isSkip);
        }

        /** @var \Bundle\Component\Page\Page $pageObject */
        $pageTotal = \count($getData);
        if ($pageTotal > 0) {
            $pageTotal = $memberService->foundRowsByListsWithCoupon($requestParams);
        }
        $pageAmount = $memberService->getCount(DB_MEMBER, 'memNo', 'WHERE sleepFl=\'n\'');
        $pageObject = new Page($request->get()->get('page'), $pageTotal, $pageAmount, $request->get()->get('pageNum'));
        $pageObject->setPage();
        $pageObject->setUrl($request->getQueryString());
        if ($request->isAjax()) {
            $this->json(
                [
                    'pagination'  => $pageObject->getPage('#'),
                    'page_index'  => $pageObject->idx,
                    'member_list' => $getData,
                    'is_skip' => $isSkip,
                ]
            );
        }

        $checked = MemberUtil::checkedByMemberListSearch($requestParams);
        $selected = MemberUtil::selectedByMemberListSearch($requestParams);

        $addSearchResultFl = $request->get()->get('addSearchResultFl', 'n');

        $this->getView()->setDefine('layout', 'layout_blank.php');

        $this->setData('page', $pageObject);
        $this->setData('data', $getData);
        $this->setData('search', $requestParams);
        $this->setData('groups', \Component\Member\Group\Util::getGroupName());
        $this->setData('combineSearch', Member::COMBINE_SEARCH);
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
        $this->setData('addSearchResultFl', $addSearchResultFl);
        $this->setData('sendMode', $request->get()->get('sendMode', 'mail'));

        $this->addScript(['member.js']);
    }
}

