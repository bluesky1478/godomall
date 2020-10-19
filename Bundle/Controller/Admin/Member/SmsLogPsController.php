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
use Framework\Debug\Exception\LayerException;

/**
 * Class SmsLogPsController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class SmsLogPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $logger = \App::getInstance('logger');
        $request = \App::getInstance('request');
        $componentSmsLog = \App::load('Component\\Sms\\SmsLog');
        $mode = $request->post()->get('mode');
        $logger->debug(__METHOD__ . ', mode=' . $mode);
        switch ($mode) {
            case 'modify':
                try {
                    $requestParams = $request->post()->all();
                    $godoSms = new GodoSmsServerApi();
                    $smsLog = $componentSmsLog->getSmsLog('*', $requestParams['smsLogSno']);
                    if ($smsLog['replaceCodeType'] == 'none') {
                        // SMS 결과 수신 처리
                        if ($godoSms->smsReserveChange($requestParams['sno'], 'modify', $requestParams['contents']) !== true) {
                            throw new LayerException(__('수정이 실패되었습니다.'));
                        } else {
                            $componentSmsLog->editContents($requestParams);
                            $this->json(__('수정되었습니다.'));
                        }
                    } else {
                        // SMS 결과 수신 처리
                        if ($godoSms->smsSendListReserveChange($requestParams['sno'], 'modify', $requestParams['contents']) !== true) {
                            throw new LayerException(__('수정이 실패되었습니다.'));
                        } else {
                            $componentSmsLog->editContents($requestParams);
                            $this->json(__('수정되었습니다.'));
                        }
                    }
                } catch (LayerException $e) {
                    $this->json($e->getMessage());
                }
                break;
        }
    }
}
