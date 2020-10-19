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
namespace Bundle\Controller\Admin\Member;

use App;
use Exception;
use Framework\Debug\Exception\LayerException;
use Request;

/**
 *
 * 메일 수신 거부 회원수 조회
 * @author cjb3333 <cjb3333@godo.co.kr>
 */
class MailSendReceiveRefuseCountController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        /**
         * @var \Bundle\Component\Member\MemberAdmin $memberAdmin
         */

        $memberAdmin = App::load('\\Component\\Member\\MemberAdmin');

        $requestPostParams = Request::post()->all();

        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        try {

            $requestPostParams['maillingFl'] = 'n';
            $tmp = $memberAdmin->searchMemberWhere($requestPostParams);
            $arrBind = $tmp['arrBind'];
            $arrWhere = $tmp['arrWhere'];

            $this->db->strField = 'count(*)';
            if (is_null($arrWhere) === false) {
                $this->db->strWhere = implode(' AND ', $arrWhere);
            }
            $arrQuery = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . ' ' . implode(' ', $arrQuery);
            $data = $this->db->query_fetch($strSQL, $arrBind,false);

            unset($arrWhere, $arrBind,$arrQuery);
            $this->json($data);

        } catch (Exception $e) {
            if (\Request::isAjax() === true) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new LayerException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}
