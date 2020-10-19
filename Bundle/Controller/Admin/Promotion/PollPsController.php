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

namespace Bundle\Controller\Admin\Promotion;

use Component\Promotion\Poll;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;
use Exception;

class PollPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $postValue = Request::post()->toArray();
        try{
            $poll = new Poll();
            $mode = $postValue['mode'];

            switch ($mode) {
                case 'regist':
                case 'modify':
                    $poll->regist($postValue);
                    $this->layer(__('등록되었습니다.'), 'top.location.href="../promotion/poll_list.php";');
                    break;
                case 'delete':
                    $poll->delete($postValue);
                    $this->layer(__('삭제가 완료되었습니다.'), 'top.location.href="../promotion/poll_list.php";');
                    break;
                case 'changeStatus':
                    $poll->pollChangeStatus($postValue['sno'], $postValue['status']);
                    $this->layer(__('진행상태가 변경되었습니다.'), 'top.location.reload();');
                    break;
                default:
                    break;
            }
        } catch (Exception $e) {
            if (\Request::isAjax() === true) {
                $this->json($this->exceptionToArray($e));
            } else {
                if ($e->getCode() == 500) {
                    throw new LayerNotReloadException($e->getMessage());
                } else {
                    throw new LayerException($e->getMessage());
                }
            }
        }
        exit;
    }
}
