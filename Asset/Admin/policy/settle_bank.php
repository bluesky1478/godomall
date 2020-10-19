<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
    <input type="button" value="무통장 입금 은행 등록" class="btn btn-red-line js-regist">
</div>

<form id="frmSearchGoods" name="frmSearchGoods" method="get" class="js-form-enter-submit">
    <input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch']; ?>"/>
    <input type="hidden" name="sort[name]" value="<?= $sort['fieldName'] ?>">
    <input type="hidden" name="sort[mode]" value="<?= $sort['sortMode'] ?>">

    <div class="table-title gd-help-manual">
        등록은행 검색
    </div>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td>
                    <div class="form-inline">
                        <?php echo gd_select_box('key', 'key', ['bankName' => '은행명', 'accountNumber' => '계좌번호', 'depositor' => '예금주'], '', $search['key'], '=통합검색='); ?>
                        <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <th>사용설정</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="useFl" value="" <?php echo gd_isset($checked['useFl']['']); ?> />전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="useFl" value="y" <?php echo gd_isset($checked['useFl']['y']); ?> />사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="useFl" value="n" <?php echo gd_isset($checked['useFl']['n']); ?> />사용안함
                </label>
            </td>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black"/>
    </div>

    <div class="table-header">
        <div class="pull-left">
            검색 <strong><?php echo number_format($page->recode['total']);?></strong>개 /
            전체 <strong><?php echo number_format($page->recode['amount']);?></strong>개
        </div>
        <div class="pull-right form-inline">
            <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
            <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>
</form>

<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <table class="table table-rows">
        <colgroup>
            <col class="width3p"/>
            <col class="width5p"/>
            <col class="width15p" />
            <col class="width30p" />
            <col class="width10p" />
            <col class="width15p" />
            <col class="width10p" />
            <col class="width10p" />
            <col class="width5p" />
        </colgroup>
        <thead>
        <tr>
            <th class="center"><input type="checkbox" class="js-checkall" data-target-name="sno"></th>
            <th>번호</th>
            <th>은행명</th>
            <th>계좌번호</th>
            <th>예금주</th>
            <th>치환코드</th>
            <th>사용설정</th>
            <th>등록일</th>
            <th>수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($data) {
            $num = 1;
            foreach ($data as $key => $val) {
                if ($val['useFl'] == 'y') {
                    $strUse = '사용함';
                } else {
                    $strUse = '사용안함';
                }
                $bankCode = '<!--{ @ dataBank(' . $val['sno'] . ') }-->은행명 : {.bankName}, 계좌번호 : {.accountNumber}, 예금주 : {.depositor}<!--{ / }-->';
                ?>
                <tr>
                    <td class="center">
                        <input type="checkbox" name="sno[]" value="<?php echo $val['sno']; ?>" data-default-fl="<?php echo $val['defaultFl']?>" data-bank-name="<?php echo $val['bankName']; ?>"/>
                    </td>
                    <td class="center number"><?php echo number_format($page->idx--); ?></td>
                    <td class="center"><?php echo $val['bankName'];?><?php if($val['defaultFl'] =='y') { echo "<br/>(기본설정)"; } ?></td>
                    <td class="center"><?php echo $val['accountNumber'];?></td>
                    <td class="center"><?php echo $val['depositor'];?></td>
                    <td class="center font-eng">
                        <?php if ($val['useFl'] == 'y') { ?>
                        <button type="button" title="" class="btn btn-gray btn-sm js-popover" data-original-title="<?php echo $val['bankName'];?> 치환코드" data-content="<?php echo $bankCode;?>" data-placement="bottom">코드보기</button>
                        <button type="button" title="<?php echo $val['bankName'];?>" class="btn btn-white btn-sm js-clipboard"  data-clipboard-text="<?php echo $bankCode;?>">복사</button>
                        <?php }?>
                    </td>
                    <td class="center"><?php echo $strUse; ?></td>
                    <td class="center"><?php echo gd_date_format('Y-m-d', $val['regDt']); ?></td>
                    <td class="center">
                        <button type="button" data-sno="<?php echo $val['sno']; ?>" class="btn btn-sm btn-white js-regist">수정</button>
                    </td>
                </tr>
                <?php
                $num++;
            }
        } else { ?>
            <tr><td colspan="8" class="center">검색된 정보가 없습니다.</td></tr>
        <?php }
        ?>
        </tbody>
    </table>
