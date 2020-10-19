<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Mobile\Share;


class PlusShopContainer extends \PlusShop\Mobile\PlusShopListen
{
    public function index()
    {
        $this->setData('gPageName', '');
        $this->getView()->setPageName('error/plus_shop_container_mobile');
    }
}
