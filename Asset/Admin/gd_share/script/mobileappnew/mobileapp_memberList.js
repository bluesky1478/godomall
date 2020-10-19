/**
* 모바일앱 회원리스트
**/
$(document).ready(function(){
    var page = 1;
    var pageNum = $("#pageNum").val();
    var requestParameter = [];
    var totalCount = 0;

    $.extend({
        showProgressBar : function()
        {
            var progressImgMarginTop = Math.round(($(window).height() - 128) / 2) + $(window).scrollTop();

            $("body").append('<div id="mobileapp_progressBar" style="position:absolute;top:0;left:0;background:#44515b;filter:alpha(opacity=80);opacity:0.8;width:100%;height:'+$('body').height()+'px;cursor:progress;z-index:100000;margin:0 auto;text-align: center;"><img src="/admin/gd_share/img/mobileapp/common/page-loader-bk.gif" border="0" style="margin-top:'+progressImgMarginTop+'px;" /></div>');
        },

        hideProgressBar : function()
        {
            $("#mobileapp_progressBar").remove();
        },

        setComma : function(str)
        {
            return String(str).replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
        },

        mobileapp_checkSearchDate : function()
        {
            var mobileapp_entryDt1 = $("#mobileapp_entryDt1");
            var mobileapp_entryDt2 = $("#mobileapp_entryDt2");

            if(mobileapp_entryDt1.val().length > 0) {
                if (mobileapp_entryDt1.val().length !== 8) {
                    alert("YYYYMMDD 형식으로 입력해 주세요.");
                    mobileapp_entryDt1.focus();
                    return false;
                }
            }
            if(mobileapp_entryDt2.val().length > 0){
                if(mobileapp_entryDt2.val().length !== 8){
                    alert("YYYYMMDD 형식으로 입력해 주세요.");
                    mobileapp_entryDt2.focus();
                    return false;
                }
            }

            return true;
        },

        mobileapp_checkDisplayMore : function()
        {
            var trLength = Number($('#mobileapp_memberListArea > tr').length);
            if(Number(totalCount) <= Number(trLength)){
                $("#mobileapp_moreMemberList").css('display', 'none');
            }
            else{
                $("#mobileapp_moreMemberList").css('display', '');
            }
        },

        mobileapp_setRequestParameter : function(pageNum, moreStatus)
        {
            //더보기 클릭시
            if(moreStatus === true){
                var requestParameter = JSON.parse(sessionStorage.getItem("memberList_request"));
                page += 1;
                requestParameter['page'] = page;
                requestParameter['pageNum'] = pageNum;
            }
            else {
                page = 1;
                var requestParameter = {
                    mode : 'get_member_list',
                    page : page,
                    pageNum : pageNum,
                    key : $('#mobileapp_key').val(),
                    keyword : $('#mobileapp_keyword').val(),
                    groupSno : $('#groupSno').val(),
                    entryDt : [$('#mobileapp_entryDt1').val(), $('#mobileapp_entryDt2').val()]
                };
                sessionStorage.setItem("memberList_request", JSON.stringify(requestParameter));
            }

            return requestParameter;
        },

        mobileapp_getMemberList : function(requestParameter, listReset)
        {
            var $ajax = $.ajax('mobileapp_ps.php', {
                method: "POST",
                data: requestParameter,
                beforeSend : function (){
                    $.showProgressBar();
                }
            });
            $ajax.done(function (responseData) {
                if(responseData.result !== 'ok'){
                    alert("로딩을 실패하였습니다.\n고객센터에 문의해 주세요.");
                    return;
                }
                //회원리스트 리셋
                if(listReset === true){
                    $('#mobileapp_memberListArea').empty();
                }

                totalCount = responseData.count;
                var html = '';

                $('#mobileapp_totalCount').html($.setComma(totalCount));
                if(totalCount > 0){
                    //리스트 출력
                    $.each(responseData.data.memberData, function(index, row){
                        var memberName = '<div style="text-decoration: underline; color: #0b97c4;">'+row.memNm+'</div>';
                        var memberID = '<div style="text-decoration: underline; color: grey;">'+row.memId+'</div>';

                        html += '<tr class="lists text-center mView-contents-row" data-memNo="'+row.memNo+'">';
                        html += '<td>' + memberName + memberID + '</td>';
                        html += '<td>'+responseData.data.groupData[row.groupSno]+'</td>';
                        html += '<td>'+row.entryDt.substring(0,10)+'</td>';
                        html += '</tr>';
                    });
                }
                else {
                    html = '<tr class="no-list"><td colspan="3" class="text-center text-muted">회원이 존재하지 않습니다.</td></tr>';
                }

                $('#mobileapp_memberListArea').append(html);

                $.mobileapp_checkDisplayMore();

                $.mobileapp_storageSave();
            });
            $ajax.always(function() {
                $.hideProgressBar();
            });
        },

        mobileapp_storageSave : function()
        {
            localStorage.setItem("mobileapp-member-list", $('#mobileapp_memberListArea').html());
            localStorage.setItem("mobileapp-member-list-totalCount", totalCount);
            localStorage.setItem("mobileapp-member-list-page", page);
            localStorage.setItem("mobileapp-goods-list-searchKeyText", $("#mobileapp_key").siblings('label').text());
            localStorage.setItem("mobileapp-goods-list-groupSnoText", $("#groupSno").siblings('label').text());
        },

        mobileapp_storageLoad : function()
        {
            var mobileapp_page = localStorage.getItem("mobileapp-page");
            var mobileapp_page_parameter_memNo =  localStorage.getItem("mobileapp-page-parameter-memNo");

            if(mobileapp_page === 'mobileapp_member_view.php' && mobileapp_page_parameter_memNo){
                //리스트 컨텐츠 복구
                $('#mobileapp_memberListArea').html(localStorage.getItem("mobileapp-member-list"));
                //카운트
                totalCount = localStorage.getItem("mobileapp-member-list-totalCount");
                $('#mobileapp_totalCount').html(totalCount);

                //페이지
                page = Number(localStorage.getItem("mobileapp-member-list-page"));
                //화면위치 조정
                var memNo = localStorage.getItem("mobileapp-page-parameter-memNo");
                if($.trim(memNo) !== ''){
                    var moveTargetEl = $(".mView-contents-row[data-memNo='"+memNo+"']");
                    if(moveTargetEl.length > 0){
                        $(window).scrollTop(moveTargetEl.offset().top - 45);
                    }
                }

                //검색유형
                $("#mobileapp_key").siblings('label').text(localStorage.getItem("mobileapp-goods-list-searchKeyText"));
                //회원그룹
                $("#groupSno").siblings('label').text(localStorage.getItem("mobileapp-goods-list-groupSnoText"));

                $.mobileapp_checkDisplayMore();

                return true;
            }

            return false;
        },

        //초기화
        mobileapp_resetSearch : function()
        {
            $('#mobileapp_keyword, input[name="entryDt[]"], #groupSno').val('');
            $('#mobileapp_key').val('all').attr("selected", "selected");
            $('#mobileapp_key').siblings('label').text($('#mobileapp_key option:first').text());
            $('#groupSno').siblings('label').text($('#groupSno option:first').text());
        },
    });

    //검색
    $("#mobileapp_search").click(function(){
        if($.mobileapp_checkSearchDate() === false){
            return;
        }
        requestParameter = $.mobileapp_setRequestParameter($("#pageNum").val(), false);
        $.mobileapp_getMemberList(requestParameter, true);
    });

    //페이지 노출 개수
    $("#pageNum").change(function(){
        requestParameter = $.mobileapp_setRequestParameter($(this).val(), false);
        $.mobileapp_getMemberList(requestParameter, true);
    });

    //더보기
    $("#mobileapp_moreMemberList").click(function(){
        requestParameter = $.mobileapp_setRequestParameter($("#pageNum").val(), true);
        $.mobileapp_getMemberList(requestParameter, false);
    });

    //회원 - 회원가입일 value
    $(".mobileappDateSelector").click(function(){
        $('#mobileapp_entryDt1').val($(this).attr('data-interval'));
        $('#mobileapp_entryDt2').val($('#mobileapp_standardDate').val());
    });

    //회원가입일 자릿수 체크
    $("#mobileapp_entryDt1, #mobileapp_entryDt2").keypress(function(e){
        if($(this).val().length >= 8){
            e.preventDefault();
            return false;
        }
    });

    //회원상세페이지 이동
    $('#mobileapp_memberListArea').delegate("tr", "click", function(){
        window.location.href = './mobileapp_member_view.php?memNo='+$(this).attr('data-memNo');
    });

    //초기화
    $("#mobileapp_resetBtn").click(function(){
        $.mobileapp_resetSearch();
    });

    if($.mobileapp_storageLoad() === false){
        $.mobileapp_resetSearch();

        $( "#mobileapp_search" ).trigger( "click" );
    }
});
