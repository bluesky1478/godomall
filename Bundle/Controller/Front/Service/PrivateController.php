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

namespace Bundle\Controller\Front\Service;

use Component\Agreement\BuyerInformCode;
use Component\Agreement\BuyerInform;
use Component\Mall\Mall;

/**
 * Class 개인정보취급방침
 * @package Bundle\Controller\Front\Service
 * @author  yjwee
 */
class PrivateController extends \Controller\Front\Controller
{
    /**
     * {@inheritDoc}
     */
    public function index()
    {
        $inform = new BuyerInform();
        $privateCode = \Request::get()->get('code', BuyerInformCode::BASE_PRIVATE);
        $mallSno = gd_isset(Mall::getSession('sno'), DEFAULT_MALL_NUMBER);
        $private = $inform->getPrivateWithReplaceCode($privateCode);
        $informList = $inform->makeSelectBoxByInform($private['informCd'], $mallSno);
        $private['content'] .= $informList;
        $this->setData('private', $private['content']);
    }
}
