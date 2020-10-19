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

use Component\Coupon\CouponAdmin;
use Component\Sms\SmsSender;
use Framework\Debug\Exception\LayerNotReloadException;
use Message;
use Request;

class CouponPsController extends \Controller\Admin\Controller
{

    /**
     * 쿠폰 처리
     * [관리자 모드] 쿠폰 처리
     *
     * @author su
     */
    public function index()
    {
        try {
            $couponAdmin = new CouponAdmin();
            $ypage = Request::post()->get('ypage');
            switch (Request::post()->get('mode')) {
                case 'insertCouponConfig':
                case 'modifyCouponConfig':
                    $postValue = Request::post()->toArray();
                    $couponConfigArrData = [
                        'couponUseType'               => Request::post()->get('couponUseType'),
                        'chooseCouponMemberUseType'   => Request::post()->get('chooseCouponMemberUseType'),
                        'couponDisplayType'           => Request::post()->get('couponDisplayType'),
                        'couponOptPriceType'          => Request::post()->get('couponOptPriceType'),
                        'couponAddPriceType'          => Request::post()->get('couponAddPriceType'),
                        'couponTextPriceType'         => Request::post()->get('couponTextPriceType'),
                        //                      'couponApplyDuplicateType' => Request::post()->get('couponApplyDuplicateType'),
                        'couponAutoRecoverType'       => Request::post()->get('couponAutoRecoverType'),
                        'productCouponChangeLimitType'       => Request::post()->get('productCouponChangeLimitType'),
                        'couponOfflineDisplayType'    => Request::post()->get('couponOfflineDisplayType'),
                    ];

                    // 생일 축하 쿠폰 발급시점 설정
                    if (empty($postValue['birthdayCouponReserveDate']) == false && (is_numeric($postValue['birthdayCouponReserveDate']) == false || $postValue['birthdayCouponReserveDate'] < 1 || $postValue['birthdayCouponReserveDate'] > 31)) {
                        throw new LayerNotReloadException(__("생일 축하 쿠폰 월별 발급일은 1~31 사이로 입력하세요."));
                    }
                    if ($postValue['birthdayCouponReserveType'] == 'month') {
                        if (empty($postValue['birthdayCouponReserveDate'])) {
                            throw new LayerNotReloadException(__("생일 축하 쿠폰 월별 발급일을 입력하세요."));
                        } else {
                            $couponConfigArrData['birthdayCouponReserveMonth'] = $postValue['birthdayCouponReserveMonth'];
                            $couponConfigArrData['birthdayCouponReserveDate'] = $postValue['birthdayCouponReserveDate'];
                        }
                    } else {
                        $couponConfigArrData['birthdayCouponReserveDays'] = $postValue['birthdayCouponReserveDays'];
                    }
                    $couponConfigArrData['birthdayCouponReserveType'] = $postValue['birthdayCouponReserveType'];
                    gd_set_policy('coupon.config', $couponConfigArrData);
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.href="coupon_config.php";');
                    break;
                case 'insertCouponRegist':
                case 'modifyCouponRegist':
                    // 모듈 호출
                    $postValue = Request::post()->toArray();
                    $filesValue = Request::files()->toArray();
                    if (Request::post()->get('couponKind') == 'online') {
                        $couponAdmin->saveCoupon($postValue, $filesValue);
                        $this->layer(__('저장이 완료되었습니다.'), 'parent.unload_callback("' . $postValue['couponEventType'] . '");top.location.href="coupon_list.php?page=' . $ypage . '";');
                    } else if (Request::post()->get('couponKind') == 'offline') {
                        $couponNo = $couponAdmin->saveOfflineCoupon($postValue, $filesValue);
                        if (Request::post()->get('couponAuthType') == 'y') {
                            $this->layer(__('저장이 완료되었습니다.') . '<br/>'.__('쿠폰인증번호 등록으로 이동합니다'), 'top.location.href="coupon_offline_regist.php?layer=authCode&couponNo=' . $couponNo . '";');
                        } else {
                            $this->layer(__('저장이 완료되었습니다.'), 'top.location.href="coupon_offline_list.php?page=' . $ypage . '";');
                        }
                    }
                    break;
                case 'modifyCouponList':
                case 'modifyCouponOfflineList':
                    $couponAdmin->changeCouponType();
                    break;
                case 'deleteCouponList':
                case 'deleteCouponOfflineList':
                    $couponAdmin->deleteCoupon();
                    if (Request::post()->get('mode') == 'deleteCouponList') {
                        $this->layer(__('삭제 되었습니다.'), 'top.location.reload()');
                    } else if (Request::post()->get('mode') == 'deleteCouponOfflineList') {
                        $this->layer(__('삭제 되었습니다.'), 'top.location.href="coupon_offline_list.php";');
                    }
                    break;
                case 'deleteCouponManage':
                case 'deleteCouponOfflineManage':
                    $couponAdmin->deleteMemberCoupon();
                    $this->layer(__('삭제 되었습니다.'), 'top.location.reload()');
                    break;
                case 'insertCouponSave':
                    $request = \App::getInstance('request');
                    if(!$couponAdmin->checkCouponType($request->post()->get('couponNo'), $request->post()->get('couponType'))) {
                        throw new LayerNotReloadException(__("발급종료 상태의 쿠폰은 발급이 불가합니다."));
                        break;
                    }

                    if ($request->post()->get('kmode') === 'chk') {
                        $memNoArr = $request->post()->get('chk');
                        if ($request->post()->get('smsSendFlag') === 'y') {
                            $smsSender = \App::load(SmsSender::class);
                            if($request->post()->get('passwordCheckFl') != 'n') {
                                $smsSender->validPassword($request->post()->get('password'));
                                $resultCoupon = $couponAdmin->saveMemberCouponSms($memNoArr);
                            } else {
                                $resultCoupon = $couponAdmin->saveMemberCouponSms($memNoArr, null, false);
                            }
                        } else {
                            $resultCoupon = $couponAdmin->saveMemberCoupon($memNoArr);
                        }
                    } elseif ($request->post()->get('kmode') === 'all') {
                        $searchQuery = $request->post()->get('searchQuery');
                        if ($request->post()->get('smsSendFlag') === 'y') {
                            $smsSender = \App::load(SmsSender::class);
                            if($request->post()->get('passwordCheckFl') != 'n') {
                                $smsSender->validPassword($request->post()->get('password'));
                                $resultCoupon = $couponAdmin->saveMemberCouponSms(null, $searchQuery);
                            } else {
                                $resultCoupon = $couponAdmin->saveMemberCouponSms(null, $searchQuery, false);
                            }
                        } else {
                            $resultCoupon = $couponAdmin->saveMemberCoupon(null, $searchQuery);
                        }
                    } else if ($request->post()->get('saveMemberCouponType') == 'excel') {
                        $this->streamedDownload(__('쿠폰 수동 엑셀발급 결과').'.xls');
                        $resultCoupon = $couponAdmin->saveExcelMemberCoupon($request->post()->get('smsSendFlag'));
                    }
                    if ($resultCoupon == 'T') {
                        $this->layer(__('쿠폰이 발급되었습니다.'), 'top.location.reload()');
                    } elseif ($resultCoupon == 'C') {
                        $this->layer(__('대량발급 처리되었습니다.'), 'top.location.href="coupon_save.php?couponNo=' . $request->post()->get('couponNo') . '"');
                    } elseif(!$resultCoupon) {
                        $this->layer(__('발급종료 상태의 쿠폰은 발급이 불가합니다.d'));
                    } else {
                        $nowCouponInfo = $couponAdmin->getCouponInfo($resultCoupon, 'couponNm');
                        $this->layer(__('이미 진행 중인 쿠폰 대량 발급 건이 있습니다.<br />대량 발급이 모두 완료 된 후 다시 시도해 주세요.<br /><br />발급 진행 중인 쿠폰 : (' . $nowCouponInfo['couponNm'] . ')'), 'top.location.href="coupon_save.php?couponNo=' . $request->post()->get('couponNo') . '"');
                    }
                    break;
                case 'downExcelSample':
                    $excel = \App::load('\\Component\\Excel\\ExcelDataConvert');
                    $this->streamedDownload(__('쿠폰 수동 엑셀발급 샘플파일').'.xls');
                    $excel->setExcelMemberCouponSampleDown();
                    exit();
                    break;
                case 'checkOfflineCode':
                    $offlineCode = Request::post()->get('couponOfflineCode');
                    $offlineCodeLen = mb_strlen($offlineCode);
                    $result = $couponAdmin->checkOfflineCode($offlineCode);
                    if ($offlineCodeLen < 8 || $offlineCodeLen > 12) {
                        $this->json(['result' => 'fail', 'msg' => __('쿠폰인증번호는 8자 ~ 12자 이하입니다.')]);
                    } else if ($result) {
                        $this->json(['result' => 'fail', 'msg' => __('이미 사용중인 인증번호입니다.')]);
                    } else {
                        $this->json(['result' => 'ok', 'msg' => __('사용가능한 인증번호입니다.')]);
                    }
                    break;
                case 'insertCouponOfflineCodeAuto':
                    $couponNo = Request::post()->get('couponNo');
                    $couponAmount = Request::post()->get('couponAmount');
                    $couponAdmin->setCouponOfflineAutoCode($couponNo, $couponAmount);
                    $this->layer(__('생성 되었습니다.'), 'parent.layerCouponAuth("' . $couponNo . '");');
                    break;
                case 'insertCouponOfflineCodeExcel':
                    $couponNo = Request::post()->get('couponNo');
                    //                    $this->streamedDownload('페이퍼쿠폰 인증번호 생성 결과.xls');
                    $result = $couponAdmin->setCouponOfflineExcelCode($couponNo);
                    if ($result['total']) {
                        $args = [$result['total'], $result['true'], $result['false']];
                        $msg = vsprintf(__('총 %s건 중 %s건 성공, %s건 실패 했습니다.'), $args);
                    } else {
                        $msg = __('엑셀 파일을 확인해 주세요');
                    }
                    $this->json(['msg' => $msg, 'content' => $result['content']]);
                    break;
                case 'downCouponOfflineCodeExcelSample':
                    $excel = \App::load('\\Component\\Excel\\ExcelDataConvert');
                    $this->streamedDownload(__('페이퍼쿠폰 인증번호 엑셀등록 샘플파일').'.xls');
                    $excel->setCouponOfflineExcelCodeSampleDown();
                    exit();
                    break;
                case 'deleteCouponOfflineCode':
                    $authCodeArr = Request::post()->get('layer_auth_code');
                    $result = $couponAdmin->deleteCouponOfflineCode($authCodeArr);
                    if ($result) {
                        $this->json(['result' => 'ok', 'msg' => __('삭제되었습니다.')]);
                    } else {
                        $this->json(['result' => 'fail', 'msg' => __('다시 시도해 주세요.')]);
                    }
                    break;
                case 'selectCouponNameList':
                    Request::get()->set('couponSaveType', 'auto');
                    Request::get()->set('couponEventType', Request::post()->get('couponEventType'));
                    /** @var \Bundle\Component\Coupon\CouponAdmin $couponAdmin */
                    $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
                    $couponAdminList = $couponAdmin->getCouponAdminList();
                    $couponData = [];
                    foreach ($couponAdminList['data'] as $index => $item) {
                        $couponData[$item['couponNo']] = $item['couponNm'];
                    }
                    \Logger::debug(__METHOD__, $couponData);
                    $this->json($couponData);
                    break;
                case 'registComebackCoupon':
                case 'modifyComebackCoupon':
                    // 모듈 호출
                    $postValue = Request::post()->toArray();
                    $couponAdmin->saveComebackCoupon($postValue);
                    $this->layer(__('저장이 완료되었습니다.'), 'parent.unload_callback();top.location.href="comeback_coupon_list.php?page=' . $ypage . '";');
                    break;
                case 'deleteComebackCouponList':
                    $couponAdmin->deleteComebackCoupon();
                    $this->layer(__('삭제 되었습니다.'), 'top.location.href="comeback_coupon_list.php";');
                    break;
                case 'copyComebackCouponList':
                    $couponAdmin->copyComebackCoupon();
                    $this->layer(__('복사 되었습니다.'), 'top.location.href="comeback_coupon_list.php";');
                    break;
                case 'ajaxComebackCouponActionCount':
                    $getValue = Request::post()->toArray();
                    if(!$couponAdmin->checkCouponType($getValue['couponNo'])) {
                        $this->json(array('count' => 0, 'checkCoupon' => false));
                    }
                    $getData = $couponAdmin->getComebackCouponInfo(Request::post()->get('sno'), '*');
                    $aComebackCouponMemberList = $couponAdmin->getComebackCouponMemberList($getData, 'y');
                    $checkCoupon = $couponAdmin->checkCouponLimit(Request::post()->get('couponNo'));
                    $this->json(array('count' => $aComebackCouponMemberList, 'checkCoupon' => $checkCoupon));
                    break;
                case 'sendComebackCoupon':
                    $aSno = Request::post()->get('chkCoupon');
                    $couponAdmin->sendComebackCoupon($aSno[0]);
                    $this->layer(__('컴백쿠폰 발송이 완료되었습니다.'), 'top.location.href="comeback_coupon_list.php";');
                    break;
                case 'validateBarcodeCoupon' : //바코드 기능 제거로 해당 로직 제거 (19.10.15)
                    $this->json(array('isSuccess'=>false));
                    break;
                case 'checkCouponType':
                    $getValue = Request::post()->toArray();
                    $return = $couponAdmin->checkCouponTypeArr($getValue['couponNo']);
                    $this->json(array('isSuccess'=>$return));
                    break;
                case 'saveCouponzoneConfig':
                    $postValue = Request::post()->toArray();
                    $filesValue = Request::files()->toArray();
                    $couponAdmin->saveCouponzone($postValue, $filesValue);
                    $this->layer(__('저장이 완료되었습니다.'));
                    break;
            }
        } catch (\Exception $e) {
            throw new LayerNotReloadException($e->getMessage()); //새로고침안됨
        }
    }
}
