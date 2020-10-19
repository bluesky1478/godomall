<div class="order-grid-area">
    <form name="orderGridForm" id="orderGridForm" action="./order_ps.php" method="post" target="ifrmProcess">
        <input type="hidden" name="mode" value="get_order_admin_grid_list" />
        <input type="hidden" name="orderGridMode" value="<?=$orderGridMode?>" />

    <!-- 좌측 -->
    <div class="order-grid-left-area">
        <div class="order-grid-act-top">
            <span>전체 조회항목</span>
            <select class="form-control js-order-grid-sort" name="gridSort">
                <option value="">기본순서</option>
                <option value="desc">가나다순</option>
                <option value="asc">가나다역순</option>
            </select>
        </div>

        <div class="js-field-select-wapper">
            <table class="js-field-default">
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <!-- 좌측 -->

    <!-- 중간 -->
    <div class="order-grid-center-area">
        <p><button type="button" class="btn btn-sm btn-white btn-icon-left js-add">추가</button></p>
        <p><button type="button" class="btn btn-sm btn-white btn-icon-right js-remove">삭제</button></p>
    </div>
    <!-- 중간 -->

    <!-- 우측 -->
    <div class="order-grid-right-area">
        <div class="btn-group order-grid-act-top">
            <span>노출 조회항목</span>

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
        <div class="js-field-select-wapper">
            <table class="js-field-select table table-rows">
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <!-- 우측 -->
    <div class="order-grid-open-link-option-area">
        <table class="table-option">
            <tbody>
                <tr>
                    <th>주문상세창 노출 설정</th>
                    <td>
                        <select class="form-control open-link-option" name="openLinkOption">
                            <option value="newTab">주문상세창 탭으로 띄우기</option>
                            <option value="oneTab">주문상세창 하나의 탭으로 띄우기</option>
                            <option value="newWindow">주문상세창 새창으로 띄우기</option>
                            <option value="oneWindow">주문상세창 하나의 새창으로 띄우기</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="order-grie-bottom-info-area">
        <div class="notice-danger">노출 조회항목이 많은 경우 주문 검색 속도가 느려질 수 있습니다.</div>
        <div class="notice-info">Shift 버튼을 누른 상태에서 선택하면 여러 항목을 동시에 선택할 수 있습니다.</div>
    </div>

    <div class="order-grie-bottom-area">
        <input type="button" value="설정" class="btn btn-gray js-save" />
        <input type="button" value="취소" class="btn btn-white js-close" />
    </div>
    </form>
</div>

<style>
.order-grid-area { height: 470px; }
.order-grid-left-area,
.order-grid-right-area { width: 45%; float: left; }
.order-grid-center-area {
    width:9%;
    float: left;
    text-align:center;
    margin-top: 150px;
}
.order-grid-act-top {
    width: 100%;
    text-align: right;
    margin-bottom: 5px;
    float: right;
}
.order-grid-act-top select,
.order-grid-act-top .js-moverow { float: right; }
.order-grid-act-top span { float: left; line-height: 20px; }
.order-grie-bottom-info-area {
    float: left;
    text-align: left;
    width: 100%;
}
.order-grie-bottom-info-area>div{ margin-bottom: 3px !important; }
.order-grie-bottom-info-area>div:first-child{ margin-top: 3px; }
.order-grie-bottom-area {
    width: 100%;
    float: left;
    text-align: center;
    margin-top: 10px;
 }
