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

namespace Bundle\Controller\Mobile\Service;

use Component\Promotion\Poll;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertBackException;
use Request;
use Exception;
use Session;

class PollPsController extends \Controller\Mobile\Controller
{
    public function index()
    {
        $postValue = Request::post()->toArray();

        try{
            $poll = new Poll();
            $data = $poll->getPollData($postValue['code'], null, ['sno', 'pollMileage', 'pollMemberLimitFl', 'pollMemberLimitCnt', 'pollTitle']);

            switch ($postValue['mode']) {
                case 'regist':
                    if ($postValue['resultData']) {
                        $resultData = json_decode(base64_decode($postValue['resultData']),true);
                        $resultData[$postValue['itemSno']] = $postValue['result'][$postValue['itemSno']];
                        $postValue['result'] = $resultData;
                        unset($resultData);
                    }
                    if ($postValue['resultEtcData']) {
                        $resultEtcData = json_decode(base64_decode($postValue['resultEtcData']),true);
                        $resultEtcData[$postValue['itemSno']] = $postValue['resultEtc'][$postValue['itemSno']];
                        $postValue['resultEtc'] = $resultEtcData;
                        unset($resultEtcData);
                    }

                    $pollResultSno = $poll->save($postValue, $data);

                    $script = "parent.location.href='../service/poll_end.php?code=" . $postValue['code'] . "';";
                    $this->js($script);
                    break;
                default:
                    throw new Exception(__('설문조사 경로가 유효하지 않습니다.'));
                    break;
            }
        } catch (Exception $e) {
            throw new AlertOnlyException($e->getMessage());
        }
    }
}
