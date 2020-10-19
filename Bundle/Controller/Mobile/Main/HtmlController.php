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
namespace Bundle\Controller\Mobile\Main;

use Globals;
use Request;

/**
 * Controller 없는 페이지의 Controller
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class HtmlController extends \Controller\Mobile\Controller
{

    /**
     * html 메인
     *
     */
    public function index()
    {
        $pageTmp = explode('?', Request::get()->get('htmid'));
        $pageName = $this->_realpath($pageTmp[0]);
        $pageName = $this->_setPageName($pageName);

        $this->getView()->setPageName($pageName);
    }

    /**
     * 실제 경로
     * @param string $path 경로
     * @return string
     */
    private function _realpath($path)
    {
        $path = str_replace('\\',  '/', $path);
        $path = preg_replace('/\/+/', '/', $path);

        $segments = explode('/', $path);
        $parts = array();

        foreach ($segments as $segment) {
            if ($segment == '..') {
                array_pop($parts);
            }
            else if ($segment == '.') {
                continue;
            }
            else {
                $parts[] = $segment;
            }
        }
        return implode(DS, $parts);
    }

    /**
     * 페이지명
     * @param string $pageName 페이지명
     * @return string
     */
    private function _setPageName($pageName)
    {
        // . 을 기준으로 배열
        $parts = explode('.', $pageName);

        if (count($parts) <= 1) {
            return $parts[0];
        }

        // 마지막 배열 버림
        array_pop($parts);

        return implode('.', $parts);
    }
}
