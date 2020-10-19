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

namespace Bundle\Controller\Mobile\Member\Ipin;

use Core\Base\PageNameResolver\ControllerPageNameResolver;

/**
 * Class IpinMainController
 * @package Bundle\Controller\Mobile\Member\Ipin
 * @author  yjwee
 */
class IpinMainController extends \Controller\Front\Member\Ipin\IpinMainController
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        parent::index('member/ipin/ipin_process.php');
        $this->setPageName(new ControllerPageNameResolver());
    }
}
