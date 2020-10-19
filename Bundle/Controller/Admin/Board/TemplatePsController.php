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
namespace Bundle\Controller\Admin\Board;

use Component\Board\BoardTemplate;
use Framework\Debug\Exception\Except;
use Message;
use Request;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\Framework\Debug\Exception;

class TemplatePsController extends \Controller\Admin\Controller
{

    /**
     * Description
     * @throws Except
     */
    public function index()
    {

        /**
         * 게시물 처리
         *
         * @author sj
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */
        $bdTemplate = new BoardTemplate();
        // --- 모듈 호출
        switch (Request::post()->get('mode')) {
            case 'getData' :
                $data = $bdTemplate->getData(Request::post()->get('sno'));
                if(Request::isAjax()) {
                    $this->json($data);
                }
                break;
            case 'write':
            case 'modify':
                try {

                    $data = Request::post()->toArray();
                    $bdTemplate->saveData($data);
                    if(Request::isAjax()){
                        $selectData = $bdTemplate->getSelectData($data['templateType']);
                        $this->json(['result'=>'ok','data'=>$selectData,'selected'=>max(array_keys($selectData))]);
                    }
                    else {
                        $this->layer(__('저장이 완료되었습니다.'),null,null,null,'top.location.reload()');  //@todo: 레이어수정
                    }

                    exit;
                } catch (\Exception $e) {
                    if(Request::isAjax()){
                        $this->json(['result'=>'fail','msg'=>$e->getMessage()]);
                    }
                    throw new LayerException($e->getMessage());
                }
                break;
            case 'delete':
                try {
                    $bdTemplate = new BoardTemplate();
                    if (empty(Request::post()->get('sno')) === false) {
                        foreach (Request::post()->get('sno') as $sno) {
                            $bdTemplate->deleteData($sno);
                        }
                    }
                    $this->layer(__('삭제 되었습니다.'));
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
        }
    }
}
