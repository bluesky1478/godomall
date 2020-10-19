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

namespace Bundle\Controller\PlusShop;

use App;
use Core\View\Resolver\TemplateResolver;
use Core\View\Template;

class PlusShopController extends \Core\Base\Controller\Controller
{
    /**
     * {@inheritdoc}TODO:PLUS
     */
    public function __construct()
    {
        parent::__construct();
        // @formatter:off
        $view = new Template($this->getPageName(), new TemplateResolver());
        // @formatter:on

        $this->setView($view);
    }

    /**
     * {@inheritdoc}
     */
    final protected function setUp()
    {
        parent::setUp();

        $this->setInterceptors(App::getConfig('bundle.interceptor')->getService());
    }
}
