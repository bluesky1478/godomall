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
namespace Bundle\Controller\Admin\Provider;

use Component\Member\Manager;
use Framework\Utility\ArrayUtils;
use Globals;

class IndexController extends \Controller\Admin\Controller
{
    /**
     * 솔루션 이용현황 페이지
     * [관리자 모드] 솔루션 이용현황 페이지
     *
     * @author artherot, blue
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        $this->redirect(URI_PROVIDER . 'base/index.php');
    }
}
