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

namespace Bundle\Controller\Front\Popup;

use Component\Design\DesignPopup;
use Message;
use Globals;
use Request;
use Exception;

/**
 * 팝업 데이터 처리
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class PopupPsController extends \Controller\Front\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $postValue = Request::request()->toArray();

        //--- DesignPopup 정의
        $designPopup = new DesignPopup();

        switch (Request::request()->get('mode')) {
            // 사용 가능한 팝업 체크
            case 'popupOpen':
                try {
                    // 팝업 테이터
                    $getData = $designPopup->getUsePopupData($postValue['currentUrl']);
                    if (empty($getData) === false) {
                        echo json_encode($getData);
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                break;
            // 회원정보 이벤트 팝업 오픈
            case 'memberEventPopupOpen':
                try {
                    $modifyEvent = \App::load('\\Component\\Member\\MemberModifyEvent');
                    $session = \App::getInstance('session')->get(\Component\Member\Member::SESSION_MEMBER_LOGIN);
                    $mallSno = \Component\Mall\Mall::getSession('sno');
                    $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);
                    $getData = $modifyEvent->getActiveMemberModifyEvent($mallSno); // 진행중인 이벤트 (회원정보 수정 / 평생회원)
                    $couponConfig = gd_policy('coupon.config'); // 쿠폰 설정값 정보
                    $mileageBasic = gd_policy('member.mileageBasic'); // 마일리지 사용 여부
                    foreach ($getData as $eventKey => $eventInfo) {
                        if (\Globals::get('gGlobal.isUse')) {
                            foreach (\Globals::get('gGlobal.useMallList') as $val) {
                                if ($val['sno'] == $mallSno) {
                                    $getData[$eventKey]['domainFl'] = $val['domainFl'];
                                }
                            }
                        } else {
                            $getData[$eventKey]['domainFl'] = 'kr'; //해외몰을 사용 하지 않을 경우 $getData[$eventKey]['domainFl']가 리턴되지 않아 강제로 기본 사이트 코드를 할당
                        }

                        if ($eventInfo['benefitType'] == 'coupon') {
                            $couponData = $modifyEvent->getDataByTable(DB_COUPON, $eventInfo['benefitCouponSno'], 'couponNo', 'couponNm');
                            $getData[$eventKey]['couponNm'] = gd_htmlspecialchars_stripslashes($couponData['couponNm']);
                        }

                        // 이벤트 참여시 미노출
                        if ($eventInfo['eventType'] === 'modify') {
                            $memberModifyEventCnt = $modifyEvent->checkDuplicationModifyEvent($eventInfo['sno'], $session['memNo'], 'modify');
                            if ($memberModifyEventCnt) {
                                unset($getData[$eventKey]);
                            }
                            if (\Cookie::has('memberEventPopup_modify') === true) {
                                unset($getData[$eventKey]);
                            }
                        }

                        if ($eventInfo['eventType'] === 'life') {
                            $memberLifeEventCnt = $modifyEvent->checkDuplicationModifyEvent($eventInfo['sno'], $session['memNo'], 'life');
                            $getMemberLifeEventCount = $modifyEvent->getMemberLifeEventCount($session['memNo']);
                            $getData[$eventKey]['memNo'] = $session['memNo'];
                            if ($memberLifeEventCnt  || $getMemberLifeEventCount > 0) {
                                unset($getData[$eventKey]);
                            }
                            if (\Cookie::has('memberEventPopup_life') === true) {
                                unset($getData[$eventKey]);
                            }
                        }

                        // 쿠폰 or 마일리지 미사용시 혜택내용 미노출 처리
                        if ($eventInfo['benefitType'] == 'coupon' && $couponConfig['couponUseType'] === 'n') {
                            $getData[$eventKey]['benefitType'] = 'manual';
                        }

                        if ($eventInfo['benefitType'] == 'mileage' && $mileageBasic['payUsableFl'] === 'n') {
                            $getData[$eventKey]['benefitType'] = 'manual';
                        }
                    }

                    if (empty($getData) === false) {
                        echo json_encode($getData);
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                break;
            // 마이앱 팝업 오픈
            case 'myapp_popup_open':
                try {
                    // myapp 설정
                    $myappConfig = gd_policy('myapp.config');
                    // 사용자 팝업 이미지 등록되어 있는 경우
                    if (is_file(\UserFilePath::getBasePath() . \UserFilePath::data('commonimg', 'myapp', 'img_install_custom.' . $myappConfig['promote_popup']['fileExtension'])->www()) && $myappConfig['promote_popup']['popupType'] != 'default') {
                        $myappConfig['promote_popup']['popupImage'] = \UserFilePath::data('commonimg', 'myapp', 'img_install_custom.' . $myappConfig['promote_popup']['fileExtension'])->www();
                    } else {
                        $myappConfig['promote_popup']['popupImage'] = \UserFilePath::data('commonimg', 'myapp', 'img_install_default.png')->www();
                    }

                    // 하루 보이지 않음 기능
                    if ($myappConfig['promote_popup']['checkDayUse'] == 'n') {
                        unset($myappConfig['promote_popup']['checkDayUse']);
                    }

                    // 팝업 링크
                    $myapp = \App::load('Component\\Myapp\\Myapp');
                    $userOs = $myapp->getMyappOsAgent();
                    $myappConfig['promote_popup']['appPkgName'] = $myappConfig['app_store'][$userOs. 'PkgName'];
                    $myappConfig['promote_popup']['device'] = $userOs;
                    $myappConfig['promote_popup']['appUrl'] =$myappConfig['app_store'][$userOs. 'AppUrl'];

                    // 앱 설치 권장 팝업
                    echo json_encode($myappConfig['promote_popup']);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                break;
        }
        exit();
    }
}
