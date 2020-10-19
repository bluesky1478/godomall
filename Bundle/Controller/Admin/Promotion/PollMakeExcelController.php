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
use Component\Excel\ExcelRequest;
use Exception;
use Request;

/**
 * Class ShortUrlListController
 *
 * @package Bundle\Controller\Admin\Promotion
 * @author  Young-jin Bag <kookoo135@godo.co.kr>
 */
class PollMakeExcelController extends \Controller\Admin\Controller
{

    public function index()
    {
        $poll = new Poll();
        $getValue = Request::get()->all();

        $data = $poll->getPollData($getValue['code']);
        $resultData = $poll->getpollResult($data['pollCode'], $data);
        $total['all'] = $poll->getPollCnt($data['pollCode']);
        $total['member'] = $poll->getPollCnt($data['pollCode'], true);
        $total['nonMember'] = $poll->getPollCnt($data['pollCode'], false);
        $total['mileage'] = $poll->getPollMileage($data['pollCode']);

        $pollDate = gd_date_format('Y년 m월 d일', $data['pollStartDt']) . ' ~ ';
        if ($data['pollEndDtFl'] == 'Y') $pollDate .= __('종료 시');
        else $pollDate .= gd_date_format('Y년 m월 d일', $data['pollEndDt']);

        if (empty($data['pollGroupSno']) === false) {
            $data['pollGroupNm'] = [];
            $groupSno = str_replace(INT_DIVISION, ',', $data['pollGroupSno']);
            $data['pollGroupNm'] = GroupUtil::getGroupName("sno IN (" . $groupSno . ")");
            $data['pollGroupCnt'] = $poll->getGroupCnt($data['pollCode'], array_keys($data['pollGroupNm']));
        }

        $item = json_decode($data['pollItem'], true);

        $sortData = [];
        foreach ($resultData as $k => $v) {
            $result = json_decode(stripslashes($v['pollResult']), true);
            $resultEtc = json_decode(stripslashes($v['pollResultEtc']), true);

            $sortData[$k]['key'] = $k + 1;
            $sortData[$k]['regDt'] = $v['regDt'];
            $sortData[$k]['memId'] = $v['memId'] ?? '('.__('비회원').')';
            $sortData[$k]['memNm'] = $v['memNm'] ?? '-';
            $sortData[$k]['group'] = $v['groupSno'] ? GroupUtil::getGroupName("sno IN (" . $v['groupSno'] . ")")[$v['groupSno']] : '-';

            foreach ($item['itemAnswerType'] as $key => $val) {
                if ($val == 'sub') {
                    if (empty($result[$key]) === false) $sortData[$k]['item' . $key] = $result[$key];
                } else {
                    foreach ($item['itemAnswer'][$key] as $_key => $_value) {
                        $itemAnswer = $item['itemAnswer'][$key];
                        $itemLastAnswer = array_pop($itemAnswer);
                        if ($item['itemResponseType'][$key] == 'radio') {
                            if ($_key == $result[$key]) {
                                if ($item['itemAnswer'][$key][$result[$key]] == 'ETC') {
                                    $sortData[$k]['item' . $key] = __('기타').' : ';
                                    $sortData[$k]['item' . $key] .= $resultEtc[$key];
                                } else {
                                    $sortData[$k]['item' . $key] = $item['itemAnswer'][$key][$result[$key]];
                                }
                            }
                        } else {
                            $checkboxItem = [];
                            foreach ($result[$key] as $__key => $__value) {
                                if ($item['itemAnswer'][$key][$__value] == 'ETC') {
                                    $checkboxItem[] = __('기타').' : ' . $resultEtc[$key];
                                } else {
                                    $checkboxItem[] = $item['itemAnswer'][$key][$__value];
                                }
                            }
                            $sortData[$k]['item' . $key] = @implode(',', $checkboxItem);
                        }
                    }
                }
            }
        }

        $groupFl = $poll->getObject('groupFl');

        $dataTitle = [
            __('번호'),
            __('응답시간'),
            __('응답자 회원아이디'),
            __('응답자명'),
            __('응답자 회원등급'),

        ];
        $dataTitleMerge = array_merge($dataTitle,array_values($item['itemTitle']));

        $excel = \App::load('\\Component\\Excel\\ExcelDataConvert');
        $this->streamedDownload($data['pollTitle'] . '.xls');

        $excelHeader = '<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko">' . chr(10);
        $excelHeader .= '<head>' . chr(10);
        $excelHeader .= '<title>Excel Down</title>' . chr(10);
        $excelHeader .= '<meta http-equiv="Content-Type" content="text/html; charset=' . SET_CHARSET . '" />' . chr(10);
        $excelHeader .= '<style>' . chr(10);
        $excelHeader .= 'br{mso-data-placement:same-cell;}' . chr(10);
        $excelHeader .= '.xl31{mso-number-format:"0_\)\;\\\(0\\\)";}' . chr(10);
        $excelHeader .= '.xl24{mso-number-format:"\@";} ' . chr(10);
        $excelHeader .= '.title{font-weight:bold; background-color:#F6F6F6; text-align:center;} ' . chr(10);
        $excelHeader .= 'table{border-collapse: collapse; font-size:12px;} ' . chr(10);
        $excelHeader .= 'table, th, td{border: 1px solid #000; padding:5px;} ' . chr(10);
        $excelHeader .= '</style>' . chr(10);
        $excelHeader .= '</head>' . chr(10);
        $excelHeader .= '<body>' . chr(10);

        $tableData .= '
            <table>
            <tr>
                <th>'.__('참여대상').'</th>
                <td colspan="4">' . $groupFl[$data['pollGroupFl']] . '</td>
            </tr>
            <tr>
                <th>'.__('참여자 현황').'</th>
                <td colspan="4">'.
                    __('총 응답자'). __('%d명', number_format($total['all']));
        if ($data['pollGroupFl'] == 'all' || $data['pollMileage'] > 0) {
            $tableData .= '(';
            if ($data['pollGroupFl'] == 'all') {
                $tableData .= __('회원 ') . __('%d명', number_format($total['member'])).' / '. __('비회원') . __('%d명', number_format($total['nonMember']));
            }
            if ($data['pollMileage'] > 0) {
                $tableData .= __('총 지급 마일리지 ') . number_format($total['mileage']) . __('원');
            }
            $tableData .= ')';
        }
        if ($data['pollGroupFl'] == 'select' && is_array($data['pollGroupNm']) === true) {
            $tableData .= '
                </td>
            </tr>
            <tr>
                <th>'.__('회원등급별<br />참여자 현황').'</th>
                <td colspan="4">
            ';
            foreach ($data['pollGroupNm'] as $k => $v) {
                $tableData .= '- ' . $v . ' : ' . __('%d명', number_format($data['pollGroupCnt'][$k])).'(' . floor(($data['pollGroupCnt'][$k] * 100) / $total['all']) . '%)<br />';
            }
            $tableData .= '
                </td>
            </tr>
            ';
        }
        $tableData .= '
            </table><br />
        ';

        $tableData .= '
            <table>
            <tr>
        ';
        foreach ($dataTitleMerge as $v) {
            $tableData .= '<th>' . $v . '</th>';
        }
        $tableData .= '
            </tr>
            ';
        foreach ($sortData as $key => $val) {
            $tableData .= '<tr>';
            foreach ($val as $k => $v) {
                $tableData .= '<td>' . $v . '</td>';
            }
            $tableData .= '</tr>';
        }
        $tableData .= '
            </table>
        ';

        $excelFooter = '</body>' . chr(10);
        $excelFooter .= '</html>' . chr(10);

        echo $excelHeader;
        echo $tableData;
        echo $excelFooter;
        exit();
    }
}
