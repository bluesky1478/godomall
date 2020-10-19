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

namespace Bundle\Controller\Front\Mypage;

use Component\Database\DBTableField;
use Component\Goods\GoodsCate;
use Component\Page\Page;
use Exception;
use Framework\Debug\Exception\AlertReloadException;
use Request;
use Session;

/**
 * Class MypageQnaController
 *
 * @package Bundle\Controller\Front\Mypage
 * @author  cjb3333
 */
class LayerDepositReasonController extends \Controller\Front\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // POST 리퀘스트
            $postValue = Request::post()->toArray();
            $orderReorderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');
            $handleData = $orderReorderCalculation->getOrderHandleData($postValue['orderNo'], null, null, $postValue['handleSno']);
            $data = $orderReorderCalculation->getOrderDepositHandleData($handleData[0]);
            if ($data !== false) {
                $this->setData('data', $data);
            } else {
                if (Request::isAjax()) {
                    $this->json([
                        'code' => 0,
                        'message' => __('조회하실 사유가 존재하지 않습니다.'),
                    ]);
                } else {
                    throw new AlertReloadException(__('조회하실 사유가 존재하지 않습니다.'));
                }
            }

        } catch (Exception $e) {
            throw $e;
        }
    }
}
