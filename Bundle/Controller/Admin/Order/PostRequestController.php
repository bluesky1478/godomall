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
namespace Bundle\Controller\Admin\Order;

use Globals;
use Request;

class PostRequestController extends \Controller\Admin\Controller
{

    /**
     * 우체국택배연동 가입
     *
     * @author atomyang
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('order', 'epostParcel', 'request');
        // --- 관리자 디자인 템플릿
        $gLicense = Globals::get('gLicense');
        $this->setData('shopSno', $gLicense['godosno']);
        $this->setData('domainUrl', Request::getHostNoPort());
    }
}
