/**
 * 모바일앱 상품
 **/

//옵션 가격 설정 적용 여부
var mobileapp_option_adjust = true;

$(document).ready(function(){
    // --- 상품 리스트 페이지 ---

    //등록일
    $(".mobileappDateSelector").click(function(){
        $('#mobileapp_searchDate1').val($(this).attr('data-interval'));
        $('#mobileapp_searchDate2').val($('#mobileapp_standardDate').val());
    });
    //등록일 자릿수 체크
    $("#mobileapp_searchDate1, #mobileapp_searchDate2").keypress(function(e){
        if($(this).val().length >= 8){
            e.preventDefault();
            return false;
        }
    });
    //상품 수정페이지, 모바일샵 페이지 이동
    $('#mobileapp_goodsListArea').on("click", "tr", function(event){
        if(event.target.className === 'gList-locationMobileGoodsView'){
            if($.trim($("body").attr('data-deviceUid')) === ''){
                alert("앱에서만 사용 가능한 기능입니다.");
                event.stopPropagation();
                return;
            }

            var mobile_goods_view_path = $(this).closest('#mobileapp_goodsListArea').attr('data-mobile-goods-view-path') + '?goodsNo=' + $(this).closest('.gList-contents-row').attr('data-goodsNo');
            if(navigator.userAgent.match(/android/ig)){
                var inAppOption = 'location=yes';
            }
            else {
                var inAppOption = 'location=no';
            }
            window.open(encodeURI(mobile_goods_view_path), '_blank', inAppOption);
        }
        else {
            window.location.href = './mobileapp_goods_register.php?mode=modify&goodsNo='+$(this).closest('.gList-contents-row').attr('data-goodsNo');
            event.preventDefault();
        }
        event.stopPropagation();
    });
    //검색
    $("#mobileapp_search").click(function(){
        if($.mobileapp_checkSearchDate() === false){
            return;
        }

        requestParameter = $.mobileapp_setRequestParameter($("#pageNum").val(), false);
        $.mobileapp_getGoodsList(requestParameter, true);
    });
    //페이지 노출 개수
    $("#pageNum").change(function(){
        requestParameter = $.mobileapp_setRequestParameter($(this).val(), false);
        $.mobileapp_getGoodsList(requestParameter, true);
    });
    //더보기
    $("#mobileapp_moreGoodsList").click(function(){
        requestParameter = $.mobileapp_setRequestParameter($("#pageNum").val(), true);
        $.mobileapp_getGoodsList(requestParameter, false);
    });
    //초기화
    $("#mobileapp_resetBtn").click(function(){
        $.mobileapp_resetSearch();
    });
    if($( "#mobileapp_search" ).length > 0){
        if($.mobileapp_storageLoad() === false){
            $.mobileapp_resetSearch();
            $( "#mobileapp_search" ).trigger( "click" );
        }
    }
    // --- 상품 리스트 페이지 ---




    // --- 상품 등록/수정 페이지 ---
    $("input[name='goodsNm']").keypress(function(e){
        var thisValue = $(this).val();
        if(thisValue.length >= 250){
            alert('상품명은 250자를 넘길 수 없습니다.');
            e.preventDefault();
            $(this).val(thisValue.substr(0, 250));
            return;
        }
    });

    //폼전송시 validation check
    $( "#mobileapp_goods_register_form" ).submit(function( event ) {
        var validation = false;
        validation = $.mobileapp_checkFormValidation();
        if(validation !== true){
            event.preventDefault();
            return;
        }

        $.showProgressBar();
    });

    //기본정보, 판매정보, 옵션정보 탭 디스플레이 조정
    $("#mobileapp_tab_default, #mobileapp_tab_sale, #mobileapp_tab_option").click(function(){
        $.mobileapp_displayTab($(this));
    });
    //자주쓰는 옵션 노출
    $("#mobileapp_add_option").click(function(){
        var param = {
            'scmNo' : '',
        }
        $.mobileapp_showLayer('mobileapp_layer_goods_option_list.php', '자주쓰는 옵션 선택', param);
    });

    //옵션 개수 변경시 이전값을 기억함
    $("#mobileapp_optionY_optionCnt").focus(function () {
        $.data(this, 'beforeValue', $(this).val());
    });

    //옵션 개수 변경에 따른 등록폼 노출
    $("#mobileapp_optionY_optionCnt").change(function(){
        var beforeValue = $.data(this, 'beforeValue');
        var thisValue = $(this).val();

        if(beforeValue){
            if (!confirm("입력된 옵션명, 옵션값이 초기화 됩니다.\n계속 진행하시겠습니까?")) {
                $(this).val($.data(this, 'beforeValue'));
                return;
            }

        }
        $.data(this, 'beforeValue', thisValue);

        if(!thisValue){
            mobileapp_option_adjust = true;
        }
        else {
            mobileapp_option_adjust = false;
        }

        $.mobileapp_changeOption('self', $(this).val(), []);
    });
    //옵션 가격설정 적용
    $(document).on("click", "#mobileapp_option_adjust", function() {
        if($(".gRegister-option-detail").length > 0){
            if(confirm("설정이 초기화 됩니다.\n계속 진행하시겠습니까?")){
                mobileapp_option_adjust = true;

                $.mobileapp_settingOption_self();
            }
        }
        else {
            mobileapp_option_adjust = true;

            $.mobileapp_settingOption_self();
        }
    });
    //[자주쓰는 옵션] - 선택 클릭시
    $(document).on("click", ".mobileapp_favoriteOption", function() {
        $.mobileapp_getOptionData($(this).attr('data-sno'));
    });
    //상세 옵션 설정 - 일괄적용
    $(document).on("click", ".gRegister-optionDetail-all-btn", function() {
        var param = {
            'detailMode' : 'all',
            'mode': $('#mobileapp_mode').val()
        };
        $.mobileapp_showLayer('mobileapp_layer_goods_option_detail.php', '옵션 가격/재고 상세 설정', param);
    });
    //상세 옵션 설정
    $(document).on("click", ".gRegister-optionDetail-btn", function() {
        var param = {
            'detailMode' : 'individual',
            'thisID' : $(this).attr('id'),
            'optionID' : $(this).attr('data-optionID'),
            'stockID' : $(this).attr('data-stockID'),
            'optionValue' : $("#"+$(this).attr('data-optionID')).val(),
            'stockValue' : $("#"+$(this).attr('data-stockID')).val(),
            'mode': $('#mobileapp_mode').val()
        };
        $.mobileapp_showLayer('mobileapp_layer_goods_option_detail.php', '옵션 가격/재고 상세 설정', param);
    });
    //옵션 가격/재고 상세 설정 저장
    $(document).on("click", "#mobileapp_saveDetailOption", function() {
        $.mobileapp_saveDetailOption();

        //저장 후 창 닫기
        $('.bootstrap-dialog-close-button').trigger('click');
    });
    //이미지 첨부 버튼
    $(".gRegister-inputFileBtn").click(function(){
        if($.trim($("body").attr('data-deviceUid')) === ''){
            alert("앱에서만 사용 가능한 기능입니다.");
            return;
        }

        var param = {
            'fileMode' : $(this).attr('id'),
        };
        $.mobileapp_showLayer('mobileapp_layer_goods_image.php', '상품사진 입력선택', param);
    });
    //수정일때 - 목록버튼
    $("#mobileapp_list_btn").click(function(){
        history.go(-1);
    });
    //수정 - 옵션 관련 이벤트 실행
    if($("#mobileapp_mode").val() === 'modify'){
        if($(".mobileapp_part_option").attr('data-realOptionCnt') > 0){
            $('.gRegister-display-add').show();
            $('.gRegister-option-input-area > .gRegister-option-selector').tagsinput();
            $('#mobileapp_detail_option_area').show();
        }
        else {
            $('.gRegister-display-add').hide();
        }
    }

    //옵션 사용여부에 따른 옵션 설정 노출여부
    if($(":radio[name='optionFl']").length > 0){
        $(":radio[name='optionFl']").click(function(){
            if($(this).val() === 'n'){
                mobileapp_option_adjust = true;
            }

            $.mobileapp_option_execute();
        });
        $.mobileapp_option_execute();
    }

    //옵션값 변경
    $(document).on("change", ".gRegister-optionName, .gRegister-option-selector", function() {
        mobileapp_option_adjust = false;
    });
});
