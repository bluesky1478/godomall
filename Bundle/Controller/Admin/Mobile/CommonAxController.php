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

namespace Bundle\Controller\Admin\Mobile;

use Component\Design\SkinDesign;
use Globals;
use Request;
use UserFilePath;

/**
 * 디자인 관리 공통 저장
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class CommonAxController extends \Controller\Admin\Design\CommonAxController
{
    /**
     * index
     *
     */
    public function index()
    {
        $this->menuType = 'mobile';
        parent::index();
    }
}
