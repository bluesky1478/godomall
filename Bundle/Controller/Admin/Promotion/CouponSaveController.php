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

namespace Bundle\Controller\Admin\Promotion;

use Component\Member\Member;
use Component\Member\Util\MemberUtil;
use Component\Page\Page;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Utility\ComponentUtils;

/**
 * Class CouponSaveController
 * @package Bundle\Controller\Admin\Promotion
 * @author  su
 */
class CouponSaveController extends \Controller\Admin\Controller
{
    /**
     * 쿠폰 관리
     * [관리자 모드] 쿠폰 관리
     * @version   1.0
     * @since     1.0
     * @throws LayerException
     */
    public function index()
    {
        $request = \App::getInstance('request');

        // --- 모듈 호출
        $couponAdmin = \App::load(\Component\Coupon\CouponAdmin::class);

        // cli 대량쿠폰발급중 체크
        if (file_exists(\App::getUserBasePath() . '/config/CliSaveCouponCount')) {
            $sFiledata = \FileHandler::read(\App::getUserBasePath() . '/config/CliSaveCouponCount');
            $getCliData = json_decode($sFiledata, true);
            if ($getCliData['couponNo'] == $request->get()->get('couponNo')) {
                $nowCount = $couponAdmin->getMemberCouponTotalCount($getCliData['couponNo']);
                $getCliData['nowCount'] = $nowCount - $getCliData['orgCount'];
                if (!file_exists(\App::getUserBasePath() . '/config/CliSaveCoupon')) {
                    if ($getCliData['nowCount'] == $getCliData['totalCount']) {
                        $aCount = array('couponNo' => 0, 'totalCount' => 0, 'orgCount' => 0, 'couponName' => '');
                        \FileHandler::write(\App::getUserBasePath() . '/config/CliSaveCouponCount', json_encode($aCount));
                        $message = '쿠폰 대량 발급이 완료되었습니다.';
                        throw new AlertOnlyException(__($message), null, null, 'top.location.reload()');
                    }
                }
                $this->setData('nowCli', 'T');
                $this->setData('cliData', $getCliData);
            } else {
                $this->setData('nowCli', 'F');
            }
        }

        // --- 쿠폰 사용 설정 정보
        try {
            // 쿠폰 고유 번호
            $couponNo = $request->get()->get('couponNo');
            $getData = $couponAdmin->getCouponInfo($couponNo, 'couponNo, couponNm, couponDescribed, couponUseType, couponSaveType, couponUsePeriodType, couponUsePeriodStartDate, couponUsePeriodEndDate, couponUsePeriodDay, couponUseDateLimit, couponBenefit, couponBenefitType, couponBenefitFixApply, couponKindType');
            $getConvertData = $couponAdmin->convertCouponData($getData);

            $request->get()->set('mallSno', DEFAULT_MALL_NUMBER);
            if (!$request->get()->has('page')) {
                $request->get()->set('page', 1);
            }
            if (!$request->get()->has('pageNum')) {
                $request->get()->set('pageNum', 10);
            }

            $memberService = \App::load(Member::class);
            $getParams = $request->get()->all();
            $funcSkipOverTime = function () use ($memberService, $request) {
                $page = $request->get()->get('page');
                $pageNum = $request->get()->get('pageNum');

                return $memberService->listsWithCoupon($request->get()->all(), $page, $pageNum);
            };
            $funcCondition = function () use ($request) {
                return \count($request->get()->all()) === 4
                    && $request->get()->has('couponNo')
                    && $request->get()->get('mallSno') === DEFAULT_MALL_NUMBER
                    && $request->get()->get('page') === 1
                    && $request->get()->get('pageNum') === 10;
            };
            $getMemberData = $this->skipOverTime($funcSkipOverTime, $funcCondition, [], $isSkip);

            /** @var \Bundle\Component\Page\Page $pageObject */
            $pageTotal = \count($getMemberData);
            if ($pageTotal > 0) {
                $pageTotal = $memberService->foundRowsByListsWithCoupon($getParams);
            }
            $pageAmount = $memberService->getCount(DB_MEMBER, 'memNo', 'WHERE sleepFl=\'n\'');
            $pageObject = new Page($request->get()->get('page'), $pageTotal, $pageAmount, $request->get()->get('pageNum'));
            $pageObject->setPage();
            $pageObject->setUrl($request->getQueryString());

            $checked = MemberUtil::checkedByMemberListSearch($getParams);
            $selected = MemberUtil::selectedByMemberListSearch($getParams);

            // SMS 수동쿠폰 발급 안내 설정값 불러오기
            $smsAuto = ComponentUtils::getPolicy('sms.smsAuto');
            $smsSendFlag = 'n';
            if ($smsAuto['promotion']['COUPON_MANUAL']) {
                if ($smsAuto['promotion']['COUPON_MANUAL']['memberSend']
                    || $smsAuto['promotion']['COUPON_MANUAL']['adminSend']) {
                    $smsSendFlag = 'y';
                }
            }
        } catch (\Exception $e) {
            throw new LayerException($e->getMessage());
        }

        // --- 메뉴 설정
        $this->callMenu('promotion', 'coupon', 'couponSave');

        //@formatter:off
        $this->addScript([
            'member.js',
            'jquery/jquery.multi_select_box.js',
        ]);
        //@formatter:on
        $this->setData('isSkip', $isSkip);
        $this->setData('getData', gd_isset($getData));
        $this->setData('disableGlobalSearch', true); // 쿠폰 발급은 회원 검색은 기준몰만
        $this->setData('getConvertData', gd_isset($getConvertData));
        $this->setData('getMemberData', gd_isset($getMemberData));
        $this->setData('groups', gd_member_groups());
        $this->setData('combineSearch', Member::COMBINE_SEARCH);
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
        $this->setData('search', $getParams);
        $this->setData('searchJson', gd_isset(htmlspecialchars(json_encode($getParams))));
        $this->setData('page', $pageObject);
        $this->setData('smsSendFlag', $smsSendFlag);
    }
}
