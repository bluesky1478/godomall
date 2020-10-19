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

use Component\Promotion\Poll;
use Component\Member\Group\Util as GroupUtil;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\Except;
use Exception;
use Request;

/**
 * Class ShortUrlListController
 *
 * @package Bundle\Controller\Admin\Promotion
 * @author  Young-jin Bag <kookoo135@godo.co.kr>
 */
class PollResultController extends \Controller\Admin\Controller
{

    public function index()
    {
        $poll = new Poll();

        // --- 메뉴 설정
        $this->callMenu('promotion', 'poll', 'pollResult');
        $getValue = Request::get()->all();

        try{
            $data = $poll->getPollData(null, $getValue['sno']);
            $resultData = $poll->getpollResult($data['pollCode'], $data);
            $total['all'] = $poll->getPollCnt($data['pollCode']);
            $total['member'] = $poll->getPollCnt($data['pollCode'], true);
            $total['nonMember'] = $poll->getPollCnt($data['pollCode'], false);
            $total['mileage'] = $poll->getPollMileage($data['pollCode']);

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
            /*foreach ($item['itemAnswerType'] as $key => $val) {
                if ($val == 'obj') {
                    $itemLastAnswer = array_pop($itemAnswer);
                    if ($itemLastAnswer == 'ETC') {
                        $item['itemLastAnswer'][$key] = true;
                    }
                }
            }*/

            if (empty($data['pollGroupSno']) === false) {
                $data['pollGroupNm'] = [];
                $groupSno = str_replace(INT_DIVISION, ',', $data['pollGroupSno']);
                $data['pollGroupNm'] = GroupUtil::getGroupName("sno IN (" . $groupSno . ")");
                $data['pollGroupCnt'] = $poll->getGroupCnt($data['pollCode'], array_keys($data['pollGroupNm']));
            }

            $sortData = $graphData = [];
            foreach ($resultData as $value) {
                $result = json_decode(stripslashes($value['pollResult']), true);
                $resultEtc = json_decode(stripslashes($value['pollResultEtc']), true);

                foreach ($item['itemAnswerType'] as $key => $val) {
                    if (empty(isset($result[$key])) === false) {
                        if ($val == 'sub') {
                            if (empty($result[$key]) === false) $sortData['total'][$key]++;
                        } else {
                            if (empty($result[$key]) === false || $result[$key] === '0') $sortData['total'][$key]++;
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
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        $this->setData('data', $data);
        $this->setData('item', $item);
        $this->setData('title', $data['pollTitle']);
        $this->setData('date', $pollDate);
        $this->setData('total', $total);
        $this->setData('deviceFl', $poll->getObject('deviceFl'));
        $this->setData('groupFl', $poll->getObject('groupFl'));
        $this->setData('sortData', $sortData);
        $this->setData('graphData', $graphData);
    }
}
