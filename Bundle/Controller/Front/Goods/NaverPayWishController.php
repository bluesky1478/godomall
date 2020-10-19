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

use Component\Goods\Goods;
use Component\Naver\NaverPay;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Utility\SkinUtils;

class NaverPayWishController extends \Controller\Front\Controller
{
    public function index()
    {
        try {
            $postValue = \Request::request()->all();

            $naverPay = new NaverPay();
            $naverPayConfig = $naverPay->getConfig();
            if ($naverPay->checkUse() === false) {
                throw new \Exception(__('네이버페이 사용을 중단하였습니다.'));
            }

            $queryString = 'SHOP_ID=' . urlencode($naverPayConfig['naverId']);
            $queryString .= '&CERTI_KEY=' . urlencode($naverPayConfig['connectId']);
            $queryString .= '&RESERVE1=&RESERVE2=&RESERVE3=&RESERVE4=&RESERVE5=';

            $goodsNo = $postValue['wishGoodsNo'];
            $goods = new Goods();
            $goodsData = $goods->getGoodsView($goodsNo);

            //이미지명
            $goodsImage = $goods->getGoodsImage($goodsNo, 'main');
            if ($goodsImage) {
                $goodsImageName = $goodsImage[0]['imageName'];
                $goodsImageSize = $goodsImage[0]['imageSize'];
                $_imageInfo = pathinfo($goodsImageName);
                if(!$goodsImageSize) {
                    $goodsImageSize= SkinUtils::getGoodsImageSize($_imageInfo['extension']);
                    $goodsImageSize= $goodsImageSize['size1'];
                }
            }

            $goodsImageSrc = SkinUtils::imageViewStorageConfig($goodsImageName, $goodsData['imagePath'], $goodsData['imageStorage'], $goodsImageSize, 'goods',false)[0];
            $goodsImageSrc = str_replace('https','http',$goodsImageSrc);
            $goodsImageSrc = str_replace(':443','',$goodsImageSrc);

            $id = $goodsNo;
            $name = $goodsData['goodsNm'];
            $uprice = $goodsData['goodsPrice'];
            $image = $goodsImageSrc;
            $url = URI_HOME . 'goods' . DS . 'goods_view.php?inflow=naverPay&goodsNo=' . $goodsData['goodsNo'];
            $queryString .= '&' . $this->makeQueryString($id, $name, $uprice, $image, $url);
            //echo($queryString . "<br>\n");
            if ($naverPayConfig['testYn'] == 'y') {
                $req_host = 'test-pay.naver.com';
                $req_addr = 'ssl://test-pay.naver.com';
            } else {
                $req_host = 'pay.naver.com';
                $req_addr = 'ssl://pay.naver.com';
            }
            $req_url = 'POST /customer/api/wishlist.nhn HTTP/1.1'; // utf-8

            $req_port = 443;
            $nc_sock = fsockopen($req_addr, $req_port, $errno, $errstr);
            if ($nc_sock) {
                fwrite($nc_sock, $req_url . "\r\n");
                fwrite($nc_sock, "Host: " . $req_host . ":" . $req_port . "\r\n");
                fwrite($nc_sock, "Content-type: application/x-www-form-urlencoded; charset=utf-8\r\n");
                fwrite($nc_sock, "Content-length: " . strlen($queryString) . "\r\n");
                fwrite($nc_sock, "Accept: */*\r\n");
                fwrite($nc_sock, "\r\n");
                fwrite($nc_sock, $queryString . "\r\n");
                fwrite($nc_sock, "\r\n");

                // get header
                $headers = '';
                while (!feof($nc_sock)) {
                    $header = fgets($nc_sock, 4096);
                    if ($header == "\r\n") {
                        break;
                    } else {
                        $headers .= $header;
                    }
                }

                // get body
                $bodys = '';
                if (strpos($headers, 'Transfer-Encoding: chunked') !== false) {
                    while ($line = fgets($nc_sock)) {
                        $size = hexdec(trim($line));

                        if ($size == 0) break;

                        $buffer = '';
                        while (strlen($buffer) < $size + 2) {
                            $buffer .= fread($nc_sock, $size + 2 - strlen($buffer));
                        }

                        $bodys .= substr($buffer, 0, strlen($buffer) - 2);
                    }
                } else {
                    while (!feof($nc_sock)) {
                        $bodys .= fgets($nc_sock);
                    }
                }

                fclose($nc_sock);
                $resultCode = substr($headers, 9, 3);

                if ($resultCode == 200) {
                    $itemId = $bodys;
                } else {
                    throw new \Exception(__('동시에 접속하는 이용자 수가 많거나 네트워크 상태가 불안정하여\n현재 네이버페이 서비스 접속이 불가합니다.\n이용에 불편을 드린 점 진심으로 사과드리며, 잠시 후 다시 접속해 주시기 바랍니다.'));
                }
            } else {
                echo "$errstr ($errno)<br>\n";
                exit(-1);
            }

//리턴받은 itemId로 주문서 page를 호출한다.
            if (\Request::isMobileDevice()) {
                if ($naverPayConfig['testYn'] == 'y') {
                    $wishlistPopupUrl = "https://test-m.pay.naver.com/mobile/customer/wishList.nhn";
                } else {
                    $wishlistPopupUrl = "https://m.pay.naver.com/mobile/customer/wishList.nhn";
                }
            } else {
                $wishlistPopupUrl = "https://" . $req_host . "/customer/wishlistPopup.nhn";
            }

            ?>
            <html>
            <body>
            <form name="frm" method="get" action="<?= $wishlistPopupUrl ?>">
                <input type="hidden" name="SHOP_ID" value="<?= $naverPayConfig['naverId'] ?>">
                <input type="hidden" name="ITEM_ID" value="<?= $itemId ?>">
            </form>
            </body>
            <script>
                <?php if ($resultCode == 200) { ?>
                <?php if(\Request::isMobileDevice()){ ?>
                <?php if($naverPayConfig['mobileButtonTarget'] == 'new') {?>
                document.frm.target = "_blank";
                <?php }
                else {?>
                document.frm.target = "_top";
                <?php }?>

                document.frm.submit();
                <?php }else{ ?>
                document.frm.submit();
                <?php } ?>
                <?php } ?>
            </script>
            </html>
            <?php
            exit;
        } catch (\Exception $e) {
            if(\Request::request()->get('popupMode') == 'y'){
                throw new AlertCloseException($e->getMessage());
            }else {
                throw new AlertOnlyException($e->getMessage());
            }
        }
    }

    private function makeQueryString($id, $name, $uprice, $image, $url)
    {
        $ret = 'ITEM_ID=' . urlencode($id);
        $ret .= '&ITEM_NAME=' . urlencode($name);
        $ret .= '&ITEM_UPRICE=' . (int)$uprice;
        $ret .= '&ITEM_IMAGE=' . urlencode($image);
        $ret .= '&ITEM_URL=' . urlencode($url);
        return $ret;
    }
}
