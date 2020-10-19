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

use Exception;
use Framework\Utility\DateTimeUtils;

/**
 * 회원정보 수정 이벤트 리스트
 *
 * @author haky <haky2@godo.co.kr>
 */
class MemberModifyEventListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('member', 'member', 'modifyMemberEventList');
        $request = \App::getInstance('request');
        $request->get()->xss();
        if ($request->get()->has('searchPeriod') === false) {
            $request->get()->set('searchPeriod', 6);
        }
        if ($request->get()->has('searchDate') === false) {
            $request->get()->set('searchDate', DateTimeUtils::getBetweenDateString('-' . $request->get()->get('searchPeriod') . 'days'));
        }
        $params = $request->get()->xss()->all();

        try {
            // 모듈 호출
            $modifyEvent = \App::load('\\Component\\Member\\MemberModifyEvent');
            // 이벤트 리스트
            $data = $modifyEvent->getMemberModifyEventList($params);
            // 이벤트 노출 데이터 설정
            $eventList = $modifyEvent->setDisplayMemberModifyEventList($data);
            // 페이지
            $page = \App::load('\\Component\\Page\\Page');
        } catch (Exception $e) {
            throw $e;
        }

        $this->setData('eventList', $eventList);
        $this->setData('search', $eventList['search']);
        $this->setData('checked', $eventList['checked']);
        $this->setData('page', $page);
    }
}
