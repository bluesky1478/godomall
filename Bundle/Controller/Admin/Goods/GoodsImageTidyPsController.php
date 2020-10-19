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
namespace Bundle\Controller\Admin\Goods;

use App;
use Component\Storage\Storage;use Globals;
use UserFilePath;
use Request;

class GoodsImageTidyPsController extends \Controller\Admin\Controller
{
    /**
     * 상품 이미지 일괄정리 페이지
     * [관리자 모드] 상품 이미지 일괄정리 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     */
    public function index()
    {
        // --- 모듈 호출
        if (gd_isset(Request::get()->get('mode')) == 'goods_image_tidy') {
            $db = App::getInstance('DB');

            // 페이지
            gd_isset(Request::get()->get('page'), 1);
            $pageNum = 30;
            $startNum = (Request::get()->get('page') - 1) * $pageNum;
            $nextPage = Request::get()->get('page') + 1;

            // --- 기존의 상품 불러옴 DB_GOODS_IMAGE
            $strSQL = 'SELECT g.goodsNo, g.imageStorage, g.imagePath
                FROM ' . DB_GOODS . ' g
                WHERE g.imageStorage = \'local\'
                ORDER BY g.goodsNo ASC LIMIT ' . $startNum . ',' . $pageNum;
            $result = $db->query($strSQL);

            // 페이지 계산
            $strSQL = 'SELECT COUNT(*) AS cnt
                FROM ' . DB_GOODS . ' g
                WHERE g.imageStorage = \'local\'';
            $res = $db->query($strSQL);
            $totalRecode = $res['cnt']; // 검색 레코드 수
            $totalPage = ceil($totalRecode / $pageNum);

            // dataFile 모듈
            //$dataFile = \App::load('\\Component\\File\\DataFile');
            while ($getData = $db->fetch($result)) {
                // 이미지 경로가 없는경우
                if (empty($getData['imagePath']) === true) {
                    continue;
                }

                // 보관된 이미지 경로
                //$goodsImageDir = UserFilePath::data('goods', $getData['imagePath']);

                // 이미지 경로 체크
//                if ($goodsImageDir->isDir() === false) {
//                    continue;
//                }

                // 상품 이미지명
                $setData = array();
                $strSQL = 'SELECT imageName FROM ' . DB_GOODS_IMAGE . ' WHERE goodsNo = \'' . $getData['goodsNo'] . '\'';
                $rstImg = $db->query($strSQL);
                while ($tmpData = $db->fetch($rstImg)) {
                    if (empty($tmpData['imageName']) === false) {
                        $setData[] = $tmpData['imageName'];
                        $setData[] = PREFIX_GOODS_THUMBNAIL . $tmpData['imageName'];
                    }
                }

                // 옵션 이미지
                $strSQL = 'SELECT iconImage,goodsImage FROM ' . DB_GOODS_OPTION_ICON . ' WHERE goodsNo = \'' . $getData['goodsNo'] . '\'';
                $rstImg = $db->query($strSQL);
                while ($tmpData = $db->fetch($rstImg)) {
                    if (empty($tmpData['iconImage']) === false) {
                        $setData[] = $tmpData['iconImage'];
                    }
                    if (empty($tmpData['goodsImage']) === false) {
                        $setData[] = $tmpData['goodsImage'];
                        $setData[] = PREFIX_GOODS_THUMBNAIL . $tmpData['goodsImage'];
                    }
                }

                // 이미지 저장소 세팅
                //$dataFile->setImageStorage($getData['imageStorage'], 'goodsImage', 'goods');

                // 폴더안의 화일 이름
                $imgData = array();
                if ($openDir = opendir($goodsImageDir)) {
                    while (($goodsImageNm = readdir($openDir)) !== false) {
                        if ($goodsImageNm != '.' && $goodsImageNm != '..') {
                            $imgData[] = $goodsImageNm;
                        }
                    }
                }

                sort($setData);
                sort($imgData);

                $delImage = array_diff($imgData, $setData); // 삭제할 이미지 (디비에는 없는데 남아 있는 이미지)
                $thumbImage = array_diff($setData, $imgData); // 썸네일 만들 이미지 (디비에는 있는데 폴더에는 없는 이미지 중에서 t50_로 시작하는 이미지)

                // 이미지 삭제
                if (empty($delImage) === false) {
                    foreach ($delImage as $imageNm) {
                        try {
                            ob_start();
                            //$dataFile->setImageDelete($getData['imageStorage'], 'goodsImage', 'goods', $getData['imagePath'] . $imageNm, 'file');
                            Storage::disk(Storage::PATH_CODE_GOODS,$getData['imageStorage'])->delete($getData['imagePath'] . $imageNm);
                            if ($out = ob_get_clean()) {
                                throw new Except('IMG_DEL_ERROR', $out);
                            }
                        } catch (Except $e) {
                            echo ($e->ectMessage);
                            debug($getData['imagePath'] . $imageNm);
                        }
                    }
                }

                // 썸네일 이미지 만들기
                if (empty($thumbImage) === false) {
                    foreach ($thumbImage as $imageNm) {
                        if (preg_match('/^' . PREFIX_GOODS_THUMBNAIL . '/', $imageNm)) {
                            try {
                                ob_start();
                                // GD 이용한 썸네일 이미지 저장
                                //$dataFile->saveImage($goodsImageDir . str_replace(PREFIX_GOODS_THUMBNAIL, '', $imageNm), preg_replace('/[^0-9]/', '', PREFIX_GOODS_THUMBNAIL), 80, 'goodsImage', $getData['imagePath'] . $imageNm);
                                $originalFile = $goodsImageDir . str_replace(PREFIX_GOODS_THUMBNAIL, '', $imageNm);
                                Storage::disk(Storage::PATH_CODE_GOODS)->uploadThumbImage($originalFile,$getData['imageStorage'],$getData['imagePath'] . $imageNm, preg_replace('/[^0-9]/', '', PREFIX_GOODS_THUMBNAIL),80);
                                if ($out = ob_get_clean()) {
                                    throw new Except('IMG_THUMBNAIL_ERROR', $out);
                                }
                            } catch (Except $e) {
                                echo ($e->ectMessage);
                                debug($getData['imagePath'] . $imageNm);
                                debug($goodsImageDir . str_replace(PREFIX_GOODS_THUMBNAIL, '', $imageNm));
                            }
                        }
                    }
                }
            }

            // 진행률
            $processPercent = (100 * Request::get()->get('page')) / $totalPage;

            if (Request::get()->get('page')== $totalPage) {
                $pageUrl = '';

                // --- 재실행요청시 du 파일삭제
                $serialPath = UserFilePath::get('config', 'serial.pg');
                if ($serialPath->isFile() === true) {
                    @unlink($serialPath);
                }
            } else {
                $pageUrl = '<meta http-equiv="Refresh" Content="0;url=' . Request::getFileUri() . '?mode=goods_image_tidy&page=' . $nextPage . '">';
            }
        }
        ?>
<?php echo HTML_DOCTYPE;?>
<head>
<title>:: <?php echo __('상품 이미지 일괄 정리'); ?> ::</title>
<meta http-equiv="Content-Type"
    content="text/html; charset=<?php echo SET_CHARSET;?>" />
<meta http-equiv="imagetoolbar" content="no" />
<meta http-equiv="Cache-Control" content="No-Cache" />
<meta http-equiv="Pragma" content="No-Cache" />
<meta name="robots" content="noindex, nofollow" />
<meta name="robots" content="noarchive" />
<script type="text/javascript">
<!--
parent.$('#progressText').html(Math.round(<?php echo $processPercent;?>)+' %');
parent.$('#progressBar').css('width','<?php echo $processPercent;?>%');
<?php if (Request::get()->get('page')== 1) {?>
parent.$('#processBtn').html('<span class="notice-ref">[ <?php echo __('정리중'); ?> ]</span>');
<?php } else if (Request::get()->get('page') == $totalPage) {?>
parent.$('#processBtn').html('<span class="button black"><a href="./goods_image_tidy.php"><?php echo __('정리완료'); ?></a></span>');
<?php }?>
//-->
</script>
<?php echo gd_isset($pageUrl);?>
</head>
<body>
</body>
</head>
<?php
    }
}
