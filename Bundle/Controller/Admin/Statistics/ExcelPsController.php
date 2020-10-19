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

namespace Bundle\Controller\Admin\Statistics;


use Component\Excel\ExcelFromHtmlStatisticsConvert;
use Component\Excel\ExcelVisitStatisticsConvert;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ArrayUtils;
use Framework\Utility\StringUtils;
use Logger;
use Request;

/**
 * Class 통계 엑셀 요청 처리 컨트롤러
 * @package Bundle\Controller\Admin\Statistics
 * @author  yjwee
 */
class ExcelPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            $requestPostParams = Request::post()->all();
            Logger::info(__METHOD__ . ', mode=>' . $requestPostParams['mode']);

            /**
             * 요청 처리
             */
            switch ($requestPostParams['mode']) {
                case 'excel_form_html_convert':
                    if ($requestPostParams['excel_name'] == '') {
                        throw new Exception(__('요청을 찾을 수 없습니다.'));
                        break;
                    }

                    $this->streamedDownload($requestPostParams['excel_name'] . '.xls');
                    $excel = new ExcelFromHtmlStatisticsConvert();
                    $excel->setExcelDownByJoinData(urldecode($requestPostParams['data']));
                    exit();
                    break;
                case 'visit_excel_download' :
                    if ($requestPostParams['excel_name'] == '') {
                        throw new Exception(__('요청을 찾을 수 없습니다.'));
                        break;
                    }

                    $this->streamedDownload($requestPostParams['excel_name'] . '.xls');
                    $excel = new ExcelVisitStatisticsConvert();
                    $excel->setExcelDownByJoinData(urldecode($requestPostParams['data']));
                    exit();
                    break;
                // 방문자 IP 통계 다운로드
                case 'visit_ip_excel_download' :
                    if ($requestPostParams['excel_name'] == '') {
                        throw new Exception(__('요청을 찾을 수 없습니다.'));
                        break;
                    }

                    $this->streamedDownload($requestPostParams['excel_name'] . '.xls');
                    $excel = new ExcelVisitStatisticsConvert();
                    $excelData = $excel->setExcelVisitIpStatisticsDown($requestPostParams);
                    $excel->setExcelDownByJoinData($excelData);
                    exit();
                    break;

                // 매출통계 현황 다운로드
                case 'sales_excel_download':
                    if ($requestPostParams['excel_name'] == '') {
                        throw new Exception(__('요청을 찾을 수 없습니다.'));
                        break;
                    }

                    $this->streamedDownload($requestPostParams['excel_name'] . '.xls');
                    $excel = new ExcelFromHtmlStatisticsConvert();
                    $excel->setExcelDownByJoinData(urldecode($requestPostParams['data']));
                    exit();
                    break;

                // 주문분석 현황 다운로드
                case 'order_excel_download':
                    if ($requestPostParams['excel_name'] == '') {
                        throw new Exception(__('요청을 찾을 수 없습니다.'));
                        break;
                    }

                    $this->streamedDownload($requestPostParams['excel_name'] . '.xls');
                    $excel = new ExcelFromHtmlStatisticsConvert();
                    $excel->setExcelDownByJoinData(urldecode($requestPostParams['data']));
                    exit();
                    break;

                // 엑셀다운로드 공통
                case 'excel_download':
                    if ($requestPostParams['excel_name'] == '') {
                        throw new Exception(__('요청을 찾을 수 없습니다.'));
                        break;
                    }

                    $this->streamedDownload($requestPostParams['excel_name'] . '.xls');
                    $excel = new ExcelFromHtmlStatisticsConvert();
                    $data = urldecode($requestPostParams['data']);
                    $replaceData = $requestPostParams['replaceData'];
                    // 특수문자 처리
                    if (empty($replaceData) === false) {
                        $replaceData = ArrayUtils::removeEmpty(explode(STR_DIVISION, urldecode($replaceData)));
                        foreach ($replaceData as $value) {
                            if (StringUtils::contains($data, $value)) {
                                $data = str_replace($value, htmlentities($value), $data);
                            }
                        }
                    }
                    $excel->setExcelDownByJoinData($data);
                    exit();
                    break;

                default:
                    throw new Exception(__('요청을 찾을 수 없습니다.'));
                    break;
            }
        } catch (Exception $e) {
            Logger::error($e->getMessage(), $e->getTrace());
            throw new AlertBackException($e->getMessage());
        }
    }
}
