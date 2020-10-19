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
namespace Bundle\Controller\Admin\Order;

use Component\Board\ArticleListAdmin;
use Component\Order\Order;
use Exception;
use Framework\Debug\Exception\AlertCloseException;
use Request;
use Session;

/**
 * Class PopupAdminOrderGoodsMemoController
 *
 * @package Bundle\Controller\Admin\Order
 * @author  choisueun <cseun555@godo.co.kr>
 */
class PopupAdminOrderGoodsMemoController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws AlertCloseException
     */
    public function index()
    {
        try {
            Request::get()->set('page', Request::get()->get('page', 0));
            Request::get()->set('pageNum', Request::get()->get('pageNum', 10));
            Request::get()->set('sort', Request::get()->get('sort', 'regDt DESC'));

            /* 모듈 설정 */
            $orderAdmin = \App::load('\\Component\\Order\\OrderAdmin');

            $requestGetParams = Request::get()->all();

            /* 메모 구분 */
            $memoCd = $orderAdmin->getOrderMemoList(true);
            $arrMemoVal = [];
            foreach($memoCd as $key => $val){
                $arrMemoVal[$val['itemCd']] = $val['itemNm'];
            }
            $this->setData('memoCd', $arrMemoVal);

            /* 상품과 관련된 모든 데이터 가져오기 */
            $goodsData = $orderAdmin->getOrderGoodsListToMemo($requestGetParams['orderNo']);

            // 주문 단계 설정
            $tmpStatus = gd_policy('order.status');
            foreach($goodsData as $fKey => $fVal) {
                foreach ($tmpStatus as $key => $val) {
                    if ($key != 'autoCancel') {
                        foreach ($val as $oKey => $oVal) {
                            if ($oKey == $fVal['orderStatus']){
                                $fVal['orderStatus'] = $oVal['admin'];
                                $arrGoodsData[] = $fVal;
                            }
                        }
                    }
                }
            }
            $this->setData('goodsData', $arrGoodsData);

            /* 상품º주문번호별 메모 데이터 가져오기 */
            $getData = $orderAdmin->getAdminOrderGoodsMemoData($requestGetParams);
            $page = $orderAdmin->getPage($requestGetParams, Request::getQueryString());

            $this->setData('memoData', $getData);
            $this->setData('requestGetParams', $requestGetParams);
            $this->setData('managerSno', Session::get('manager.sno'));
            $this->setData('page', $page);

            $this->getView()->setDefine('layout', 'layout_blank.php');

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/popup_admin_order_goods_memo.php');
        } catch (\Exception $e) {

            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
