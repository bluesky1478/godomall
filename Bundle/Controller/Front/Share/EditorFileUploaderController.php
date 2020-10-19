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
namespace Bundle\Controller\Front\Share;

use Request;

class EditorFileUploaderController extends \Controller\Front\Controller
{
    public function index()
    {
        $mode = Request::get()->get('mode');
        switch ($mode) {
            case 'deleteGarbage' :  //에디터업로드 가비지 파일 삭제
                $deleteImages = Request::get()->get('uploadImages');
                if($deleteImages) {
                    $deleteImagesArr = explode('^|^',$deleteImages);
                    foreach($deleteImagesArr as $imagePath){
                        //TODO:임시 수정
                    //    @unlink(Request::server()->get('DOCUMENT_ROOT').$imagePath);
                    }
                }
                break;
        }

        exit;
    }
}
