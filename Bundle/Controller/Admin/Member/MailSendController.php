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
namespace Bundle\Controller\Admin\Member;

use Component\Mail\MailUtil;
use Component\Validator\Validator;
use Exception;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Request;

/**
 * Class 관리자 메일 관리 개별/전체메일발송
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MailSendController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws AlertCloseException
     * @throws Exception
     */
    public function index()
    {
        /**
         * 네비게이션 설정
         */
        $this->callMenu('member', 'mail', 'send');

        /**
         * 요청 처리
         */
        /** @var \Bundle\Component\Mail\MailAdmin $mailAdmin */
        $mailAdmin = \App::load('\\Component\\Mail\\MailAdmin');
        $mailConfig = $mailAdmin->getMailConfig();
        $policy = ComponentUtils::getPolicy('basic.info');

        /**
         * View 데이터 설정
         */
        $template['footerReceive'] = MailUtil::loadAutoMailTemplate('footer_RECEIVE.html');
        $template['footerReject'] = MailUtil::loadAutoMailTemplate('footer_REJECT.html');
        //기본 문구 추가
        if (empty($template['footerReceive'])) {
            $template['footerReceive'] = '<p>본 메일은 {rc_today}기준, 메일 수신에 동의하신 회원님께 발송한 메일입니다.</p>';
        }
        if (empty($template['footerReject'])) {
            $template['footerReject'] = '<p style="margin-top:10px;">- 이메일의 수신을 더 이상 원하지 않으시면 [{rc_refusalKo}]를 클릭해 주세요.</p><p>- If you don’t want to receive this mail, [{rc_refusalEn}].</p>';
        }
        $this->setData('template', $template);
        $this->setData('freeMailCount', $mailConfig['freePoint']);
        $this->setData('freeMailCountView', in_array(\Globals::get('gLicense.ecKind'), ['standard', 'pro']));
        $this->setData('centerEmail', $policy['centerEmail']);

        /**
         * js 추가
         */
        $this->addScript(['member.js']);

        /**
         * PopupMode 일 경우
         */
        if (Request::get()->get('popupMode', '') === 'yes') {
            $memNo = Request::get()->get('memNo');
            if (Validator::number($memNo, null, null, true) === false) {
                throw new Exception(__('유효하지 않은 회원번호 입니다.'));
            }

            /**@var  \Bundle\Component\Member\Member $member */
            $member = \App::load('\\Component\\Member\\Member');
            $memberData = $member->getMember($memNo, 'memNo', 'memNo,memNm,email,memId,maillingFl');

            if (empty($memberData)) {
                throw new AlertCloseException('회원정보가 없습니다.');
            }

            $this->setData('memberData', $memberData);
            $this->getView()->setDefine('layout', 'layout_blank.php');
        }
    }
}
