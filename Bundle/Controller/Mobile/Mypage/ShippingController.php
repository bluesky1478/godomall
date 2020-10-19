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

use Component\Validator\Validator;
use Framework\Utility\DateTimeUtils;
use App;
use Request;

/**
 * Class ShippingController
 *
 * @package Bundle\Controller\Front\Mypage
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class ShippingController extends \Controller\Mobile\Controller
{
    /**
     * {@inheritdoc}
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function index()
    {
        $regTerm = Request::post()->get('regTerm', 7);
        $regDt = Request::post()->get(
            'regDt',
            [
                DateTimeUtils::dateFormat('Y-m-d', '-7 days'),
                DateTimeUtils::dateFormat('Y-m-d', 'now'),
            ]
        );
        if ($regTerm < 0) {
            $regDt = [];
        }

        $active['regTerm'][$regTerm] = 'active';

        /** @var \Bundle\Component\Mileage\Mileage $mileage */
        $order = App::load('\\Component\\Order\\Order');

        $pageNo = Request::get()->get('page', 1);

        $shippingList = $order->getShippingAddressList($pageNo, 100);
        $this->setData('shippingList', $shippingList);

        // 페이징 처리
        $page = App::load(\Component\Page\Page::class);
        $this->setData('page', gd_isset($page));

        $this->setData('regTerm', $regTerm);
        $this->setData('regDt', $regDt);
        $this->setData('active', $active);
    }
}
