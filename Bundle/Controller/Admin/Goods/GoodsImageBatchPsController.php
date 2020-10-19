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

use Component\Goods\GoodsAdmin;
use Component\Storage\Storage;
use Component\File\StorageHandler;
use Framework\Debug\Exception\LayerNotReloadException;
use Framework\Utility\SkinUtils;
use Globals;
use Request;

/**
 * 상품 이미지 일괄처리
 * @author Lee Namju <lnjts@godo.co.kr>
 */
class GoodsImageBatchPsController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // --- 모듈 호출
        try {
            if (!is_object($this->db)) {
                $this->db = \App::load('DB');
            }
            $mode = Request::request()->get('mode');
            $goodsAdmin = new GoodsAdmin();
            if ($mode == 'goods_image_batch') { //전체 일괄처리
                $search['imageName'] = Request::post()->get('imageName');

                $reImage = $search['imageName'];
                foreach($reImage as $k => $v){
                    $search['imageName'][$k] = addslashes($reImage[$k]);
                }
                $tempImageList = $goodsAdmin->getTmpGoodsImage($search, false, false);   //적용가능 상품이 존재하는경우만
                $tmpResult = [];
                $successCopyGoodsNo = [];
                foreach ($tempImageList as $data) {

                    if ($data['status'] == 'noGoods') {
                        $tmpResult['list'][$data['imageName']][$data['goodsNo']]['check'] = 'n';
                        continue;
                    } else if ($data['status'] == 'fileExists' || in_array(stripslashes($data['imageName']), $successCopyGoodsNo[$data['goodsNo']])) {
                        continue;
                    }

                    // 이미지 저장소 세팅
                    // 이미지 설정
                    $targetImageFile = stripslashes($data['imagePath'] . $data['oriImageName']);
                    $thumbFilename = PREFIX_GOODS_THUMBNAIL . stripslashes($data['oriImageName']);
                    $thumbnailImageFile = stripslashes($data['imagePath'] . DS . $thumbFilename);
                    if (Storage::disk(Storage::PATH_CODE_GOODS, $data['imageStorage'])->isFileExists($targetImageFile) === true) {    //파일이 서버에 이미 존재하면
                        $goodsAdmin->updateTmpGoodsImage(['sno' => $data['sno'], 'status' => 'fileExists']);
                        $tmpResult['list'][$data['imageName']][$data['goodsNo']]['check'] = 'n';
                        continue;
                    }

                    $data['imageName'] = stripslashes($data['imageName']);
                    $copyResult = Storage::copy(Storage::PATH_CODE_GOODS, $data['imageStorage'], 'tmp/' . $data['imageName'], $data['imageStorage'], $targetImageFile);
                    if (Storage::disk(Storage::PATH_CODE_GOODS, $data['imageStorage'])->isFileExists($thumbnailImageFile) === false) {    //섬네일이 서버에 존재하지 않으면
                        $copyThumbResult = Storage::copy(Storage::PATH_CODE_GOODS, $data['imageStorage'], 'tmp/' . $data['imageName'], $data['imageStorage'], $thumbnailImageFile, true);
                    }

                    if (!$copyResult) {
                        $tmpResult['list'][$data['imageName']][$data['goodsNo']]['check'] = 'n';
                        $goodsAdmin->updateTmpGoodsImage(['sno' => $data['sno'], 'status' => 'fileExists']);
                    } else {
                        $tmpResult['list'][$data['imageName']][$data['goodsNo']]['check'] = 'y';
                        $successCopyGoodsNo[$data['goodsNo']][] = $data['imageName'];
                        $tmpPath = Storage::disk(Storage::PATH_CODE_GOODS, 'local')->getRealPath('tmp/' . $data['imageName']);
                        $imgSize = getimagesize($tmpPath);
                        $imgWidth = 0;
                        if ($imgSize && is_array($imgSize)) {
                            $imgWidth = $imgSize[0];
                        }
                        $tmpResult['list'][$data['imageName']][$data['goodsNo']]['size'] = $imgWidth;
                    }
                }
                $successCnt = 0;

                foreach ($tmpResult['list'] as $imageName => $val) {
                    $isSuccess = true;
                    foreach ($val as $goodsNo => $_val) {
                        if ($_val['check'] == 'y') {
                            $imageName = addslashes($imageName);
                            $arrBind = [];
                            $this->db->bind_param_push($arrBind, 'i', $_val['size']);
                            $this->db->bind_param_push($arrBind, 'i', $goodsNo);
                            $this->db->bind_param_push($arrBind, 's', $imageName);

                            $this->db->set_update_db(DB_GOODS_IMAGE, ' imageSize = ?  ', ' goodsNo = ? AND imageName = ? ', $arrBind);

                            $imageName = stripslashes($imageName);
                            Storage::disk(Storage::PATH_CODE_GOODS, 'local')->delete('tmp/' . $imageName);
                            Storage::disk(Storage::PATH_CODE_GOODS, 'local')->delete('tmp/' . PREFIX_GOODS_THUMBNAIL . $imageName);
                        }
                        else {
                            $isSuccess = false;
                        }
                    }
                    if($isSuccess){
                        $successCnt++;
                    }
                }

                $totalImageCnt = count($tmpResult['list']);
                exit('<script>parent.complete("' . $totalImageCnt . '","' . $successCnt . '","' . ($totalImageCnt - $successCnt) . '")</script>');

            } else if ($mode == 'saveTempGoodsImage') { //임시폴더 이미지 저장하기
                $query = "TRUNCATE TABLE " . DB_TMP_GOODS_IMAGE;
                $this->db->query($query);
                $tempImageList = Storage::disk(Storage::PATH_CODE_GOODS, 'local')->listContents('tmp/');
                $goodsAdmin = new GoodsAdmin();
                foreach ($tempImageList as $file) {
                    $fileName = trim(iconv('CP949', 'UTF-8', $file['basename']));
                    $fileName = addslashes(addslashes($fileName));
                    $data = $goodsAdmin->getGoodsByImageName($fileName);
                    $goodsImageSrc = null;
                    $status = 'ready';
                    if ($data) {
                        foreach ($data as $val) {
                            $status = 'ready';
                            $targetImageFile = $val['imagePath'] . $fileName;
                            if (Storage::disk(Storage::PATH_CODE_GOODS, $val['imageStorage'])->isFileExists($targetImageFile) === true) {    //파일이 서버에 이미 존재하면
                                $status = 'fileExists';
                            }
                            $arrData = [
                                'goodsNo' => $val['goodsNo'],
                                'imageName' => $fileName,
                                'imageKind' => $val['imageKind'],
                                'status' => $status,
                                'imagePath' => $val['imagePath'],
                            ];
                            $goodsAdmin->saveTmpGoodsImage($arrData);
                        }

                    } else {
                        $status = 'noGoods';
                        $arrData = [
                            'goodsNo' => 0,
                            'imageName' => $fileName,
                            'imageStorage' => '',
                            'status' => $status,
                            'imagePath' => '',
                        ];
                        $goodsAdmin->saveTmpGoodsImage($arrData);
                    }
                }

                $this->js("parent.location.href='./goods_image_batch.php'");
            } else if ($mode == 'delete') {
                $goodsAdmin->deleteTmpGoodsImage(Request::post()->get('imageName'));
                $this->layer(__('삭제 되었습니다.'));
            }
        } catch (\Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }
    }
}
