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
namespace Bundle\Controller\Admin\Promotion;

use Exception;
use Component\Godo\GodoSmsServerApi;
use Component\Sms\SmsAdmin;
use Framework\Debug\Exception\LayerException;
use Request;

class ComebackCouponListController extends \Controller\Admin\Controller
{

    /**
     * 쿠폰 리스트
     * [관리자 모드] 쿠폰 리스트
     *
     * @author su
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @throws LayerException
     * @internal param array $get
     * @internal param array $post
     * @internal param array $files
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('promotion', 'coupon', 'comebackCouponList');

        // --- 모듈 호출
        try {
            $smsAdmin = new SmsAdmin();
            $smsAutoData = $smsAdmin->getSmsAutoData();

            // SMS 발신번호 사전 등록 번호 정보
            $godoSms = new GodoSmsServerApi();
            $smsPreRegister = $godoSms->checkSmsCallNumber($smsAutoData['smsCallNum']);

            // 리스트 정보가져오기
            $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
            $getData = $couponAdmin->getComebackCouponList();

            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            // 데이터 가공
            foreach ($getData['data'] as $key => $val) {
                $getData['data'][$key]['couponSet'] = '';
                $getData['data'][$key]['sendContents'] = '';
                $getData['data'][$key]['sendInfo'] = '';
                $getData['data'][$key]['sendAction'] = '';
                $getData['data'][$key]['btnAction'] = '';
                if ($val['couponNo'] == null || $val['couponNo'] == 0) {
                    $getData['data'][$key]['couponSet'] = '없음';
                } else {
                    $getData['data'][$key]['couponSet'] = '있음<br/>
                    <input type="button" value="상세보기" class="btn btn-sm btn-white" id="viewCoupon' . $val['sno'] . '" data-sno="' . $val['sno'] . '" data-coupon-no="' . $val['couponNo'] . '" />
                    ';
                }
                if ($val['smsFl'] == 'n') {
                    $getData['data'][$key]['sendContents'] = '없음';
                } else {
                    $getData['data'][$key]['sendContents'] = '있음<br/><input type="button" value="상세보기" class="btn btn-sm btn-white" id="viewSms' . $val['sno'] . '" data-sno="' . $val['sno'] . '" />';
                }
                if ($val['sendDt'] == null || $val['sendDt'] == '0000-00-00 00:00:00') {
                    $getData['data'][$key]['sendInfo'] = '<input type="button" value="대상보기" class="btn btn-sm btn-gray" id="viewMember' . $val['sno'] . '" data-sno="' . $val['sno'] . '" />';
                    $getData['data'][$key]['sendAction'] = '<input type="button" value="보내기" class="btn btn-sm btn-red" id="viewAction' . $val['sno'] . '" data-sno="' . $val['sno'] . '" data-couponNo="' . $val['couponNo'] . '" />';
                    $getData['data'][$key]['btnAction'] = '<a href="comeback_coupon_regist.php?sno=' . $val['sno'] . '&ypage=' . $page->page['now'] . '" class="btn btn-sm btn-white">수정</a>';
                    $getData['data'][$key]['btnAction'] .= '<input type="hidden" id="dataInfo' . $val['sno'] . '" data-smsFl="' . $val['smsFl'] . '" data-couponNo="' . $val['couponNo'] . '" data-send="n">';
                } else {
                    $getData['data'][$key]['sendInfo'] = '<input type="button" value="내역보기" class="btn btn-sm btn-white" id="viewResult' . $val['sno'] . '" data-sno="' . $val['sno'] . '" />';
                    $getData['data'][$key]['sendAction'] = '완료<br/>' . gd_date_format('Y-m-d', $val['sendDt']);
                    $getData['data'][$key]['btnAction'] = '<a href="comeback_coupon_regist.php?sno=' . $val['sno'] . '&ypage=' . $page->page['now'] . '" class="btn btn-sm btn-gray">보기</a>';
                    $getData['data'][$key]['btnAction'] .= '<input type="hidden" id="dataInfo' . $val['sno'] . '" data-smsFl="' . $val['smsFl'] . '" data-couponNo="' . $val['couponNo'] . '" data-send="y">';
                }

            }
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->setData('smsAutoData', gd_htmlspecialchars($smsAutoData));
        $this->setData('smsPreRegister', $smsPreRegister);
        $this->setData('data', gd_isset($getData['data']));
        $this->setData('list', gd_isset($getData['list']));
        $this->setData('page', $page);
    }
}
