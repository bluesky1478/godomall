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
use Framework\Utility\DateTimeUtils;
use Framework\Utility\SkinUtils;

/**
 * Class MemberBatchDepositListController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MemberBatchDepositListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $this->callMenu('member', 'point', 'depositList');
        $request->get()->set('page', $request->get()->get('page', 1));
        $request->get()->set('pageNum', $request->get()->get('pageNum', 10));
        $request->get()->set('mode', $request->get()->get('mode', 'all'));
        $request->get()->set('regDtPeriod', $request->get()->get('regDtPeriod', '6'));
        $request->get()->set('regDt', $request->get()->get('regDt', DateTimeUtils::getBetweenDateString('-' . $request->get()->get('regDtPeriod') . ' days')));

        $boardAdmin = \App::load('\\Component\\Board\\BoardAdmin');
        /** @var \Bundle\Component\Deposit\Deposit $deposit */
        $deposit = \App::load('\\Component\\Deposit\\Deposit');

        // ISMS 인증관련 추가
        if (array_search($request->get()->get('pageNum'), SkinUtils::getPageViewCount()) === false) {
            $request->get()->set('pageNum', 10);
        }

        $requestGetParams = $request->get()->all();
        $depositList = $deposit->getDepositList($requestGetParams);
        $boardList = $boardAdmin->selectList();
        $arrBoard = [];
        foreach ($boardList as $board) {
            $arrBoard[$board['bdId']] = $board['bdNm'];
        }
        $page = \App::load('Component\\Page\\Page');
        $page->setPage();
        $page->setUrl($request->getQueryString());
        $this->setData('page', $page);
        $this->setData('depositList', $depositList);
        $this->setData('checked', $deposit->setChecked($requestGetParams));
        $this->setData('selected', $deposit->setSelected($requestGetParams));
        $this->setData('searchKey', Deposit::COMBINE_SEARCH);
        $this->setData('boards', $arrBoard);
        $this->addScript(['member.js']);
    }
}
