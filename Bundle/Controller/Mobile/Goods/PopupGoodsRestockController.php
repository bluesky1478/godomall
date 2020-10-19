<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2017, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Mobile\Goods;

use App;
use Session;
use Request;
use Exception;
use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Framework\Debug\Exception\AlertCloseException;

/**
 * Class LayerDeliveryAddress
 *
 * @package Bundle\Controller\Front\Goods
 * @author  by
 */
class PopupGoodsRestockController extends \Controller\Mobile\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            $getValue = Request::get()->toArray();
            if(trim($getValue['goodsNo']) === ''){
                throw new Exception("상품정보를 확인 할 수 없습니다.");
            }
            if (gd_is_plus_shop(PLUSSHOP_CODE_RESTOCK) !== true) {
                throw new Exception("[플러스샵] 미설치 또는 미사용 상태입니다. 설치 완료 및 사용 설정 후 플러스샵 앱을 사용할 수 있습니다.");
            }

            //상품정보
            $goods = App::load('\\Component\\Goods\\Goods');
            $goodsData = $goods->getGoodsInfo($getValue['goodsNo'], 'goodsNo, goodsNm, optionFl, optionName, soldOutFl, stockFl, minOrderCnt, optionDisplayFl');
            if($goodsData['optionFl'] === 'y'){
                $goodsData['option'] = gd_htmlspecialchars($goods->getGoodsOption($getValue['goodsNo']));
                $goodsData['option'] = $goods->setGoodsOptionRestockCare($goodsData);
            }

            //회원정보
            if(Session::has('member')){
                $cellPhone = Session::get('member.cellPhone');
                if(trim($cellPhone) !== ''){
                    $cellPhone = str_replace("-", "", $cellPhone);
                    if(strlen($cellPhone) > 11){
                        $cellPhone = substr($cellPhone, 0, 11);
                    }
                    $this->setData('cellPhone', gd_isset($cellPhone));
                }
                $this->setData('name', gd_isset(Session::get('member.memNm')));
                $this->setData('memNo', gd_isset(Session::get('member.memNo')));
            }

            //개인정보 수집 안내
            $inform = new BuyerInform();
            if(gd_is_login() === true){
                $privateData = $inform->getInformData(BuyerInformCode::PRIVATE_APPROVAL);
            }
            else {
                $privateData = $inform->getInformData(BuyerInformCode::PRIVATE_GUEST_ORDER);
            }

            //분리형일시 옵션명 합치기
            if(gettype($goodsData['optionName']) === 'array'){
                $goodsData['optionName'] = implode("/", $goodsData['optionName']);
            }

            $this->setData('privateData', $privateData);
            $this->setData('goodsData', $goodsData);
            $this->setData('goodsNo', $getValue['goodsNo']);
        } catch (Exception $e) {
            throw new AlertCloseException(__($e->getMessage()));
        }
    }
}
