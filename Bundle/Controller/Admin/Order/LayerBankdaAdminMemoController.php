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

use Exception;
use Request;

/**
 * Class LayerUserMemoController
 * 고객 신청 메모
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerBankdaAdminMemoController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            // POST 리퀘스트
            $postValue = Request::post()->toArray();
            $this->setData('bankdaNo', $postValue['bankdaNo']);

            // 모듈 설정
            $bankda = \App::load('\\Component\Bankda\Bankda');

            // 상품과 관련된 모든 데이터 가져오기
            $getData = $bankda->bankAdminMemoSelect($postValue['bankdaNo']);
            if($getData['bankdaNo']) {
                $writeMode = 'modify';
            } else {
                $writeMode = 'insert';
            }
            $getData['adminMemo'] = str_replace(['\r\n', '\n'], chr(10), gd_htmlspecialchars_stripslashes($getData['adminMemo']));
            $this->setData('writeMode', $writeMode);
            $this->setData('data', $getData);

            $this->getView()->setDefine('layout', 'layout_layer.php');

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/layer_bankda_admin_memo.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
