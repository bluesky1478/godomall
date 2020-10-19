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

use Component\VisitStatistics\VisitStatistics;
use Component\Excel\ExcelRequest;
use Request;

/**
 * 방문자 IP분석 엑셀 다운로드
 */
class VisitIpExcelController extends \Controller\Admin\Controller
{
    const LIST_FIELD = ['visitIP', 'visitOS', 'visitBrowser', 'visitPageView'];
    const DETAIL_FIELD = ['regDt', 'visitPageView', 'visitReferer'];
    public $excelFormType = '';
    public $fileName = '방문자 IP분석';

    public function index()
    {
        $postValue = empty(\Request::post()->toArray()) === false ? \Request::post()->toArray() : \Request::get()->toArray();
        $ExcelRequest = new ExcelRequest();

        switch($postValue['mode']) {
            case 'download':
                $downloadPath = \UserFilePath::data('excel', $postValue['filename'])->getRealPath();
                $ext = pathinfo($downloadPath)['extension'];
                if($postValue['dataTypes'] == 'detail') {
                    $this->fileName .= ' 상세보기';
                }
                $this->download($downloadPath, $this->fileName.".".$ext);
                unlink($downloadPath);
                break;
            default:
                // 상점별 고유번호 - 해외상점
                $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

                // 모듈호출
                $visitStatistics = new VisitStatistics();

                $searchDevice = $postValue['searchDevice'];
                if (!$searchDevice) {
                    $searchDevice = 'all';
                }
                $searchDate = $visitStatistics->getVisitIpSearchDate($postValue['searchDate']);
                $searchIP = $postValue['searchIP'];

                if($postValue['dataTypes'] == "list") {
                    $searchData = [
                        'searchIP' => $searchIP,
                        'page' => $postValue['page'],
                        'pageNum' => $postValue['pageNum'],
                        'limit' => 'unlimited',
                    ];
                    $data = $visitStatistics->getVisitStatisticsPage($searchDate, $searchDevice, $mallSno, true, $searchData);
                } else {
                    $searchData = [
                        'searchIP' => $searchIP,
                        'searchOS' => $postValue['searchOS'],
                        'searchBrowser' => $postValue['searchBrowser'],
                        'limit' => 'unlimited',
                    ];
                    $data = $visitStatistics->getVisitStatisticsPage($searchDate, $searchDevice, $mallSno, false, $searchData);
                }

                $this->excelFormType = $postValue['dataTypes'] == 'list' ? 'visit_ip_list' : 'visit_ip_detail';
                $excelForm = \App::load('\\Component\\Excel\\ExcelForm');
                $getExcelForm = $excelForm->setExcelFormStatistics($this->excelFormType);

                $getExcelData = $ExcelRequest->getVisitIpExcel($data, $postValue['dataTypes'], $searchData, $postValue['dataTypes'] == 'list' ? self::LIST_FIELD : self::DETAIL_FIELD, $getExcelForm);
                break;
        }
        exit;
    }
}