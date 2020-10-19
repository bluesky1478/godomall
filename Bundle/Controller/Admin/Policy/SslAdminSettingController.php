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
class SslAdminSettingController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 모듈 호출
        $sslAdmin = \App::load('\\Component\\SiteLink\\SecureSocketLayer');
        // --- 페이지 데이터
        try {
            $sslData = $sslAdmin->getSslSetting('admin');

            $this->setData('position', 'admin');
            $this->setData('sslData', $sslData);
            $infoMsg[0]['notice-info'] = '관리자 보안서버는 <a href="javascript:gotoGodomall(\'domain\')">마이고도 > 도메인 연결</a>에서 연결한 대표도메인에만 설정이 가능합니다.';
            $this->setData('infoMsg', $infoMsg);
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->callMenu('policy', 'ssl', 'adminSetting');
        $this->getView()->setPageName('policy/ssl_setting');
    }
}
