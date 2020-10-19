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
namespace Bundle\Widget\Mobile;

use Core\Base\PageNameResolver\WidgetPageNameResolver;
use Core\View\Template;
use Core\View\Resolver\TemplateResolver;

/**
 *
 * @author Lee Seungjoo <slowj@godo.co.kr>
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 */
class Widget extends \Core\Base\Controller\Controller
{
    /**
     * Widget 생성자.
     */
    public function __construct($controllerData)
    {
        $this->setPageName(new WidgetPageNameResolver());

        $view = new Template(
            $this->getPageName(),
            new TemplateResolver()
        );

        $this->setView($view);
        $this->setData($controllerData);
    }

    /**
     * {@inheritdoc}
     */
    public function index()
    {
        // 아무 처리 하지 않음.
    }
}
