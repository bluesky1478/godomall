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

use Request;
use App;
use Component\Godo\GodoNhnServerApi;
use Component\Nhn\AcecounterCommonScript;
use Exception;
use Globals;

/**
 * Class 통계-에이스카운터-에이스카운터 신청/관리
 * @package Bundle\Controller\Admin\Statistics
 * @author  yoonar
 */
class AcecounterPsController extends \Controller\Admin\Controller {

    public function Index() {
        // --- 메뉴 설정
        $this->callMenu('statistics', 'acecounter', 'info');
        try {
            $requestPostParams = Request::post()->all();
            $mode = $requestPostParams['mode'];
            $sno = Globals::get('gLicense.godosno');

            $acecounter = new AcecounterCommonScript();
            $acecounterConfig = $acecounter->getConfig();
            $acecounterApi = new GodoNhnServerApi();

            switch($mode) {
                case 'regist':
                    if(!$acecounterConfig['isEnabled']) {
                        $statusCheck = $acecounterApi->request('acecounterGetStatus');
                        $result = $acecounterApi->request('acecounterRegist', $statusCheck);
                        if($result['resultCode'] == 'OK') {
                            if(strtolower($statusCheck['aceStatus']) == 'y' && strtolower($statusCheck['shopStatus']) == 'n') {
                                $msg = '';
                            } else {
                                $msg = 'alert("에이스카운터 신청이 완료되었습니다.");';
                            }
                            $this->js($msg . 'location.replace("../statistics/acecounter_manager.php"); ');
                        } else {
                            $this->js('alert("가입이 실패되었습니다. 관리자에게 문의해주세요.(Error Code:' . $result['resultCode']  . ')"); location.replace("../statistics/acecounter_info.php"); ');
                        }
                    }
                    break;
                case 'modify':
                    if(gd_isset($acecounterConfig['shopStatus'])) {
                        $sendData = [
                            'shopSno' => $sno,
                            'shopKey' => $acecounterConfig['shopKey'],
                            'mode' => 'status',
                            'shopStatus' => $requestPostParams['aceCommonScriptFl'],
                        ];
                        $result = $acecounterApi->request('acecounterModifyStatus', $sendData);
                        if($result) {
                            $this->js('alert("상태가 변경되었습니다."); location.replace("../statistics/acecounter_info.php"); ');
                        } else {
                            $this->js('alert("상태가 변경이 실패되었습니다. 다시 시도해주세요."); location.replace("../statistics/acecounter_info.php"); ');
                        }
                    }
                    break;
            }

        } catch(Exception $e) {

        }
    }
}