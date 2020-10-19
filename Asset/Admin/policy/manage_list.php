<?php
gd_isset($scmList, []);
gd_isset($csList, []);
gd_isset($useAppCodes, []);
$bankdaPolicy = gd_policy('order.bankda');
?>
<script type="text/javascript">
    <!--
    var gd_layer = {};
    $(document).ready(function () {
        $("#selectedAll").bind('click', function () {
            $("input[name='chk[]']").prop("checked", $("#selectedAll").prop("checked"));
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchManager').submit();
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchManager').submit();
        });

        // 선택한 운영자 삭제
        $('.js-btn-delete').bind('click', function () {
            if ($("input[name='chk[]']:checked").length < 1) {
                alert('삭제할 운영자를 선택해주세요.');
                return;
            }

            dialog_confirm('선택하신 운영자를 정말 삭제 하시겠습니까?\n\n삭제된 운영자는 복구 되지 않습니다.', function (data) {
                if (data) {
                    $('input[name=mode]').val('delete');
                    $('#listForm').submit();
                }
            });
        });

        gd_layer = new gd_layer_manager_cs($, _);
        gd_layer.set_policy({
            bankda: {
                use: '<?= $bankdaPolicy['useFl'] ?>'
            }
        });
        gd_layer.set_scm_list(<?= json_encode($scmList) ?>);
        gd_layer.set_cs_list(<?= json_encode($csList) ?>);
        gd_layer.set_use_app_codes(<?= json_encode($useAppCodes) ?>);
    });

    /**
     * 공급사 선택 레이어
     */
    function layer_register(typeStr, mode) {
        var layerFormID = 'addSearchForm';

        var parentFormID = typeStr + 'Layer';
        var dataFormID = 'id' + typeStr;

        // 레이어 창
        var typeVar = 'scm';
        var layerTitle = '공급사';
        var dataInputNm = typeStr + "ID";

        $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);

        var addParam = {
            "mode": mode
        };

        layer_add_info(typeVar, layerFormID, parentFormID, dataFormID, dataInputNm, layerTitle, '', addParam);
    }

    //-->
</script>

<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <?php if (gd_is_provider() === false) { ?>
            <input type="button" value="CS 계정 관리" class="btn btn-white btn-cs-layer"/>
        <?php } ?>
        <input type="button" onclick="location.href='./manage_register.php'" value="운영자 등록" class="btn btn-red-line"/>
    </div>
</div>

