<form method="post" name="frmSuperAdminMemo" id="frmSuperAdminMemo" action="../order/order_ps.php" target="ifrmProcess">
    <input type="hidden" name="mode" value="admin_order_goods_memo_register">
    <input type="hidden" name="orderNo" id="orderNo" value="<?= $requestGetParams['orderNo']; ?>">
    <input type="hidden" name="sort" value="<?= $requestGetParams['sort']; ?>"/>
    <input type="hidden" name="pageNum" value="<?= $requestGetParams['pageNum']; ?>"/>
    <input type="hidden" name="no" value="">
    <input type="hidden" name="oldManagerId" value="">

    <div class="page-header">
        <h3>관리자메모</h3>
        <?php if(!$isProvider){ ?>
        <div class="btn-group">
            <button type="submit" class="btn btn-red">저장</button>
        </div>
        <?php }?>
    </div>
    <div class="super-admin-memo-title mgb20">
        <div class="pdl30">
            <p>주문번호 : <?= $requestGetParams['orderNo']; ?></p>
            <p>주문일시 : <?= $requestGetParams['regDt']; ?></p>
        </div>
    </div>

    <?php if(!$isProvider){ ?>
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
                    <th>메모 유형</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="memoType" value="order" checked="checked" />주문번호별
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="memoType" value="goods" />상품주문번호별
                        </label>
                    </td>
                    <th>메모 구분</th>
                    <td>
                        <?= gd_select_box('orderMemoCd', 'orderMemoCd', $memoCd, null, null, '=메모 구분='); ?>
                    </td>
                </tr>
                <tr id="tr_goodsSelect" class="display-none">
                    <th>상품 선택</th>
                    <td colspan="3">
                        <table cellpadding="0" cellpadding="0" width="100%" id="tbl_add_goods_set" class="table table-rows table-fixed">
                            <thead>
                            <tr id="orderGoodsList">
                                <th class="width5p"><input type="checkbox" id="allCheck" value="y"
                                                           onclick="check_toggle(this.id,'sno');"/></th>
                                <th class="width10p">상품주문번호</th>
                                <th class="width20p">주문상품</th>
                                <th class="width15p">처리상태</th>
                            </tr>
                            </thead>
                            <?php
                            foreach($goodsData as $fKey => $fVal) {
                                ?>
                                <tbody id="addGoodsList<?= $fKey; ?>">
                                <tr data-mall-sno="<?=$fVal['mallSno']?>">
                                    <td class="text-center">
                                        <input type="checkbox" name="sno[]" value="<?=$fVal['sno'];?>" >
                                    </td>
                                    <td class="text-center">
                                        <span class="sno"><?=$fVal['sno'];?></span>
                                    </td>
                                    <td>
                                        <span class="goodsNm" style="font-weight: bold;"><?=$fVal['goodsNm'];?></span><br/>
                                        <span class="optionInfo"><?=$fVal['optionInfo'];?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="orderStatus"><?=$fVal['orderStatus'];?></span>
                                    </td>
                                </tr>
                                </tbody>
                                <?php
                            }
                            ?>
                        </table>
                    </td>
                </tr>
                <tr>
                    <th>메모 내용</th>
                    <td colspan="3">
                        <textarea name="adminOrderGoodsMemo" class="form-control" rows="6"><?=$data['content']?></textarea>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="text-center mgt10">
            <button type="button" class="btn btn-black js-memo-reset">초기화</button>
        </div>
    <?php }?>

    <table cellpadding="0" cellpadding="0" width="100%" id="orderGoodsMemoList" class="table table-rows table-fixed mgt10">
        <thead>
        <tr id="orderGoodsList">
            <th class="width10p">작성일</th>
            <th class="width10p">작성자</th>
            <th class="width10p">메모 구분</th>
            <th class="width10p">상품주문번호</th>
            <th class="width30p">메모 내용</th>
            <?php if (!$isProvider) { ?>
                <th class="width5p">관리</th>
            <?php }?>
        </tr>
        </thead>
        <?php
        if($memoData) {
            foreach ($memoData as $mKey => $mVal) {
                ?>
                <tbody id="orderGoodsMemoData<?= $mKey; ?>">
                <tr data-mall-sno="<?= $mVal['mallSno'] ?>">
                    <td class="text-center">
                        <span><?php if ($mVal['modDt']) {
                                echo $mVal['modDt'];
                            } else {
                                echo $mVal['regDt'];
                            } ?></span></td>
                    <td class="text-center">
                        <span class="managerId"><?= $mVal['managerId']; ?></span><br/>
                        <?php if($mVal['managerNm']){?><span class="managerNm">(<?= $mVal['managerNm']; ?>)</span><?php }?>
                    </td>
                    <td class="text-center">
                        <span class="itemNm"><?= $mVal['itemNm']; ?></span>
                    </td>
                    <td class="text-center">
                        <span class="orderGoodsSno"><?php if ($mVal['type'] == 'goods') {
                                echo $mVal['orderGoodsSno'];
                            } else {
                                echo '-';
                            } ?></span>
                    </td>
                    <td>
                        <span class="content-memo"><?=str_replace(['\r\n', '\n', chr(10)], '<br>', $mVal['content']);?></span>
                    </td>
                    <?php if (!$isProvider) { ?>
                    <td class="text-center">
                        <div class="pdb5">
                            <span class="mod-button" style="padding-bottom: 5px;">
                                <button type="button" class="btn btn-sm btn-gray js-admin-memo-modify" data-sno="<?=$mVal['orderGoodsSno'];?>" data-type="<?=$mVal['type'];?>" data-memocd="<?=$mVal['memoCd'];?>" data-manager-sno="<?=$mVal['managerSno'];?>" data-m-sno="<?=$managerSno;?>" data-content="<?=$mVal['content'];?>" data-no="<?=$mVal['sno'];?>">수정</button>
                            </span>
                        </div>
                        <div>
                            <span class="del-button">
                                <button type="button" class="btn btn-sm btn-gray js-admin-memo-delete" data-sno="<?=$mVal['orderGoodsSno'];?>" data-type="<?=$mVal['type'];?>" data-manager-sno="<?=$mVal['managerSno'];?>" data-m-sno="<?=$managerSno;?>" data-no="<?=$mVal['sno'];?>">삭제</button>
                            </span>
                        </div>
                    </td>
                    <?php } ?>
                </tr>
                </tbody>
                <?php
            }
        }else{
            ?>
            <tr>
                <td colspan="6" class="no-data">
                    등록된 메모가 없습니다.
                </td>
            </tr>
        <?php }?>
    </table>
    <div class="center"><?= $page->getPage(); ?></div>

