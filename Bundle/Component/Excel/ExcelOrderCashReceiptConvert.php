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
 * Class ExcelOrderCashReceiptConvert
 * @package Bundle\Component\Excel
 * @author  sueun
 */
class ExcelOrderCashReceiptConvert
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

}

