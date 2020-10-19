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

use Framework\Debug\Exception\Except;
use Component\Memo\MemoActAdmin;
use Message;
use Request;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\Framework\Debug\Exception;

class MemoPsController extends \Controller\Admin\Controller
{

    /**
     * Description
     *
     * @throws Except
     */
    public function index()
    {

        // 기본 정보
        switch (Request::post()->get('mode')) {
            case 'write':
            case 'modify':
            case 'reply' :
                try {
                    $memoActAdmin = new MemoActAdmin(Request::post()->toArray());
                    $memoActAdmin->saveData();
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            case 'delete':
                try {
                    $memoActAdmin = new MemoActAdmin(Request::post()->toArray());
                    $memoActAdmin->deleteData();
                    $this->layer(__('삭제 되었습니다.'));
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
        }
    }
}
