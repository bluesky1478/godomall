<style type="text/css">
.formField {height:250px; padding:5px;}
.formField li {border:solid 1px #CECECE; height:23px; padding:3px; margin:1px;}
.ui-selecting { background: #FECA40; }
.ui-selected { background: #F39814; color: white; }
.formField .added { color:#CDCDCD; }
</style>
<script type="text/javascript">
<!--
function changeForm(obj) {
    if ($(obj).val() == '') {
        $("[name='newFormNm']").show();
    }
    else {
        location.href = "order_list_download_form.php?sno=" + $(obj).val();
    }
}

function saveForm() {
    $("[name='submode']").val("save");
    $("#frm").submit();
}

function removeForm() {
    if (confirm("삭제하시겠습니까?")) {
        $("[name='submode']").val("remove");
        $("#frm").submit();
    }
}


function addFormField(mode) {
    if (mode == 'blank') {
        $(".sortable.selected").append("<li><input type=\"hidden\" value=\"\" /><input type=\"text\" value=\"\" class=\"display-none\" /><div class=\"pull-right\"><span class=\"button small red\"><a onclick=\"removeFormField(this)\">삭제</a></span></div></li>");
    }
    else {
        var objs = $(".selectable." + mode).find(".ui-selected");
        objs.each(function() {
            $(this).removeClass('ui-selected');
            var item = $(this).clone().css("cursor", "move");
            item.find("div").removeClass("display-none");
            $(".sortable.selected").append(item);
            $(this).addClass('added').prop("disabled", true);
            if ($(".sortable.selected").find("li").length > 10) {
                $(".sortable.selected").height("auto");
            }
            $("[name='formSortField[]']").append("<option value='" + $(this).find(":hidden").val() + "'>" + $(this).find(":text").val() + "</option>");
        });
    }
}

function editFormField(obj) {
    var item = $(obj).parents("li").first();
    item.find(":text").toggle();
    item.find("span.text").toggle();
}

function changeFormFieldTxt(obj) {
    var txt = $(obj).val();
    $(obj).next().html(txt);
    var val = $(obj).parents("li").find(":hidden").val();
    $("[name='formSortField[]']").find("option[value=" + val +"]").html(txt);
}

function removeFormField(obj) {
    var item = $(obj).parents("li").first();
    var val = item.find(":hidden").val();
    item.remove();
    $(".selectable").find("li:has([value=" + val + "])").removeClass("added").prop("disabled", false);
    $("[name='formSortField[]']").find("option[value=" + val +"]").remove();
}

$(document).ready(function() {
    $(".selectable").selectable({
        filter: 'li:not(.added)',
        selected: function(event, ui) {
            if ($(ui['selected']).attr("class").match(/added/g) != null) {
                $(ui['selected']).removeClass('ui-selected');
            }
        }
    }).disableSelection();

    $(".sortable").sortable({
        placeholder : "ui-selecting"
    });

    if ($("[name='formSno']").val() == "") $("[name='formSno']").trigger("change");

    <?php if (gd_array_is_empty($data['formField']) === false) { ?>
    $(".sortable li").each(function() {
        var val = $(this).find(":hidden").val();
        if (val) $(".selectable li:has(:hidden[value=" + val + "])").addClass("added");
    });
    <?php } ?>

    $("#frm").formProcess('alert', [
        {'before': function(fObj) {
            switch($("[name='submode']").val()) {
                case "save" : {
                    if ($(".sortable li").length == 0) {
                        alert("선택된 항목이 없습니다.");
                        return false;
                    }

                    if ($("[name='formSno']").val() == "" && $("[name='newFormNm']").val().trim() == "") {
                        alert("새로운 양식 이름을 입력하세요.");
                        return false;
                    }

                    $(".sortable li").find(":hidden").attr("name", "formField[]");
                    $(".sortable li").find(":text").attr("name", "formFieldTxt[]");
                    return true;
                    break;
                }
                case "remove": {
                    if ($("[name='formSno']").val() == "") {
                        alert("삭제할 양식을 선택하세요.");
                        return false;
                    }
                    return true;
                    break;
                }
            }
        }}
    ], '', '');
});

//-->
</script>
<form id="frm" action="order_ps.php" method="post">
<input type="hidden" name="mode" value="downloadForm" />
<input type="hidden" name="submode" value="" />
<div class="subtitle_excel">다운로드 양식 관리</div>
<table class="list_top_excel">
<tr>
    <td>
        <select name="formSno" onchange="changeForm(this);" class="input_select">
            <option value="">새로운양식등록</option>
            <?php foreach($formList as $val) {?>
            <option value="<?php echo $val['sno']?>" <?php if ($val['sno']==Globals::get('sno') ) echo 'selected="selected"' ?>><?php echo $val['formNm']?></option>
            <?php }?>
        </select>
        <input type="text" name="newFormNm" class="form-control display-none" />

        <span class="button black small"><a onclick="saveForm()">저장</a></span>
        <span class="button red small"><a onclick="removeForm()">삭제</a></span>
    </td>
</tr>
</table>

<div class="subtitle_excel">다운로드 항목 관리</div>
<table class="list_excel_form">
<colgroup><col class="width50p" /><col class="width50p" /></colgroup>
<tr>
    <th>선택 가능한 주문서 항목</th>
    <th>선택된 주문서 항목</th>
</tr>
<tr>
    <td>
        <div class="table-title gd-help-manual">주문/배송</div>
        <div style="height:250px; overflow:auto; margin-bottom:5px; border:solid 1px #DEDEDE;">
            <ul class="formField selectable order">
                <?php
                    if (gd_array_is_empty($formFieldOrder) === false) {
                        foreach($formFieldOrder as $key => $val) {
                ?>
                <li>
                    <input type="hidden" value="<?php echo $key?>" />
                    <input type="text" value="<?php echo $val?>" class="form-control display-none" onchange="changeFormFieldTxt(this)" />
                    <span class="text"><?php echo $val?></span>
                    <div class="pull-right display-none">
                        <span class="button small black"><a onclick="editFormField(this)">수정</a></span>
                        <span class="button small red"><a onclick="removeFormField(this)">삭제</a></span>
                    </div>
                </li>
                <?php
                        }
                    }
                ?>
            </ul>
        </div>
        <div class="center" style="margin-bottom:5px;"><span class="button blue small"><a onclick="addFormField('order')">주문/배송항목 추가</a></span></div>

        <div class="table-title gd-help-manual">상품관련</div>
        <div style="height:250px; overflow:auto; margin-bottom:5px; border:solid 1px #DEDEDE;">
            <ul class="formField selectable goods">
                <?php
                    if (gd_array_is_empty($formFieldOrderGoods) === false) {
                        foreach($formFieldOrderGoods as $key => $val) {
                ?>
                <li>
                    <input type="hidden" value="<?php echo $key?>" />
                    <input type="text" value="<?php echo $val?>" class="form-control display-none" onchange="changeFormFieldTxt(this)" />
                    <span class="text"><?php echo $val?></span>
                    <div class="pull-right display-none">
                        <span class="button small black"><a onclick="editFormField(this)">수정</a></span>
                        <span class="button small red"><a onclick="removeFormField(this)">삭제</a></span>
                    </div>
                </li>
                <?php
                        }
                    }
                ?>
            </ul>
        </div>
        <div class="center"><span class="button blue small"><a onclick="addFormField('goods')">상품관련항목 추가</a></span></div>
    </td>
    <td style="vertical-align:top; padding:5px; border-bottom:0px;">
        <div style="height:570px; overflow:auto; margin-bottom:5px; border:solid 1px #DEDEDE;">
            <ul class="formField sortable selected" style="height:98%;">
                <?php
                    if (gd_array_is_empty($data['formField']) === false) {
                        for($i = 0; $i < count($data['formField']); $i++) {
                ?>
                <li>
                    <input type="hidden" value="<?php echo $data['formField'][$i]?>" />
                    <input type="text" value="<?php echo $data['formFieldTxt'][$i]?>" class="form-control display-none" onchange="changeFormFieldTxt(this)" />
                    <span class="text"><?php echo $data['formFieldTxt'][$i]?></span>
                    <div class="pull-right">
                        <?php if (empty($data['formField'][$i]) === false) {?><span class="button small black"><a onclick="editFormField(this)">수정</a></span><?php }?>
                        <span class="button small red"><a onclick="removeFormField(this)">삭제</a></span>
                    </div>
                </li>
                <?php
                        }
                    }
                ?>
            </ul>
        </div>
        <div class="center"><span class="button blue small"><a onclick="addFormField('blank')">항목에 공란 추가</a></span></div>
    </td>
</tr>
</table>
<br />

<table class="list_excel_form">
<colgroup><col class="width-sm" /><col /><col class="width-sm" /></colgroup>
<tr>
    <th>정렬기준선택</th>
    <th>정렬기준항목</th>
    <th>정렬방식</th>
</tr>
<?php for($i = 0; $i < 3; $i++) { ?>
<tr align="center">
    <td style="padding:5px"><?php echo $i+1?>차</td>
    <td style="padding:5px">
        <select name="formSortField[]" class="form-control">
            <option value="">정렬기준항목</option>
            <?php
                for($j = 0; $j < count($data['formField']); $j++) {
                    if (empty($data['formField'][$j]) === false) {
                        $item = &$data['formField'][$j];
            ?>
            <option value="<?php echo $data['formField'][$j]?>" <?php if (gd_isset($data['formSort'][$i][0]) == $data['formField'][$j]) echo 'selected="selected"'?>><?php echo $data['formFieldTxt'][$j]?></option>
            <?php
                    }
                }
            ?>
        </select>
    </td>
    <td style="padding:5px">
        <?php echo gd_select_box(null, 'formSortOrder[]', array('ASC'=>'오름차순', 'DESC'=>'내림차순'), null, gd_isset($data['formSort'][$i][1])); ?>
    </td>
</tr>
<?php } ?>
</table>
</form>
