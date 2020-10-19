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

namespace Bundle\Controller\Admin\Promotion;

use Component\Godo\GodoInsgoServerApi;
use League\Flysystem\Exception;
use Request;
use FileHandler;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\LayerNotReloadException;
use Logger;

/**
 * Class InsgoRequestPsController
 * @package Bundle\Controller\Admin\Promotion
 */
class InsgoRequestPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            $insgoApi = new GodoInsgoServerApi();
            $insgoWidget = \App::load('\\Component\\Promotion\\Insgov2Widget');
            $value = Request::post()->all() ? Request::post()->all() : Request::get()->all();
            $value['requestUri'] = Request::getDomainUrl();

            switch ($value['mode']) {
                case 'setShopInfo': // 인스고 API 연동 중계서버 통신
                    $result = $insgoApi->setShopInfo($value);
                    $this->js('location.href="'.$result.'";');
                    break;

                case 'getToken':    // 인스고 토큰 쇼핑몰 통신
                    $res = $insgoWidget->saveAccessToken($value);
                    if($res['result'] == 'success') {
                        $script = "location.href='../../promotion/insgov2_widget_config.php';";
                        $script .= "opener.document.location.reload();";
                        $script .= "self.close();";
                        $this->js($script);
                    }else{
                        throw new AlertRedirectException(__('인스타그램 토큰이 확인되지 않습니다. 인스타그램 API연동을 다시 해주세요.'));
                    }
                    break;

                case 'refreshLongToken':    // 인스고 장기토큰 새로고침 통신
                    $insgoWidget->saveRefreshAccessToken($value);

                case 'insgoInfoDelete': // 인스고 신청 정보 삭제 통신
                    $insgoApi->insgoInfoDelete($value);

                case 'insgoConnectRelease': // 인스고 연동 해제
                    $insgoWidget->insgoConnectRelease($value);
                    $script = "location.reload();";
                    $this->js($script);
                    break;

                case 'insgoUserDenied': // 인스고 동의창에서 취소
                    Logger::channel('insgo')->info(__METHOD__ . ' Insgov2Widget UserDenied');
                    $script = "location.href='../../promotion/insgov2_widget_config.php';";
                    $script .= "opener.document.location.reload();";
                    $script .= "self.close();";
                    $this->js($script);
                    break;
            }
        } catch (Exception $e) {
            \Logger::info(__METHOD__ . ' - ' . $value['mode'] . ' : 설정 저장 중 오류가 발생하였습니다.');
            throw new LayerNotReloadException(__('설정 저장 중 오류가 발생하였습니다.'), $e->getCode(), $e);
        }
    }
}
