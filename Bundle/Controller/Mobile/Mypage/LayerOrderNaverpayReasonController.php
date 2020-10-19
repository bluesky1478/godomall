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

namespace Bundle\Controller\Mobile\Mypage;

use Bundle\Component\Order\Order;
use Component\Database\DBTableField;
use Component\Goods\GoodsCate;
use Component\Page\Page;
use Cookie;
use Exception;
use Request;
use Session;

/**
 * Class MypageQnaController
 *
 * @package Bundle\Controller\Front\Mypage
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerOrderNaverpayReasonController extends \Controller\Mobile\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        $req = \Request::get()->toArray();
        $order = new Order();
        $data = $order->getOrderGoodsData($req['orderNo'],$req['orderGoodsNo'],null,null,null,false);
        $this->setData('data',$data['naverpayStatus']);
    }
}
