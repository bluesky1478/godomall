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
namespace Bundle\Controller\Admin\Order;

use Framework\Debug\Exception\Except;
use Message;
use Request;

class OrderListExcelController extends \Controller\Admin\ExcelController
{
    /**
     * 주문리스트 엑셀다운로드
     *
     * @author sj, artherot
     * @version 1.0
     * @since 1.0
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        // 화일명 prefix
        $this->getHeader()->setFilePrefix('order_list');

        $postValue = Request::post()->toArray();

        try {
            ob_start();

            $orderAdmin = \App::load('\\Component\\Order\\OrderAdmin');

            // 주문일별 리스트에서 다운
            if (gd_isset($postValue['mode']) == 'excelDownWhole') {
                $getData = $orderAdmin->getOrderListForAdminDownload(gd_isset($postValue['formSno']), gd_isset($postValue['orderNo']));

                // 주문 상태별 리스트에서 다운
            } elseif (gd_isset($postValue['mode']) == 'excelDownStatus') {
                // --- 주문 리스트 설정 config 불러오기
                $data = gd_policy('order.defaultSearch');
                $arrOrderNo = $orderAdmin->getOrderListForAdminOrderNo(gd_isset($postValue['excelSearch']), gd_isset($postValue['excelStatus']), $data['searchPeriod']);
                $getData = $orderAdmin->getOrderListForAdminDownload(gd_isset($postValue['formSno']), gd_isset($arrOrderNo));

                // 모드가 없는경우
            } else {
                $getData = null;
                $errorMsg = __('오류가 발생 하였습니다.');
            }

            $excelError = false;

            if ($out = ob_get_clean()) {
                throw new Except('ECT_LOAD_FAIL', $out);
            }
        } catch (Except $e) {
            if ($e->ectName == 'EXCEL_DOWN_ERROR') {
                $errorMsg = $e->ectMessage;
            } else {
                // echo $e->ectMessage;
                $e->actLog();
                $errorMsg =  __('오류가 발생 하였습니다.');
            }
            $excelError = true;
        }

        $excelHeader = '<html>' . chr(10);
        $excelHeader .= '<head>' . chr(10);
        $excelHeader .= '<title>Excel Down</title>' . chr(10);
        $excelHeader .= '<meta http-equiv="Content-Type" content="text/html; charset=' . SET_CHARSET . '" />' . chr(10);
        $excelHeader .= '<style>' . chr(10);
        $excelHeader .= 'br {mso-data-placement:same-cell;}' . chr(10);
        $excelHeader .= 'td {mso-number-format:"\@";} ' . chr(10);
        $excelHeader .= '.title{font-weight:bold; background-color:#F6F6F6; text-align:center;} ' . chr(10);
        $excelHeader .= '</style>' . chr(10);
        $excelHeader .= '</head>' . chr(10);
        $excelHeader .= '<body>' . chr(10);

        $excelFooter = '</body>' . chr(10);
        $excelFooter .= '</html>' . chr(10);

        // 엑셀 상단
        echo $excelHeader;

        // 엑셀 내용
        if ($excelError === true || empty($getData) === true) {
            echo $errorMsg;
        } else {
            echo '<table border="1">' . chr(10);

            // 상단 타이틀
            echo '<tr>' . chr(10);
            if (empty($getData['formFieldTxt']) === false) {
                foreach ($getData['formFieldTxt'] as $val) {
                    echo '<td class="title">' . $val . '</td>' . chr(10);
                }
            }
            echo '</tr>' . chr(10);

            // 주문 내역
            if (empty($getData['list']) === false) {
                foreach ($getData['list'] as $data) {
                    echo '<tr>' . chr(10);
                    if (empty($getData['formFieldTxt']) === false) {
                        foreach ($getData['formField'] as $val) {
                            if ($val == 'og.optionInfo') {
                                $data[preg_replace('/[a-z]*\./', '', $val)] = str_replace(STR_DIVISION, ':', str_replace(MARK_DIVISION, ', ', $data[preg_replace('/[a-z]*\./', '', $val)]));
                            }
                            if ($val == 'og.optionAddInfo') {
                                $data[preg_replace('/[a-z]*\./', '', $val)] = str_replace(STR_DIVISION, ':', str_replace(MARK_DIVISION, ', ', $data[preg_replace('/[a-z]*\./', '', $val)]));
                            }
                            if ($val == 'og.optionTextInfo') {
                                $data[preg_replace('/[a-z]*\./', '', $val)] = str_replace(STR_DIVISION, ':', str_replace(MARK_DIVISION, ', ', $data[preg_replace('/[a-z]*\./', '', $val)]));
                            }
                            if ($val == 'o.orderStatus') {
                                $data[preg_replace('/[a-z]*\./', '', $val)] = $orderAdmin->getOrderStatusAdmin($data[preg_replace('/[a-z]*\./', '', $val)]);
                            }
                            if ($val == 'o.settleKind') {
                                $data[preg_replace('/[a-z]*\./', '', $val)] = $orderAdmin->printSettleKind($data[preg_replace('/[a-z]*\./', '', $val)]);
                            }
                            if (empty($val) === false) {
                                echo '<td> ' . $data[preg_replace('/[a-z]*\./', '', $val)] . '</td>' . chr(10);
                            } else {
                                echo '<td></td>' . chr(10);
                            }
                        }
                    }
                    echo '</tr>' . chr(10);
                }
            }

            echo '</table>' . chr(10);
        }

        // 엑셀 하단
        echo $excelFooter;
    }
}
