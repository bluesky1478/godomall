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
namespace Bundle\Controller\Admin\Provider\Base;

use Exception;
use Globals;
use Request;
use Session;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\AlertRedirectException;


/**
 * Class LoginController
 *
 * @package Bundle\Controller\Admin\Base
 * @author  Lee Hun <akari2414@godo.co.kr>
 */
class LayerScreenSecurityController extends \Controller\Admin\Base\LayerScreenSecurityController
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        parent::index();
    }
}
