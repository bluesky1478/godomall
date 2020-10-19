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

use Exception;
use Request;

class TimeSaleListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('promotion', 'timeSale', 'list');

        try {

            Request::get()->set('delFl','n');
            // --- 모듈 호출
            $timeSaleAdmin = \App::load('\\Component\\Promotion\\TimeSaleAdmin');

            $getData = $timeSaleAdmin->getAdminListTimeSale();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

        } catch (Exception $e) {
            throw $e;
        }

        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('page', $page);
    }
}
