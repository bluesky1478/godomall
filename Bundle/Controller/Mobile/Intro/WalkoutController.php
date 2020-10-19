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
namespace Bundle\Controller\Mobile\Intro;

use Globals;
/**
 * 인트로 - 일반
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class WalkoutController extends \Controller\Mobile\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        $this->setData('gMall', Globals::get('gMall'));
    }
}
