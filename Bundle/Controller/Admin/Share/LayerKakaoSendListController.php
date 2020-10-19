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
use Component\Member\KakaoAlrim;
use Globals;
use Request;

/**
 * 카카오알림톡 발송 내역 상세보기
 *
 */
class LayerKakaoSendListController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        // --- SMS 모듈
        $smsLog = new SmsLog();
        $oKakao = new KakaoAlrim;

        try {
            // _GET Data
            $getValue = Request::get()->toArray();
            // 템플릿 버튼 데이터
            $getValue['templateButtonFl'] = true;

            if ($getValue['sendStatus'] == 'r') {
                $oKakao->updateSingleKakaoSendLogDb($getValue['smsSendKey']);
            }
            if($getValue['smsKey'] == 'receiverCellPhone') {
                $getValue['smsKeyword'] = str_replace('-', '', $getValue['smsKeyword']);
            }

            // SMS 발송 내역 상세 Data
            $getData = $smsLog->getSmsSendList($getValue);
        } catch (\Exception $e) {
            $this->layer($e->getMessage());
        }

        // page Url
        $pageUrl = '../' . Request::getDirectoryUri() . '/' . Request::getFileUri();

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->setData('layerFormID', $getValue['layerFormID']);
        $this->setData('smsSendStatus', Sms::SMS_SEND_STATUS);
        $this->setData('pageUrl', $pageUrl);
        $this->setData('oKakao', $oKakao);
        $this->setData($getData);


    }
}
