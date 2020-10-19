<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <a href="./scm_adjust_manual.php" class="btn btn-red-line">수기등록</a>
    </div>
</div>

<form id="frmSearchAdjust" name="frmSearchAdjust" method="get" class="content-form">
    <div class="table-title">
        정산 검색
    </div>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>공급사 구분</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="all" <?= gd_isset($checked['scmFl']['all']); ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="0" <?= gd_isset($checked['scmFl']['0']); ?>/>본사
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="1" class="js-layer-register" <?= gd_isset($checked['scmFl']['1']); ?> data-type="scm" data-mode="checkbox"/> 공급사
                    </label>
                    <input type="button" value="공급사 선택" class="btn btn-sm btn-gray js-layer-register" data-type="scm" data-mode="search"/>

                    <div id="scmLayer" class="selected-btn-group <?= $search['scmFl'] == '1' && !empty($search['scmNo']) ? 'active' : '' ?>">
                        <h5>선택된 공급사 : </h5>
                        <?php if ($search['scmFl'] == '1') { ?>
                            <?php foreach ($search['scmNo'] as $k => $v) { ?>
                                <div id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                                    <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                    <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                    <span class="btn"><?= $search['scmNoNm'][$k] ?></span>
                                    <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $v ?>">삭제</button>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>정산타입</th>
                <td>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustType[]" value="" class="js-not-checkall" data-target-name="scmAdjustType[]" <?php echo gd_isset($checked['scmAdjustType']['']); ?>/>전체
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustType[]" value="o" <?php echo gd_isset($checked['scmAdjustType']['o']); ?>/>주문상품
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustType[]" value="d" <?php echo gd_isset($checked['scmAdjustType']['d']); ?>/>배송비
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustType[]" value="oa" <?php echo gd_isset($checked['scmAdjustType']['oa']); ?>/>정산후환불(주문상품)
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustType[]" value="da" <?php echo gd_isset($checked['scmAdjustType']['da']); ?>/>정산후환불(배송비)
                    </label>
                </td>
            </tr>
            <tr>
                <th>요청타입</th>
                <td>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustKind[]" value="" class="js-not-checkall" data-target-name="scmAdjustKind[]" <?php echo gd_isset($checked['scmAdjustKind']['']); ?>/>전체
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustKind[]" value="a" <?php echo gd_isset($checked['scmAdjustKind']['a']); ?>/>일반
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustKind[]" value="m" <?php echo gd_isset($checked['scmAdjustKind']['m']); ?>/>수기
                    </label>
                </td>
            </tr>
            <tr>
                <th>정산상태</th>
                <td>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustState[]" value="" class="js-not-checkall" data-target-name="scmAdjustState[]" <?php echo gd_isset($checked['scmAdjustState']['']); ?>/>전체
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustState[]" value="1" <?php echo gd_isset($checked['scmAdjustState']['1']); ?>/>정산요청
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustState[]" value="40" <?php echo gd_isset($checked['scmAdjustState']['40']); ?>/>이월
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustState[]" value="50" <?php echo gd_isset($checked['scmAdjustState']['50']); ?>/>보류
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustState[]" value="-1" <?php echo gd_isset($checked['scmAdjustState']['-1']); ?>/>반려
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustState[]" value="10" <?php echo gd_isset($checked['scmAdjustState']['10']); ?>/>정산확정
                    </label>
<!--                    <label class="checkbox-inline">-->
<!--                        <input type="checkbox" name="scmAdjustState[]" value="20" --><?php //echo gd_isset($checked['scmAdjustState']['20']); ?><!--/>세금계산서 발급-->
<!--                    </label>-->
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmAdjustState[]" value="30" <?php echo gd_isset($checked['scmAdjustState']['30']); ?>/>지급완료
                    </label>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td>
                    <div class="form-inline">
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[]" value="<?= $search['treatDate'][0]; ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[]" value="<?= $search['treatDate'][1]; ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                        <?= gd_search_date($search['treatDate'], 'treatDate', false) ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>

    <div class="table-header">
        <div class="pull-left">
            검색 <strong class="text-danger"><?= number_format(gd_isset($page->recode['total'], 0)); ?></strong>개 / 전체
            <strong class="text-danger"><?= number_format(gd_isset($page->recode['amount'], 0)); ?></strong>개
        </div>
        <div class="pull-right">
            <ul>
                <li>
                    <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
                </li>
                <li>
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
                        ]
                    ), '개 출력', Request::get()->get('pageNum'), null
                    ); ?>
                </li>
            </ul>
        </div>
    </div>
</form>

