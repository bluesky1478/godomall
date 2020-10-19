<style type="text/css">body {width:100%; min-width:100%; padding:3px;} </style>

<div id="content" class="row">
    <div class="col-xs-12">
        <form id="frmSearchBase" name="frmSearchBase" method="get" class="js-form-enter-submit">
            <div class="table-title gd-help-manual" style="margin-top:10px;">
                발주서 양식 관리
            </div>
            <input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch']; ?>"/>
            <input type="hidden" name="menu" value="orderDraft" />
            <input type="hidden" name="location" value="order_list_pay" />
            <input type="hidden" name="pageNum" value="10" />
            <input type="hidden" name="sort" value="" />

            <div class="search-detail-box">
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-sm"/>
                        <col/>
                        <col class="width-sm"/>
                        <col/>
                    </colgroup>
                    <tbody>
                    <tr>
                        <th>검색어</th>
                        <td colspan="3"><div class="form-inline">
                                <?php echo gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                                <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control" style="width:300px;" />
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
                    검색 <strong><?php echo number_format($page->recode['total']);?></strong>개 /
                    전체 <strong><?php echo number_format($page->recode['amount']);?></strong>개
                </div>
            </div>
        </form>

        <form id="frmList" action="" method="get" target="ifrmProcess">
            <input type="hidden" name="mode" value="">
            <input type="hidden" name="layerFl" value="n">
            <table class="table table-rows">
                <thead>
                <tr>
                    <th class="width5p"><input type="checkbox" id="allCheck" value="y" /></th>
                    <th class="width5p">번호</th>
                    <th>다운로드 양식명</th>
                    <th class="width10p">등록자</th>
                    <th class="width10p">등록일</th>
                    <th class="width5p">수정</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (gd_isset($data)) {
                    foreach ($data as $key => $val) { ?>
                        <tr>
                            <td class="center"><input type="checkbox" class="chk-excel" name="sno[<?php echo $val['sno']; ?>]" value="<?php echo $val['sno']; ?>" <?php if($val['defaultFl'] =='y') { echo "disabled = 'true'"; }  ?>/></td>
                            <td class="center number"><?php echo number_format($page->idx--);?></td>
                            <td> <?php echo $val['title'];?></td>
                            <td class="center"><?php echo $val['managerNm'];?><br/>(<?php echo $val['managerId'];?>)
                                <?=$val['deleteText']?></td>
                            <td class="center date"><?php echo gd_date_format('Y-m-d', $val['regDt']);?></td>
                            <td class="center">
                                <a href="#" onclick="locationModifyForm('<?=$val['sno']?>');" class="btn btn-white btn-xs">수정</a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td class="center" colspan="9">검색된 정보가 없습니다.</td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>

            <div class="table-action">
                <div class="pull-left">
                    <button type="button" class="btn btn-white  checkDelete">선택 삭제</button>
                </div>
            </div>
        </form>
        <div class="center"><?php echo $page->getPage();?></div>
        <div class="center">
            <input type="button" value="확인" class="btn btn-lg btn-white" onclick="self.close();" />
        </div>
    </div>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 삭제
        $('button.checkDelete').click(function () {
            var chkCnt = $('input[name*="sno["]:checkbox:checked').length;
            if (chkCnt == 0) {
                alert('선택된 다운로드 양식이 없습니다.');
                return;
            }
            dialog_confirm('선택한 ' + chkCnt + '개 다운로드 양식을  정말로 삭제하시겠습니까?\n삭제시 정보는 복구 되지 않습니다.', function (result) {
                if (result === false) { return false; }
                $('#frmList input[name=\'mode\']').val('delete');
                $.ajax({
                    url : '../policy/excel_form_ps.php',
                    data : $('#frmList').serialize(),
                    dataType : 'html',
                    type : 'post',
                    success : function(res) {
                        $('#frmList input[name=\'mode\']').val('');
                        document.location.reload();
                    }
                })
            });
        });

        $('#allCheck').click(function(){
            var checked = $(this).prop('checked');
           $('#frmList .chk-excel').prop('checked', checked);
        });

        $('#frmList .chk-excel').click(function(){
            var checked = $(this).prop('checked');
            var len = $('#frmList .chk-excel:checked').length;
            if (len == $('#frmList .chk-excel').length) {
                $('#frmList #allCheck').prop('checked', true);
            } else {
                $('#frmList #allCheck').prop('checked', false);
            }
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchBase').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchBase').submit();
        });

    });

    var locationModifyForm = function(sno) {

        win = popup({
            url: './order_draft_excel_register.php?sno=' + sno,
            target: 'modifyOrderDraftPopup',
            width: 950,
            height: 800,
            scrollbars: 'yes',
            resizable: 'no'
        });
        win.focus();
        return win;
    }

    //-->
</script>
