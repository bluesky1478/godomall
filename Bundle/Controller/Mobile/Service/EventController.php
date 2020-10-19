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

namespace Bundle\Controller\Mobile\Service;

use App;

/**
 * Class CooperationController
 * @package Bundle\Controller\Mobile\Service
 * @author  atomyang
 */
class EventController extends \Controller\Mobile\Controller
{
    public function index()
    {
        //프론트 주소 리다이렉트 용
        header('location: '.URI_OVERSEAS_MOBILE. "board/list.php?bdId=event");
        exit;
    }
}