</form>

<script type="text/javascript">
    <!--
    $(document).ready(function(){
        // 폼 체크 후 전송
        $('#frmSuperAdminMemo').validate({
            dialog: false,
            rules: {
                adminOrderGoodsMemo: 'required',
            },
            messages: {
                adminOrderGoodsMemo: '관리자메모를 입력해주세요.',
            },
            submitHandler: function(form) {
                var checkedValue = $("input[type=radio][name=memoType]:checked").val();
                var snoLength = $('input[name=\"sno[]\"]:checked').length;
                if(checkedValue == 'goods'){
                    if (!snoLength) {
                        alert('선택된 상품이 없습니다.');
                        return false;
                    }
                }else if(checkedValue == 'order'){
                    if(snoLength) {
                        $('input:checkbox[name=\"sno[]\"]').attr("checked", false);
                    }
                }
                form.submit();
            }
        });

        // 상품 주문번호별 일때 상품선택 노출
        $('input:radio[name="memoType"]').click(function () {
            if($(this).val() == 'goods'){
                $('#tr_goodsSelect').removeClass('display-none');
            }else{
                $('#tr_goodsSelect').addClass('display-none');
            }
        });

        // 메모 수정
        $('.js-admin-memo-modify').click(function () {
            if(($(this).data('manager-sno') == $(this).data('m-sno')) || ($(this).data('manager-sno') == 0)) {
                var contentStr = $(this).data('content').toString().replace(/\\r\\n/gi, "\n");
                //var contentStr = $(this).data('content').replace(/\\r\\n/gi, "\n");

                // 수정 버튼 누를때마다 체크박스 초기화
                $("#allCheck, input:checkbox[name=\"sno[]\"]").prop("checked",false);

                // 수정 버튼 누를때마다 체크박스 초기화
                $("#allCheck, input:checkbox[name=\"sno[]\"]").prop("checked",false);

                // 수정 모드로 변경
                $('input[name="mode"]').attr('value', 'admin_order_goods_memo_modify');
                $('input[name="no"]').attr('value',$(this).data('no'));
                $('input[name="oldManagerId"]').attr('value',$(this).data('manager-sno'));
                $("input:radio[name=memoType]:radio[value=\'" + $(this).data('type') + "\']").prop('checked', true);
                $("input:checkbox[name=\"sno[]\"][value=\'" + $(this).data('sno') + "\']").prop("checked", true);
                $("#allCheck, input:checkbox[name=\"sno[]\"][value!=\'" + $(this).data('sno') + "\']").prop("disabled", true);
                $("#orderMemoCd").val($(this).data('memocd')).prop("selected", true);
                $("textarea[name='adminOrderGoodsMemo']").val(contentStr);

                if ($(this).data('type') == 'order') {
                    $('#tr_goodsSelect').addClass('display-none');
                } else {
                    $('#tr_goodsSelect').removeClass('display-none');
                }
            }else{
                alert('메모를 등록한 관리자만 수정가능합니다.');
                return false;
            }
        });

        // 메모 삭제
        $('.js-admin-memo-delete').click(function () {
            if(($(this).data('manager-sno') == $(this).data('m-sno')) || ($(this).data('manager-sno') == 0)) {
                //var orderGoodsSno = $(this).data('sno');
                //var memoType = $(this).data('type');
                var no = $(this).data('no');
                dialog_confirm('선택한 관리자메모를 삭제하시겠습니까? 삭제하시면 복구 하실 수 없습니다.', function (result) {
                    if (result) {
                        //var orderNo = "<?= $orderNo;?>";
                        $.ajax({
                            method: "POST",
                            cache: false,
                            url: "../order/order_ps.php",
                            data: "mode=admin_order_goods_memo_delete&no=" + no,
                        }).success(function (data) {
                            alert(data);
                        }).error(function (e) {
                            alert(e.responseText);
                            return false;
                        });
                    }
                });
            }else{
                alert('메모를 등록한 관리자만 삭제가능합니다.');
                return false;
            }
        });

        // 초기화
        $('.js-memo-reset').click(function () {
            $("input[name='memoType'][value='order']").prop("checked",true);
            $("#orderMemoCd").val($(this).data('memocd')).prop("selected", false);
            $("#allCheck, input:checkbox[name=\"sno[]\"][value!=\'" + $(this).data('sno') + "\']").prop("checked", false);
            $("#allCheck, input:checkbox[name=\"sno[]\"]").prop("disabled", false);
            $("textarea[name='adminOrderGoodsMemo']").val('');
            $('#tr_goodsSelect').addClass('display-none');
            $('input[name="mode"]').attr('value', 'admin_order_goods_memo_register');
            $('input[name="no"]').attr('value','');
        });
    });
    //-->
</script>
