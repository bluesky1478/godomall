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

namespace Bundle\Controller\Front\Service;

use Component\Promotion\Poll;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertBackException;
use Request;
use Exception;
use Session;

class PollPsController extends \Controller\Front\Controller
{
    public function index()
    {
        $postValue = Request::post()->toArray();

        try{
            $poll = new Poll();
            $data = $poll->getPollData($postValue['code'], null, ['sno', 'pollMileage', 'pollItem', 'pollMemberLimitFl', 'pollMemberLimitCnt', 'pollTitle']);

            switch ($postValue['mode']) {
                case 'regist':
                    $item = json_decode($data['pollItem'],true);

                    foreach ($item['itemRequired'] as $k => $v) {
                        if ($v == 'Y' && count($postValue['result'][$k]) === 0) {
                            throw new Exception(__("응답이 완료되지 않은 필수항목이 있습니다.\n확인 후 설문 응답을 완료해주세요."));
                        }
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
