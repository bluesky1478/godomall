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

namespace Bundle\Controller\Front\Goods;

use Request;
use Framework\Utility\HttpUtils;

class PaycosearchUrlController extends \Controller\Front\Controller
{
    public function index()
    {
        $getValue = Request::get()->all();
        $time = time() . substr(microtime(), 1, 4);
        $url = $getValue['url'] . '&ts=' . $time;
        $response = HttpUtils::remotePost($url);
        parse_str($url, $param);
        $this->redirect($param['u']);
        exit();
    }
}
