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

/**
 * Class LayerVisitIpDetailController
 * @package Bundle\Controller\Admin\Statistics
 * @author  yoonar
 */
namespace Bundle\Controller\Admin\Statistics;

use Component\VisitStatistics\VisitStatistics;
use Framework\Debug\Exception\AlertOnlyException;
use DateTime;
use Request;

class LayerVisitIpDetailController extends \Controller\Admin\Controller
{
    public function index()
    {
        // 모듈호출
        $visitStatistics = new VisitStatistics();

        try {
            // 상점별 고유번호 - 해외상점
            $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

            //debug(Request::get()->all());
            $searchDevice = Request::get()->get('searchDevice');
            if (!$searchDevice) {
                $searchDevice = 'all';
            }
            $searchPeriod = Request::get()->get('searchPeriod');
            $searchDate = $visitStatistics->getVisitIpSearchDate(Request::get()->get('searchDate'));
            $searchIP = Request::get()->get('searchIP');
            $searchOS = Request::get()->get('searchOS');
            $searchBrowser = Request::get()->get('searchBrowser');

            $checked['searchPeriod'][$searchPeriod] = 'checked="checked"';
            $checked['searchDevice'][$searchDevice] = 'selected="selected"';
            $active['searchPeriod'][$searchPeriod] = 'active';
            $this->setData('searchDate', $searchDate);
            $this->setData('checked', $checked);
            $this->setData('active', $active);
            $this->setData('searchIP', $searchIP);
            $this->setData('searchOS', $searchOS);
            $this->setData('searchBrowser', $searchBrowser);
            $this->setData('reloadDisplay', Request::get()->get('reloadDisplay'));

            $searchData = [
                'searchIP' => $searchIP,
                'searchOS' => $searchOS,
                'searchBrowser' => $searchBrowser,
                'page' => Request::get()->get('page'),
                'pageNum' => Request::get()->get('pageNum'),
                'pagelink' => Request::get()->get('pagelink'),
            ];
            $getDataArr = $visitStatistics->getVisitStatisticsPage($searchDate, $searchDevice, $mallSno, false, $searchData, 'layer');

            $visitCount = count($getDataArr);

            if ($visitCount > 20) {
                $rowDisplay = 20;
            } else if ($visitCount == 0) {
                $rowDisplay = 5;
            } else {
                $rowDisplay = $visitCount;
            }
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
            $this->setData('page', $page);
            $this->setData('rowList', json_encode($getDataArr));
            $this->setData('visitCount', $visitCount);
            $this->setData('rowDisplay', $rowDisplay);

            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setDefine('layoutContent', \Request::getDirectoryUri() . '/' . \Request::getFileUri());
        } catch (Exception $e) {
            throw new AlertOnlyException($e->getMessage(), $e->getCode(), $e);
        }


    }
}
