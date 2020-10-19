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

use Component\Board\ArticleListAdmin;
use Component\Board\Board;
use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;


/**
 * Class 관리자-CRM 요약내역
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 * @see     \Core\Base\Interceptor\AdminLayout
 */
class MemberCrmController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('member', 'member', 'crm');

        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        /** @var \Bundle\Controller\Admin\Controller $this */
        $memberData = $this->getData('memberData');
        $memberData['memberFl'] = $memberData['memberFl'] == 'business' ? __('사업자회원') : __('개인회원');
        $countries = \Component\Mall\MallDAO::getInstance()->selectCountries();
        $countryNames = [];
        foreach ($countries as $key => $val) {
            if ($val['callPrefix'] > 0) {
                $countryNames[$val['code']] = $val['countryNameKor'] . '(+' . $val['callPrefix'] . ')';
            }
        }
        $memberData['phoneCountryCodeNameKor'] = $countryNames[$memberData['phoneCountryCode']];
        $memberData['cellPhoneCountryCodeNameKor'] = $countryNames[$memberData['cellPhoneCountryCode']];

        // 1:1 문의 (3건만)
        $memberNo = $request->get()->get('memNo', $request->post()->get('memNo'));
        $articleListAdmin = new ArticleListAdmin(
            [
                'bdId'  => Board::BASIC_QA_ID,
                'memNo' => $memberNo,
            ]
        );
        $resQna = $articleListAdmin->getList(true, 3);
        $qnaList = $resQna['data'];
        unset($resQna, $articleListAdmin);

        // 주문내역
        $getOrderSummary = function () use ($request) {
            $order = \App::load('Component\\Order\\OrderAdmin');
            $orderDefaultSearch = ComponentUtils::getPolicy('order.defaultSearch'); // 주문 리스트 설정 config 불러오기
            StringUtils::strIsSet($orderDefaultSearch['searchPeriod'], 7);
            $getValue = $request->get()->toArray(); // 리스트 설정
            $memberNo = $request->get()->get('memNo', $request->post()->get('memNo'));  // CRM 회원 주문 검색어 설정
            $getValue['memNo'] = $memberNo;
            $getValue['pageNum'] = 3;
            $getData = $order->getOrderListForAdmin($getValue, $orderDefaultSearch['searchPeriod']);
            $page = \App::load('Component\\Page\\Page');    // 페이지 설정
            if ($page->idx > 3) $page->idx = $getValue['pageNum'];  // crm 은 3건만 보여주기 때문에 3건 이상인 경우에만 idx 를 3건으로 고정 시킨다.

            return [
                'orderGoodsData'    => StringUtils::strIsSet($getData['data']),
                'statusListCombine' => $order->statusListCombine,
                'page'              => StringUtils::strIsSet($page),
            ];
        };
        $orderSummaryResult = $getOrderSummary();
        foreach ($orderSummaryResult as $index => $item) {
            $this->setData($index, $item);
        }

        // 상담내역
        $crmAdmin = \App::load('Component\\Member\\Counsel');
        $request->get()->set('page', 0);
        $request->get()->set('pageNum', 3);
        $memberCrmList = $crmAdmin->getList($request->get()->all());
        $crmAdmin->replaceList($memberCrmList);
        // 관리자 정보
        $managerData = $session->get('manager');

        // --- 관리자 디자인 템플릿
        $this->setData('memberData', gd_set_default_value($memberData, '-'));
        $this->setData('memberCrmList', $memberCrmList);
        $this->setData('qnaList', $qnaList);
        $this->setData('managerData', $managerData);
    }
}
