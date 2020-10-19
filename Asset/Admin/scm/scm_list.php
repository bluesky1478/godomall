<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <a href="../scm/scm_regist.php" class="btn btn-red-line">공급사 등록</a>
    </div>
</div>

<form id="frmSearchScm" method="get" class="js-form-enter-submit">
    <div class="table-title">
        공급사 검색
    </div>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm">
                <col>
                <col class="width-sm">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td>
                    <div class="form-inline">
                        <?= gd_select_box('key', 'key', $search['combineSearch'], null, $search['key']); ?>
                        <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
                <th>상태</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="scmType" value="" <?= $checked['scmType']['']; ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmType" value="y" <?= $checked['scmType']['y']; ?>/>운영
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmType" value="x" <?= $checked['scmType']['x']; ?>/>탈퇴
                    </label>
                </td>
            </tr>
            <tr>
                <th>상품등록권한</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="scmPermissionInsert" value="" <?= $checked['scmPermissionInsert']['']; ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmPermissionInsert" value="a" <?= $checked['scmPermissionInsert']['a']; ?>/>자동승인
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmPermissionInsert" value="c" <?= $checked['scmPermissionInsert']['c']; ?>/>관리자승인
                    </label>
                </td>
            </tr>
            <tr>
                <th>상품수정권한</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="scmPermissionModify" value="" <?= $checked['scmPermissionModify']['']; ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmPermissionModify" value="a" <?= $checked['scmPermissionModify']['a']; ?>/>자동승인
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmPermissionModify" value="c" <?= $checked['scmPermissionModify']['c']; ?>/>관리자승인
                    </label>
                </td>
            </tr>
            <tr>
                <th>상품삭제권한</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="scmPermissionDelete" value="" <?= $checked['scmPermissionDelete']['']; ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmPermissionDelete" value="a" <?= $checked['scmPermissionDelete']['a']; ?>/>자동승인
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmPermissionDelete" value="c" <?= $checked['scmPermissionDelete']['c']; ?>/>관리자승인
                    </label>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="text-center table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>

    <div class="table-header">
        <div class="pull-left">
            공급사 리스트 (검색결과
            <strong><?= number_format($page->recode['total'], 0); ?></strong>건, 전체<strong><?= number_format($page->recode['amount'], 0); ?></strong>건)
        </div>
        <div class="pull-right">
            <div class="form-inline">
                <?= gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
                <?php echo gd_select_box(
                    'pageNum', 'pageNum', gd_array_change_key_value(
                    [
                        10,
                        20,
                        30,
                        40,
                        50,
                        60,
                        70,
                        80,
                        90,
                        100,
                        200,
                        300,
                        500,
                    ]
                ), '개 보기', Request::get()->get('pageNum'), null
                ); ?>
            </div>
        </div>
    </div>
</form>

