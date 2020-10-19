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
 * Class SslPcSettingController
 * @package Bundle\Controller\Admin\Policy
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class SslPcSettingController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 모듈 호출
        $sslAdmin = \App::load('\\Component\\SiteLink\\SecureSocketLayer');
        // --- 페이지 데이터
        try {
            $sslData = $sslAdmin->getSslSetting('pc');

            $this->setData('position', 'pc');
            $this->setData('sslData', $sslData);
            $infoMsg[0]['notice-info'] = '기본 제공 도메인의 경우 무료 보안서버 사용 설정만 적용되며, 유료 보안서버 설정은 <a href="javascript:gotoGodomall(\'domain\')">마이고도 > 도메인 연결</a>에서 연결한 도메인 별로 사용 설정이 가능합니다.';
            $infoMsg[1]['notice-danger'] = '기준몰에는 무료와 유료 보안서버 동시사용이 불가합니다. 무료 보안서버 사용 시 기준몰에 사용중인 유료보안서버가 해제되므로 이점 참고하시기 바랍니다.';
            $infoMsg[2]['notice-info'] = '보안서버 도메인에 대한 연결 상점 설정은 <a href="../policy/mall_config.php" target="_blank">“기본설정 > 해외상점 > 해외 상점 설정”</a> 의 도메인 연결을 통해 설정하실 수 있습니다.';
            $infoMsg[3]['notice-danger'] = '자동입금확인 서비스를 이용하는 경우, 보안서버 적용 후 서비스정보에서 ‘결과수신 URL’을 ‘사용중인 대표 보안서버 도메인/outconn/bank_sock.php’으로
    변경해주셔야 정상적인 서비스 이용이 가능합니다. <a href="../order/bankda_service.php" target="_blank">바로가기></a>';
            $infoMsg[4]['notice-info'] = 'PC쇼핑몰과 모바일쇼핑몰 보안서버는 별도로 적용하셔야 합니다. 모바일쇼핑몰 보안서버 관리 <a href="../policy/ssl_mobile_setting.php" target="_blank">바로가기></a>';
            $this->setData('infoMsg', $infoMsg);
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->callMenu('policy', 'ssl', 'pcSetting');
        $this->getView()->setPageName('policy/ssl_setting');
    }
}
