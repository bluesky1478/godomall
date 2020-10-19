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

namespace Bundle\Controller\Admin\Policy;

use Component\Member\Manager;
use Component\Member\ManagerCs;
use Component\Database\DBTableField;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\DatabaseException;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\GodoUtils;
use Framework\Utility\StringUtils;

/**
 * Class LayerManagerCsPsController
 * @package Bundle\Controller\Admin\Policy
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class LayerManagerCsPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $globals = \App::getInstance('globals');
        $request = \App::getInstance('request');
        $ecKind = $globals->get('gLicense.ecKind', 'standard') === 'standard' ? 's' : 'p';

        $useAppCodes = GodoUtils::getUsePlusShopCodes();

        $funcGetMenuDepth = function ($depth, $adminMenuType, $selected = null) use ($ecKind, $useAppCodes) {
            $adminMenuFields = DBTableField::getFieldTypes(DBTableField::getFuncName(DB_ADMIN_MENU));
            $db = \App::getInstance('DB');
            $db->query_reset();
            $hiddenMenuNo = 'godo00611,godo00469,godo00472,godo00519,godo00644,godo00042,godo00294,godo00765';
            if ($ecKind === 's') {
                $hiddenMenuNo .= ',godo00458';
            }

            if ($depth === 3) {
                $goodsBenefit = \App::load(\Component\Goods\GoodsBenefit::class);
                if ($goodsBenefit->getConfig() === 'n') {
                    $hiddenMenuNo .= ',godo00723,godo00724';
                }
            }
            $hiddenMenuNo = explode(',', $hiddenMenuNo);

            $db->strField = 'adminMenuNo, adminMenuName, adminMenuPlusCode, adminMenuParentNo, adminMenuSettingType, adminMenuDepth';
            $db->strOrder = 'adminMenuSort ASC';
            $db->strWhere = 'adminMenuEcKind IN (\'a\', \'' . $ecKind . '\')';

            if ($depth > 0) {
                $db->strWhere .= ' AND adminMenuDepth = ?';
                $db->bind_param_push($arrBind, $adminMenuFields['adminMenuDepth'], $depth);
            }

            $db->strWhere .= ' AND adminMenuType = ?';
            $db->bind_param_push($arrBind, $adminMenuFields['adminMenuType'], $adminMenuType);

            if ($selected !== null) {
                $db->strWhere .= ' AND adminMenuParentNo = ?';
                $db->bind_param_push($arrBind, $adminMenuFields['adminMenuParentNo'], $selected);
            }

            $srcVersion = GodoUtils::getSrcVersion();
            $db->strWhere .= ' AND INSTR(adminMenuHideVersion, ?) < 1';
            $db->bind_param_push($arrBind, $adminMenuFields['adminMenuHideVersion'], $srcVersion);

            $db->strWhere .= ' AND adminMenuDisplayType = ?';
            $db->bind_param_push($arrBind, $adminMenuFields['adminMenuDisplayType'], 'y');

            $query = $db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADMIN_MENU . implode(' ', $query);
            $resultSet = $db->query_fetch($strSQL, $arrBind);

            foreach ($resultSet as $index => $item) {
                if (\in_array($item['adminMenuNo'], $hiddenMenuNo, true)
                    || strpos($item['adminMenuNo'], 'godo') === false) {
                    unset($resultSet[$index]);
                }
            }

            if (\is_array($useAppCodes) && \count($useAppCodes) > 0) {
                foreach ($resultSet as $index => $item) {
                    if ($item['adminMenuPlusCode'] === null || $item['adminMenuSettingType'] !== 'p') {
                        continue;
                    }
                    if (!\in_array($item['adminMenuPlusCode'], $useAppCodes, true)) {
                        unset($resultSet[$index]);
                    }
                }
            }

            return $resultSet;
        };

        switch ($request->request()->get('mode', '')) {
            case 'getAccess':
                $adminMenuType = (int) $request->get()->get('scmNo', 0) === DEFAULT_CODE_SCMNO ? 'd' : 's';
                $depth = $request->get()->get('depth', 0);
                $selected = $request->get()->get('selected');
                $menus = $funcGetMenuDepth($depth, $adminMenuType, $selected);

                if ($depth === 0) {
                    $sortMenus = [
                        'depth1' => [],
                        'depth2' => [],
                        'depth3' => [],
                    ];
                    foreach ($menus as $index => $menu) {
                        if ($menu['adminMenuDepth'] === 1) {
                            $sortMenus['depth1'][] = $menu;
                        } elseif ($menu['adminMenuDepth'] === 2) {
                            $sortMenus['depth2'][$menu['adminMenuParentNo']][] = $menu;
                        } elseif ($menu['adminMenuDepth'] === 3) {
                            $sortMenus['depth3'][$menu['adminMenuParentNo']][] = $menu;
                        }
                    }

                    return $this->json($sortMenus);
                }

                $this->json($menus);
                break;
            case 'register':
                $session = \App::getInstance('session');
                if ((int) $session->get(Manager::SESSION_MANAGER_LOGIN . '.scmNo', 0) !== DEFAULT_CODE_SCMNO) {
                    $this->json(
                        [
                            'error'   => 100,
                            'message' => '본사만 생성 가능합니다.',
                        ]
                    );
                }
                $scmNo = $request->post()->get('scm_no', 0);
                $permissionFl = $request->post()->get('permission_fl');
                $permissionMenu = $request->post()->get('permission_menu', []);
                $functionAuth = $request->post()->get('function_auth', []);
                $hasCs = $request->post()->get('has_cs', false);
                $component = \App::load(ManagerCs::class);
                $permissions = [
                    'permissionFl'   => $permissionFl,
                    'functionAuth'   => $functionAuth,
                    'permissionMenu' => $permissionMenu,
                ];

                // cs 수동생성 계정값
                $mCsAccount['createType'] = $request->post()->get('type');
                $mCsAccount['csId'] = $request->post()->get('csId');
                $mCsAccount['csPw'] = $request->post()->get('csPw');

                if ($hasCs) {
                    $expiredDate = DateTimeUtils::dateFormat('Y-m-d', 'now');
                    $expireResult = $component->expireManagerCs($expiredDate);
                    $logger = \App::getInstance('logger');
                    $message = 'has expire customer service account.';
                    $message .= ' expireDate=' . $expiredDate . ', expireResultRows=' . $expireResult;
                    $logger->notice($message);
                }

                try {
                    if ($component->createManagerCs($permissions, $scmNo, $mCsAccount) > 0) {
                        $this->json(
                            [
                                'error'   => 0,
                                'message' => '생성완료',
                                'csList'  => $component->getDecryptListAll(),
                            ]
                        );
                    }
                } catch (\Throwable $e) {
                    $this->json(
                        [
                            'error'   => 500,
                            'message' => $e->getMessage(),
                        ]
                    );
                }

                $this->json(
                    [
                        'error'   => 500,
                        'message' => '생성 중 오류',
                    ]
                );
                break;
            case 'modify':
                $session = \App::getInstance('session');
                if ((int) $session->get(Manager::SESSION_MANAGER_LOGIN . '.scmNo', 0) !== DEFAULT_CODE_SCMNO) {
                    $this->json(
                        [
                            'error'   => 100,
                            'message' => '본사만 수정 가능합니다.',
                        ]
                    );
                }
                $sno = $request->post()->get('sno', 0);
                $permissionFl = $request->post()->get('permission_fl');
                $permissionMenu = $request->post()->get('permission_menu', []);
                $functionAuth = $request->post()->get('function_auth', []);
                $component = \App::load(ManagerCs::class);
                $permissions = [
                    'permissionFl'   => $permissionFl,
                    'functionAuth'   => $functionAuth,
                    'permissionMenu' => $permissionMenu,
                ];
                if ($component->updateManagerCs($permissions, $sno) > 0) {
                    $this->json(
                        [
                            'error'   => 0,
                            'message' => '수정완료',
                            'csList'  => $component->getDecryptListAll(),
                        ]
                    );
                }
                $this->json(
                    [
                        'error'   => 500,
                        'message' => '수정 중 오류',
                    ]
                );
                break;

            case 'overlap':
                $id = $request->post()->get('csId');
                $component = \App::load(ManagerCs::class);
                $result = $component->manualAccountOverlapChk($id);
                if($result){
                    $this->json(['result' => 'fail', 'msg' => '']);
                }else{
                    $this->json(['result' => 'empty', 'msg' => '']);
                }
                break;

            default:
                break;
        }
    }
}
