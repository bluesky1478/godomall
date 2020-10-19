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
namespace Bundle\Controller\Admin\Provider\Base;

use Component\Member\Manager;

/**
 * 공급사 관리자 메인 페이지
 *
 * @author Jont-tae Ahn <qnibus@godo.co.kr>
 * @author Lee Namju <lnjts@godo.co.kr>
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class IndexController extends \Controller\Admin\Base\IndexController
{
    /**
     * index
     *
     */
    public function index()
    {
        // 관리자 접속 권한 체크 (강제로 운영정책으로 이동)
        parent::index();
    }
}
