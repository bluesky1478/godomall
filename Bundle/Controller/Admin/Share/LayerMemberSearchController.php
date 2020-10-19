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
use Component\Member\MemberGroup;
use Component\Page\Page;
use Framework\Utility\StringUtils;

/**
 * Class 레이어 회원 검색
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class LayerMemberSearchController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        if (!$request->get()->has('mallSno')) {
            $request->get()->set('mallSno', '');
        }
        if (!$request->get()->has('loadPageType')) { //레이어를 띄운 페이지 명
            $request->get()->set('loadPageType', '');
        }
        if (!$request->get()->has('page')) {
            $request->get()->set('page', 1);
        }
        if (!$request->get()->has('pageNum')) {
            $request->get()->set('pageNum', 10);
        }

        // 회원 아이디 검증 (영문, 숫자, 특수문자(-),(_),(.),(@)만 가능함)
        if ($request->get()->get('key') === 'memId' && !preg_match('/^[a-zA-Z0-9\.\-\_\@]*$/', $request->get()->get('keyword'))) {
            $request->get()->set('keyword', preg_replace("/([^a-zA-Z0-9\.\-\_\@])/", "", $request->get()->get('keyword')));
        } else {
            $request->get()->set('keyword', preg_replace("!<script(.*?)<\/script>!is", "", $request->get()->get('keyword')));
        }

        $requestParams = $request->get()->all();
        $memberService = \App::load(Member::class);
        $funcSkipOverTime = function () use ($memberService, $request) {
            $page = $request->get()->get('page');
            $pageNum = $request->get()->get('pageNum');

            return $memberService->listsWithCoupon($request->get()->all(), $page, $pageNum);
        };
        $funcCondition = function () use ($request) {
            return \count($request->get()->all()) === 5
                && $request->get()->get('key') === 'memId'
                && $request->get()->get('mallSno') === ''
                && $request->get()->get('loadPageType') === ''
                && $request->get()->get('page') === 1
                && $request->get()->get('pageNum') === 10;
        };
        $getData = $this->skipOverTime($funcSkipOverTime, $funcCondition, [], $isSkip);
        $pageTotal = \count($getData);
        if ($pageTotal > 0) {
            $pageTotal = $memberService->foundRowsByListsWithCoupon($request->get()->all());
        }
        $pageAmount = $memberService->getCount(DB_MEMBER, 'memNo', 'WHERE sleepFl=\'n\'');

        $pageObject = new Page($request->get()->get('page'), $pageTotal, $pageAmount, $request->get()->get('pageNum'));
        $pageObject->setPage();
        $pageObject->setUrl($request->getQueryString());

        $combineSearch = Member::getCombineSearchSelectBox();
        unset($combineSearch['recommId'], $combineSearch['company'], $combineSearch['__disable7']);

        //회원등급정보
        $memberGroupInfo = [];
        $memberGroup = \App::load(MemberGroup::class);
        $memberGroupList = $memberGroup->getGroupList();
        foreach ($memberGroupList['data'] as $val) {
            $memberGroupInfo[$val['sno']] = $val;
        }
        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');

        $this->setData('isSkip', $isSkip);
        $this->setData('mode', StringUtils::strIsSet($requestParams['mode'], 'search'));
        $this->setData('callFunc', StringUtils::strIsSet($requestParams['callFunc'], ''));
        $this->setData('data', $getData);
        $this->setData('search', $requestParams);
        $this->setData('combineSearch', $combineSearch);
        $this->setData('groups', \Component\Member\Group\Util::getGroupName());
        $this->setData('page', $pageObject);
        $this->setData('memberGroupInfo', $memberGroupInfo);
    }
}
