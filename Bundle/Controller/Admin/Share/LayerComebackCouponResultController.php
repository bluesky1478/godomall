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
 * @link http://www.godo.co.kr
 */
namespace Bundle\Controller\Admin\Share;

use Component\Sms\Sms;
use Exception;

/**
 * 컴백쿠폰 SMS발송 결과 레이어 페이지
 *
 */
class LayerComebackCouponResultController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // 리퀘스트
            $getValue = \Request::get()->toArray();

            // --- 모듈 호출
            $coupon = \App::load('\\Component\\Coupon\\CouponAdmin');
            $getData = $coupon->getComebackCouponInfo($getValue['dataSno'], '*');
            $getCouponData = $coupon->getCouponInfo($getData['couponNo'], '*');
            $aComebackCouponMemberList = $coupon->getComebackCouponMemberResultList($getValue);

            // 페이지
            $page = \App::load('\\Component\\Page\\Page');
            $this->setData('page', $page);

            // 쿠폰유형 셀렉트 박스
            $searchMode = [
                'all' => '= ' . __('통합검색') . ' =',
                'memId' => __('아이디'),
                'memNm' => __('이름'),
                'nickNm' => __('닉네임'),
                'email' => __('이메일'),
                'phone' => __('전화번호'),
                'cellPhone' => __('휴대폰번호'),
            ];
            $this->setData('searchMode', $searchMode);

            // 쿠폰발급여부
            $issueCouponFl = [
                'all' => __('전체'),
                'y' => __('발급함'),
                'n' => __('발급안함'),
            ];
            $this->setData('issueCouponFl', $issueCouponFl);

            // SMS발송여부
            $sendSmsFl = [
                'all' => __('전체'),
                'y' => __('발송함'),
                'n' => __('발송안함'),
            ];
            $this->setData('sendSmsFl', $sendSmsFl);

            // 사용여부
            $useType = [
                'all' => __('전체'),
                'y' => __('사용함'),
                'n' => __('사용안함'),
            ];
            $this->setData('useType', $useType);

            // 템플릿 변수
            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('parentFormID', $getValue['parentFormID']);
            $this->setData('dataFormID', $getValue['dataFormID']);
            $this->setData('dataInputNm', $getValue['dataInputNm']);
            $this->setData('mode', gd_isset($getValue['mode'], 'search'));
            $this->setData('disabled', gd_isset($getValue['disabled'], ''));
            $this->setData('callFunc', gd_isset($getValue['callFunc'], ''));
            $this->setData('data', gd_isset($aComebackCouponMemberList['data']));
            $this->setData('getValue', gd_isset($getValue));
            $this->setData('title', gd_isset($getData['title']));
            $this->setData('couponName', gd_isset($getCouponData['couponNm']));
            $this->setData('couponNo', gd_isset($getCouponData['couponNo']));
            $this->setData('page', $page);

            // 레이어 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
