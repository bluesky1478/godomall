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

use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\HttpException;
use Framework\Debug\Exception\LayerNotReloadException;
use Framework\StaticProxy\Proxy\Session;
use Message;
use Globals;
use Request;

/**
 * 스케줄(일정관리) 처리
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SchedulePsController extends \Controller\Admin\Base\SchedulePsController
{

    /**
     * index
     *
     * @throws LayerException
     * @throws HttpException
     */
    public function index()
    {
        parent::index();
    }
}
