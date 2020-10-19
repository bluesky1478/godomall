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

use App;
use Session;
use Request;
use Exception;
use Framework\Utility\ArrayUtils;
use Component\Cart\CartAdmin;

/**
 * 수기주문 옵션변경
 *
 * @package Bundle\Controller\Admin\Order
 * @author  <bumyul2000@godo.co.kr>
 */
class LayerOptionController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            $postValue = Request::post()->toArray();

            $goods = \App::load('\\Component\\Goods\\Goods');

            $selectGoodsFl = false;

            // 상품 정보
            $goodsView = $goods->getGoodsView($postValue['goodsNo']);

            if($postValue['cartSno']) {
                $cart = new CartAdmin($postValue['memNo']);
                $optionInfo = $cart->getCartInfo($postValue['cartSno']);

                if($optionInfo) {
                    if ($optionInfo['memberCouponNo']) {
                        throw new Exception(__('쿠폰 적용 취소 후 옵션 변경 가능합니다.'));
                    }

                    // 추가 상품 정보
                    if (empty($optionInfo['addGoodsNo']) === false) {
                        $optionInfo['addGoodsNo'] = json_decode($optionInfo['addGoodsNo']);
                        $optionInfo['addGoodsCnt'] = json_decode($optionInfo['addGoodsCnt']);

                    } else {
                        $optionInfo['addGoodsNo'] = '';
                        $optionInfo['addGoodsCnt'] = '';
                    }

                    // 텍스트 옵션 정보 (sno, value)
                    $optionInfo['optionTextSno'] = [];
                    $optionInfo['optionTextStr'] = [];
                    if (empty($optionInfo['optionText']) === false) {
                        $arrText = json_decode($optionInfo['optionText']);
                        foreach ($arrText as $key => $val) {
                            $optionInfo['optionTextSno'][] = $key;
                            $optionInfo['optionTextStr'][$key] = $val;
                            unset($tmp);
                        }
                    }
                    unset($optionInfo['optionText']);

                    if($goodsView['optionDisplayFl'] =='d' && $optionInfo['optionSno'] ) {
                        foreach($goodsView['option'] as $k => $v) {
                            if($v['sno'] == $optionInfo['optionSno']) {
                                for($i = 1; $i <= 5; $i++) {
                                    if(gd_isset($v['optionValue'.$i])) $optionName[] = $v['optionValue'.$i];
                                }
                                $optionInfo['optionSnoText'] = $v['sno'].INT_DIVISION.gd_money_format($v['optionPrice'],false).INT_DIVISION.$v['mileageOption'].INT_DIVISION.$v['stockCnt'].STR_DIVISION.implode("/",$optionName);
                            }
                        }
                    }

                    $this->setData('optionInfo', gd_isset($optionInfo));
                    $selectGoodsFl = true;

                }

            }

            $this->setData('goodsView', gd_isset($goodsView));
            $this->setData('cartSno', gd_isset($postValue['cartSno']));
            $this->setData('type', gd_isset($postValue['type']));
            $this->setData('page', gd_isset($postValue['page']));
            $this->setData('selectGoodsFl', $selectGoodsFl);
            $this->setData('int_division', INT_DIVISION);
            $this->setData('str_division', STR_DIVISION);

            //상품 노출 필드
            $displayField = gd_policy('display.goods');
            $this->setData('displayAddField', $displayField['goodsDisplayAddField']['pc']);

            //상품 품절 설정 코드 불러오기
            $code = \App::load('\\Component\\Code\\Code',$mallSno);
            $optionSoldOutCode = $code->getGroupItems('05002');
            $optionSoldOutCode['n'] = $optionSoldOutCode['05002002'];
            $this->setData('optionSoldOutCode', $optionSoldOutCode);

            //상품 배송지연 설정 코드 불러오기
            $code = \App::load('\\Component\\Code\\Code',$mallSno);
            $optionDeliveryDelayCode = $code->getGroupItems('05003');
            $this->setData('optionDeliveryDelayCode', $optionDeliveryDelayCode);

            $this->getView()->setDefine('layout', 'layout_layer.php');
        } catch (Exception $e) {
            $this->json([
                'error' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
