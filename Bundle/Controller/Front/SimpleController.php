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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Front;


use Core\View\Resolver\TemplateResolver;
use Core\View\Template;

/**
 * Class SimpleController
 * @package Bundle\Controller\Front
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class SimpleController extends \Core\Base\Controller\Controller
{
    /**
     * {@inheritdoc}
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
        $interceptors = \App::getConfig('bundle.interceptor')->getFront();
        foreach ($interceptors as $index => $interceptor) {
            if (count($interceptor[1]) > 0) {
                unset($interceptors[$index]);
            }
        }
        $this->setInterceptors($interceptors);
    }
}
