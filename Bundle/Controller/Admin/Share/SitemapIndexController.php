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

namespace Bundle\Controller\Admin\Share;

use Component\Admin\AdminMenu;
use Component\Admin\AdminMenuProvider;

/**
 * 관리자 사이트 맵 - 가나다순
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SitemapIndexController extends \Controller\Admin\Controller
{

    /**
     * index
     * @todo 영문용으로 별도 구분해서 개발해야 함
     */
    public function index()
    {
        //--- 페이지 데이터
        try {

            function get_cho($str) {

                $choChr = array('ㄱ','ㄲ','ㄴ','ㄷ','ㄸ','ㄹ','ㅁ','ㅂ','ㅃ','ㅅ','ㅆ','ㅇ','ㅈ','ㅉ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ');
                $chos = array('가','까','나','다','따','라','마','바','빠','사','싸','아','자','짜','차','카','타','파','하');

                if (strlen($str) == 0) return '';
                $str = strtoupper(gd_html_cut($str, 1));

                if (is_numeric($str) || ('A' <= $str && 'Z' >= $str)) return $str;

                for($i = 0; $i < count($chos); $i++) {
                    if ($chos[$i] <= $str && ($i == count($chos) - 1 || $chos[$i+1] > $str)) return $choChr[$i];
                }

                return '';
            }

            $adminMenu = new AdminMenu();
            if (gd_is_provider() === true) {
                $adminMenuType = 's';
                $adminMenuLink = URI_PROVIDER;
            } else {
                $adminMenuType = 'd';
                $adminMenuLink = URI_ADMIN;
            }

            $menuList = $adminMenu->getAdminMenuList($adminMenuType);

            // 메뉴 추출
            $menuSortTreeList = [];
            $arrNumber = 0;
            foreach ($menuList as $menuKey => $menuVal) {
                if ($menuVal['depth'] == 3 && $menuVal['fDisplay'] == 'y' && $menuVal['sDisplay'] == 'y' && $menuVal['tDisplay'] == 'y') {
                    $menuSortTreeList[get_cho($menuVal['tName'])][$menuVal['tName']] = $menuVal['fCode'].DS.$menuVal['tUrl'];
                    $arrNumber++;
                }
            }

            foreach ($menuSortTreeList as $menuSortTreeKey => $menuSortTreeVal) {
                ksort($menuSortTreeList[$menuSortTreeKey]);
            }
            ksort($menuSortTreeList);
        }
        catch(\Exception $e) {
            echo $e->getMessage();
        }

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout','layout_fluid.php');
        $this->setData('menuSortTreeList', gd_isset($menuSortTreeList));
        $this->setData('adminMenuLink', gd_isset($adminMenuLink));
    }
}
