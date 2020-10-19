/**
* 모바일앱 게시판
**/
$(document).ready(function(){
    $(document).on({
        ajaxStart: function() { $("#ajax_loading").show();  },
        ajaxError: function() { $("#ajax_loading").show();  },
        ajaxStop: function() { $("#ajax_loading").hide(); }
    });

    window.onerror = function() {
        $("#ajax_loading").hide();    // global error
    };

    if( /Android/i.test(navigator.userAgent)) {
        // 안드로이드
        $('.modal-center-board').css('height', '90%');
        $('.modal-body').css('max-height', screen.availHeight*0.4 + 'px');
    } else {
        // 그 외 디바이스
        //$('.modal-center-board').css('height', '70%');
        $('.modal-body').css('max-height', screen.availHeight*0.4 + 'px');
    }

    var page = 1;
    var pageNum = 10;
    var requestParameter = [];

    $.extend({
        mobileapp_setRequestParameter : function(pageNum, moreStatus)
        {
            // 더보기 클릭시
            if(moreStatus === true){
                var requestParameter = JSON.parse(sessionStorage.getItem("articleList_request"));
                page += 1;
                requestParameter['page'] = page;
                requestParameter['pageNum'] = pageNum;
            }
            else {
                page = 1;
                var requestParameter = {
                    mode : 'get_article_list',
                    bdId : $('#bdId').val(),
                    page : page,
                    pageNum : pageNum,
                    sort : 'b.regDt desc',
                    searchPeriod : -1,
                };
                sessionStorage.setItem("articleList_request", JSON.stringify(requestParameter));
            }

            return requestParameter;
        },

        mobileapp_getArticleList : function(requestParameter, listReset)
        {
            var $ajax = $.ajax('mobileapp_board_ajax.php', {
                method: "POST",
                data: requestParameter
            });
            $ajax.done(function (responseData) {
                // 글 리스트 리셋
                if(listReset === true){
                    $('#article_area').empty();
                }

                var totalCount = responseData.totalCount;
                var nowCount = responseData.nowCount;
                var listCount = $('#nowCount').val();
                var html = '';
                var bgColor = '';

                if(nowCount > 0){
                    //리스트 출력
                    $.each(responseData.list, function(Key, Val) {
                        listCount++;

                        html += '<tr id="boardArticleTr' + Val.sno + '" data-sno="' + Val.sno + '">';
                        html += '    <td style="height:28px; !important; ">';
                        html += '        <div style="margin-left: 10px;">';
                        html += '            <strong>';
                        if (Val.category) {
                            html += '[' + Val.category + '] ';
                        }
                        html += Val.subject + '</strong>';
                        html += '        </div>';
                        html += '    </td>';
                        html += '    <td id="viewIconTd' + Val.sno + '" style="border-top: 2px #e7e7e7;" rowspan="2"><span id="viewIcon' + Val.sno + '" class="pull-right" style="margin-left: 5px; margin-right: 10px;"><img src="/admin/gd_share/img/mobileapp/icon/arrow_open.png" width="15"><span></td>';
                        html += '</tr>';
                        html += '<tr id="boardArticleTr2' + Val.sno + '" data-sno="' + Val.sno + '">';
                        html += '    <td>';
                        html += '        <div class="overflow-h" style="margin-left: 10px;">';
                        html += '            <div class="pull-left" style="color: #666; ">' + Val.writer + ' | ' + Val.regDate + '</div>';
                        if (responseData.cfg.bdReplyStatusFl == 'y') {
                            if (Val.replyStatusText == '답변완료') {
                                html += '<div id="replyState' + Val.sno + '" class="pull-right" style="font-size:9px; color:red; width:50px; height:22px; text-align:center; line-height: 22px; border:solid red; border-width:1px 1px">' + Val.replyStatusText + '</div>';
                            } else {
                                html += '<div id="replyState' + Val.sno + '" class="pull-right" style="font-size:9px; width:50px; height:22px; text-align:center; line-height: 22px; border:solid black; border-width:1px 1px">' + Val.replyStatusText + '</div>';
                            }
                        } else if (responseData.cfg.bdGoodsPtFl == 'y') {
                            html += '<div class="rating-b pull-right mgt5" style="line-height: 24px;"><span style="width:' + Val.goodsPt*20 + '%;">별 다섯개중 다섯개</span></div>';
                        }
                        html += '        </div>';
                        html += '    </td>';
                        html += '</tr>';
                        html += '<tr id="viewSno' + Val.sno + '" style="display:none; background-color: #fafafa;">';
                        html += '    <td id="viewContent' + Val.sno + '" colspan="2">';
                        html += '    </td>';
                        html += '</tr>';
                        html += '<tr id="viewSno2' + Val.sno + '" style="display:none; background-color: #fafafa;">';
                        html += '    <td id="viewReply' + Val.sno + '" colspan="2">';
                        html += '    </td>';
                        html += '</tr>';
                        html += '<tr>';
                        html += '    <td id="viewReply2' + Val.sno + '" style="height:10px; !important; padding-top: 9px; border-bottom:1px solid #e7e7e7; !important;" colspan="2">';
                        html += '    </td>';
                        html += '</tr>';
                    });

                    $('#nowCount').val(listCount);

                    if ($('#nowCount').val() == totalCount) {
                        $('#moreDisplay').css('display', 'none');
                    }
                    else {
                        $('#moreDisplay').css('display', 'block');
                    }
                }
                else {
                    html = '<tr class="no-list"><td colspan="2" class="text-center text-muted">글이 존재하지 않습니다.</td></tr>';
                    $('#moreDisplay').css('display', 'none');
                }

                $('#article_area').append(html);
            });
        },
    });

    // 검색
    $("#getArticle").on('click', function(){
        $('#nowCount').val(0);
        requestParameter = $.mobileapp_setRequestParameter(10, false);
        $.mobileapp_getArticleList(requestParameter, true);
    });

    // 더보기
    $("#mobileapp_moreArticleList").on('click', function(){
        requestParameter = $.mobileapp_setRequestParameter(10, true);
        $.mobileapp_getArticleList(requestParameter, false);
    });

    // 글내용불러오기
    $(document).on('click', '#article_area tr', function(){
    //$('#article_area').delegate("tr", "click", function(){
        if ($(this).attr('data-sno') != undefined) {
            var sId = $(this).attr('data-sno');

            if ($('#viewSno' + sId).css('display') == 'none') {
                var $ajax = $.ajax('mobileapp_board_ajax.php', {
                    method: "POST",
                    data: {
                        mode: 'get_article_view',
                        bdId: $('#bdId').val(),
                        sno: sId,
                    }
                });
                $ajax.done(function (responseData) {
                    if (responseData.data) {
                        $('[id^="viewSno"]').each(function() {
                            $(this).css('display', 'none');
                        });

                        $('#viewContent' + sId).empty();
                        $('#orgContent').empty();
                        $('#answerSubject').val('');
                        $('#answerContents').val('');
                        $('#answerContents').html('');

                        var html = '';
                        if (responseData.cfg.goodsType == 'goods' && responseData.data.goodsData.goodsNo != undefined && responseData.data.auth.authView !== 'fail') {
                            html += '        <div id="goods_link' + responseData.data.goodsData.goodsNo + '" style="border: 1px solid #bfbfbf; margin: 5px 10px 5px 10px; padding: 5px 10px 5px 10px;">';
                            html += responseData.data.goodsData.goodsNm + ' | ' + responseData.data.goodsData.goodsPrice;
                            html += '<span class="pull-right"><strong>></strong><span></div>';
                            html += '<div style="word-break: break-all !important; margin: 0 10px 0 10px; font-size:13px;">' + responseData.data.workedContents + '</div>';
                        } else {
                            var addStyle = '';
                            if (responseData.data.auth.authView === 'fail') {
                                addStyle = 'text-align: center; padding-top: 10px;';
                            }
                            html += '<div style="word-break: break-all !important; margin: 10px 10px 0 10px; font-size:13px;' + addStyle +'">' + responseData.data.workedContents + '</div>';
                        }
                        html += '<div id="re_area' + sId + '">';
                        if (responseData.cfg.bdReplyStatusFl == 'y' && responseData.data.replyStatusText == '답변완료' && responseData.data.auth.authView !== 'fail') {
                            html += '<!--hr style="border:1px dotted black; margin-left: 10px; margin-top:1px; margin-bottom:1px"-->';
                            html += '<div style="word-break: break-all !important; margin: 13px 10px 0 17px;" id="subject' + sId + '">';
                            html += '<span><img src="/admin/gd_share/img/mobileapp/icon/icon_board_re.png" id="re_icon"></span> <strong>' + responseData.data.answerSubject + '</strong>';
                            html += '</div>';
                            html += '<div style="word-break: break-all !important; margin: 0 10px 0 30px; color: #666; ">' + responseData.data.answerWriter + ' | ' + responseData.data.answerModDt.substring(0, 10) + '</div>';
                            html += '<div style="word-break: break-all !important; margin: 0 10px 0 30px; font-size:13px;" id="workedContents' + sId + '">' + responseData.data.workedAnswerContents + '</div>';
                        }
                        html += '</div>';
                        html += '        <div class="container-default bView-footer-btn-area">';
                        html += '            <center>';
                        html += '            <div class="row">';
                        html += '                <div class="col-xs-8">';
                        if (responseData.cfg.bdReplyStatusFl == 'y') {
                            var replyModal = 'data-toggle="modal" data-target="#myModal"';
                            var replyBtnClass = 'btn btn-lg btn-info border-r-n';
                            if (responseData.data.auth.authReply === 'fail') {
                                replyModal = '';
                                replyBtnClass += ' js-deny-reply';
                            }
                            if (responseData.data.replyStatusText == '답변완료') {
                                html += '                    <button type="button" class="' + replyBtnClass +'" style="width:100%; background-color:#fa2828;" id="articleReplyBtn' + sId + '" ' + replyModal +'>답변수정</button>';
                            } else {
                                html += '                    <button type="button" class="' + replyBtnClass +'" style="width:100%; background-color:#fa2828;" id="articleReplyBtn' + sId + '" ' + replyModal +'>답변</button>';
                            }
                        }
                        html += '                </div>';
                        html += '                <div class="col-xs-4">';
                        html += '                    <button type="button" class="btn btn-lg btn-default-gray border-r-n" style="width:100%" id="articleDeleteBtn' + sId + '">삭제</button>';
                        html += '                </div>';
                        html += '            </div>';
                        html += '            </center>';
                        html += '        </div>';

                        var html2 = '';
                        html2 += '<table style="width:100%; border:0;">';
                        html2 += '    <tr>';
                        html2 += '        <td>';
                        html2 += '            <div class="pull-left"><strong>' + responseData.data.subject + '</strong></div>';
                        html2 += '        </td>';
                        html2 += '    </tr>';
                        html2 += '    <tr>';
                        html2 += '        <td>';
                        html2 += '            <div class="pull-left">' + responseData.data.writer + ' | ' + responseData.data.regDate.substring(0, 10) + '</div>';
                        if (responseData.cfg.bdReplyStatusFl == 'y') {
                            if (responseData.data.replyStatusText == '답변완료') {
                                html2 += '            <div class="pull-right mgt5" style="font-size:8px; color:red; width:50px; height:24px; text-align:center; line-height: 24px; border:solid red; border-width:1px 1px">' + responseData.data.replyStatusText + '</div>';
                            } else {
                                html2 += '            <div class="pull-right mgt5" style="font-size:8px; width:50px; height:24px; text-align:center; line-height: 24px; border:solid black; border-width:1px 1px">' + responseData.data.replyStatusText + '</div>';
                            }
                        } else if (responseData.cfg.bdGoodsPtFl == 'y') {
                            html2 += '            <div class="rating-b pull-right mgt10" style="line-height: 24px;"><span style="width:' + responseData.data.goodsPt*20 + '%;">별 다섯개중 다섯개</span></div>';
                        }
                        html2 += '        </td>';
                        html2 += '    </tr>';
                        html2 += '    <tr>';
                        html2 += '        <td>';
                        html2 += '            <div style="word-break: break-all !important; margin-top:10px;">' + responseData.data.workedContents + '</div>';
                        html2 += '        </td>';
                        html2 += '    </tr>';
                        html2 += '</table>';

                        $('#viewContent' + sId).append(html);
                        $('#viewContent' + sId + ' img').not('#re_icon').addClass('js-smart-img');
                        $('#orgContent').append(html2);
                        $('#orgContent img').not('#re_icon').addClass('js-smart-img');
                        $('#selectBoxReplyTemplate').html('<label for="select-opt">=선택없음=</label>' + responseData.templateListSelect);
                        var select = $('.select-opt');
                        select.change( function () {
                            var select_name = $(this).children('option:selected').text();
                            $(this).siblings('label').text(select_name);
                        });
                        if (responseData.cfg.bdReplyStatusFl == 'y' && responseData.data.replyStatusText != '접수') {
                            $('#answerSubject').val(responseData.data.answerSubject);
                            $('#answerContents').val(responseData.data.workedAnswerContentsMobile);
                            $('#answerContents').html(responseData.data.workedAnswerContentsMobile);
                        }
                        $('#replySno').val(sId);
                        $('#viewSno' + sId).css('display', '');
                        $('#viewIcon' + sId).html('<img src="/admin/gd_share/img/mobileapp/icon/arrow_close.png" width="15">');

                        $('#boardArticleTr' + sId).css('background-color', '#fafafa');
                        $('#boardArticleTr2' + sId).css('background-color', '#fafafa');
                        $('#viewReply2' + sId).css('background-color', '#fafafa');
                    } else {
                        $('#viewContent' + sId).empty();
                        $('#viewSno' + sId).css('display', 'none');
                        $('#boardArticleTr' + sId).css('background-color', '#ffffff');
                        $('#boardArticleTr2' + sId).css('background-color', '#ffffff');
                        $('#viewReply2' + sId).css('background-color', '#ffffff');
                    }
                });
            } else {
                $('#viewContent' + sId).empty();
                $('#viewSno' + sId).css('display', 'none');
                $('#viewIcon' + sId).html('<img src="/admin/gd_share/img/mobileapp/icon/arrow_open.png" width="15">');
                $('#boardArticleTr' + sId).css('background-color', '#ffffff');
                $('#boardArticleTr2' + sId).css('background-color', '#ffffff');
                $('#viewReply2' + sId).css('background-color', '#ffffff');
            }
        }
    });

    // 템플릿 내용 가져오기
    $(document).on('change', '#bdTemplateSno', function(){
        if($(this).val() == '' || $(this).val() == 0){
            return false;
        }

        var content = $('#answerContents').val();
        content = content.replace(/<[^>]*>/gi, '').replace('&nbsp;', '');
        if (content.length > 0) {
            if (!confirm('본문이 삭제되고 게시글양식 내용이 삽입됩니다. 진행하시겠습니까?')) {
                $('option:eq(0)', this).attr('selected', 'selected');
                $(this).siblings("label").text('=선택없음=');
                return false;
            }
        }

        var $ajax = $.ajax('mobileapp_board_ajax.php', {
            method: "POST",
            data: {
                mode: 'get_reply_form',
                sno: $(this).val(),
            }
        });
        $ajax.done(function (responseData) {
            $('#answerContents').html('');
            $('#answerContents').val('');
            $('#answerContents').html(responseData.contentsMobile);
            $('#answerContents').val(responseData.contentsMobile);
        });
    });

    // 답변저장/수정
    $('#saveReply').on('click', function(){
        if ($.trim($('#answerSubject').val()).length < 1) {
            alert('제목을 입력해주세요');
            $('#answerSubject').focus();
            return false;
        }
        if ($.trim($('#answerContents').val()).length < 1) {
            alert('내용을 입력해주세요');
            $('#answerContents').focus();
            return false;
        }

        var $ajax = $.ajax('mobileapp_board_ajax.php', {
            method: "POST",
            data: {
                bdId: $('#bdId').val(),
                mode: 'save_reply',
                sno: $('#replySno').val(),
                replyStatus: $('#replyStatus').val(),
                answerSubject: $('#answerSubject').val(),
                answerContents: $('#answerContents').val(),
            }
        });

        $ajax.done(function (responseData) {
            if(responseData.result === 'ok'){
                alert('저장되었습니다.');
                $('#re_area' + $('#replySno').val()).empty();
                var tempContents = $('#answerContents').val().replace(/\n/gi, '<br/>');
                var now = new Date();
                var year = now.getFullYear();
                var mon = (now.getMonth()+1)>9 ? ''+(now.getMonth()+1) : '0'+(now.getMonth()+1);
                var day = now.getDate()>9 ? ''+now.getDate() : '0'+now.getDate();
                var today = year + '.' + mon + '.' + day;

                var html = '';
                html += '<!--hr style="border:1px dotted black; margin-left: 10px; margin-top:1px; margin-bottom:1px"-->';
                html += '<div style="word-break: break-all !important; margin: 13px 10px 0 17px;" id="subject' + $('#replySno').val() + '">';
                html += '<span><img src="/admin/gd_share/img/mobileapp/icon/icon_board_re.png" id="re_icon"></span> <strong>' + $('#answerSubject').val() + '</strong>';
                html += '</div>';
                html += '<div style="word-break: break-all !important; margin: 0 10px 0 30px; color: #666; ">' + responseData.managerId + '(' + responseData.managerNm + ')' + ' | ' + today + '</div>';
                html += '<div style="word-break: break-all !important; margin: 0 10px 0 30px; font-size:13px;" id="workedContents' + $('#replySno').val() + '">' + tempContents + '</div>';
                $('#re_area' + $('#replySno').val()).append(html);

                $('#replyState' + $('#replySno').val()).css({color:"red", border:"solid red 1px"});
                $('#replyState' + $('#replySno').val()).html('답변완료');

                $('#myModal').modal('hide');
                //location.replace('/mobileapp/mobileapp_board_article.php?sno=' + $('#sno').val());
            }
            else {
                alert(responseData);
            }
        });
    });

    // 상품상세 링크
    $(document).on('click', '[id^="goods_link"]', function(){
        var tempId = $(this).attr('id');
        var sId = tempId.replace('goods_link', '');
        location.href = '/mobileapp/mobileapp_goods_register.php?mode=modify&goodsNo=' + sId;
    });
    // 글 삭제
    $(document).on('click', '[id^="articleDeleteBtn"]', function(){
        var tempId = $(this).attr('id');
        var sId = tempId.replace('articleDeleteBtn', '');

        if (!confirm('정말 삭제하시겠습니까?')) {
            return false;
        }

        var $ajax = $.ajax('mobileapp_board_ajax.php', {
            method: "POST",
            data: {
                bdId: $('#bdId').val(),
                mode: 'delete_article',
                sno: sId,
            }
        });

        $ajax.done(function (responseData) {
            if(responseData === 'ok'){
                alert('삭제되었습니다.');
                location.reload();
            }
            else {
                alert(responseData);
            }
        });
    });

    $(document).on('click', '[id^="articleReplyBtn"]', function(){
        if ($(this).hasClass('js-deny-reply') === false) {
            setTimeout(function () {
                $('.modal-body').scrollTop(0);
            }, 200);
        }
    });

    $(document).on('click', '.js-deny-reply', function() {
        alert('답변 권한이 없습니다.');
        return false;
    });

    var positionResetHandler = function () {
        var self = {},
            lastY = null;

        self.inputFieldFocusHandler = function () {
            lastY = window.scrollY;
        };

        self.inputFieldBlurHandler = function () {
            if (lastY) {
                window.scrollTo(0, lastY);
                lastY = null;
            }
        };

        return self;
    };

    var handler = positionResetHandler();

    $("input").bind("focus", handler.inputFieldFocusHandler);
    $("input").bind("blur", handler.inputFieldBlurHandler);

    $( "#getArticle" ).trigger( "click" );
});