<div class="content_list">
    <form id="frmScmAdjust" action="./scm_adjust_ps.php" method="post" class="content_list">
        <input type="hidden" name="mode" value="modifyScmAdjustList">
        <textarea name="scmAdjustMemo" class="hidden"></textarea>
        <table class="table table-rows table-fixed">
            <thead>
            <tr>
                <th class="width5p center"><input type="checkbox" class="js-checkall" data-target-name="chk"></th>
                <th class="width5p">번호</th>
                <th class="width8p">정산요청번호</th>
                <th class="width8p">요청일</th>
                <th class="width8p">공급사</th>
                <th class="width8p">정산타입</th>
                <th class="width5p">요청타입</th>
                <th class="width8p">판매(배송)금액</th>
                <th class="width8p">수수료</th>
                <th class="width8p">정산금액</th>
                <th>요청자</th>
                <th class="width8p">정산상태</th>
                <th class="width8p">정산정보</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data)) {
                foreach ($data as $key => $val) {
                    ?>
                    <tr>
                        <td class="center">
                            <input type="checkbox" name="chk[]" data-state="<?php echo $val['scmAdjustState']; ?>" value="<?php echo $val['scmAdjustNo']; ?>"/>
                        </td>
                        <td class="center number"><?php echo number_format($page->idx--); ?></td>
                        <td class="center number"><?php echo $val['scmAdjustCode']; ?></td>
                        <td class="center date"><?php echo $val['scmAdjustDt']; ?> </td>
                        <td class="center number"><?php echo $conventData[$key]['scm']['name']; ?></td>
                        <td class="center lmenu"><?php echo $conventData[$key]['scmAdjustType']; ?></td>
                        <td class="center lmenu"><?php echo $conventData[$key]['scmAdjustKind']; ?></td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($val['scmAdjustTotalPrice']) . ' ' . gd_currency_string(); ?></td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($val['scmAdjustCommissionPrice']) . ' ' . gd_currency_string(); ?></td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($val['scmAdjustPrice']) . ' ' . gd_currency_string(); ?></td>
                        <td class="center number"><?php echo $val['managerScmNo'] . '/' . $val['managerId'] . '/' . $val['managerNm']; ?><?=$val['deleteText']?></td>
                        <td class="center lmenu"><?php echo $conventData[$key]['scmAdjustState']; ?></td>
                        <td class="center">
                            <input type="button" value="상세정보" class="btn btn-white btn-sm btnModify" onclick="layer_info_view('<?= $val['scmAdjustNo'] ?>')">
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td class="center" colspan="12">검색된 정산 건이 없습니다.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>

        <div class="table-action">
            <div class="pull-left form-inline">
                <span class="action-title">선택한 정산을</span>
                <?php echo gd_select_box('scmAdjustState', 'scmAdjustState', $selectBoxScmAdjustState, null, null, '=정산상태='); ?>
                <button type="submit" class="btn btn-white"/>
                일괄처리</button>
            </div>
            <div class="pull-right">
                <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchAdjust" data-target-list-form="frmScmAdjust" data-target-list-sno="chk" data-search-count="<?=$page->recode['total']?>" data-total-count="<?=$page->recode['amount']?>">엑셀다운로드</button>
            </div>
        </div>

    </form>
    <div class="center"><?php echo $page->getPage(); ?></div>

</div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 폼체크
        $('#frmScmAdjust').validate({
            dialog: false,
            submitHandler: function (form) {
                if ($('input[name*=chk]:checked').length < 1) {
                    alert('선택된 정산이 없습니다.');
                    return false;
                }
                if (!$('select[name="scmAdjustState"]').val()) {
                    alert('변경될 정산상태를 선택하세요.');
                    return false;
                }
                var chkState = 0;
                var chkNum = 0;
                $('input[name*=chk]:checked').each(function (index) {
                    if (index == 0) {
                        chkState = $(this).data('state');
                    } else {
                        if (chkState != $(this).data('state')) {
                            chkNum++;
                        }
                        chkState = $(this).data('state');
                    }
                });
                if (chkNum > 0) {
                    alert('같은 정산상태끼리만 일괄처리 가능합니다.');
                    return false;
                }

                if ($('select[name="scmAdjustState"]').val() == '-1') {
                    BootstrapDialog.confirm({
                        type: BootstrapDialog.TYPE_WARNING,
                        dialog: false,
                        title: '반려 사유',
                        message: '<textarea name="scmAdjustRejectMemo" class="textarea width90p height100"></textarea>',
                        callback: function (result) {
                            // 확인 버튼 클릭시
                            if (result) {
                                if ($('textarea[name="scmAdjustRejectMemo"]').val()) {
                                    $('textarea[name="scmAdjustMemo"]').val($('textarea[name="scmAdjustRejectMemo"]').val());
                                    form.target = 'ifrmProcess';
                                    form.submit();
                                } else {
                                    alert('반려 사유를 입력하셔야 합니다.');
                                    return false;
                                }
                            } else {
                                return false;
                            }
                        }
                    });
                } else {
                    form.target = 'ifrmProcess';
                    form.submit();
                }
            }
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchAdjust').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchAdjust').submit();
        });
    });

    /**
     * 상세 정보 보기
     */
    function layer_info_view(scmAdjustNo) {
        var loadChk = $('#scmAdjustInfoForm').length;
        var title = "정산 상세 정보";

        $.post('./layer_adjust_info.php', {scmAdjustNo: scmAdjustNo}, function (data) {
            if (loadChk == 0) {
                data = '<div id="scmAdjustInfoForm">' + data + '</div>';
            }
            var layerForm = data;
            BootstrapDialog.show({
                title: title,
                size: BootstrapDialog.SIZE_WIDE,
                message: $(layerForm),
                closable: true
            });
        });
    }


    //-->
</script>
