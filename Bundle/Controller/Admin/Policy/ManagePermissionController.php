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

namespace Bundle\Controller\Admin\Policy;

//use Globals;
//use Request;
//use Session;

/**
 * 운영자 권한 설정
 *
 * @author Sunny <bluesunh@godo.co.kr>
 */
class ManagePermissionController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'management', 'managePermission');

        $this->addCss([
            'managePermissionStyle.css?'.time(), // 운영자 권한 설정 CSS
        ]);
        $this->addScript([
            'managePermission.js?'.time(), // 운영자 권한 설정 JS
        ]);
    }

    public function post()
    {
        // 관리자 메뉴 쓰기 권한에 따른 쓰기 기능 제한
        $writable = $this->getAdminMenuWritableAuth();
        if ($writable['check'] === false) {
            $returnMenuAccessAuth = [];
            $returnMenuAccessAuth[] = '$("#frmManagerPermission").validate().destroy();';
            $returnMenuAccessAuth[] = '$("#frmManagerPermission").submit(function(){ dialog_alert("__PAGE_TITLE__의 쓰기 권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다."); return false; });';
            $returnMenuAccessAuth = array_map(function($script) use ($writable) {return str_replace("__PAGE_TITLE__", $writable['title'], $script);}, $returnMenuAccessAuth);
            $this->setData('menuAccessAuth', implode("\n", $returnMenuAccessAuth));
        }
    }
}
