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

use App;
use Component\Godo\GodoNhnServerApi;
use Component\Nhn\AcecounterCommonScript;
use Framework\Debug\Exception\AlertOnlyException;

/**
 * Class 통계-에이스카운터-에이스카운터 신청/관리
 * @package Bundle\Controller\Admin\Statistics
 * @author  yoonar
 */
class AcecounterInfoController extends \Controller\Admin\Controller
{
    private $acecounterConfig;
    private $isEnabled;

    public function Index() {
        // --- 메뉴 설정
        $this->callMenu('statistics', 'acecounter', 'info');
        try {
            $acecounter = new AcecounterCommonScript();
            $this->acecounterConfig = $acecounter->getConfig();
            $this->shopStatus = $this->acecounterConfig['shopStatus'];

            $acecounterApi = new GodoNhnServerApi();
            //$getStatus = $acecounterApi->request('acecounterGetStatus');
            $terms = $acecounterApi->request('acecounterTermUrl');
            $privateTerms = $acecounterApi->request('acecounterPrivateUrl');

            $this->setData('terms', $terms);
            $this->setData('privateTerms', $privateTerms);

            if($this->acecounterConfig['shopStatus']) {
                // 분석 사이트 관리 버튼
                $sendData = [
                    'shopKey' => $this->acecounterConfig['shopKey'],
                ];
                $directLoginResult = $acecounterApi->getParams('managerDirectLogin', $sendData);
                $this->setData('directLogin', $directLoginResult);

                $period = $this->acecounterConfig['acePeriod'] == '9999-99-99' ? '무제한' : $this->acecounterConfig['acePeriod'];
                $this->setData('period', $period);
                $this->setData('sName', $this->acecounterConfig['aceServiceName']);
                $this->setData('shopKey', $this->acecounterConfig['shopKey']);
                $this->setData('aceCode', $this->acecounterConfig['aceCode']);
                $checked = array();
                $checked['aceCommonScriptFl'][$this->acecounterConfig['shopStatus']] = 'checked="checked"';
                $this->setData('checked',gd_isset($checked));
            }
            $this->setData('shopStatus', !gd_isset($this->shopStatus) ? 'e' : $this->shopStatus);
            $this->setData('aceStatus', $this->acecounterConfig['shopStatus']);
        } catch(AlertOnlyException $e) {
            throw new AlertOnlyException($e->getMessage());
        }
    }
}