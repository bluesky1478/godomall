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

namespace Bundle\Controller\Admin\Myapp;

use Exception;
use Request;

/**
 * 모바일샵 기본 설정 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class MyappController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $mobileShopConfig = gd_policy('mobile.config');
        if ($mobileShopConfig['mobileShopFl'] != 'y') {
            $this->alert("현재 모바일샵 미사용 상태임으로 앱등록 및 설정이 불가합니다.\n모바일샵 사용 후 재시도 바랍니다.", null, '../mobile/mobile_config.php');
        }

        $myappConfig = gd_policy('myapp.config');
        $this->callMenu('myapp', 'myappMiddle', 'myappBottom');
        if (empty($myappConfig['builder_auth'])) {
            $getValue = Request::get()->toArray();
            $menu = $getValue['menu'];
            if (empty($getValue)) {
                $menu = 'myapp_info';
            }
            $this->setData('menu', $menu);
        }
        else {
            // 권한 체크
            $infoAccess = $this->getData('naviMenu')->getAccessMenuStatus('myapp', 'myappMiddle', 'myappBottom', gd_is_provider());

            if (!empty($infoAccess)) {
                $myappApi = \App::load('Component\\Myapp\\MyappApi');
                $builderCode = $myappApi->appBuilderIssued();
                $pageUrl = $myappApi->getBuilderUrl();

                header('P3P: CP="CAO PSA OUR"');
                header('X-Frame-Options: ' . $pageUrl);

                $this->logger = \App::getInstance('logger')->channel('myapp');
                $this->logger->info('빌더 인증 코드 : ', [$builderCode]);
                $this->setData('builderCode', $builderCode); // 빌더 인증코드
                $this->setData('adminPageUrl', $pageUrl . '?code=' . $builderCode); // 마이앱 관리자 페이지 url

                $this->getView()->setDefine('layoutHeader', 'header_myapp.php');
                $this->getView()->setDefine('layout', 'layout_basic_myapp.php');

                $myapp = \App::load('Bundle\\Component\\Myapp\\Myapp');
                $myappConnectBrowser = $myapp->getMyappBrowserAgent();

                $myappIframeWidthUnit = "vw";
                $myappIframeHeightUnit = "vh";

                $this->setData('myappConnectBrowser', $myappConnectBrowser);
                $this->setData('myappIframeWidthUnit', $myappIframeWidthUnit);
                $this->setData('myappIframeHeightUnit', $myappIframeHeightUnit);
            }
        }
    }
}
