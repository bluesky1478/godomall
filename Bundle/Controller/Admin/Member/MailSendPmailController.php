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

use Component\Mail\Pmail;
use Component\Member\Member;
use Component\Member\Util\MemberUtil;
use Component\Page\Page;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;


/**
 * Class 회원-메일 관리-대량메일보내기 컨트롤러
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee.
 */
class MailSendPmailController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('member', 'mail', 'sendPmail');
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

        $pMailService = \App::load(Pmail::class);
        $memberService = \App::load(Member::class);
        $configPmail = $pMailService->getMailConfigPmail();
        // ## 파라미터 인증
        if (!StringUtils::strIsSet($configPmail['userId'])
            || !StringUtils::strIsSet($configPmail['userNm'])
            || !StringUtils::strIsSet($configPmail['email'])
            || !StringUtils::strIsSet($configPmail['tel'])
            || !StringUtils::strIsSet($configPmail['mobile'])) {
            $message = '대량메일을 발송하시려면 대량메일 등록정보가 설정되어 있어야 합니다. 대량메일 등록정보를 입력해주세요.';
            throw new AlertRedirectException(__($message), null, null, '/member/mail_config_pmail.php');
        }
        $requestParams = $request->get()->all();
        $combineSearch = $pMailService->getCombineSearch();
        $combineSearchSelect = SkinUtils::makeSelectBox('key', 'key', $combineSearch, null, $requestParams['key']);
        if ($request->get()->get('indicate') !== 'search') { //기본설정
            $requestParams['maillingFl'] = 'y';
        }

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
        $memberList = $this->skipOverTime($funcSkipOverTime, $funcCondition, [], $isSkip);
        $pageTotal = \count($memberList);
        if ($pageTotal > 0) {
            $pageTotal = $memberService->foundRowsByListsWithCoupon($request->get()->all());
        }
        $pageAmount = $memberService->getCount(DB_MEMBER, 'memNo', 'WHERE sleepFl=\'n\'');
        $pageObject = new Page($request->get()->get('page'), $pageTotal, $pageAmount, $request->get()->get('pageNum'));
        $pageObject->setPage();
        $pageObject->setUrl($request->getQueryString());
        $this->setData('isSkip', $isSkip);
        $this->setData('combineSearchSelect', $combineSearchSelect);
        $this->setData('page', $pageObject);
        $this->setData('data', $memberList);
        $this->setData('search', StringUtils::htmlSpecialChars($request->get()->all()));
        $this->setData('groups', \Component\Member\Group\Util::getGroupName());
        $this->setData('checked', MemberUtil::checkedByMemberListSearch($request->get()->all()));
        $this->setData('selected', MemberUtil::selectedByMemberListSearch($request->get()->all()));
        $this->addScript(['member.js']);
    }
}
