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

namespace Bundle\Controller\Admin\Member;

use Component\Godo\GodoPaycoServerApi;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;

/**
 * Class LayerPaycoLoginRequestController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class LayerPaycoLoginRequestController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        try {
            $paycoApi = new GodoPaycoServerApi();

            $this->setData('terms', $paycoApi->getPaycoTerms());

            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setDefine('layoutContent', \Request::getDirectoryUri() . '/' . \Request::getFileUri());
        } catch (Exception $e) {
            throw new AlertOnlyException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
