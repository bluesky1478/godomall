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
 * Class ShortUrlListController
 *
 * @package Bundle\Controller\Admin\Promotion
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class ShortUrlListController extends \Controller\Admin\Controller
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
        $this->callMenu('promotion', 'shortUrl', 'shortUrlList');

        // --- 페이지 데이터
        $requestData = Request::request()->toArray();

        // ShortUrl 객체 생성
        $shortUrl = new ShortUrl();

        // 설치여부 판독
        if (!$shortUrl->getIsInstalled()) {
//            $this->getView()->setPageName('share/warning_premium.php');
            throw new AlertBackException(__('플러스샵 설치가 필요합니다.'));
        } else {
            $data = $shortUrl->getList($requestData);

            // 페이지 설정
            $page = \App::load('Component\\Page\\Page');
            $this->setData('total', count($data['data']));
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));

            // --- 관리자 디자인 템플릿
            $this->setData('requestData', $requestData);
            $this->setData('search', $data['search']);
            $this->setData('checked', $data['checked']);
            $this->setData('data', $data['data']);
        }

    }
}
