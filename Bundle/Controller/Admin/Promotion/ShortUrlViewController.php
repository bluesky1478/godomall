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
namespace Bundle\Controller\Admin\Promotion;

use Component\Promotion\ShortUrl;
use Framework\Debug\Exception\AlertBackException;
use Request;

/**
 * Class ShortUrlViewController
 *
 * @package Bundle\Controller\Admin\Promotion
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class ShortUrlViewController extends \Controller\Admin\Controller
{

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('promotion', 'shortUrl', 'shortUrlView');

        // --- 페이지 데이터
        $requestData = Request::request()->toArray();

        // --- QR코드 설정 조회
        $shortUrl = new ShortUrl();

        // 설치여부 판독
        if (!$shortUrl->getIsInstalled()) {
            throw new AlertBackException(__('플러스샵 설치가 필요합니다.'));
        } else {
            $result = $shortUrl->getView($requestData);

            // 페이지 설정
            $page = \App::load('Component\\Page\\Page');
            $this->setData('total', count($result['statistics']));
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));

            // --- 관리자 디자인 템플릿
            $this->setData('sno', $requestData['sno']);
            $this->setData('data', $result['data']);
            $this->setData('search', $result['search']);
            $this->setData('checked', $result['checked']);
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));
            $this->setData('requestData', $requestData);
            $this->setData('statistics', $result['statistics']);
        }
    }
}