/*주문 상세창 노출 옵션*/
.order-grid-open-link-option-area {
    float: left;
    text-align: left;
    width: 97%;
}
.table-option {
    width: 100%;
    border-top: 1px solid #cccccc;
    border-bottom: 1px solid #cccccc;
    margin: 5px 5px;
}
.table-option > tbody > tr > th {
    background-color: #F6F6F6;
    padding: 8px 8px;
}
.table-option > tbody > tr > td {
    float: left;
    padding: 8px 8px;
}
/*주문 상세창 노출 옵션*/
.js-field-select-wapper {
    height:300px;
    overflow:scroll;
    overflow-x:hidden;
    border:1px solid #dddddd;
}
.js-field-default{ width: 100%; }
.js-field-default td { border:1px solid #dddddd; }
.select-item { color:#999; }
</style>

<script type="text/javascript">
    <!--
    var iciRow = '';
    var preRow = '';

    $(document).ready(function () {
        //document 에 할당된 event 가 레이어 클로즈 후 다시 레이어 실행시 중복 이벤트 등록되어 초기화 해줌.
        $(document).off("click", ".js-field-default tbody tr");
        $(document).off("click", ".js-field-select tbody tr");
        $(document).off("keydown");

        $(".js-save").click(function(e){
            $("input[name='mode']").val('save_order_admin_grid_list');
            $("#orderGridForm").submit();
        });

        //기본리스트 클릭시
        var lastDefaultRow;
        $(document).on('click', '.js-field-default tbody tr', function (event) {
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
            }
            else {
                if($(this).hasClass('default_select')) {
                    $(this).removeClass('default_select');
                    $(this).css('background','#ffffff');
                }
                else {
                    $(this).addClass('default_select');
                    $(this).css('background','#fcf8e3');
                }
            }

            lastDefaultRow = $(this);
        });

        //선택 리스트 클릭시
        var lastSelectedRow = "";
        $(document).on('click', '.js-field-select tbody tr', function (event) {
            if (iciRow) {
                preRow = iciRow;
            }
            iciRow = $(this);

            if (event.shiftKey) {
                var ia = lastSelectedRow.index();
                var ib = $(this).index();

                var bot = Math.min(ia, ib);
                var top = Math.max(ia, ib);

                for (var i = bot; i <= top; i++) {
                    $('.js-field-select tbody tr').eq(i).addClass('warning');
                    $('.js-field-select tbody tr').eq(i).css('background','#fcf8e3');
                }
            }
            else {
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

        //추가
        $(".js-add").click(function(e){
            if($(".js-field-default tr.default_select").length == 0 ) {
                alert("이동할 항목을 선택해주세요.");
                return false;
            }

            var checkCnt = 0;
            $(".js-field-default tr.default_select").each(function () {
                var key = $(this).data("field-key");
                var check = true;

                $('.js-field-select tbody tr').each(function () {
                    if(key == $(this).data("field-key")) {
                        checkCnt++;
                        check = false;
                    }
                });

                if(check == true){
                    var newClone = $(this).clone();
                    newClone.append("<input type='hidden' name='orderGridList[]' value='"+$(this).data("field-key")+"' />");
                    newClone.removeClass('default_select');
                    newClone.addClass('move-row');
                    newClone.css('background','#ffffff');

                    $(".js-field-select tbody").append(newClone);
                }

                $(this).removeClass('default_select');
                $(this).css('background', '#ffffff').addClass("select-item");
            });

            if(checkCnt > 0 ) {
                alert("중복된 항목은 추가 되지 않습니다.");
            }

            return false;
        });

        //삭제
        $(".js-remove").click(function(e){
            if($(".js-field-select tr.warning").length == 0 ) {
                alert("삭제할 항목을 선택해주세요.");
                return false;
            }

            $(".js-field-select tbody tr.warning").each(function () {
                var key = $(this).data('field-key');
                $(this).remove();
                $(".js-field-default tr[data-field-key='" + key + "']").removeClass("select-item");
                $(".js-field-default tr[data-field-key='" + key + "']").removeClass("select-item");
            });

            $(".js-field-select").css("height","");
            return false;
        });

        // 위/아래이동 버튼 이벤트
        $('.js-moverow').click(function(e){
            if (iciRow) {
                switch ($(this).data('direction')) {
                    case 'up':
                        iciRow.moveRow(-1);
                        break;
                    case 'down':
                        if(!$(".js-field-select tr").last().hasClass('warning')){
                            iciRow.moveRow(1);
                        }
                        break;
                    case 'top':
                        iciRow.moveRow(-100);
                        break;
                    case 'bottom':
                        if(!$(".js-field-select tr").last().hasClass('warning')){
                            iciRow.moveRow(100);
                        }
                        break;
                }
            }
            else {
                alert('순서 변경을 원하시는 항목을 선택해주세요. 클릭해주세요.');
            }
        });

        $('.js-close').click(function(){
            $(document).off("keydown");

            layer_close();
        });

        $('div.bootstrap-dialog-close-button').click(function() {
            $(document).off("keydown");
        });

        // 리스트 키보드 이동
        $(document).keydown(function (event) {
            if (iciRow) {
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

        //전체 조회항목 진열 순서 변경
        $(".js-order-grid-sort").change(function(){
            $("input[name='mode']").val('get_grid_all_list_sort');
            $.post('order_ps.php', $("#orderGridForm").serialize(), function (data) {
                if(data){
                    var order_grid_data_all = $.parseJSON(data);
                    //전체리스트
                    if(order_grid_data_all){
                        var order_grid_data_select_values = $(".js-field-select>tbody>tr").map(function () {
                            return $(this).attr("data-field-key");
                        });

                        var contentsHtml = '';
                        $.each(order_grid_data_all, function (key, val) {
                            var addClass = '';
                            if($.inArray(key, order_grid_data_select_values) !== -1){
                                addClass = "select-item";
                            }
                            contentsHtml+= "<tr class='"+addClass+"' data-field-key='"+key+"'><td style='padding: 10px;'>";
                            <?php if($orderGridMode == 'view_refund') { // 주문상세-환불내역탭 일 경우 줄바꿈 치환
                            ?>
                            contentsHtml+= val.replace(/<br\s*[\/]?>/gi, '');
                            <?php } else { ?>
                            contentsHtml+= val;
                            <?php } ?>
                            contentsHtml+= "</td></tr>";
                        });
                        $(".js-field-default>tbody").html(contentsHtml);
                    }
                }
            });
        });

        $.post('order_ps.php', $("#orderGridForm").serialize(), function (data) {
            if(data){
                var order_grid_data = $.parseJSON(data);
                var order_grid_data_all = order_grid_data.all;
                var order_grid_data_select = order_grid_data.select;
                var order_grid_data_intersect= $.map(order_grid_data.intersect, function(value) {
                    return [value];
                });

                //전체리스트
                if(order_grid_data_all){
                    var contentsHtml = '';
                    $.each(order_grid_data_all, function (key, val) {
                        var addClass = '';
                        if($.inArray(key, order_grid_data_intersect) !== -1){
                            addClass = "select-item";
                        }
                        contentsHtml+= "<tr class='"+addClass+"' data-field-key='"+key+"'><td style='padding: 10px;'>";
                        <?php if($orderGridMode == 'view_refund') { // 주문상세-환불내역탭 일 경우 줄바꿈 치환
                        ?>
                        contentsHtml+= val.replace(/<br\s*[\/]?>/gi, '');
                        <?php } else { ?>
                        contentsHtml+= val;
                        <?php } ?>
                        contentsHtml+= "</td></tr>";
                    });
                    $(".js-field-default>tbody").html(contentsHtml);
                }
                //선택리스트
                if(order_grid_data_select){
                    var contentsHtml = '';
                    $.each(order_grid_data_select, function (key, val) {
                        if(key != 'openLinkOption') { //주문 상세창 열기 옵션 노출 금지
                            contentsHtml += "<tr class='move-row' data-field-key='" + key + "'><td style='padding: 10px;'>";
                            <?php if($orderGridMode == 'view_refund') { // 주문상세-환불내역탭 일 경우 줄바꿈 치환
                            ?>
                            contentsHtml+= val.replace(/<br\s*[\/]?>/gi, '');
                            <?php } else { ?>
                            contentsHtml+= val;
                            <?php } ?>
                            contentsHtml += "<input type='hidden' name='orderGridList[]' value='" + key + "' />";
                            contentsHtml += "</td></tr>";
                        }
                    });
                    //주문상세창 열기 옵션
                    if(order_grid_data_select['openLinkOption']) {
                        //저장된 값이 있으면 선택하기
                        $(".open-link-option").val(order_grid_data_select['openLinkOption']).attr("selected", "selected");
                    }else {
                        //default 로 newTab
                        $(".open-link-option").val('newTab').attr("selected", "selected");
                    }
                    $(".js-field-select>tbody").html(contentsHtml);
                }
            }
        });

        //주문 상세정보 페이지 조회항목설정 orderGridMode Arr
        var orderDetailPageArr = Array('view_order', 'view_cancel', 'view_exchange', 'view_exchangeCancel', 'view_exchangeAdd', 'view_back', 'view_refund');
        var orderGridModeVal = $("input[name=orderGridMode]").attr("value");

        if($.inArray(orderGridModeVal, orderDetailPageArr) >= 0) {
            //주문 상세정보 페이지 조회항목설정 주문상세창 열기 옵션 숨김 처리
            $('.order-grid-open-link-option-area').css('display', 'none');
            $('.order-grid-area').css('height', '400px');
        }
    });
    //-->
</script>
