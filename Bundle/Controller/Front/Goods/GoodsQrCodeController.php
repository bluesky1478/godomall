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

use Framework\Debug\Except;
use Request;
use Logger;
use Endroid\QrCode\QrCode as EndroidQrCode;

class GoodsQrCodeController extends \Core\Base\Controller\StreamedController
{
    /**
     * @{inheritdoc}
     */
    /**
     * index
     * 상품 QR코드 다운로드 컨트롤러
     *
     * @throws \Endroid\QrCode\Exceptions\ImageFunctionUnknownException
     */
    public function index()
    {
        $goodsNo = Request::get()->get('goodsNo');

        // --- QrCode
        $endroidQrCode = new EndroidQrCode();
        $endroidQrCode->setText("http://" . Request::server()->get("SERVER_NAME") . "/goods/goods_view.php?goodsNo=" . $goodsNo);
        $endroidQrCode->setModuleSize(2);
        $endroidQrCode->render();
        echo $endroidQrCode->get();

        $this->streamedDownload('QrCode_' . $goodsNo . '.png');
    }
}
