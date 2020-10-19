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

use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;

/**
 * Class MemberModifyController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MemberModifyController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        try {
            $this->callMenu('member', 'member', 'modify');
            $memNo = $request->request()->get('memNo', '');
            $memberService = \App::getInstance('Member');
            if (!is_object($memberService)) {
                $memberService = new \Component\Member\Member();
            }
            $buyerInformService = \App::getInstance('BuyerInform');
            if (!is_object($buyerInformService)) {
                $buyerInformService = new \Component\Agreement\BuyerInform();
            }
            $historyService = \App::getInstance('History');
            if (!is_object($historyService)) {
                $historyService = new \Component\Member\History();
            }
            $memberService->getMemberDataWithChecked($memNo, $memberData, $checked);
            $memberData = array_merge($memberData, $memberService->getLastAgreementNotificationByMember($memNo));
            $privateApprovalOption = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_APPROVAL_OPTION, 'sno,informNm,content', true, $memberData['mallSno']);
            $privateOffer = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_OFFER, 'sno,informNm,content', true, $memberData['mallSno']);
            $privateConsign = $buyerInformService->getInformDataArray(\Component\Agreement\BuyerInformCode::PRIVATE_CONSIGN, 'sno,informNm,content', true, $memberData['mallSno']);
            $memberData = array_merge($memberData, $historyService->getLastReceiveAgreementByMember($memNo));
            $memberHistory = $historyService->getMemberHistory($memNo, $request->get()->get('page', 1), $request->get()->get('pageNum', 10));
            $memberData['recommendCnt'] = $memberService->getRecommendCount($memberData['memId']);
            if ($memberData['rncheck'] == 'realname') {
                $memberData['rncheckSrt'] = __('실명인증');
            } elseif ($memberData['rncheck'] == 'ipin') {
                $pakey = substr($memberData['pakey'], 0, 2) . str_repeat('*', strlen(substr($memberData['pakey'], 2)));
                $memberData['rncheckSrt'] = __('아이핀 인증') . '(' . $pakey . ')';
            } elseif ($memberData['rncheck'] == 'authCellphone') {
                $pakey = explode(STR_DIVISION, $memberData['pakey']);
                $memberData['rncheckSrt'] = __('휴대폰 본인확인') . ' (' . $pakey[1] . ')';
            } else {
                $memberData['rncheckSrt'] = __('인증안함');
            }
            $memberData['fax'] = str_replace("-", "", $memberData['fax']);
            $memberData['phone'] = str_replace("-", "", $memberData['phone']);
            $memberData['cellPhone'] = str_replace("-", "", $memberData['cellPhone']);
            if ($memberData['mallSno'] > DEFAULT_MALL_NUMBER) {
                // 해외상점 회원인 경우 임시로 해외상점 사용 상태로 처리한다.
                $globals = \App::getInstance('globals');
                $globals->set('gGlobal.isUse', true);
            }

            // 설치앱 디바이스 정보 및 설치 혜택 여부
            if (empty($memNo) === false) {
                $myapp = \App::load('Component\\Myapp\\Myapp');
                $myappParams['memNo'] = $memNo;
                $myappParams['getMode'] = 'memberCrm';
                $appDeviceInfo = $myapp->getAppDeviceInfo($myappParams);
                if (empty($appDeviceInfo) === false) {
                    $this->setData('appDeviceInfo', $appDeviceInfo);
                    $giveMyappBenefitFl = count($myapp->getAppInstallBenefitInfo($memNo)) > 0 ? '혜택지급' : '미지급';
                    $this->setData('installBenefitFl', $giveMyappBenefitFl);
                }
            }

            // 개인정보유효기간 > 평생회원 전환일
            $memberData['lifeMemberConversionDt'] = StringUtils::strIsSet($memberData['lifeMemberConversionDt'], '0000-00-00 00:00:00') == '0000-00-00 00:00:00' ? '' : '(' . DateTimeUtils::dateFormat('Y-m-d H:i', $memberData['lifeMemberConversionDt']) . ')';

            $this->setData('mode', 'modify');
            $this->setData('joinField', \Component\Member\Util\MemberUtil::getJoinField($memberData['mallSno']));
            $this->setData('htmlExtra', \Component\Member\Util\MemberUtil::makeExtraField($memberData));
            $this->setData('data', $memberData);
            $this->setData('checked', $checked);
            $this->setData('history', $memberHistory['data']);
            $this->setData('historyPage', $memberHistory['page']);
            $this->setData('privateApprovalOption', $privateApprovalOption);
            $this->setData('privateOffer', $privateOffer);
            $this->setData('privateConsign', $privateConsign);
            $this->setData('groupCouponConditionManual', gd_policy('member.group')['couponConditionManual']);
        } catch (\Exception $e) {
            $logger = \App::getInstance('logger');
            $logger->error($e->getTraceAsString());
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
