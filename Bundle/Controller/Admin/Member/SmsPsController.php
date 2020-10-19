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

use Component\Godo\GodoSmsServerApi;
use Component\Sms\Exception\PasswordException;
use Component\Sms\SmsAdmin;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\LayerNotReloadException;

/**
 * SMS 관련 처리
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SmsPsController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws LayerException
     */
    public function index()
    {
        // --- 모듈 호출
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');
        $postData = $request->post()->toArray();
        $mode = $request->post()->get('mode');

        switch (gd_isset($mode)) {
            case 'smsAuto': // SMS 자동발송 설정
                try {
                    $smsAdmin = \App::load(SmsAdmin::class);
                    $smsAdmin->saveSmsAuto($postData);
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            case 'smsCallNumSave': // SMS 발신번호 저장
                try {
                    $smsAdmin = \App::load(SmsAdmin::class);
                    $result = $smsAdmin->saveCallNum($postData['smsCallNum']);
                    if ($result === true) {
                        echo 'OK';
                    } else {
                        echo 'FAIL';
                    }
                } catch (\Exception $e) {
                    echo 'FAIL';
                }
                break;

            case 'contentsRegister': // SMS 내용 등록
            case 'contentsModify': // SMS 내용 수정
                try {
                    $smsAdmin = \App::load(SmsAdmin::class);
                    $smsAdmin->saveContentsData($postData);
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            case 'contentsDelete': // SMS 내용 수정
                try {
                    $smsAdmin = \App::load(SmsAdmin::class);
                    $smsAdmin->deleteContentsData($request->post()->get('sno'));
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            case 'smsSend': // 발송
                try {
                    $smsAdmin = \App::load(SmsAdmin::class);
                    $cnt = $smsAdmin->sendSms($postData);
                    $info = ' (' . $cnt['success'] . __('건 성공') . ' / ' . $cnt['fail'] . __('건 실패') . ')';
                    $logger->info($info, $postData);
                    if ((int) $cnt['success'] > 0) {
                        echo "<script> parent.BootstrapDialog.closeAll(); </script>";
                        $this->layer(__('SMS 발송 요청이 완료되었습니다. 자세한 내용은 SMS 발송 내역 보기 확인 바랍니다.'), 'parent.location.href="' . $request->getReferer() . '"');
                    } else {
                        throw new \Exception('SEND_FAIL');
                    }
                } catch (PasswordException $e) {
                    echo "<script> parent.BootstrapDialog.closeAll(); </script>";
                    throw new LayerNotReloadException($e->getMessage(), null, null, $e->getScript(), 4000);
                } catch (\Exception $e) {
                    echo "<script> parent.BootstrapDialog.closeAll(); </script>";
                    if ($e->getMessage() == 'SEND_FAIL') {
                        throw new LayerNotReloadException(__('SMS 발송 요청이 실패했습니다.'), null, null, null, 4000);
                    } else {
                        $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                        throw new LayerNotReloadException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item, null, null, null, 4000);
                    }
                }
                break;
            case 'cancel_all_member'://예약문자 전체회원 취소
                try {
                    // --- SMS 모듈
                    $godoSms = new GodoSmsServerApi();
                    $componentSmsLog = \App::load('Component\\Sms\\SmsLog');
                    $smsLog = $componentSmsLog->getSmsLog('*', $postData['smsLogSno']);
                    if ($smsLog['replaceCodeType'] == 'none') {
                        // SMS 결과 수신 처리
                        $godoSms->smsReserveChange($postData['smsLogSno'], 'cancelAll', [], true);
                        $js = 'parent.hide_process();';
                        $js .= 'top.dialog_alert(\'전체 예약취소가 완료되었습니다.\', \'SMS 발송 내역 상세보기\', {callback: function(){parent.layer_list_search(\''.$postData['page'].'\')}});';
                        $this->js($js);
                    } else {
                        // SMS 결과 수신 처리
                        if ($godoSms->smsSendListReserveChange($postData['smsLogSno'], 'cancelAll', [], true)) {
                            //SMS 발송 내역 상세보기
                            $js = 'parent.hide_process();';
                            $js .= 'top.dialog_alert(\'전체 예약취소가 완료되었습니다.\', \'SMS 발송 내역 상세보기\', {callback: function(){parent.layer_list_search(\''.$postData['page'].'\')}});';
                            $this->js($js);
                        } else {
                            throw new LayerException('통신이 정상적이지 않습니다.\r\n잠시 후 다시 시도해 주세요.', null, null, null, 3000);
                        }
                    }
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage(), null, null, null, 3000);
                }
                break;
            case 'cancel_select_member'://예약문자 특정회원 취소
            case 'cancel_search_member'://예약문자 검색회원 취소
                try {
                    // --- SMS 모듈
                    $godoSms = new GodoSmsServerApi();
                    $componentSmsLog = \App::load('Component\\Sms\\SmsLog');
                    $smsLog = $componentSmsLog->getSmsLog('*', $postData['smsLogSno']);

                    $smeModeText = '선택';
                    if($mode == 'cancel_search_member'){
                        if($postData['smsKey'] == 'receiverCellPhone') {
                            $postData['smsKeyword'] = str_replace('-', '', $postData['smsKeyword']);
                        }
                        $postData['noPaging'] = 'y';
                        unset($postData['sno']);
                        $getData = $componentSmsLog->getSmsSendList($postData);
                        $postData['sno'] = array();
                        foreach($getData['data'] as $smsSendItem){
                            if($smsSendItem['sendCheckFl'] != 'c'){
                                $postData['sno'][] = $smsSendItem['sno'];
                            }
                        }
                        $smeModeText = '검색';
                    }

                    if ($smsLog['replaceCodeType'] == 'none') {
                        // SMS 결과 수신 처리
                        $godoSms->smsReserveChange($postData['smsLogSno'], 'cancel', $postData['sno'], true);
                        $js = 'parent.hide_process();';
                        $js .= 'top.dialog_alert(\''.$smeModeText.'회원 예약취소가 완료되었습니다.\', \'SMS 발송 내역 상세보기\', {callback: function(){parent.layer_list_search(\''.$postData['page'].'\')}});';
                        $this->js($js);
                    } else {
                        // SMS 결과 수신 처리
                        if ($godoSms->smsSendListReserveChange($postData['smsLogSno'], 'cancel', $postData['sno'], true)) {
                            $js = 'parent.hide_process();';
                            $js .= 'top.dialog_alert(\''.$smeModeText.'회원 예약취소가 완료되었습니다.\', \'SMS 발송 내역 상세보기\', {callback: function(){parent.layer_list_search(\''.$postData['page'].'\')}});';
                            $this->js($js);
                        } else {
                            echo "<script> parent.hide_process(); </script>";
                            throw new LayerException('통신이 정상적이지 않습니다.\r\n잠시 후 다시 시도해 주세요.', null, null, null, 3000);
                        }
                    }
                } catch (LayerException $e) {
                    throw new LayerException($e->getMessage(), null, null, null, 3000);
                }
                break;
        }

        exit();
    }
}
