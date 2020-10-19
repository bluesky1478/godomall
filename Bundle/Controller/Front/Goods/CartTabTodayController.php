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

namespace Bundle\Controller\Front\Goods;

use App;
use Session;
use Request;
use Exception;
use Framework\Utility\ArrayUtils;
use FileHandler;

/**
 * Class LayerDeliveryAddress
 *
 * @package Bundle\Controller\Front\Order
 * @author  su
 */
class CartTabTodayController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {

            if (!Request::isAjax()) {
                throw new Exception('Ajax ' . __('전용 페이지 입니다.'));
            }

            if (FileHandler::isExists( USERPATH_SKIN.'js/slider/slick/slick.js')) {
                $addScript[] =  'slider/slick/slick.js';
            }
            if (FileHandler::isExists( USERPATH_SKIN.'js/bxslider/dist/jquery.bxslider.min.js')) {
                $addScript[] =  'bxslider/dist/jquery.bxslider.min.js';
            }

            if($addScript) $this->addScript($addScript);

        } catch (Exception $e) {
            $this->json([
                'error' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