<form id="frmScmList" name="frmScmList" action="../scm/scm_ps.php" method="post">
    <input type="hidden" name="mode" value="deleteScmList"/>
    <table class="table table-rows">
        <thead>
        <tr>
            <th><input type="checkbox" class="js-checkall" data-target-name="chkScm[]"/></th>
            <th>상태</th>
            <th>아이디</th>
            <th>공급사명</th>
            <th>공급사타입</th>
            <th>상품수수료</th>
            <th>배송수수료</th>
            <th>상품등록권한</th>
            <th>상품수정권한</th>
            <th>상품삭제권한</th>
            <th>등록자</th>
            <th>등록일</th>
            <th>수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($data) === false && is_array($data)) {
            foreach ($data as $row) {
                if ($row['scmType'] == 'y') {
                    $row['scmType'] = '운영';
                    $disabled = 'disabled="disabled"';
                } else if ($row['scmType'] == 'n') {
                    $row['scmType'] = '일시정지';
                    $disabled = '';
                } else if ($row['scmType'] == 'x') {
                    $row['scmType'] = '탈퇴';
                    $disabled = '';
                }
                if ($row['scmKind'] == 'c') {
                    $row['scmKind'] = '본사';
                } else if ($row['scmKind'] == 'p') {
                    $row['scmKind'] = '공급사';
                }
                if ($row['scmPermissionInsert'] == 'a') {
                    $row['scmPermissionInsert'] = '자동승인';
                } else if ($row['scmPermissionInsert'] == 'c') {
                    $row['scmPermissionInsert'] = '관리자승인';
                }
                if ($row['scmPermissionModify'] == 'a') {
                    $row['scmPermissionModify'] = '자동승인';
                } else if ($row['scmPermissionModify'] == 'c') {
                    $row['scmPermissionModify'] = '관리자승인';
                }
                if ($row['scmPermissionDelete'] == 'a') {
                    $row['scmPermissionDelete'] = '자동승인';
                } else if ($row['scmPermissionDelete'] == 'c') {
                    $row['scmPermissionDelete'] = '관리자승인';
                }
                $addScmCommission = '';
                $addScmCommissionDelivery = '';
                $sellCnt = 1;
                $deliveryCnt = 1;
                $sellDivision = '';
                $deliveryDivision = '';
                //추가 수수료
                foreach ($row['addCommissionData']  as $key => $val) {
                    if ($sellCnt != 1) { $sellDivision = ' / '; }
                    if ($val['commissionType'] == 'sell' && $val['commissionValue'] != 0.00) {
                        $addScmCommission .= $sellDivision.$val['commissionValue'].'%';
                        $sellCnt++;
                    }
                    if ($deliveryCnt != 1) { $deliveryDivision = ' / '; }
                    if ($val['commissionType'] == 'delivery' && $val['commissionValue'] != 0.00) {
                        $addScmCommissionDelivery .= $deliveryDivision.$val['commissionValue'].'%';
                        $deliveryCnt++;
                    }
                }
                //판매수수료 동일 적용
                if ($row['scmSameCommission']) {
                    $scmCommissionDeliveryTd = $row['scmSameCommission'];
                } else {
                    $scmCommissionDeliveryTd = $row['scmCommissionDelivery'].'%(기본)<br/>'.$addScmCommissionDelivery;
                }
                ?>
                <tr class="text-center">
                    <td>
                        <input type="checkbox" name="chkScm[]" value="<?= $row['scmNo']; ?>" data-type="<?= $row['scmType']; ?>" <?= $disabled; ?> />
                    </td>
                    <td><?= $row['scmType']; ?></td>
                    <td><?= $row['managerId'].$row['deleteText']; ?></td>
                    <td><?= $row['companyNm']; ?></td>
                    <td><?= $row['scmKind']; ?></td>
                    <td><?= $row['scmCommission']; ?>%(기본)<br/><?=$addScmCommission?></td>
                    <td><?= $scmCommissionDeliveryTd; ?></td>
                    <td><?= $row['scmPermissionInsert']; ?></td>
                    <td><?= $row['scmPermissionModify']; ?></td>
                    <td><?= $row['scmPermissionDelete']; ?></td>
                    <td><?= $row['scmInsertAdminId']; ?></td>
                    <td><?= gd_date_format('Y-m-d', $row['regDt']); ?></td>
                    <td><a href="../scm/scm_regist.php?scmno=<?= $row['scmNo']; ?>" class="btn btn-dark-gray btn-sm">수정</a></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="13" class="no-data">
                    검색된 공급사가 없습니다.
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white js-scm-delete">선택 삭제</button>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchScm" data-target-list-form="frmScmList" data-target-list-sno="chkScm" data-search-count="<?=$page->recode['total']?>" data-total-count="<?=$page->recode['amount']?>">엑셀다운로드</button>
        </div>
    </div>
</form>

<div class="center"><?= $page->getPage(); ?></div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#frmScmList').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                'chkScm[]': 'required',
            },
            messages: {
                'chkScm[]': {
                    required: "삭제할 공급사를 선택해 주세요.",
                }
            }
        });

        $('.js-scm-delete').click(function (e) {
            if ($('#frmScmList').valid()) {
                BootstrapDialog.confirm({
                    type: BootstrapDialog.TYPE_DANGER,
                    title: '공급사삭제',
                    message: '선택된 ' + $('input[name*=chkScm]:checked').length + '개의 공급사를 정말로 삭제 하시겠습니까?<br />삭제 시 정보는 복구 되지 않습니다.',
                    closable: false,
                    callback: function (result) {
                        if (result) {
                            $('#frmScmList').submit();
                        }
                    }
                });
            }
        });
    });
    //-->
</script>
