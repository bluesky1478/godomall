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

namespace Bundle\Controller\Mobile\Member;

use App;
use Component\Member\Member;
use Component\Member\MemberSleep;
use Component\Validator\Validator;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Request;
use Session;

/**
 * Class WakePsController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class WakePsController extends \Controller\Mobile\Controller
{
    private $isLogin = false;

    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            if (!Session::has(MemberSleep::SESSION_WAKE_INFO)) {
                throw new Exception(__('휴면회원 해제에 필요한 정보를 찾을 수 없습니다.'));
            }
            $wakeInfo = Session::get(MemberSleep::SESSION_WAKE_INFO);

            /** @var \Bundle\Component\Member\Member $member */
            $member = App::load('\\Component\\Member\\Member');
            /** @var \Bundle\Component\Member\MemberSleep $memberSleep */
            $memberSleep = App::load('\\Component\\Member\\MemberSleep');
            $memberSns = App::load('\\Component\\Member\\MemberSnsService');
            $memberSnsData = $memberSns->getMemberSns($wakeInfo['memNo']);

            // SMS/Email 인증번호 체크 후 로직이 프론트와 다르기때문에 모바일 컨트롤러에서 처리
            switch (Request::post()->get('mode')) {
                case 'normal':
                    $this->isLogin = true;
                    break;
                case 'info':
                    $selectWakeInfo = Request::post()->get('selectWakeInfo');
                    $wakeCheckInfo = Request::post()->get('wakeCheckInfo');
                    if (!Validator::required($wakeCheckInfo)) {
                        throw new Exception(__('가입 시 입력하신 회원정보를 입력해 주시기 바랍니다.'));
                    }
                    $checkEmail = $selectWakeInfo == 'email' && $wakeInfo['email'] == $wakeCheckInfo;
                    $checkCellPhone = $selectWakeInfo == 'cellPhone' && str_replace('-', '', $wakeInfo['cellPhone']) == $wakeCheckInfo;
                    if ($checkEmail || $checkCellPhone) {
                        $this->isLogin = true;
                    } else {
                        throw new Exception(__('입력하신 정보와 일치하지 않습니다.'));
                    }
                    break;
                case 'certificationAuthNumber':
                    if (Session::has(MemberSleep::SESSION_WAKE_INFO) === false) {
                        throw new AlertRedirectException(__('본인인증 필수 값이 없습니다. 다시 시도하세요.'), null, null, '../member/login.php');
                    }
                    $inputCertify = Request::post()->get('number', -1);

                    // 인증번호 비교
                    if ($wakeInfo['certificationCode'] != $inputCertify) {
                        throw new Exception(__("인증번호가 틀렸습니다."));
                    }
                    $this->isLogin = true;
                    break;
                default:
                    /** @var \Bundle\Controller\Front\Member\WakePsController $front */
                    $front = \App::load('\\Controller\\Front\\Member\\WakePsController');
                    $front->index();
                    break;
            }

            if ($this->isLogin) {
                $coupon = \App::load('\\Component\\Coupon\\Coupon');
                $memberData = $memberSleep->wake($wakeInfo['sleepNo']);
                $coupon->setAutoCouponMemberSave('wake', $wakeInfo['memNo'], $memberData[0]['groupSno']);
                $this->json('../member/wake_complete.php');
            }
        } catch (AlertRedirectException $e) {
        } catch (AlertBackException $e) {
            throw $e;
        } catch (Exception $e) {
            if (Request::isAjax() === true) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new AlertOnlyException($e->getMessage());
            }
        }
    }
}
