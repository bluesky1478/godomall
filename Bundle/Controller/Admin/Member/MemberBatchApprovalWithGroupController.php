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
use Component\Member\Util\MemberUtil;
use Component\Page\Page;
use Framework\Utility\ArrayUtils;
use Framework\Utility\SkinUtils;

/**
 * Class MemberBatchApplyGroupController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MemberBatchApprovalWithGroupController extends \Controller\Admin\Controller
{
    /**
     * index
     */
    public function index()
    {
        $this->callMenu('member', 'member', 'applyAndGroup');
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

        // ISMS 인증관련 추가
        if (array_search($request->get()->get('pageNum'), SkinUtils::getPageViewCount()) === false) {
            $request->get()->set('pageNum', 10);
        }

        $memberService = \App::load(Member::class);
        $getParams = $request->get()->all();
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

        /** @var \Bundle\Component\Page\Page $pageObject */
        $pageObject = new Page($request->get()->get('page'), 0, 0, $request->get()->get('pageNum'));
        $pageTotal = \count($getData);
        $pageObject->setTotal($pageTotal);
        $pageObject->setCache(true);
        if ($pageTotal > 0 && $pageObject->hasRecodeCache('total') === false) {
            $total = $memberService->foundRowsByListsWithCoupon($getParams);
            $pageObject->setTotal($total);
        }
        if ($pageObject->hasRecodeCache('amount') === false) {
            $amount = $memberService->getCount(DB_MEMBER, 'memNo', 'WHERE sleepFl=\'n\'');
            $pageObject->setAmount($amount);
        }

        $pageObject->setUrl($request->getQueryString());
        $pageObject->setPage();
        $checked = MemberUtil::checkedByMemberListSearch($getParams);
        $selected = MemberUtil::selectedByMemberListSearch($getParams);

        // 처리항목
        $search['mode'] = gd_isset($getParams['mode'], 'batch_app');
        $checked['mode'][$search['mode']] = 'checked="checked"';
        // 변경상태 선택
        $search['approvalStatus'] = gd_isset($getParams['approvalStatus'], 'y');
        $checked['approvalStatus'][$search['approvalStatus']] = 'checked="checked"';
        // 대상회원 선택
        $search['memberType'] = gd_isset($getParams['memberType'], 'select');
        $checked['memberType'][$search['memberType']] = 'checked="checked"';
        // 변경등급선택
        $search['groupSno'] = gd_isset($getParams['groupSno']);

        $searchItem = ArrayUtils::removeEmpty($getParams);
        if (empty($searchItem) === false) {
            $searchJson = htmlspecialchars(json_encode($searchItem));
        }

        $this->setData('isSkip', $isSkip);
        $this->setData('page', $pageObject);
        $this->setData('data', $getData);
        $this->setData('search', $getParams);
        $this->setData('groups', gd_member_groups());
        $this->setData('combineSearch', Member::getCombineSearchSelectBox());
        $this->setData('searchJson', gd_isset($searchJson, ''));
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);

        $this->addScript(['member.js']);
    }
}
