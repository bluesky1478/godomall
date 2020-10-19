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

use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\Except;
use Component\Faq\FaqAdmin;
use Message;
use Request;
use Framework\Debug\Exception\Framework\Debug\Exception;

class FaqPsController extends \Controller\Admin\Controller
{

    /**
     * Description
     * @throws Except
     */
    public function index()
    {

        /**
         * FAQ 처리
         *
         * @author sj
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        // --- 모듈 호출
        switch (Request::post()->get('mode')) {
            case 'register':
                try {
                    $faqAdmin = new FaqAdmin();
                    $faqAdmin->insertFaqData(Request::post()->toArray());
                    $this->layer(__('저장이 완료되었습니다.') , null , null , null , 'location.href="faq_list.php"');
                } catch (\Exception $e) {
                    throw new AlertBackException($e->getMessage());
                }
                break;
            case 'modify':
                try {
                    $faqAdmin = new FaqAdmin();
                    $faqAdmin->modifyFaqData(Request::post()->toArray(), 'modify', Request::post()->get('isBest'));
                    $this->layer(__('저장이 완료되었습니다.'),null,null,null,'location.href="faq_list.php"');
                } catch (\Exception $e) {
                    throw new AlertBackException($e->getMessage());
                }
                break;
            case 'delete':
                try {
                    $faqAdmin = new FaqAdmin();
                    if (empty(Request::post()->get('chk')) === false) {
                        foreach (Request::post()->get('chk') as $sno) {
                            $faqAdmin->deleteFaqData($sno);
                        }
                    }
                   $this->layer(__('삭제 되었습니다.'));
                } catch (\Exception $e) {
                    throw new AlertOnlyException($e->getMessage());
                }
                break;
            case 'batch':
                try {
                    $faqAdmin = new FaqAdmin();
                    $dataCnt = count(Request::post()->get('chk'));
                    $isBest = 'n';
                    for ($i = 0; $i < $dataCnt; $i++) {
                        $request = array('sno' => Request::post()->get('chk')[$i], 'category' => Request::post()->get('category')[$i], 'isBest' => Request::post()->get('isBest')[$i]);
                        if (Request::post()->get('bestSortNo')[$i]) {
                            $isBest = 'y';
                            $request['bestSortNo'] = Request::post()->get('bestSortNo')[$i];
                        }
                        if (Request::post()->get('sortNo')[$i]) {
                            $request['sortNo'] = Request::post()->get('sortNo')[$i];
                        }

                        $faqAdmin->modifyFaqData($request, 'batch', $isBest);
                    }

                    $this->layer(__('저장이 완료되었습니다.'),null,null,null,'location.href="faq_list.php"');
                } catch (\Exception $e) {
                    throw new AlertBackException($e->getMessage());
                }
                break;
            case 'getAnswer' :
                $faqAdmin = new FaqAdmin();
                $data = $faqAdmin->getFaqView(Request::get()->get('sno'));
                echo $data['data']['answer'];
                break;
        }
    }
}
