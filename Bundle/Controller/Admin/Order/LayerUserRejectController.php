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

use Request;
use Exception;

/**
 * Class LayerUserMemoController
 * 고객 신청 메모
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerUserRejectController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // POST 리퀘스트
            $postValue = Request::post()->toArray();
            $this->setData('data', $postValue);

            // 타이틀 설정
            $title = null;
            switch ($postValue['statusMode']) {
                case 'e':
                    $title =  __('교환');
                    break;
                case 'b':
                    $title =  __('반품');
                    break;
                case 'r':
                    $title =  __('환불');
                    break;
            }
            $this->setData('title', $title);

            // 넘어온 선택 주문상품 그대로 던짐
            foreach ($postValue['statusCheck'] as $val) {
                $statusCheck[] = $val;
            }
            $this->setData('statusCheck', $statusCheck);

            // 템플릿 설정
            $this->getView()->setDefine('layout', 'layout_layer.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
