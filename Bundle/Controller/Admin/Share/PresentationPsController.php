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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Share;


use Component\Admin\AdminMain;
use Exception;
use Framework\Debug\Exception\LayerNotReloadException;

/**
 * Class PresentationPsController
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class PresentationPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            $search = \Request::get()->get('search');
            $adminMainService = new AdminMain();
            $mode = \Request::get()->get('mode');
            \Logger::info(__METHOD__ . ', mode:' . $mode);
            switch ($mode) {
                case 'salesTotal':
                    // throw new Exception('TEST');
                    $adminMainService->setPresentationFilters($search);
                    $statistics = $adminMainService->getPresentationBySalesTotal();
                    $this->json(
                        [
                            'success' => 'OK',
                            'result'  => $statistics,
                        ]
                    );
                    break;
                case 'salesGoods':
                    // throw new Exception('TEST');
                    $adminMainService->setPresentationFilters($search);
                    $statistics = $adminMainService->getPresentationBySalesGoods();
                    $this->json(
                        [
                            'success'      => 'OK',
                            'result'       => $statistics['statistics'],
                            'searchPeriod' => $statistics['searchPeriod'],
                        ]
                    );
                    break;
                case 'order':
                    // throw new Exception('TEST');
                    $adminMainService->setPresentationFilters($search);
                    $statistics = $adminMainService->getPresentationByOrder();
                    $this->json(
                        [
                            'success' => 'OK',
                            'result'  => $statistics,
                        ]
                    );
                    break;
                case 'visit':
                    // throw new Exception('TEST');
                    $adminMainService->setPresentationFilters($search);
                    $statistics = $adminMainService->getPresentationByVisit();
                    $this->json(
                        [
                            'success' => 'OK',
                            'result'  => $statistics,
                        ]
                    );
                    break;
                case 'memberTotal':
                    // throw new Exception('TEST');
                    $adminMainService->setPresentationFilters();
                    $statistics = $adminMainService->getPresentationByMember();
                    $this->json(
                        [
                            'success' => 'OK',
                            'result'  => $statistics,
                        ]
                    );
                    break;
                case 'mileage':
                    // throw new Exception('TEST');
                    $adminMainService->setPresentationFilters();
                    $statistics = $adminMainService->getPresentationByMileage();
                    $this->json(
                        [
                            'success' => 'OK',
                            'result'       => $statistics['dateArr'],
                            'result_week'       => $statistics['lastWeek'],
                            'result_15th'       => $statistics['last15th'],
                            'result_month'       => $statistics['lastMonth'],
                        ]
                    );
                    break;
                case 'deposit':
                    // throw new Exception('TEST');
                    $adminMainService->setPresentationFilters();
                    $statistics = $adminMainService->getPresentationByDeposit();
                    $this->json(
                        [
                            'success' => 'OK',
                            'result'       => $statistics['dateArr'],
                            'result_week'       => $statistics['lastWeek'],
                            'result_15th'       => $statistics['last15th'],
                            'result_month'       => $statistics['lastMonth'],
                        ]
                    );
                    break;
                case 'dataReload':
                    // throw new Exception('TEST');
                    $statistics = $adminMainService->setStatisticsDataReload();
                    $this->json(
                        [
                            'success' => 'OK',
                            'result'  => $statistics,
                        ]
                    );
                    break;
                default:
                    $this->json(
                        [
                            'fail' => 'NOT FOUND',
                        ]
                    );
                    break;
            }
        } catch (Exception $e) {
            \Logger::error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            if (\Request::isAjax()) {
                $this->json(
                    [
                        'fail' => 'ERROR',
                    ]
                );
            } else {
                throw new LayerNotReloadException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}
