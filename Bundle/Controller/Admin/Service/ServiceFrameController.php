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
namespace Bundle\Controller\Admin\Service;

use Component\Admin\AdminMenu;
use Framework\Debug\Exception\Except;
use Request;
use Globals;
use Framework\Utility\StringUtils;

/**
 * 부가서비스 아이프레임 페이지
 *
 * @author    yoonar
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class ServiceFrameController extends \Controller\Admin\Controller
{
    public function index()
    {
        try{
            // _GET 데이터
            $getValue = Request::get()->toArray();

            if (empty($getValue['menu']) === true) {
                throw new \Exception('NO_MEMU');
            }

            //--- 메뉴 설정
            $menu = explode('_', $getValue['menu']);
            $this->callMenu('service', $menu[0], $menu[1]);
            unset($menu);

            // 직접 이동
            $directUrl = array(
                'convenience_packing_info' => 'http://www.mcbox.co.kr/hosting/godo/main.php?restart=1&userid=' . StringUtils::encodeGodoConnect(Globals::get('gLicense.godosno')),
            );

            if(array_key_exists($getValue['menu'], $directUrl)) {
                $remoteUrl = $directUrl[$getValue['menu']];
            } else {
                throw new \Exception('NO_MEMU');
            }

            // css 깨짐 현상때문에 아이프레임으로 노출
            $contents = '<iframe name="serviceFrm" id="serviceFrm" src="' . $remoteUrl . '" frameborder="0" marginwidth="0" marginheight="0" width="100%" height="3100"></iframe>';

        } catch (Except $e) {
            echo($e->ectMessage);
        }

        // 공용 페이지 사용
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/service_frame.php');
        $this->setData('contents', $contents);
    }
}
