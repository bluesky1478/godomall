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

use Framework\Debug\Exception\AlertBackException;
use Bundle\Component\Design\DesignConnectUrl;
use Request;

/**
 * 모바일 연결 페이지 리스트(url도우미)
 * @author choisueun <cseun555@godo.co.kr>
 */
class LayerConnectListController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // --- 페이지 데이터
        try {
            $postValue = Request::post()->all();

            // --- 페이지 기본설정
            gd_isset($postValue['page'], 1);
            gd_isset($postValue['pageNum'], 10);

            $designConnectUrl = new DesignConnectUrl();
            $skinNm = $postValue['skinName'];

            $mobileConnectPageList = $designConnectUrl->getMobileConnectPageList($skinNm, $postValue['page'], $postValue['pageNum']);
            $page = \App::load('\\Component\\Page\\Page');
            $page->recode['amount'] = $page->recode['total'] = $mobileConnectPageList['total']; //전체 레코드 수
            $page->page['list'] = $postValue['pageNum']; // 페이지당 리스트 수
            $page->idx = $page->recode['amount'] - (($postValue['page'] - 1) * $postValue['pageNum']);
            $page->page['now'] = $page->block['now'] = $postValue['page'];
            $page->setPage();
            $page->setUrl('__total='.$page->recode['total'].'&__amount='.$page->recode['amount']);
            $this->setData('page', $page);
            $this->setData('result', $mobileConnectPageList['data']);
            $this->setData('skinNm', $skinNm);
            $this->getView()->setDefine('layout', 'layout_layer.php');
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}
