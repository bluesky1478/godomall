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


namespace Bundle\Component\Excel;

/**
 * Class ExcelVisitStatisticsConvert
 * @package Bundle\Component\Excel
 * @author  yjwee
 */
class ExcelVisitStatisticsConvert
{
    /**
     * @var string
     */
    private $excelHeader;
    /**
     * @var string
     */
    private $excelFooter;

    public function __construct()
    {
        $this->excelHeader = '<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko">' . chr(10);
        $this->excelHeader .= '<head>' . chr(10);
        $this->excelHeader .= '<title>Excel Down</title>' . chr(10);
        $this->excelHeader .= '<meta http-equiv="Content-Type" content="text/html; charset=' . SET_CHARSET . '" />' . chr(10);
        $this->excelHeader .= '<style>' . chr(10);
        $this->excelHeader .= 'br{mso-data-placement:same-cell;}' . chr(10);
        $this->excelHeader .= '.xl31{mso-number-format:"0_\)\;\\\(0\\\)";}' . chr(10);
        $this->excelHeader .= '.xl24{mso-number-format:"\@";} ' . chr(10);
        $this->excelHeader .= '.title{font-weight:bold; background-color:#F6F6F6; text-align:center;} ' . chr(10);
        $this->excelHeader .= '</style>' . chr(10);
        $this->excelHeader .= '</head>' . chr(10);
        $this->excelHeader .= '<body>' . chr(10);

        $this->excelFooter = '</body>' . chr(10);
        $this->excelFooter .= '</html>' . chr(10);
    }

    /**
     * setExcelDownByJoinDay
     *
     * @param $data
     */
    public function setExcelDownByJoinData($data)
    {
        $excel = [];
        $excel[] = $this->excelHeader;
        $excel[] = $data;
        $excel[] = '</table>';
        $excel[] = $this->excelFooter;

        echo join('', $excel);
    }

    /**
     * 방문자 IP 통계 다운
     *
     * @param $getData
     *
     * @return string $excelData
     */
    public function setExcelVisitIpStatisticsDown($getData)
    {
        // 모듈호출
        $visitStatistics = \App::load('\\Component\\VisitStatistics\\VisitStatistics');

        $searchDate[0] = $getData['sDate'];
        $searchDate[1] = $getData['eDate'];
        if (!$searchDate) {
            $searchDate[0] = date('Y-m-d 00:00:00', strtotime(' -7 days'));
            $searchDate[1] = date('Y-m-d 23:59:59');
        }
        if (!$searchDate[0]) {
            $searchDate[0] = date('Y-m-d 00:00:00', strtotime(' -7 days'));
        } else {
            $searchDate[0] = date('Y-m-d 00:00:00', strtotime(($searchDate[0])));
        }
        if (!$searchDate[1]) {
            $searchDate[1] = date('Y-m-d 23:59:59');
        } else {
            $searchDate[1] = date('Y-m-d 23:59:59', strtotime(($searchDate[1])));
        }
        $getDataArr = $visitStatistics->getVisitStatistics($searchDate);
        $excelData = '
                    <div id="excel_data" class="table-dashboard">
                        <table class="table table-cols" border="1">
                            <colgroup>
                                <col class="width-lg"/>
                                <col class="width-lg"/>
                                <col class="width-sm"/>
                                <col class="width-sm"/>
                                <col class="width-sm"/>
                                <col/>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>접속시간</th>
                                    <th>IP</th>
                                    <th>운영체제</th>
                                    <th>브라우저</th>
                                    <th>페이지뷰</th>
                                    <th>방문경로</th>
                                </tr>
                            </thead>
                            <tbody>';
        foreach ($getDataArr['data'] as $key => $val) {
            $excelData .= '<tr class="text-center">
                               <td>' . $val['regDt'] . '</td>
                               <td>' . $val['visitIP'] . '</td>
                               <td>' . $val['visitOS'] . '</td>
                               <td>' . $val['visitBrowser'] . '</td>
                               <td class="text-right">' . $val['visitPageView'] . '</td>
                               <td class="text-left">' . $val['visitReferer'] . '</td>
                           </tr>';
        }

        $excelData .= '</tbody></table></div>';

        return $excelData;
    }

}

