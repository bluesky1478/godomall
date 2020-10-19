<form id="frmExcelForm" name="frmExcelForm" action="./excel_form_ps.php" method="post">
    <input type="hidden" name="mode" value="<?php echo $data['mode']; ?>"/>
    <?php if ($data['mode'] == 'modify') { ?><input type="hidden" name="sno" value="<?php echo gd_isset($data['sno']); ?>" />
        <input type="hidden" name="defaultFl" value="<?php echo $data['defaultFl']; ?>"/>
    <?php } ?>



    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./excel_form_list.php');" />
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        <?php echo end($naviMenu->location); ?>
    </div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col/>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">다운로드 양식명</th>
            <td colspan="3">
                <div class="form-inline"><label title=""><input type="text" name="title" value="<?php echo gd_isset($data['title']); ?>"
                                                                class="form-control width-2xl js-maxlength js-type-korea" maxlength="30"/></label>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">메뉴 분류</th>
            <td><div class="form-inline">
                    <select name="menu" class="form-control width-xl" onchange="select_location(this.value)">
                        <option value="">선택</option>
                        <?php foreach($menuList as $k => $v) { ?>
                            <option value="<?=$k?>" <?php if($data['menu'] == $k || ($k == 'board' && $data['menu'] == 'plusreview')) { echo "selected='selected'"; }  ?>><?=$v?></option>
                        <?php } ?>
                    </select></div>
            </td>
            <th class="require">상세 항목</th>
            <td>
                <div class="form-inline">
                    <select name="location" class="form-control width-xl" onchange="select_field(false)">
                        <option value="">선택</option>
                    </select></div>
            </td>
        </tr>
        <tr>
            <th class="require">다운로드 항목</th>
            <td colspan="3">
                <span class="js-download-filed-select-text">메뉴분류 및 상세항목을 선택하세요.</span>
                <div class="js-download-filed-select">
                    <div style="width:300px;float:left"/>
                    <div class="table-action mgb0 mgt0" style="background:#fff;border:0px;">
                        <div class="pull-left">
                            <span class="item_other items">전체 항목</span>
                            <div class="item_order items" style="display:none;">
                                <select class="form-control" name="item" onchange="select_item_field()" >
                                    <option value="">전체 항목</option>
                                    <option value="order">주문서 항목</option>
                                    <option value="goods">상품 항목</option>
                                </select>
                            </div>
                        </div>
                        <div class="pull-right">
                            <select class="form-control" name="sort" onchange="select_field(false)" >
                                <option value="">기본순서</option>
                                <option value="desc">가나다순</option>
                                <option value="asc">가나다역순</option>
                                <option value="select-asc">선택 항목 위로</option>
                                <option value="select-desc">선택 항목 아래로</option>
                            </select>
                        </div>
                    </div>
                    <div class="js-field-select-wapper">
                        <table class="js-field-default">
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div  style="width:70px;float:left;text-align:center;padding-top:100px;">

                    <p><button class="btn btn-sm btn-white btn-icon-left js-move-left">추가</button></p>

                    <p><button class="btn btn-sm btn-white btn-icon-right js-move-right">삭제</button></p>

                    <p><button class="btn btn-sm btn-white btn-icon-left-all js-move-left-all" />전체<br/>추가</button></p>

                    <p><button class="btn btn-sm btn-white btn-icon-right-all js-move-right-all" />전체<br/>삭제</button></p>

                </div>
                <div style="width:300px;float:left;"/> <div class="table-action mgb0 mgt0" style="background:#fff;border:0px;">
                    <div class="pull-left">
                        선택항목 <input type="button" value="선택항목 전체보기" class="btn btn-sm btn-gray js-excel-filed-all" />
                    </div>
                    <div class="pull-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">
                                맨아래
                            </button>
                            <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">
                                아래
                            </button>
                            <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">
                                위
                            </button>

                            <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">
                                맨위
                            </button>
                        </div>
                    </div>
                </div>
                <div class="js-field-select-wapper">
                    <table class="js-field-select table table-rows" data-toggle="" data-use-row-attr-func="false" data-reorderable-rows="true">
                        <tbody>
                        </tbody>
                    </table>
                </div>
                </div>

                <div class="notice-info" style="clear:both">
                    <span class="js-desc-order" style="display:none">"<img src="/admin/gd_share/img/bl_required.png">" 표기된 주문서 항목만 선택하여 엑셀다운로드 시 주문서별 다운로드가 가능합니다.<br/></span>
                    Shift 버튼을 누른 상태에서 선택하면 여러 항목을 동시에 선택할 수 있습니다.
                </div>

                </div>

            </td>
        </tr>
        <tr class="js-download-filed-select">
            <th>선택항목<br/>전체보기</th>
            <td colspan="3" class="js-excel-field">
                [선택항목 전체보기] 버튼을 클릭하면 현재 선택된 다운로드 항목을 확인할 수 있습니다.
            </td>
        </tr>
    </table>