</form>

<div class="table-action">
    <div class="pull-left">
        <button type="button" class="btn btn-white  js-select-copy">선택 복사</button>
        <button type="button" class="btn btn-white  js-select-delete">선택 삭제</button>
    </div>
    <div class="pull-right">
        <div class="form-inline">
            <?php
            $bankCode = '<!--{ @ dataBank(\'all\') }--><div>은행명 : {.bankName}, 계좌번호 : {.accountNumber}, 예금주 : {.depositor}</div><!--{ / }-->';
            ?>
            일괄노출 치환코드 :
            <button type="button" title="" class="btn btn-gray btn-sm js-popover" data-original-title="일괄노출 치환코드" data-content="<?php echo $bankCode;?>" data-placement="bottom">코드보기</button>
            <button type="button" title="사용중인 전체 은행" class="btn btn-white btn-sm js-clipboard"  data-clipboard-text="<?php echo $bankCode;?>">복사</button>
        </div>
    </div>
</div>

<div class="center"><?php echo $page->getPage();?></div>

<div class="clear-right">&nbsp;</div>

<script type="text/javascript">
    <!--
    $(function () {
        /**
         * 등록처리
         */
        $('.js-regist').click(function (e) {
            var sno = $(this).data('sno') != undefined ? $(this).data('sno') : '';
            var params = {
                sno: sno
            };
            $.get('layer_settle_bank.php', params, function (data) {
                BootstrapDialog.show({
                    title: '무통장 입금 은행 ' + (sno ? '수정' : '등록'),
                    message: $(data),
                    closable: false
                });
            });
        });

        /**
         * 복사처리
         */
        $('.js-select-copy').click(function (e) {
            var chkCnt = $('#frmList input[name="sno[]"]:checkbox:checked').length;
            if (chkCnt == 0) {
                alert('선택된 무통장 입금은행이 없습니다.');
                return;
            }

            dialog_confirm('선택한 ' + chkCnt + '개 무통장 입금은행을  정말로 복사하시겠습니까?', function (result) {
                if (result) {
                    var params = {
                        mode: 'bank_copy',
                        url: './settle_ps.php',
                        data: $('#frmList').serializeArray()
                    };
                    $.get('../share/layer_godo_sms.php', params, function (data) {
                        BootstrapDialog.show({
                            message: $(data),
                            closable: false
                        });
                    });
                }
            });
        });

        /**
         * 삭제 처리
         *
         * @param string modeStr 프로세스 종류(삭제, 복사)
         * @param string titleName 상품명
         * @param string dataSno sno
         */
        $('.js-select-delete').click(function (e) {

            var chkCnt = $('#frmList input[name="sno[]"]:checkbox:checked').length;

            if(chkCnt > 0) {
                var addMsg = "";
                $('#frmList input[name="sno[]"]:checkbox:checked').each(function () {
                    if($(this).data('default-fl') =='y') {
                        addMsg = "무통장 입금 은행 정보 ["+$(this).data('bank-name')+"]은 현재 기본설정으로 삭제가 불가능합니다.<br/>";
                        $(this).prop("checked",false);
                        chkCnt--;
                    }
                });
            }

            if (chkCnt == 0) {
                if(addMsg) {
                    alert(addMsg);
                } else {
                    alert('선택된 무통장 입금은행이 없습니다.');
                }

                return;
            }

            dialog_confirm(addMsg+'선택한 ' + chkCnt + '개 무통장 입금은행을 정말로 삭제하시겠습니까?<br/>삭제시 정보는 복구 되지 않습니다.', function (result) {
                if (result) {
                    var params = {
                        mode: 'bank_delete',
                        url: './settle_ps.php',
                        data: $('#frmList').serializeArray()
                    };
                    $.get('../share/layer_godo_sms.php', params, function (data) {
                        BootstrapDialog.show({
                            message: $(data),
                            closable: false
                        });
                    });
                }
            });
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchGoods').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchGoods').submit();
        });
    });
    //-->
</script>
