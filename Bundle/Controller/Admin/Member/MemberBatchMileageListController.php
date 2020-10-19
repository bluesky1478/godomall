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

use Component\Mileage\Mileage;
use Component\Mileage\MileageUtil;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\SkinUtils;

/**
 * Class MemberBatchMileageListController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MemberBatchMileageListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        if ($request->get()->has('regDtPeriod') === false) {
            $request->get()->set('regDtPeriod', 6);
        }
        if ($request->get()->has('regDt') === false) {
            $request->get()->set('regDt', DateTimeUtils::getBetweenDateString('-' . $request->get()->get('regDtPeriod') . 'days'));
        }
        /** @var \Bundle\Controller\Admin\Controller $this */
        $this->callMenu('member', 'point', 'mileageList');

        // ISMS 인증관련 추가
        if (array_search($request->get()->get('pageNum'), SkinUtils::getPageViewCount()) === false) {
            $request->get()->set('pageNum', 10);
        }

        /** @var \Bundle\Component\Member\MemberAdmin $memberAdmin */
        $memberAdmin = \App::load('Component\\Member\\MemberAdmin');
        $arrData = $request->get()->all();
        $arrData['mode'] = gd_isset($arrData['mode'], 'all');
        $arrData['regDtPeriod'] = gd_isset($arrData['regDtPeriod'], '7');
        $getData = $memberAdmin->getMemberMileagePageList($arrData);
        // 마일리지 소멸예정일을 저장된 데이터가 아닌 전날의 23:59:59 로 변경하여 노출되도록 수정
        $getData['data'] = MileageUtil::changeDeleteScheduleDt($getData['data'], true);
        $funcBoardList = function () {
            $db = \App::getInstance('DB');
            $db->strField = 'bdId, bdNm';
            $resultSet = $db->query_fetch('SELECT /* 마일리지 리스트 사유 조회 */ bdId, bdNm FROM ' . DB_BOARD . ' ORDER BY sno DESC');

            return $resultSet;
        };
        $boardList = $funcBoardList();
        $arrBoard = [];
        foreach ($boardList as $board) {
            $arrBoard[$board['bdId']] = $board['bdNm'];
        }

        $mileage = \App::load('\\Component\\Mileage\\Mileage');
        $modifyEvent = \App::load('\\Component\\Member\\MemberModifyEvent');
        foreach ($getData['data'] as &$milageData) {
            if($milageData['reasonCd'] == $mileage::REASON_CODE_GROUP . $mileage::REASON_CODE_MEMBER_MODIFY_EVENT){
                $res = $modifyEvent->getMemberModifyEventInfo($milageData['handleCd']);
                $milageData['modifyEventNm'] = $res['data']['eventNm'];
            }
        }

        $mileageReasons = MileageUtil::getReasons();
        if (empty($mileageReasons[Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_REGISTER_RECOMMEND]) === false) {
            $mileageReasons[Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_REGISTER_RECOMMEND] = '추천인 등록';
        }

        $page = \App::load('Component\\Page\\Page');
        $this->setData('page', $page);
        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('groups', $getData['groups']);
        $this->setData('combineSearch', $getData['combineSearch']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('mileageReasons', $mileageReasons);
        $this->setData('boards', $arrBoard);
        $this->setData('searchKey', Mileage::COMBINE_SEARCH);
        $this->addScript(['member.js']);
    }
}
