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
namespace Bundle\Controller\Front;

use Globals;
use Request;

/**
 *
 * @author Lee Seungjoo <slowj@godo.co.kr>
 */
class CheckSslFreeController extends \Controller\Front\Controller
{

    public function index()
    {
        if (Request::isSecure()) {
            $godoSsl = \App::load('Component\\Godo\\GodoSslServerApi');
            $domainApiList = $godoSsl->getShopDomainList();
            $domainApiList = json_decode($domainApiList, true);
            $basicDomain = $domainApiList['data']['basicDomain'];
            if (Request::getServerName() === $basicDomain) {
                echo Globals::get('gLicense.godosno');
            } else {
                echo 1;
            }
        } else {
            echo 0;
        }
        exit;
    }
}
