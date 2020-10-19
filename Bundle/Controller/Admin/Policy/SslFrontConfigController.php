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

use Component\SiteLink\SiteLinkAdmin;
use Exception;
use Framework\Debug\Exception\LayerNotReloadException;

/**
 * Class SslFrontConfigController
 * @package Bundle\Controller\Admin\Policy
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class SslFrontConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 모듈 호출
        $sslAdmin = new SiteLinkAdmin();
        // --- 페이지 데이터
        try {
            $getData = $sslAdmin->getSslView();
            if ($getData) {
                $mode = 'modifySslConfig';
            } else {
                $mode = 'insertSslConfig';
            }
            $checked = $getData['checked'];
            $selected = $getData['selected'];
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->callMenu('policy', 'ssl', 'frontConfig');

        $this->setData('data', gd_htmlspecialchars(gd_isset($getData['data'])));
        $this->setData('sslRule', $sslAdmin->_sslRule);

        $this->setData('mode', $mode);
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
    }
}
