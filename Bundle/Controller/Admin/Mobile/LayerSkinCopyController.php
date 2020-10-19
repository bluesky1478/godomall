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

namespace Bundle\Controller\Admin\Mobile;

use Framework\Debug\Exception\LayerException;
use Message;
use Globals;
use Request;

/**
 * 디자인 스킨 복사
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class LayerSkinCopyController extends \Controller\Admin\Design\LayerSkinCopyController
{
    /**
     * index
     *
     * @throws LayerException
     */
    public function index()
    {
        parent::index();
    }
}
