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
namespace Bundle\Controller\Admin\Design;

/**
 * 디자인 메인 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class IndexController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // 관리자 접속 권한 체크
        $this->redirect('./design_skin_list.php');

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layoutMenu', 'menu_design.php');
    }
}
