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
namespace Bundle\Controller\Admin\Policy;

use Component\ExchangeRate\ExchangeRate;
use Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;
use Session;

/**
 * Class ExchangeRatePsController
 * @package Bundle\GlobalController\Admin\Policy
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class ExchangeRatePsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $postValue = Request::post()->toArray();
        $postValue['managerNo'] = Session::get('manager.sno');
        $postValue['managerNm'] = Session::get('manager.managerNm');
        $postValue['managerId'] = Session::get('manager.managerId');

        $exchangeRate = new ExchangeRate();
        // 각 모드에 따른 처리
        switch ($postValue['mode']) {
            case 'insert':
                try {
                    $exchangeRate->setExchangeRateConfig($postValue);
                    $this->layer(__('저장 되었습니다.'), 'parent.location.replace("exchange_rate_config.php");');
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;
        }
        exit;
    }
}
