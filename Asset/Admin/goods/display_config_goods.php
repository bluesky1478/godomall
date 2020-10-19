    <form id="frmForm" name="frmForm" action="./display_config_ps.php" method="post">
    <input type="hidden" name="mode" value="goods_register"/>

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?> <?=$data['mode'] == "modify" ?  "수정" : "등록"; ?></h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
     노출 항목 설정 <span class="notice-info">노출함으로 설정되어 있더라도 내용이 등록되지 않은 항목은 쇼핑몰에 노출되지 않습니다.</span>
    </div>

    <table class="table table-cols">
        <colgroup>
            <col style="width:160px"/>
            <col/>
        </colgroup>
        <tr>
            <th>적용범위</th>
            <td colspan="3">
                <label class="checkbox-inline">
                    <input type="checkbox" name="mobileFl" value="y" <?=gd_isset($checked['mobileFl']['y']);?> >모바일 쇼핑몰 동일 적용
                </label>
            </td>
        </tr>
        <tbody>
        <tr>
            <th>PC쇼핑몰 노출항목</th>
            <td colspan="3">
                <div class="js-download-filed-select">
                    <div style="width:300px;float:left"/>
                    <div class="table-action mgb0 mgt0" style="background:#fff;border:0px;">
                        <div class="pull-left">
                            전체 항목
                        </div>
                        <div class="pull-right">
                            <select class="form-control" name="sort" onchange="select_field('pc',this.value)" >
                                <option value="">기본순서</option>
                                <option value="desc">가나다순</option>
                                <option value="asc">가나다역순</option>
                            </select>
                        </div>
                    </div>
                    <div class="js-field-select-wapper">
                        <table class="js-field-default-pc table table-rows" data-type="pc">
                            <tbody>
                            <?php
                            $fieldListSort = 1;
                            foreach($fieldList as $k => $v) { ?>
                            <tr class="default_field_<?=$fieldListSort?>" data-sort="<?=$fieldListSort?>" data-field-key="<?=$k?>" data-field-name="<?=$v?>"><td ><?=$v?></td></tr>
                            <?php
                                $fieldListSort++;
                            } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div  style="width:70px;float:left;text-align:center;padding-top:100px;">

                    <p><button type="button" class="btn btn-sm btn-white btn-icon-left js-move-left" data-type="pc">추가</button></p>

                    <p><button type="button" class="btn btn-sm btn-white btn-icon-right js-move-right" data-type="pc">삭제</button></p>

                    <p><button class="btn btn-sm btn-white btn-icon-left-all js-move-left-all" data-type="pc"/>전체<br/>추가</button></p>

                    <p><button class="btn btn-sm btn-white btn-icon-right-all js-move-right-all" data-type="pc" />전체<br/>삭제</button></p>

                </div>
                <div style="width:300px;float:left;"/> <div class="table-action mgb0 mgt0" style="background:#fff;border:0px;">
                    <div class="pull-left">
                        선택항목 <input type="button" value="선택항목 전체보기" class="btn btn-sm btn-gray js-excel-filed-all" data-type="pc" />
                    </div>
                    <div class="pull-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">
                                맨아래
                            </button>
                            <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down" >
                                아래
                            </button>
                            <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up" >
                                위
                            </button>

                            <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top" >
                                맨위
                            </button>
                        </div>
                    </div>
                </div>
                <div class="js-field-select-wapper">
                    <table class="js-field-select-pc table table-rows" data-type="pc" data-toggle="" data-use-row-attr-func="false" data-reorderable-rows="true">
                        <tbody data-index=".move-row-pc">
                        <?php
                        if($data['goodsDisplayField']['pc']) {
                            foreach ($data['goodsDisplayField']['pc'] as $k => $v) {
                                $sort = array_search($v, array_keys($fieldList))+1;
                                ?>
                                <tr class="move-row-pc select_field_<?=$sort?>" data-sort="<?=$sort?>" data-field-key="<?=$v?>" data-field-name="<?=$fieldList[$v]?>" >
                                    <td><?=$fieldList[$v]?><input type="hidden" name="goodsDisplayField[pc][]" value="<?=$v?>">
                                    </td>
                                </tr>
                                <?php
                            }
                        }?>
                        </tbody>
                    </table>
                </div>
                </div>

                <div class="notice-info" style="clear:both">
                    Shift 버튼을 누른 상태에서 선택하면 여러 항목을 동시에 선택할 수 있습니다.
                </div>
                <div class="notice-info" style="clear:both">
                    "배송비"항목 미노출 설정 시, 배송비 선불/착불 선택조건 상품은 자동으로 선불로 선택되어 배송비가 부과됩니다.
                </div>

                </div>

            </td>
        </tr>
        <tr class="js-download-filed-select">
            <th>선택항목 전체보기</th>
            <td colspan="3" class="js-excel-field-pc">
                [선택항목 전체보기] 버튼을 클릭하면 현재 선택된 다운로드 항목을 확인할 수 있습니다.
            </td>
        </tr>
        <tr class="js-download-filed-select">
            <th>할인적용가 설정</th>
            <td colspan="3">
                <?php foreach($themeGoodsDiscount as $k => $v) { ?>
                    <label  class="checkbox-inline width-sm">
                        <input type="checkbox" name="goodsDiscount[pc][]" value="<?=$k?>" <?php if(in_array($k,array_values($data['goodsDiscount']['pc']))) { echo "checked='checked'"; } ?>  >  <?=$v?>
                    </label>
                <?php } ?>
                <div class="notice-info" style="clear:both">할인적용가 노출 시 적용할 할인금액을 설정합니다.</div>
            </td>
        </tr>
        <tr>
            <th>노출항목 추가 설정</th>
            <td colspan="3">

                <?php
                foreach($addFieldList as $k => $v) { ?>
                    <label class="checkbox-inline width-sm" >
                        <input type="checkbox" name="goodsDisplayAddField[pc][]" value="<?=$k?>" <?php if(in_array($k, $data['goodsDisplayAddField']['pc'])) { echo "checked='checked'"; } ?> /><?=$v?>
                    </label>
                <?php
                } ?>
                <div class="notice-info" style="clear:both">
                    [할인율] 체크 시 판매가 대비 할인율이 할인금액에 노출됩니다. (쿠폰가와 할인적용가에 적용됩니다.)
                </div>
            </td>
        </tr>
        <tr>
            <th>취소선 추가 설정</th>
            <td colspan="3">

                <input type="checkbox" name="goodsDisplayStrikeField[pc][]" value="default" checked='checked' style="display: none" />
                <?php
                foreach($strikeFieldList as $k => $v) { ?>
                    <label class="checkbox-inline width-sm" >
                        <input type="checkbox" name="goodsDisplayStrikeField[pc][]" value="<?=$k?>" <?php if(in_array($k, $data['goodsDisplayStrikeField']['pc'])) { echo "checked='checked'"; } ?> /><?=$v?>
                    </label>
                    <?php
                } ?>

                <div class="notice-info" style="clear:both">
                    체크시 쇼핑몰에 취소선 효과가 적용되어 출력됩니다. (예시. 정가 <s>12,000</s>원)<br/>
                    단, 판매가의 경우 할인가가 없는 상품에는 취소선이 적용되지 않습니다.
                </div>
            </td>
        </tr>
        </tbody>
        </table>
        <table class="table table-cols">
            <colgroup>
                <col style="width:160px"/>
                <col/>
            </colgroup>
        <tbody class="js-goods-mobile">
        <tr>
            <th>모바일쇼핑몰 노출항목</th>
            <td colspan="3">
                <div class="js-download-filed-select">
                    <div style="width:300px;float:left"/>
                    <div class="table-action mgb0 mgt0" style="background:#fff;border:0px;">
                        <div class="pull-left">
                            전체 항목
                        </div>
                        <div class="pull-right">
                            <select class="form-control" name="sort" onchange="select_field('mobile',this.value)" >
                                <option value="">기본순서</option>
                                <option value="desc">가나다순</option>
                                <option value="asc">가나다역순</option>
                            </select>
                        </div>
                    </div>
                    <div class="js-field-select-wapper">
                        <table class="js-field-default-mobile table table-rows" data-type="mobile">
                            <tbody>
                            <?php
                            $fieldListSort = 1;
                            foreach($fieldList as $k => $v) { ?>
                                <tr class="default_field_<?=$fieldListSort?>" data-sort="<?=$fieldListSort?>" data-field-key="<?=$k?>" data-field-name="<?=$v?>"><td ><?=$v?></td></tr>
                                <?php
                                $fieldListSort++;
                            } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div  style="width:70px;float:left;text-align:center;padding-top:100px;">

                    <p><button class="btn btn-sm btn-white btn-icon-left js-move-left" data-type="mobile">추가</button></p>

                    <p><button class="btn btn-sm btn-white btn-icon-right js-move-right" data-type="mobile">삭제</button></p>

                    <p><button class="btn btn-sm btn-white btn-icon-left-all js-move-left-all" data-type="mobile"/>전체<br/>추가</button></p>

                    <p><button class="btn btn-sm btn-white btn-icon-right-all js-move-right-all" data-type="mobile" />전체<br/>삭제</button></p>

                </div>
                <div style="width:300px;float:left;"/> <div class="table-action mgb0 mgt0" style="background:#fff;border:0px;">
                    <div class="pull-left">
                        선택항목 <input type="button" value="선택항목 전체보기" class="btn btn-sm btn-gray js-excel-filed-all" data-type="mobile" />
                    </div>
                    <div class="pull-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom" >
                                맨아래
                            </button>
                            <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down" >
                                아래
                            </button>
                            <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up" >
                                위
                            </button>

                            <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top" >
                                맨위
                            </button>
                        </div>
                    </div>
                </div>
                <div class="js-field-select-wapper">
                    <table class="js-field-select-mobile table table-rows" data-type="mobile" data-toggle="" data-use-row-attr-func="false" data-reorderable-rows="true">
                        <tbody data-index=".move-row-mobile">
                        <?php
                        if($data['goodsDisplayField']['mobile']) {
                            foreach ($data['goodsDisplayField']['mobile'] as $k => $v) {
                                $sort = array_search($v, array_keys($fieldList))+1;
                                ?>
                                <tr class="move-row-mobile select_field_<?=$sort?>" data-sort="<?=$sort?>" data-field-key="<?=$v?>" data-field-name="<?=$fieldList[$v]?>" >
                                    <td><?=$fieldList[$v]?><input type="hidden" name="goodsDisplayField[mobile][]" value="<?=$v?>">
                                    </td>
                                </tr>
                                <?php
                            }
                        }?>
                        </tbody>
                    </table>
                </div>
                </div>

                <div class="notice-info" style="clear:both">
                    Shift 버튼을 누른 상태에서 선택하면 여러 항목을 동시에 선택할 수 있습니다.
                </div>

                </div>

            </td>
        </tr>
        <tr class="js-download-filed-select">
            <th>선택항목 전체보기</th>
            <td colspan="3" class="js-excel-field-mobile">
                [선택항목 전체보기] 버튼을 클릭하면 현재 선택된 다운로드 항목을 확인할 수 있습니다.
            </td>
        </tr>
        <tr class="js-download-filed-select">
            <th>할인적용가 설정</th>
            <td colspan="3">
                <?php foreach($themeGoodsDiscount as $k => $v) { ?>
                    <label  class="checkbox-inline width-sm">
                        <input type="checkbox" name="goodsDiscount[mobile][]" value="<?=$k?>" <?php if(in_array($k,array_values($data['goodsDiscount']['mobile']))) { echo "checked='checked'"; } ?>  >  <?=$v?>
                    </label>
                <?php } ?>
                <div class="notice-info" style="clear:both">할인적용가 노출 시 적용할 할인금액을 설정합니다.</div>
            </td>
        </tr>
        <tr>
            <th>노출항목 추가 설정</th>
            <td colspan="3">

                <?php
                foreach($addFieldList as $k => $v) { ?>
                    <label class="checkbox-inline width-sm" >
                        <input type="checkbox" name="goodsDisplayAddField[mobile][]" value="<?=$k?>" <?php if(in_array($k, $data['goodsDisplayAddField']['mobile'])) { echo "checked='checked'"; } ?> /><?=$v?>
                    </label>
                    <?php
                } ?>
                <div class="notice-info" style="clear:both">
                    [할인율] 체크 시 판매가 대비 할인율이 할인금액에 노출됩니다. (쿠폰가와 할인적용가에 적용됩니다.)
                </div>
            </td>
        </tr>
        <tr>
            <th>취소선 추가 설정</th>
            <td colspan="3">

                <input type="checkbox" name="goodsDisplayStrikeField[mobile][]" value="default" checked='checked' style="display: none" />
                <?php
                foreach($strikeFieldList as $k => $v) { ?>
                    <label class="checkbox-inline width-sm" >
                        <input type="checkbox" name="goodsDisplayStrikeField[mobile][]" value="<?=$k?>" <?php if(in_array($k, $data['goodsDisplayStrikeField']['mobile'])) { echo "checked='checked'"; } ?> /><?=$v?>
                    </label>
                    <?php
                } ?>

            </td>
        </tr>
        </tbody>
    </table>
