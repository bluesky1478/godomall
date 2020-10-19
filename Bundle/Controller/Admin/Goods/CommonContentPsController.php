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

use Framework\Debug\Exception\LayerException;
use Globals;
use Request;

/**
 * @author Bag YJ <kookoo135@godo.co.kr>
 */
class CommonContentPsController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        $commonContent = \App::load('\\Component\\Goods\\CommonContent');
        $postValue = Request::post()->all();

        try {


            switch ($postValue['mode']) {
                case 'regist':
                    $commonSno = $commonContent->save($postValue);

                    if (empty($commonSno) === true) {
                        throw new \Exception('등록이 실패하였습니다.');
                    } else {
                        $this->layer('정상적으로 등록되었습니다.', 'top.location.href = "./common_content_list.php";');
                    }
                    break;
                case 'delete':
                    $commonContent->delete($postValue);

                    $this->layer('공통정보가 삭제되었습니다.', 'top.location.reload();');
                    break;
            }

        } catch (\Exception $e) {
            throw new LayerException($e->getMessage());
        }
        exit;
    }
}
