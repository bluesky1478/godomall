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
use Framework\Debug\Exception\AlertBackException;
use Request;

class PollResultController extends \Controller\Front\Controller
{
    public function index()
    {
        $getValue = Request::get()->toArray();

        try{
            $poll = new Poll();

            $data = $poll->getPollData($getValue['code']);
            $resultData = $poll->getpollResult($getValue['code'], $data);
            $total = $poll->getPollCnt($getValue['code']);

            $pollDate = gd_date_format('Y년 m월 d일', $data['pollStartDt']) . ' ~ ';
            if ($data['pollEndDtFl'] == 'Y') $pollDate .= __('종료 시');
            else $pollDate .= gd_date_format('Y년 m월 d일', $data['pollEndDt']);

            $item = json_decode($data['pollItem'], true);

            foreach ($item['itemAnswerType'] as $key => $val) {
                if ($val == 'obj') {
                    $itemAnswer = $item['itemAnswer'][$key];
                    $itemLastAnswer = array_pop($itemAnswer);
                    if ($itemLastAnswer == 'ETC') {
                        $maxKey = max(array_keys($item['itemAnswer'][$key]));
                        $item['itemLastAnswer'][$key] = true;
                        $item['itemAnswer'][$key][$maxKey] = __('기타');
                    }
                }
            }

            $sortData = $graphData = [];
            foreach ($resultData as $value) {
                $result = json_decode(stripslashes($value['pollResult']), true);
                $resultEtc = json_decode(stripslashes($value['pollResultEtc']), true);

                foreach ($item['itemAnswerType'] as $key => $val) {
                    if (empty(isset($result[$key])) === false) {
                        if ($val == 'sub') {
                            if (count($sortData[$key]) < 20 && $result[$key]) $sortData[$key][] = $result[$key];
                        } else {
                            foreach ($item['itemAnswer'][$key] as $_key => $_value) {
                                if ($item['itemResponseType'][$key] == 'radio') {
                                    if ($_key == $result[$key]) {
                                        $sortData[$key][$_key]++;
                                        $graphData[$key]++;
                                    }
                                } else {
                                    if (in_array($_key, $result[$key])) {
                                        $sortData[$key][$_key]++;
                                        $graphData[$key]++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (AlertBackException $e) {
            throw new AlertBackException($e->getMessage());
        } catch (\Exception $e) {
            $this->js($returnScript);
        }

        $this->setData('title', $data['pollTitle']);
        $this->setData('date', $pollDate);
        $this->setData('total', $total);
        $this->setData('data', $item);
        $this->setData('sortData', $sortData);
        $this->setData('graphData', $graphData);
    }
}