</form>


<style>
    .js-field-select-wapper {
        height:300px;
        overflow:scroll;
        overflow-x:hidden;
        border:1px solid #dddddd;
    }

    .js-field-default,  .js-field-select {
        width:100%;
    }

    .js-field-default td ,  .js-field-select td {
        border:1px solid #dddddd;
    }

    .select-item { color:#999; }

</style>


<script type="text/javascript">
    <!--

    // 리스트 클릭 활성/비활성화
    var iciRow = '';
    var preRow = '';

    $(document).ready(function () {


        $("#frmExcelForm").validate({
            submitHandler: function (form) {

                if($("input[name='excelField[]']").length == 0) {
                    alert('다운로드 항목은 최소 1개 이상 선택하셔야 합니다.');
                    return false;
                }

                form.target = 'ifrmProcess';
                form.submit();
            },
            // onclick: false, // <-- add this option
            rules: {
                title: {
                    required: true
                },
                menu: {
                    required: true
                },
                location: {
                    required: true
                }
            },
            messages: {
                title: {
                    required: "다운로드 양식명 입력하세요."
                },
                menu: {
                    required: "메뉴분류를 선택하세요"
                },
                location: {
                    required: "상세항목을 선택하세요"
                }
            }
        });

        <?php if($data['menu'] && $data['location']) { ?>
        select_location('<?=$data['menu']?>', true);
        <?php } else { ?>
        $(".js-download-filed-select").hide();
        <?php } ?>

        $("input.js-type-korea").bind('keyup', function () { //익스 11 한글 초중성분리 그래서 test후 replace
            var tmp = $(this).val();
            var pattern = /[^a-zA-Zㄱ-ㅎㅏ-ㅣ가-힣\u119E\u11A20-9!@#$%^_{}~,.]/;
            if (pattern.test(tmp)) {
                $(this).val(tmp.replace(/[^a-zA-Zㄱ-ㅎㅏ-ㅣ가-힣\u119E\u11A20-9!@#$%^_{}~,.]/g,''));
            }
        });

        $("input[name='title']").click(function(e){

            $(".js-field-select tbody tr").siblings().each(function () {
                $(this).removeClass('warning');
            });
            preRow = iciRow = '';

        });

        var lastSelectedRow = "";
        $(document).on('click', '.js-field-select tbody tr', function (event) {

            if (iciRow) preRow = iciRow;
            iciRow = $(this);

            if (event.shiftKey) {

                var ia = lastSelectedRow.index();
                var ib = $(this).index();

                var bot = Math.min(ia, ib);
                var top = Math.max(ia, ib);

                for (var i = bot; i <= top; i++) {
                    console.log($('.js-field-select tbody tr').eq(i).html());
                    $('.js-field-select tbody tr').eq(i).addClass('warning');
                    $('.js-field-select tbody tr').eq(i).css('background','#fcf8e3');
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

            if($(".js-field-select tr.warning").length == 0 ) {
                preRow = iciRow = '';
            }

        });

        // 리스트 키보드 이동
        $(document).keydown(function (event) {
            if (iciRow) {
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

            if($(".js-field-default tr.default_select").length == 0 ) {
                alert("이동할 항목을 선택해주세요.");
                return false;
            }

            var checkCnt = 0;

            $(".js-field-default tr.default_select").each(function () {

                var key = $(this).data('field-key');
                var name = $(this).data('field-name');
                var orderFl = $(this).data('field-order');
                var sort = $(this).data('sort');
                var check = true;

                $(".move-row").each(function () {
                    if(key == $(this).data("field-key")) {
                        checkCnt++;
                        check = false;
                    }
                });


                if (check == true) {
                    sort = $('.js-field-select tr').length + 1;
                    if(orderFl =='y') var fieldName = "<img src='/admin/gd_share/img/bl_required.png' style='padding-right: 5px'>"+name;
                    else var fieldName = name;
                    $(".js-field-select tbody").append("<tr class='move-row select_field_"+sort+"' data-sort='"+sort+"' data-field-key='"+key+"'  data-field-name='"+name+"' ><td style='padding:10px;'>"+fieldName+"<input type='hidden' name='excelField[]' value='"+key+"'/></td></tr>");

                    $(".js-field-default tr[data-field-key='" + key + "']").removeClass('default_select');
                    $(".js-field-default tr[data-field-key='" + key + "']").css('background', '#ffffff').addClass("select-item");
                }

            });

            if(checkCnt > 0 ) {
                alert("중복된 항목은 추가 되지 않습니다.");
            }

            return false;

        });


        $(".js-move-left-all").click(function(e){

            var checkCnt = 0;

            $(".js-field-default tr").each(function () {

                var key = $(this).data('field-key');
                var name = $(this).data('field-name');
                var sort = $(this).data('sort');
                var orderFl = $(this).data('field-order');
                var check = true;

                $(".move-row").each(function () {
                    if(key == $(this).data("field-key")) {
                        checkCnt++;
                        check = false;
                    }
                });

                if (check == true) {
                    sort = $('.js-field-select tr').length + 1;
                    if (orderFl == 'y') var fieldName = "<img src='/admin/gd_share/img/bl_required.png' style='padding-right: 5px'>" + name;
                    else var fieldName = name;
                    $(".js-field-select tbody").append("<tr class='move-row select_field_" + sort + "' data-sort='" + sort + "' data-field-key='" + key + "'  data-field-name='" + name + "' style='height:25px;' ><td style='padding:10px;'>" + fieldName + "<input type='hidden' name='excelField[]' value='" + key + "'/></td></tr>");

                    $(".js-field-default tr[data-field-key='" + key + "']").removeClass('default_select');
                    $(".js-field-default tr[data-field-key='" + key + "']").css('background', '#ffffff').addClass("select-item");
                }
            });

            if(checkCnt > 0 ) {
                alert("중복된 항목은 추가 되지 않습니다.");
            }

            return false;
        });


        $(".js-move-right").click(function(e){

            if($(".js-field-select tr.warning").length == 0 ) {
                alert("삭제할 항목을 선택해주세요.");
                return false;
            }

            $(".js-field-select tr.warning").each(function () {
                var sort = $(this).data('sort');
                var key = $(this).data('field-key');
                $(".js-field-select tr[data-field-key='" + key + "']").remove();
                $(".js-field-default tr[data-field-key='" + key + "']").removeClass("select-item");
                $(".js-field-default tr[data-field-key='" + key + "']").removeClass("select-item");
            });

            $(".js-field-select").css("height","");

            return false;

        });

        $(".js-move-right-all").click(function(e){

            $(".js-field-select tr").each(function () {
                var sort = $(this).data('sort');
                var key = $(this).data('field-key');
                $(".js-field-select tr[data-field-key='" + key + "']").remove();
                $(".js-field-default tr[data-field-key='" + key + "']").removeClass("select-item");
            });

            return false;

        });

        $(".js-excel-filed-all").click(function(e){

            var addHtml = "";
            $(".js-field-select tr").each(function (index) {

                if(index%5 ==0) addHtml +="<p style='width:80%'>";

                var key = $(this).data('field-key');
                var name = $(this).data('field-name');
                var sort = $(this).data('sort');
                addHtml += "<span style='width:20%;display:inline-block'>"+(index+1)+"."+name+"</span>";

                if(index%5 ==4) addHtml +="</p>";

            });

            $(".js-excel-field").html(addHtml);

            return false;

        });



        var lastDefaultRow;

        $('.js-field-default').on('click', 'tr', function (event) {

            $(".js-field-select tbody tr").siblings().each(function () {
                $(this).removeClass('warning').css('background','#fff');
            });
            preRow = iciRow = '';

            if (event.shiftKey) {

                var ia = lastDefaultRow.index();
                var ib = $(this).index();

                var bot = Math.min(ia, ib);
                var top = Math.max(ia, ib);

                for (var i = bot; i <= top; i++) {
                    $('.js-field-default tbody tr').eq(i).addClass('default_select');
                    $('.js-field-default tbody tr').eq(i).css('background','#fcf8e3');
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


    function select_location(val, chkFl) {

        $.post('excel_form_ps.php', {'mode': 'select_location', 'menu': val ,'displayFl' : 'y' }, function (data) {

            var locationList = $.parseJSON(data);

            var addHtml = "<option value=''>선택</option>";
            if(locationList.info) {
                $.each(locationList.info, function (key, val) {
                    addHtml += "<option value='" + key + "'>" + val+ "</option>";
                });
            }
            $('select[name="location"]').html(addHtml);

            <?php if($data['menu'] && $data['location']) { ?>
            $('select[name="location"]').val('<?=$data['location']?>');
            select_field(chkFl);
            <?php } ?>

            $(".items").hide();
            if(val == 'order') {
                $(".item_order").show();
                if($('select[name="item"]').val() == "") {
                    $('select[name="sort"]').append("<option value='order'>주문서 항목 위로</option><option value='goods'>상품 항목 위로</option>");
                }
            } else {
                $(".item_other").show();
                $("select[name='sort'] option[value='order']").remove();
                $("select[name='sort'] option[value='goods']").remove();
            }
        });

    }
    var now_location = "";

    function select_field(chkFl) {

        if(now_location != $('select[name="location"]').val()) {
            $(".js-field-select tbody tr").siblings().each(function () {
                $(this).removeClass('warning').css('background','#fff');
            });
            preRow = iciRow = '';

            $(".js-field-select tbody").html('');
            $(".js-excel-field").html('[선택항목 전체보기] 버튼을 클릭하면 현재 선택된 다운로드 항목을 확인할 수 있습니다.');
        }

        <?php if($data['menu'] && $data['location'] && $data['excelField']) { ?>
        var selectArr = [<?='"'.implode('","',$data['excelField']).'"'?>];
        var selectHtml = [];
        <?php } ?>

        var selectItemArr = [];
        $.each($(".move-row"), function () {
            selectItemArr.push($(this).attr("data-field-key"));
        });

        $.post('excel_form_ps.php', {'mode': 'select_field', 'menu':  $('select[name="menu"]').val() , 'location':  $('select[name="location"]').val(),'sort' : $('select[name="sort"]').val(),'item' : $('select[name="item"]').val(), 'select-item' : JSON.stringify(selectItemArr) }, function (data) {
            var fieldList = $.parseJSON(data);
            var addHtml = "";
            var mode = "<?php echo $data['mode']; ?>";
            if(fieldList.info) {
                var sort = 1;
                $.each(fieldList.info, function (key, val) {

                    if($('select[name="menu"]').val() =='order' && val.orderFl =='y') var fieldName = "<img src='/admin/gd_share/img/bl_required.png' style='padding-right: 5px'>"+val.name;
                    else  var fieldName = val.name;

                    <?php if($data['menu'] && $data['location'] && $data['excelField']) { ?>
                    if (chkFl == true) {
                        if($.inArray(key,selectArr) >= 0 ) {
                            selectHtml[$.inArray(key,selectArr)] ="<tr class='move-row select_field_"+sort+"' data-sort='"+sort+"' data-field-key='"+key+"'  data-field-name='"+val.name+"' ><td style='padding:10px;'>"+fieldName+"<input type='hidden' name='excelField[]' value='"+key+"'/></td></tr>";
                            val.selected = 'y';
                        }
                    }
                    <?php } ?>

                    addHtml += "<tr class='default_field_"+sort + (val.selected == 'y' ? ' select-item' : '') + "' data-sort='"+sort+"' data-field-key='"+key+"' data-field-name='"+val.name+"' data-field-order='"+val.orderFl+"'><td style='padding:10px;'>"+fieldName+"</td></tr>";

                    sort++;
                });
            }

            <?php if($data['menu'] && $data['location'] && $data['excelField']) { ?>
            if(selectHtml.length > 0 ) {
                $(".js-field-select tbody").html(selectHtml.join());
                $(".js-excel-filed-all").click();
            }
            <?php } ?>


            $(".js-field-default").html(addHtml);

            $(".js-download-filed-select-text").hide();
            $(".js-download-filed-select").show();

            if($('select[name="menu"]').val() =='order') $(".js-desc-order").show();
            else $(".js-desc-order").hide();

        });

        now_location = $('select[name="location"]').val();
    }

    function select_item_field() {
        var item = $("select[name=item]").val();
        if(item == 'order' || item == 'goods') {
            $("select[name='sort'] option[value='order']").remove();
            $("select[name='sort'] option[value='goods']").remove();
        } else {
            $('select[name="sort"]').append("<option value='order'>주문서 항목 위로</option><option value='goods'>상품 항목 위로</option>");
        }
        select_field(false);
    }

    //-->
</script>
