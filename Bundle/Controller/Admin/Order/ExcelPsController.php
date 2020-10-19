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


use Component\Excel\ExcelOrderCashReceiptConvert;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Logger;
use Request;

/**
 * Class 통계 엑셀 요청 처리 컨트롤러
 * @package Bundle\Controller\Admin\Order
 * @author  sueun
 */
class ExcelPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            $requestPostParams = Request::post()->all();
            //Logger::info(__METHOD__ . ', mode=>' . $requestPostParams['mode']);

            /**
             * 요청 처리
             */
            switch ($requestPostParams['mode']) {
                case 'receipt_excel_download':
                    if ($requestPostParams['excel_name'] == '') {
                        throw new Exception(__('요청을 찾을 수 없습니다.'));
                        break;
                    }

                    $this->streamedDownload($requestPostParams['excel_name'] . '.xls');
                    $excel = new ExcelOrderCashReceiptConvert();
                    $excel->setExcelDownByJoinData(urldecode($requestPostParams['data']));
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