</form>



<style>
    .js-field-select-wapper {
        height:300px;
        overflow:scroll;
        overflow-x:hidden;
        border:1px solid #dddddd;
    }

    .js-field-default-mobile, .js-field-default-pc,  .js-field-select-pc,  .js-field-select-mobile {
        width:100%;
    }

    .js-field-default-mobile td, .js-field-default-pc td ,  .js-field-select td  ,  .js-field-select-mobile td {
        border:1px solid #dddddd;
    }

</style>


<script type="text/javascript">
    <!--

    // 리스트 클릭 활성/비활성화
    var iciRow = '';
    var preRow = '';

    $(document).ready(function () {

        <?php if($data['mobileFl'] =='y') { ?>
        $(".js-goods-mobile").hide();
        <?php } ?>

        $("input[name='mobileFl']").click(function(e){
            if($(this).prop('checked') == true)  $(".js-goods-mobile").hide();
            else  $(".js-goods-mobile").show();
        });


        $("#frmForm").validate({
            submitHandler: function (form) {

                if($("input[name='goodsDisplayField[pc][]']").length == 0) {
                    alert('PC 상품상세 노출항목이 1개 이상 선택하셔야 합니다.');
                    return false;
                }
                if($("input[name='mobileFl']").prop('checked') == false) {
                    if ($("input[name='goodsDisplayField[mobile][]']").length == 0) {
                        alert('모바일 상품상세 노출항목이 1개 이상 선택하셔야 합니다.');
                        return false;
                    }
                }

                form.target = 'ifrmProcess';
                form.submit();
            }
        });

        var lastSelectedRow = "";
        $(document).on('click', '.js-field-select-pc tbody tr,.js-field-select-mobile tbody tr', function (event) {

            var type = $(this).closest('table').data("type");
            if(type =='pc') {
                $(".js-field-select-mobile tbody tr").removeClass('warning');
                $(".js-field-select-mobile tbody tr").css('background','#ffffff');
            } else {
                $(".js-field-select-pc tbody tr").removeClass('warning');
                $(".js-field-select-pc tbody tr").css('background','#ffffff');
            }

            if (iciRow) preRow = iciRow;
            iciRow = $(this);

            if (event.shiftKey) {

                var ia = lastSelectedRow.index();
                var ib = $(this).index();

                var bot = Math.min(ia, ib);
                var top = Math.max(ia, ib);

                for (var i = bot; i <= top; i++) {
                    $('.js-field-select-'+type+' tbody tr').eq(i).addClass('warning');
                    $('.js-field-select-'+type+' tbody tr').eq(i).css('background','#fcf8e3');
                }

            } else {
                if($(this).hasClass('warning')) {
                    $(this).removeClass('warning');
                    $(this).css('background','#ffffff');
                } else {
                    $(this).addClass('warning');
                    $(this).css('background','#fcf8e3');
                }
            }

            lastSelectedRow = $(this);

            if($(".js-field-select-"+type+" tr.warning").length == 0 ) {
                preRow = iciRow = '';
            }

        });

        // 리스트 키보드 이동
        $(document).keydown(function (event) {
            if (iciRow) {
                var type = $(iciRow).closest('table').data("type");
                console.log(type);

                var idx = (iciRow.index('.move-row') + 1);
                switch (event.keyCode) {
                    case 38:
                        iciRow.moveRow(-1);
                        break;
                    case 40:
                        iciRow.moveRow(1);
                        break;
                }
                return false;
            }
        });

        // 위/아래이동 버튼 이벤트
        $('.js-moverow').click(function(e){
            if (iciRow) {
                var idx = (iciRow.index('tr') + 1);
                switch ($(this).data('direction')) {
                    case 'up':
                        iciRow.moveRow(-1);
                        break;
                    case 'down':
                        iciRow.moveRow(1);
                        break;
                    case 'top':
                        iciRow.moveRow(-100);
                        break;
                    case 'bottom':
                        iciRow.moveRow(100);
                        break;
                }
            } else {
                alert('순서 변경을 원하시는 항목을 선택해주세요. 클릭해주세요.');
            }
        });


        $(".js-move-left").click(function(e){
            var type = $(this).data("type");
            var fieldDefault = "js-field-default-"+type;
            if($("."+fieldDefault+" tr.default_select").length == 0 ) {
                alert("이동할 항목을 선택해주세요.");
                return false;
            }

            var checkCnt = 0;

            $("."+fieldDefault+" tr.default_select").each(function () {

                var key = $(this).data('field-key');
                var name = $(this).data('field-name');
                var sort = $(this).data('sort');
                if($(".js-field-select-"+type+" .select_field_"+sort).length ==0) {
                    $(".js-field-select-"+type+" tbody").append("<tr class='move-row-"+type+"  select_field_"+sort+"' data-sort='"+sort+"' data-field-key='"+key+"'  data-field-name='"+name+"' ><td>"+name+"<input type='hidden' name='goodsDisplayField["+type+"][]' value='"+key+"'/></td></tr>");
                } else {
                    checkCnt++;
                }

                $("."+fieldDefault+" tr.default_field_"+sort).removeClass('default_select');
                $("."+fieldDefault+" tr.default_field_"+sort).css('background','#ffffff');

            });

            if(checkCnt > 0 ) {
                alert("중복된 항목은 추가 되지 않습니다.");
            }

            return false;

        });


        $(".js-move-left-all").click(function(e){

            var checkCnt = 0;
            var type = $(this).data("type");
            var fieldDefault = "js-field-default-"+type;
            $("."+fieldDefault+" tr").each(function () {

                var key = $(this).data('field-key');
                var name = $(this).data('field-name');
                var sort = $(this).data('sort');
                if($(".js-field-select-"+type+" .select_field_"+sort).length ==0) {
                    $(".js-field-select-"+type+" tbody").append("<tr class='move-row-"+type+" select_field_"+sort+"' data-sort='"+sort+"' data-field-key='"+key+"'  data-field-name='"+name+"' ><td>"+name+"<input type='hidden' name='goodsDisplayField["+type+"][]'  value='"+key+"'/></td></tr>");
                } else {
                    checkCnt++;
                }

                $("."+fieldDefault+" tr.default_field_"+sort).removeClass('default_select');
                $("."+fieldDefault+" tr.default_field_"+sort).css('background','#ffffff');
            });

            if(checkCnt > 0 ) {
                alert("중복된 항목은 추가 되지 않습니다.");
            }

            return false;
        });


        $(".js-move-right").click(function(e){
            var type = $(this).data("type");
            if($(".js-field-select-"+type+" tr.warning").length == 0 ) {
                alert("삭제할 항목을 선택해주세요.");
                return false;
            }

            $(".js-field-select-"+type+"  tr.warning").each(function () {
                var sort = $(this).data('sort');
                $(".js-field-select-"+type).find("tr.select_field_"+sort).remove();
            });

            $(".js-field-select-"+type).css("height","");

            return false;

        });

        $(".js-move-right-all").click(function(e){
            var type = $(this).data("type");
            $(".js-field-select-"+type+" tr").each(function () {
                var sort = $(this).data('sort');
                $(".js-field-select-"+type).find("tr.select_field_"+sort).remove();
            });

            return false;

        });

        $(".js-excel-filed-all").click(function(e){

            var addHtml = "";
            $(".js-field-select-"+$(this).data('type')+" tr").each(function (index) {

                if(index%5 ==0) addHtml +="<p style='width:80%'>";

                var key = $(this).data('field-key');
                var name = $(this).data('field-name');
                var sort = $(this).data('sort');
                addHtml += "<span style='width:20%;display:inline-block'>"+(index+1)+"."+name+"</span>";

                if(index%5 ==4) addHtml +="</p>";

            });

            $(".js-excel-field-"+$(this).data('type')).html(addHtml);

            return false;

        });



        var lastDefaultRow;

        $('.js-field-default-pc,.js-field-default-mobile').on('click', 'tr', function (event) {
            var fieldDefault = $(this).closest('table').attr("class");
            var type = $(this).closest('table').data("type");

            $(".js-field-select-"+type+" tbody tr").siblings().each(function () {
                $(this).removeClass('warning');
            });
            preRow = iciRow = '';

            if (event.shiftKey) {

                var ia = lastDefaultRow.index();
                var ib = $(this).index();

                var bot = Math.min(ia, ib);
                var top = Math.max(ia, ib);
                console.log(bot);
                console.log(bot);
                console.log(fieldDefault);

                for (var i = bot; i <= top; i++) {
                    $('.js-field-default-'+type+' tbody tr').eq(i).addClass('default_select');
                    $('.js-field-default-'+type+' tbody tr').eq(i).css('background','#fcf8e3');
                }

            } else {
                if($(this).hasClass('default_select')) {
                    $(this).removeClass('default_select');
                    $(this).css('background','#ffffff');
                } else {
                    $(this).addClass('default_select');
                    $(this).css('background','#fcf8e3');
                }
            }

            lastDefaultRow = $(this);
        });

    });

    function select_field(target,sort) {

        $.post('display_config_ps.php', {'mode': 'select_goods_field', 'sort' : sort }, function (data) {

            var fieldList = $.parseJSON(data);
            var addHtml = "";

            if(fieldList) {
                var sort = 1;
                $.each(fieldList, function (key, val) {

                    addHtml += "<tr class='default_field_"+sort+"' data-sort='"+sort+"' data-field-key='"+key+"' data-field-name='"+val+"'><td>"+val+"</td></tr>";

                    sort++;
                });

                $(".js-field-default-"+target).html(addHtml);
            }
        });
    }


    //-->
</script>
