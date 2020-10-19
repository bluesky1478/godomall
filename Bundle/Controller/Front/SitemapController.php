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

use Framework\Debug\Exception\HttpException;
use UserFilePath;

/**
 * sitemap.xml 구성
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SitemapController extends \Controller\Front\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // 사이트맵 설정 정보
        $sitemapData = gd_policy('basic.sitemap');

        // 사이트맵 설정
        $sitemapFl = true;

        // 설정 정보 유무
        if (empty($sitemapData['front']) === true) {
            $sitemapFl = false;
        }

        // 사이트맵 화일
        $sitemapFile = UserFilePath::data('common', $sitemapData['front']);

        // 사이트맵 화일 여부
        if (is_file($sitemapFile) === false) {
            $sitemapFl = false;
        }

        // sitemap.xml 출력
        if ($sitemapFl === true) {
            // header mine type : xml
            header('Cache-Control: no-cache, must-revalidate');
            header('Content-Type: application/xml; charset=utf-8');

            echo file_get_contents($sitemapFile);
        } else {
            throw new HttpException('sitemap.xml ' . __('페이지를 찾을 수 없습니다.'), 404);
        }
        exit;
    }
}
