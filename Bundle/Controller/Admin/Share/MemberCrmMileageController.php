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

use Component\Mileage\Mileage;
use Component\Mileage\MileageUtil;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\DateTimeUtils;

/**
 * Class 관리자-CRM 마일리지내역
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class MemberCrmMileageController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('member', 'member', 'crm');

        $request = \App::getInstance('request');
        try {
            /** @var \Bundle\Component\Member\MemberAdmin $memberAdmin */
            $memberAdmin = \App::load('\\Component\\Member\\MemberAdmin');

            if ($request->get()->has('mode') === false) {
                $request->get()->set('mode', 'all');
            }
            if ($request->get()->has('regDtPeriod') === false) {
                $request->get()->set('regDtPeriod', 6);
            }
            if ($request->get()->has('regDt') === false) {
                $request->get()->set('regDt', DateTimeUtils::getBetweenDateString('-' . $request->get()->get('regDtPeriod') . 'days'));
            }
            $arrData = $request->get()->all();
            $getData = $memberAdmin->getMemberMileagePageList($arrData);
            $funcBoardList = function () {
                $db = \App::getInstance('DB');
                $resultSet = $db->query_fetch('SELECT /* 마일리지 리스트 사유 조회 */ bdId, bdNm FROM ' . DB_BOARD . ' ORDER BY sno DESC');

                return $resultSet;
            };
            $arrBoard = [];
            $boardList = $funcBoardList();
            foreach ($boardList as $board) {
                $arrBoard[$board['bdId']] = $board['bdNm'];
            }
            $page = \App::load('Component\\Page\\Page');
            $mileage = \App::load('\\Component\\Mileage\\Mileage');
            $modifyEvent = \App::load('\\Component\\Member\\MemberModifyEvent');
            foreach ($getData['data'] as &$milageData) {
                if($milageData['reasonCd'] == $mileage::REASON_CODE_GROUP . $mileage::REASON_CODE_MEMBER_MODIFY_EVENT){
                    $res = $modifyEvent->getMemberModifyEventInfo($milageData['handleCd']);
                    $milageData['modifyEventNm'] = $res['data']['eventNm'];
                }
            }

            $mileageReasons = $modifiedMileageReasons = MileageUtil::getReasons();
            if (empty($modifiedMileageReasons[Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_REGISTER_RECOMMEND]) === false) {
                $modifiedMileageReasons[Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_REGISTER_RECOMMEND] = '추천인 등록';
            }

            $this->setData('page', $page);
            $this->setData('data', $getData['data']);
            $this->setData('search', $getData['search']);
            $this->setData('groups', $getData['groups']);
            $this->setData('combineSearch', $getData['combineSearch']);
            $this->setData('checked', $getData['checked']);
            $this->setData('selected', $getData['selected']);
            $this->setData('mileageReasons', $mileageReasons);
            $this->setData('modifiedMileageReasons', $modifiedMileageReasons);
            $this->setData('boards', $arrBoard);
            $this->setData('keyArray', Mileage::COMBINE_SEARCH);
            $this->addScript(['member.js']);
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
