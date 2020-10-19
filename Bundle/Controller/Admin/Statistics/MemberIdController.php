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

use App;
use DateTime;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ArrayUtils;
use Framework\Utility\DateTimeUtils;
use Request;

/**
 * Class MemberIdController
 * @package Bundle\Controller\Admin\Statistics
 * @author  yjwee
 */
class MemberIdController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        try {
            /**
             * @var \Bundle\Component\MemberStatistics\IdStatistics $statisticsId
             */
            $statisticsId = App::load('\\Component\\MemberStatistics\\IdStatistics');

            $this->callMenu('statistics', 'member', 'allGender');

            $checked['searchPeriod'][Request::get()->get('searchPeriod', '7')] = 'checked="checked"';

            $requestGetParams = Request::get()->all();
            $vo = $statisticsId->getStatisticsList(Request::get()->all(), null, null);
            $htmlTable = $statisticsId->makeTable($vo);

            if (gd_isset($requestGetParams['searchDt'], '') === '') {
                $requestGetParams['searchDt'] = DateTimeUtils::getBetweenDateTime('-7 days');
            } else {
                $requestGetParams['searchDt'][0] = new DateTime($requestGetParams['searchDt'][0], DateTimeUtils::getTimeZone());
                $requestGetParams['searchDt'][1] = new DateTime($requestGetParams['searchDt'][1], DateTimeUtils::getTimeZone());
            }
            if ($requestGetParams['searchDt'][0] === null || $requestGetParams['searchDt'][1] === null) {
                $requestGetParams['searchDt'] = DateTimeUtils::getBetweenDateTime();
            }
            $requestGetParams['searchDt'][0] = $requestGetParams['searchDt'][0]->format('Y-m-d');
            $requestGetParams['searchDt'][1] = $requestGetParams['searchDt'][1]->format('Y-m-d');
            $htmlPeriodTable = $statisticsId->makePeriodTable($requestGetParams['searchDt'], $requestGetParams['searchPeriod']);

            ArrayUtils::unsetDiff(
                $requestGetParams, [
                    'regDt',
                ]
            );

            $this->setData('requestParams', $requestGetParams);
            $this->setData('vo', $vo);
            $this->setData('checked', $checked);
            $this->setData('htmlTable', $htmlTable);
            $this->setData('htmlPeriodTable', $htmlPeriodTable);

            $this->addScript(
                [
                    'tui/code-snippet.min.js',
                    'raphael/effects.min.js',
                    'raphael/raphael-min.js',
                    'tui.chart-master/chart.min.js',
                ]
            );

            $this->addCss(
                [
                    'chart.css',
                ]
            );
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
