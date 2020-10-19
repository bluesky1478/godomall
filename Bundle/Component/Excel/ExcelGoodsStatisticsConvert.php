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

use App;
use Logger;

/**
 * Class ExcelGoodsStatisticsConvert
 * @package Bundle\Component\Excel
 * @author  yjwee
 */
class ExcelGoodsStatisticsConvert
{

    /**
     * @var string
     */
    private $excelHeader;
    /**
     * @var string
     */
    private $excelFooter;

    /**
     * ExcelGoodsStatisticsConvert constructor.
     */
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
     * setExcelDownBySearchWord
     *
     * @param array $list
     */
    public function setExcelDownBySearchWord(array $list)
    {
        Logger::info(__METHOD__);

        /** @var \Bundle\Component\GoodsStatistics\SearchWordStatistics $log */
        $log = App::load('\\Component\\GoodsStatistics\\SearchWordStatistics');

        $excel = [];
        $excel[] = $this->excelHeader;
        $excel[] = '<table border="1">' . chr(10);
        $excel[] = '<tr><td colspan="4">PC</td><td colspan="3">Mobile</td></tr>';
        $excel[] = '<tr><td>' . __('순위') . '</td><td>' . __('검색어') . '</td><td>' . __('검색수') . '</td><td>' . __('비율') . '</td><td>' . __('검색어') . '</td><td>' . __('검색수') . '</td><td>' . __('모바일') . '</td></tr>';
        $excel[] = $log->makeTableByRankList($list['pc'], $list['mobile']);
        $excel[] = '</table>';
        $excel[] = $this->excelFooter;

        echo join('', $excel);
    }

    /**
     * setExcelDownByCategoryOrder
     *
     * @param array $list
     */
    public function setExcelDownByCategoryOrder(array $list)
    {
        Logger::info(__METHOD__);

        /** @var \Bundle\Component\GoodsStatistics\CategoryStatistics $log */
        $log = App::load('\\Component\\GoodsStatistics\\CategoryStatistics');

        $excel = [];
        $excel[] = $this->excelHeader;
        $excel[] = '<table border="1">' . chr(10);
        $excel[] = '<tr><td>' . __('순위') . '</td><td>' . __('구분') . '</td><td>' . __('카테고리명') . '</td><td>' . __('매출금액') . '</td><td>' . __('PC쇼핑몰') . '<br/>' . __('매출금액') . '</td><td>' . __('모바일쇼핑몰') . '<br/>' . __('매출금액') . '</td><td>' . __('구매수량') . '</td><td>' . __('PC쇼핑몰') . '<br/>' . __('구매수량') . '</td><td>' . __('모바일쇼핑몰') . '<br/>' . __('구매수량') . '</td><td>' . __('구매자수') . '</td><td>' . __('PC쇼핑몰') . '<br/>' . __('구매자수') . '</td><td>' . __('모바일쇼핑몰') . '<br/>' . __('구매자수') . '</td></tr>';
        $excel[] = $log->makeTable($list);
        $excel[] = '</table>';
        $excel[] = $this->excelFooter;

        echo join('', $excel);
    }
}
