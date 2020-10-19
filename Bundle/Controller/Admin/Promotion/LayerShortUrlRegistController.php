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
namespace Bundle\Controller\Admin\Promotion;

use Component\Promotion\ShortUrl;

/**
 * Class LayerShortUrlRegistController
 *
 * @package Bundle\Controller\Admin\Promotion
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerShortUrlRegistController extends \Controller\Admin\Controller
{

    /**
     * QrCodeConfigController
     *
     * @author yjwee
     */
    public function index()
    {
        // ShortUrl 객체 생성
        $shortUrl = new ShortUrl();

        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