<form id="frmSearchManager" name="frmSearchManager" method="get" class="js-form-enter-submit">
    <input type="hidden" name="detailSearch" value="<?= $search['detailSearch']; ?>"/>

    <div class="table-title gd-help-manual">
        운영자 검색
    </div>

    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col class="width-3xl"/>
            </colgroup>
            <?php if (gd_is_provider() === false && gd_use_provider() === true) { ?>
                <?php if (gd_use_provider() === true) { ?>
                    <tr>
                        <th>공급사 구분</th>
                        <td colspan="3" class="form-inline">
                            <label class="radio-inline">
                                <input type="radio" name="scmFl" value="all" <?= gd_isset($checked['scmFl']['all']); ?> onclick="$('#scmLayer').html('');"/> 전체
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="scmFl" value="n" <?= gd_isset($checked['scmFl']['n']); ?> onclick="$('#scmLayer').html('')" ;/> 본사
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="scmFl" value="y" <?= gd_isset($checked['scmFl']['y']); ?> onclick="layer_register('scm','checkbox')"/> 공급사
                            </label>
                            <label class="mgl10">
                                <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm','checkbox')">공급사 선택</button>
                            </label>

                            <div id="scmLayer" class="selected-btn-group <?= $search['scmFl'] == 'y' && !empty($search['scmNo']) ? 'active' : '' ?>">
                                <h5>선택된 공급사 : </h5>
                                <?php
                                if ($search['scmFl'] == 'y') {
                                    foreach ($search['scmNo'] as $k => $v) { ?>
                                        <span id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                                            <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                            <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                            <span class="btn"> <?= $search['scmNoNm'][$k] ?></span>
                                            <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $v ?>">삭제</button>
                                        </span>
                                    <?php }
                                } ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
            <tr>
                <th>검색어</th>
                <td colspan="3" class="form-inline">
                    <?= gd_select_box(
                        'key', 'key', [
                        'managerId'     => '아이디',
                        'managerNm'     => '이름',
                        'email'         => '이메일',
                        'managerNickNm' => '닉네임',
                        'phone'         => '전화번호',
                        'cellPhone'     => '휴대폰번호',
                    ], '', $search['key'], '=통합검색=', null, 'form-control'
                    ); ?>
                    <input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control"/>
                </td>
            </tr>
            <tbody class="js-search-detail">
            <tr>
                <th>SMS 자동발송 <br>수신설정</th>
                <td colspan="3">
                    <?php
                    foreach ($smsAutoReceiveKind as $aKey => $aVal) {
                        echo '<label class="checkbox-inline"><input type="radio" name="smsAutoReceive" value="' . $aKey . '" ' . gd_isset($checked['smsAutoReceive'][$aKey]) . ' /> ' . $aVal . '</label>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>직원여부</th>
                <td class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="employeeFl" <?= $checked['employeeFl']['all'] ?> value=""/> 전체
                    </label>
                    <?php foreach ($employeeList as $key => $val) { ?>
                        <label class="radio-inline">
                            <input type="radio" name="employeeFl" value="<?= $key ?>" <?= gd_isset($checked['employeeFl'][$key]); ?> /><?= $val ?>
                        </label>
                    <?php } ?>
                </td>
                <th class="width-sm">부서</th>
                <td class="width40p form-inline">
                    <?= gd_select_box('departmentCd', 'departmentCd', $department, null, gd_isset($search['departmentCd']), '=부서 선택=', 'form-control'); ?>
                </td>
            </tr>
            <tr>
                <th class="width-sm">직급</th>
                <td class="form-inline">
                    <?= gd_select_box('positionCd', 'positionCd', $position, null, gd_isset($search['positionCd']), '=직급 선택=', 'form-control'); ?>
                </td>
                <th>직책</th>
                <td class="form-inline">
                    <?= gd_select_box('dutyCd', 'dutyCd', $duty, null, gd_isset($search['dutyCd']), '=직책 선택=', 'form-control'); ?>
                </td>
            </tr>
            </tbody>
        </table>
        <button class="btn btn-sm btn-link js-search-toggle bold" type="button">상세검색 <span>닫힘</span>
        </button>
    </div>
    <div class="table-btn">
        <input class="btn btn-lg btn-black" type="submit" value="검색">
    </div>

    <div class="table-header">
        <div class="pull-left"> 검색 <strong><?= number_format($page->recode['total']); ?></strong>개 / 전체
            <strong><?= number_format($page->recode['amount']); ?></strong>개
        </div>
        <div class="pull-right">
            <div class="form-inline">
                <?= gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
                <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?>
            </div>
        </div>
    </div>
</form>

<form id="listForm" target="ifrmProcess" action="manage_ps.php" method="post">
    <input type="hidden" name="mode">
    <div>
        <table class="table table-rows table-fixed">
            <thead>
            <tr>
                <th class="width5p">
                    <input type="checkbox" id="selectedAll"/>
                </th>
                <th class="width5p">번호</th>
                <th class="width10p">공급사 구분</th>
                <th class="width30p">아이디/닉네임</th>
                <th class="width10p">이름</th>
                <th class="width10p">직원여부</th>
                <th class="width10p">직원/부서/직급/직책</th>
                <th class="width10p">연락처</th>
                <th class="width7p">등록일</th>
                <th class="width5p">정보수정</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (is_array(gd_isset($data))) {
                $arrPermission = [
                    's' => '전체',
                    'l' => '제한',
                ];

                foreach ($data as $key => $val) {
                    if ($val['scmNo'] == DEFAULT_CODE_SCMNO) {
                        $scmName = '본사';
                    } else {
                        $scmName = $val['companyNm'];
                    }

                    $superText = '';
                    $checkBoxDisabled = '';
                    if ($val['isSuper'] == 'y') {
                        $checkBoxDisabled = 'disabled';
                        $superText = ($val['sno'] == 1) ? '<br/><span class="text-blue">(최고운영자)</span>' : '<br/><span class="text-blue">(대표운영자)</span>';
                    }

                    // SMS 자동발송 수신여부
                    $val['smsAutoFl'] = [];
                    if (empty($val['smsAutoReceive']) === false) {
                        foreach (explode(STR_DIVISION, $val['smsAutoReceive']) as $aVal) {
                            $val['smsAutoFl'][] = $aVal;
                        }
                        unset($aVal);
                    } else {
                        $val['smsAutoFl'][] = 'n';
                    }
                    $isLimit = json_decode($val['loginLimit'], true)['limitFlag'] == 'y';
                    ?>
                    <tr align="center" <?php if ($isLimit) { ?>class="text-red"<?php } ?>>
                        <td>
                            <input type="checkbox" name="chk[]"
                                   value="<?= $val['sno']; ?>" <?= $checkBoxDisabled ?> />
                        </td>
                        <td><?= number_format($page->idx--); ?></td>
                        <td><?= $scmName ?></td>
                        <td>
                            <?php
                            if ($isLimit) {
                                ?>
                                <span class="notice-danger js-tooltip" title="운영자 로그인을 5회 이상 실패하여 접속이 제한된 아이디입니다."></span>
                                <?php
                            }
                            ?>
                            <a href="./manage_register.php?sno=<?= $val['sno']; ?>"><?= $val['managerId']; ?>
                                <?php if ($val['managerNickNm']) {
                                    echo '&nbsp;/&nbsp;' . $val['managerNickNm'];
                                } ?>
                            </a>
                            <?= $superText ?>
                        </td>
                        <td>
                            <div><?= $val['managerNm']; ?></div>
                        </td>
                        <td>
                            <?= $employeeList[$val['employeeFl']] ?>
                        </td>
                        <td>
                            <div><?= gd_isset($department[$val['departmentCd']]); ?> / <?= $arrEmployee[$val['employeeFl']]; ?></div>
                            <div><?= gd_isset($position[$val['positionCd']]); ?> / <?= gd_isset($duty[$val['dutyCd']]); ?></div>
                        </td>
                        <td>
                            <div><?= $val['phone']; ?><?php if (empty($val['extension']) === false) {
                                    echo ' (내선:' . $val['extension'] . ')';
                                } ?></div>
                            <div><?= $val['cellPhone']; ?></div>
                            <div><?= $val['email']; ?></div>
                        </td>
                        <td><?= gd_date_format('Y-m-d', $val['regDt']); ?></td>
                        <td>
                            <a href="./manage_register.php?sno=<?= $val['sno']; ?>" class="btn btn btn-white btn-xs">수정</a></span>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="9" class="no-data">검색된 정보가 없습니다.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>

        <div class="table-action">
            <div class="pull-left">
                <button type="button" class="btn btn-white js-btn-delete">선택삭제</button>
            </div>
        </div>
</form>

<div align="center"><?= $page->getPage(); ?></div>
<?php include_once '_template_layer_manager_cs.php'; ?>
