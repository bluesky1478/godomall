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

namespace Bundle\Controller\Api;

/**
 *
 * @author Lee Seungjoo <slowj@godo.co.kr>
 */
class IndexController extends \Controller\Api\Controller
{
    /**
     * {@inheritDoc}
     */
    public function index()
    {
        echo '<h1>API Home</h1>';
        exit;
    }
}
