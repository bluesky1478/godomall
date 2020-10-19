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
 * Class SelectSalesPsController
 * @package Bundle\Controller\Admin\Statistics
 * @author  kyeonk
 */

namespace Bundle\Controller\Admin\Statistics;

use Request;
use Component\Policy\Policy;
use Exception;

class SelectSalesSystemPsController extends \Controller\Admin\Controller
{
    public function index() {
        $postValue = Request::post()->toArray();
        try {
            if ($postValue['mode'] == 'saveProcess') {
                $session = \App::getInstance('session');
                if ($session->get('manager.functionAuthState') == 'check' && $session->get('manager.functionAuth.orderSalesStatisticsProcess') != 'y') {
                    throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
                } else {
                    $policy = new Policy();
                    $policy->saveOrderSalesProcess($postValue);
                    $this->json([
                        'result' => 'success',
                        'message' => '저장 되었습니다.'
                    ]);
                }
            }
        } catch (Exception $e) {
            $this->json([
                'result' => 'fail',
                'message' => $e->getMessage()
            ]);
        }
    }
}