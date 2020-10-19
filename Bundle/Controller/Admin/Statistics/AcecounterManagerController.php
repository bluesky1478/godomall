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

namespace Bundle\Controller\Admin\Statistics;

use Bundle\Component\Nhn\AcecounterCommonScript;
use Bundle\Component\Godo\GodoNhnServerApi;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Globals;
use Logger;

class AcecounterManagerController extends \Controller\Admin\Controller
{
    public function Index() {
        $this->callMenu('statistics', 'acecounter', 'manager');
        try {
            $sno = Globals::get('gLicense.godosno');

            $acecounter = new AcecounterCommonScript();
            $acecounterConfig = $acecounter->getConfig();

            $shopStatus = $acecounterConfig['shopStatus'];

            if(gd_isset($shopStatus)) {
                $acecounterApi = new GodoNhnServerApi();
                $sendData = [
                    'shopKey' => $acecounterConfig['shopKey'],
                ];
                $result = $acecounterApi->getParams('managerLogin', $sendData);
                Logger::channel('acecounter')->info(__METHOD__ . ' managerLogin:', [$sendData, $result]);
            } else {
                throw new AlertRedirectException(__('에이스카운터+ 서비스를 신청하셔야 사용가능합니다.'), null, null, '../statistics/acecounter_info.php');
            }
            $this->setData('data', $result);
            //$this->setData('shopStatus', !gd_isset($acecounterConfig['shopStatus']) ? 'e' : $acecounterConfig['shopStatus']);

        } catch(AlertOnlyException $e) {
            throw new AlertOnlyException($e->getMessage());
        }
    }
}
