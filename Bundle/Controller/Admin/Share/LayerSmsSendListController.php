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
use Component\Sms\SmsLog;
use Globals;
use Request;

/**
 * SMS 발송 내역 상세보기
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class LayerSmsSendListController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        // --- SMS 모듈
        $smsLog = new SmsLog();

        try {
            // _GET Data
            $getValue = Request::get()->toArray();

            if($getValue['smsKey'] == 'receiverCellPhone') {
                $getValue['smsKeyword'] = str_replace('-', '', $getValue['smsKeyword']);
            }

            // SMS 발송 내역 상세 Data
            $getData = $smsLog->getSmsSendList($getValue);
            // SMS 실패 사유
            $smsApi = \App::load('Component\\Godo\\GodoCenterServerApi');
            $errorCodeList = $smsApi->getSmsFailReasonList();
            if (empty($errorCodeList) == false) {
                $smsErrorCode = $smsLog->setSmsErrorCode() + $errorCodeList;
                ksort($smsErrorCode);
            } else {
                $smsErrorCode = $smsLog->setSmsErrorCode();
            }
            if (empty($smsErrorCode) == false) {
                foreach ($getData['data'] as $key => $val) {
                    if (is_numeric($val['failCode']) && $val['failCode'] > 0) {
                        $getData['data'][$key]['failReason'] = $smsErrorCode[$val['failCode']];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->layer($e->getMessage());
        }

        //발송전 예약문자 처리
        $logData = $smsLog->getSmsLog('reserveDt, sendStatus, receiverInfo', $getValue['smsLogSno']);
        $receiverInfo = $logData['receiverInfo'];
        if (!empty($receiverInfo)) {
            $receiverInfo = json_decode($receiverInfo, true);
            if ($receiverInfo['disableResend'] === true) {
                $this->setData('disableResend', true);
            }
        }
        if($logData['reserveDt'] != '0000-00-00 00:00:00' && $logData['reserveDt'] > date('Y-m-d H:i:s') && $logData['sendStatus'] == 'r') {
            $reserveFl = true;
            $this->getView()->setDefine('layoutContent', 'share/layer_sms_reserve_list');
            $this->setData('reserveDt', $logData['reserveDt']);
        }

        // page Url
        $pageUrl = '../' . Request::getDirectoryUri() . '/' . Request::getFileUri();

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');

        $this->setData('layerFormID', $getValue['layerFormID']);
        if($reserveFl == true) {
            $this->setData('smsSendStatus', Sms::SMS_RESERVE_STATUS);
        } else {
            $this->setData('smsSendStatus', Sms::SMS_SEND_STATUS);
        }

        $this->setData('smsErrorCode', $smsErrorCode);

        $this->setData('pageUrl', $pageUrl);
        $this->setData($getData);


    }
}
