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
namespace Bundle\Controller\Front\GodoConn;

/**
 * 고도 유료 보안서버 저장
 *
 * @author Lee Seungjoo <slowj@godo.co.kr>
 */
class GodoSslController extends \Controller\Api\Godo\SslController
{
    /**
     * index
     *
     */
    public function index()
    {
        parent::index();
        exit();
    }
}
