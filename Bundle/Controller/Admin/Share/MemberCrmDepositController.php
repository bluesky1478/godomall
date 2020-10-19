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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Share;

use App;
use Component\Deposit\Deposit;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\DateTimeUtils;
use Request;

/**
 * Class 관리자-CRM 예치금 내역
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class MemberCrmDepositController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('member', 'member', 'crm');

        try {
            $request = \App::getInstance('request');
            $request->get()->set('page', $request->get()->get('page', 1));
            $request->get()->set('pageNum', $request->get()->get('pageNum', 10));
            $request->get()->set('mode', $request->get()->get('mode', 'all'));
            $request->get()->set('regDtPeriod', $request->get()->get('regDtPeriod', '6'));
            $request->get()->set('regDt', $request->get()->get('regDt', DateTimeUtils::getBetweenDateString('-' . $request->get()->get('regDtPeriod') . ' days')));
            /** @var \Bundle\Component\Deposit\Deposit $deposit */
            $deposit = \App::load('\\Component\\Deposit\\Deposit');
            $requestGetParams = $request->get()->all();
            $depositList = $deposit->getDepositList($requestGetParams);

            $orderReorderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');
            foreach($depositList as &$value){
                if($value['handleSno'] > 0) {
                    $handleData = $orderReorderCalculation->getOrderHandleData($value['handleCd'], null, null, $value['handleSno']);
                    $value['handleReason'] = $handleData[0]['handleReason'];
                }
            }
            $page = \App::load('Component\\Page\\Page');
            $page->setPage();
            $page->setUrl($request->getQueryString());
            $this->setData('page', $page);
            $this->setData('depositList', $depositList);
            $this->setData('checked', $deposit->setChecked($requestGetParams));
            $this->setData('selected', $deposit->setSelected($requestGetParams));
            $this->setData('searchKey', Deposit::COMBINE_SEARCH);
            $this->addScript(['member.js']);
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
