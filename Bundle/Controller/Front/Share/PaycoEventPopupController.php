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

namespace Bundle\Controller\Front\Share;


use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;

/**
 * Class PaycoEventPopupController
 * @package Bundle\Controller\Front\Share
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class PaycoEventPopupController extends \Controller\Front\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $policy = ComponentUtils::getPolicy('pg.payco');
        if ($request->isMobile()) {
            $policy['eventPopupTop'] = 0;
            $policy['eventPopupLeft'] = 0;
        } else {
            StringUtils::strIsSet($policy['eventPopupTop'], 0);
            StringUtils::strIsSet($policy['eventPopupLeft'], 0);
        }
        $callPaycoCouponApi = function () use ($policy) {
            /** @var \Bundle\Component\Payment\Payco\Payco $payco */
            $payco = \App::load('Component\\Payment\\Payco\\Payco');
            $result = $payco->paycoEventPopup(['seller_key' => $policy['paycoSellerKey']]);

            return $result;
            //            return $result = '{"couponList":[{"couponId":18327,"couponName":"7000원 할인 (하나카드)","couponType":"CT01","minAmount":35000,"maxAmount":999999999,"discountYn":"Y","discountType":"DT01","discountAmount":7000,"discountRate":0,"issueCondType":"ILT02","methodType":"MT02","methodCode":"CCHN","methodSubCode":"0","methodBankCode":"ALL"},{"couponId":18328,"couponName":"2000원 할인","couponType":"CT01","minAmount":10000,"maxAmount":999999999,"discountYn":"Y","discountType":"DT01","discountAmount":2000,"discountRate":0,"issueCondType":"ILT01","methodType":"MT02","methodCode":"CCBC","methodSubCode":"0","methodBankCode":"ALL"},{"couponId":18329,"couponName":"3000원 할인","couponType":"CT02","minAmount":40000,"maxAmount":999999999,"discountYn":"Y","discountType":"DT01","discountAmount":3000,"discountRate":0,"issueCondType":"ILT03","methodType":"MT02","methodCode":"ALL","methodSubCode":"0","methodBankCode":"ALL"}],"resultCode":"0","errorMessage":"","couponInfoStrList":["ILT02_35,000원 이상 결제 시 7,000원 할인 (하나SK카드 결제 시)","ILT01_10,000원 이상 결제 시 2,000원 할인 (BC카드 결제 시)","ILT03_40,000원 이상 결제 시 3,000원 할인"]}';

        };
        $usePaycoEventPopup = function () use ($policy, $request) {
            StringUtils::strIsSet($policy['useYn'], 'all');
            StringUtils::strIsSet($policy['useEventPopupYn'], 'n');
            StringUtils::strIsSet($policy['paycoSellerKey'], '');

            $usePayco = $usePayco = $policy['useYn'] == 'all' || $policy['useYn'] == 'pc';;
            if ($request->isMobile()) {
                $usePayco = $policy['useYn'] == 'all' || $policy['useYn'] == 'mobile';
            }

            return $policy['useEventPopupYn'] == 'y' && $policy['paycoSellerKey'] != '' && $usePayco;
        };

        if ($usePaycoEventPopup()) {
            $result = $callPaycoCouponApi();
            $resultCode = $result['resultCode'];
            $couponInfoStrList = $result['couponInfoStrList'];
            if ($resultCode == 0 && count($couponInfoStrList) > 0) {
                $paycoBenefitTitleSubs = [
                    'ILT01' => '신용/체크카드 할인 혜택',
                    'ILT02' => 'PAYCO 생애 첫 결제라면!',
                    'ILT03' => '여기에서 PAYCO 첫 결제라면!',
                ];
                $baseInfoPolicy = ComponentUtils::getPolicy('basic.info');

                $mallName = $baseInfoPolicy['mallNm'];
                StringUtils::strIsSet($mallName, '');
                if (empty($mallName) === false) {
                    $paycoBenefitTitleSubs['ILT03'] = '[' . $mallName . ']에서 PAYCO 첫 결제라면!';
                }
                $couponInfoArr = [];

                foreach ($couponInfoStrList as $couponInfoStr) {
                    preg_match('/(.*)\_(.*)/', $couponInfoStr, $matches);
                    $couponInfoArr[$matches[1]] = [
                        'title' => $paycoBenefitTitleSubs[$matches[1]],
                        'text'  => $matches[2],
                    ];
                }
                ksort($couponInfoArr);
                $this->setData('couponInfoArr', $couponInfoArr);
                $this->setData(
                    'eventPopupOption', [
                        'top'  => $policy['eventPopupTop'],
                        'left' => $policy['eventPopupLeft'],
                    ]
                );
                $userFilePathResolver = \App::getInstance('user.path');
                $templateDir = $userFilePathResolver->data('common', 'payco', 'event', 'pc');
                $this->getView()->setTemplateDir($templateDir);
                $this->getView()->setCompileDir($templateDir);
                $this->getView()->setPageName('00_payco_benefit');
            } else {
                $this->json(
                    [
                        'resultMessage' => count($couponInfoStrList) < 1 ? 'payco event popup list is zero' : 'payco event popup response error',
                        'resultCode'    => $resultCode,
                        'errorMessage'  => $result['errorMessage'],
                    ]
                );
            }
        } else {
            $this->json(['resultMessage' => 'not use payco event popup']);
        }
    }
}
