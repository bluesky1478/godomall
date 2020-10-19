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

use Exception;
use Framework\Debug\Exception\LayerNotReloadException;

/**
 * Class SslMobileSettingController
 * @package Bundle\Controller\Admin\Policy
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class SslMobileSettingController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 모듈 호출
        $sslAdmin = \App::load('\\Component\\SiteLink\\SecureSocketLayer');
        // --- 페이지 데이터
        try {
            $sslData = $sslAdmin->getSslSetting('mobile');

            $this->setData('position', 'mobile');
            $this->setData('sslData', $sslData);
            $infoMsg[0]['notice-info'] = '보안서버 설정은 <a href="javascript:gotoGodomall(\'domain\')">마이고도 > 도메인 연결</a>에서 연결한 도메인 별로 사용 설정이 가능합니다.';
            $infoMsg[1]['notice-info'] = '보안서버 도메인에 대한 연결 상점 설정은 <a href="../policy/mall_config.php" target="_blank">“기본설정 > 해외상점 > 해외 상점 설정”</a> 의 도메인 연결을 통해 설정하실 수 있습니다.';
            $infoMsg[2]['notice-info'] = 'PC쇼핑몰과 모바일쇼핑몰 보안서버는 별도로 적용하셔야 합니다. PC쇼핑몰 보안서버 관리 <a href="../policy/ssl_pc_setting.php" target="_blank">바로가기></a>';
            $this->setData('infoMsg', $infoMsg);
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->callMenu('policy', 'ssl', 'mobileSetting');
        $this->getView()->setPageName('policy/ssl_setting');
    }
}
