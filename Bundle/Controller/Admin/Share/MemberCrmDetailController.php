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

use App;
use Component\Agreement\BuyerInformCode;
use Component\Member\Member;
use Component\Member\MemberVO;
use Component\Member\Util\MemberUtil;
use Framework\Utility\SkinUtils;
use Request;
use Session;

/**
 * Class 관리자-CRM 회원상세내역
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class MemberCrmDetailController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('member', 'member', 'crm');

        $request = App::getInstance('request');
        $session = App::getInstance('session');

        $memberService = new Member();

        $memberNo = $request->get()->get('memNo', $request->post()->get('memNo'));
        $memberData = $this->getData('memberData');
        $memberData = array_merge($memberData, $memberService->getLastAgreementNotificationByMember($memberNo));

        $vo = new MemberVO($memberData);
        $vo->adminViewFormat();
        $memberData = $vo->toArray();

        $session->set(Member::SESSION_MODIFY_MEMBER_INFO, $memberData);
        //@formatter:off
        $checked = SkinUtils::setChecked(['memberFl', 'appFl', 'maillingFl', 'smsFl', 'sexFl', 'marriFl', 'expirationFl',], $memberData);
        //@formatter:on
        $memberData['privateApprovalOptionFl'] = json_decode(stripslashes($memberData['privateApprovalOptionFl']), true);
        $memberData['privateOfferFl'] = json_decode(stripslashes($memberData['privateOfferFl']), true);
        $memberData['privateConsignFl'] = json_decode(stripslashes($memberData['privateConsignFl']), true);

        /** @var \Bundle\Component\Member\History $history */
        $history = App::load('\\Component\\Member\\History');
        $memberData = array_merge($memberData, $history->getLastReceiveAgreementByMember($memberNo));
        $memberHistory = $history->getMemberHistory($memberNo, $request->get()->get('page', 1), $request->get()->get('pageNum', 10));

        $memberData['recommendCnt'] = $memberService->getRecommendCount($memberData['memId']);

        // 본인인증확인방법
        if ($memberData['rncheck'] == 'realname') {
            $memberData['rncheckSrt'] = __('실명인증');
        } elseif ($memberData['rncheck'] == 'ipin') {
            $pakey = substr($memberData['pakey'], 0, 2) . str_repeat('*', strlen(substr($memberData['pakey'], 2)));
            $memberData['rncheckSrt'] = __('아이핀 인증 (%s)', $pakey);
        } elseif ($memberData['rncheck'] == 'authCellphone') {
            $pakey = explode(STR_DIVISION, $memberData['pakey']);
            $memberData['rncheckSrt'] = __('휴대폰 본인확인 (%s)', $pakey[1]);
        } else {
            $memberData['rncheckSrt'] = __('인증안함');
        }

        /** @var \Bundle\Component\Agreement\BuyerInform $buyerInform */
        $buyerInform = App::load('\\Component\\Agreement\\BuyerInform');
        $privateApprovalOption = $buyerInform->getInformDataArray(BuyerInformCode::PRIVATE_APPROVAL_OPTION, 'sno,informNm,content', true, $memberData['mallSno']);
        $privateOffer = $buyerInform->getInformDataArray(BuyerInformCode::PRIVATE_OFFER, 'sno,informNm,content', true, $memberData['mallSno']);
        $privateConsign = $buyerInform->getInformDataArray(BuyerInformCode::PRIVATE_CONSIGN, 'sno,informNm,content', true, $memberData['mallSno']);

        // 하이픈 제거
        $memberData['fax'] = str_replace("-", "", $memberData['fax']);
        $memberData['phone'] = str_replace("-", "", $memberData['phone']);
        $memberData['cellPhone'] = str_replace("-", "", $memberData['cellPhone']);

        // 설치앱 디바이스 정보 및 설치 혜택 여부
        if (empty($memberNo) === false) {
            $myapp = \App::load('Component\\Myapp\\Myapp');
            $myappParams['memNo'] = $memberNo;
            $myappParams['getMode'] = 'memberCrm';
            $appDeviceInfo = $myapp->getAppDeviceInfo($myappParams);
            if (empty($appDeviceInfo) === false) {
                $this->setData('appDeviceInfo', $appDeviceInfo);
                $giveMyappBenefitFl = count($myapp->getAppInstallBenefitInfo($memberNo)) > 0 ? '혜택지급' : '미지급';
                $this->setData('installBenefitFl', $giveMyappBenefitFl);
            }
        }

        // 관리자 정보
        $this->setData('managerData', $session->get('manager'));
        // 회원 그룹 정보
        $this->setData('memberData', $memberData);
        $this->setData('mode', 'modify');
        $this->setData('modeTxt', '회원수정');
        $this->setData('joinField', MemberUtil::getJoinField($memberData['mallSno']));
        $this->setData('htmlExtra', MemberUtil::makeExtraField($memberData));
        $this->setData('data', $memberData);
        $this->setData('checked', $checked);
        $this->setData('history', $memberHistory['data']);
        $this->setData('historyPage', $memberHistory['page']);
        $this->setData('privateApprovalOption', $privateApprovalOption);
        $this->setData('privateOffer', $privateOffer);
        $this->setData('privateConsign', $privateConsign);
        $this->setData('groupCouponConditionManual', gd_policy('member.group')['couponConditionManual']);
    }
}
