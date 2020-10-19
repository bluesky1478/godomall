/**
 * 관리자 패널 호출
 * @param string menuCode 현재 폴더명
 * @param string menuKey 현재 화일 키
 * @param string menuFile 현재 화일 명
 */
function adminPanelApiAjax(menuCode, menuKey, menuFile) {
    var params = {
        menuCode: menuCode,
        menuKey: menuKey,
        menuFile: menuFile,
    };
    $.ajax({
        method: 'POST',
        cache: false,
        url: '/share/admin_panel_api.php',
        data: params,
        async: true,
        dataType: 'json',
        success: function (data) {
            if (data === null || typeof data == 'undefined') {
                return;
            }
            $.each(data, function (index, value) {
                switch (index) {
                    case 'banner' :
                        $.each(value, function (idx, val) {
                            if ($("#panel_" + index + '_' + val.panelCode)) {
                                $("#panel_" + index + '_' + val.panelCode).append(val.panelData);
                            }
                        });
                        break;

                    case 'board' :
                        $.each(value, function (idx, val) {
                            if (val.panelData.indexOf('Client error') === -1) {
                                var panel = $("#panel_" + index + '_' + val.panelCode);
                                if (panel) {
                                    if (val.panelCode == 'eduAPI') {
                                        panel.html(edu_panel(val.panelData));
                                    } else {
                                        $("#panel_" + index + '_' + val.panelCode).html(add_new_mark(val.gdSharePath, val.panelData));
                                    }
                                }
                            }
                        });
                        break;

                    case 'link' :
                        $.each(value, function (idx, val) {
                            var html = '';
                            if ($("#panel_" + index + '_' + val.panelCode)) {
                                html = '<a href="' + val.panelData + '" target="_blank" class="btn btn-sm btn-link">더보기</a>';
                                $("#panel_" + index + '_' + val.panelCode).html(html);
                            }
                        });
                        break;

                    case 'customer' :
                        $.each(value, function (idx, val) {
                            var html = '';
                            if ($("#panel_" + index + '_' + val.panelCode)) {
                                html = '<span class="call">' + val.panelData.tel + '</span>' +
                                    '<table>' +
                                    '<tr><td>평일</td><td>' + val.panelData.text1 + '</td></tr>' +
                                    '<tr><td>토요일</td><td>' + val.panelData.text2 + '</td></tr>' +
                                    '</table>';
                                $("#panel_" + index + '_' + val.panelCode).html(html);
                            }
                        });
                        break;

                    case 'popup' :
                        $.each(value, function (idx, val) {
                            if ($("#panel_" + val.panelCode)) {
                                $("#panel_" + val.panelCode).html(val.panelData);
                            }
                        });

                        // 팝업 열지 않기
                        $.each($.cookie(), function (idx, val) {
                            var prefix = idx.split('_');
                            if (prefix[0] == 'adminPanel') {
                                var popupId = idx.replace('adminPanel_', '');
                                if ($('#' + popupId)) {
                                    $('#' + popupId).hide();
                                }
                            }
                        });
                        break;
                }
            });
        },
        error: function (data, text) {
            //alert('error : ' + text);
        }
    });

    if (params['menuCode'] == 'base' && params['menuFile'] == 'index') {
        $.ajax({
            method: 'POST',
            cache: false,
            url: '/share/layer_super_admin_security.php',
            data: params,
            async: true,
            dataType: 'text',
            success: function (data) {
                if (data === null || typeof data == 'undefined') {
                    return;
                }
                $("#panel_noticePanel").append(data);
            },
            error: function (data, text) {
                //alert('error : ' + text);
            }
        });
    }
}


/**
 * 관리자 패널 팝업창 Cookie 생성
 * @param string name 팝업창 이름 (코드_창종류)
 * @param int expireDay 쿠키 기간
 * @param object elemnt elemnt
 * @return
 */
function adminPanelCookie(name, expireDay, elemnt) {
    if (expireDay == '') {
        expireDay = 7;
    }

    var cookieName = 'adminPanel_' + name;

    $.cookie(cookieName, 'true', {expires: expireDay, path: '/'});
    setTimeout("$('#" + name + "').hide()");

    return;
}

function edu_panel(panel_data) {
    var result = [];
    var li_by_data = $(panel_data).find('li:lt(2)');
    try {
        result.push('<div class="edunews-items">');
        result.push('<ul>');
        $.each(li_by_data, function (idx, item) {
            var a_by_item = $(item).find('a');
            var img_by_item = $(item).find('img');
            result.push('<li><a href="' + a_by_item.attr('href') + '" target="_blank">');
            result.push('<div class="edunews-head">');
            if (img_by_item.length > 0) {
                result.push('<img src="' + img_by_item.attr('src') + '">');
            }
            result.push('</div>');
            result.push('<div class="edunews-body">');
            result.push('<div class="edunews-title">' + $(item).find('.edunews-title').text() + '</div>');
            result.push('<div class="edunews-date">' + $(item).find('.edunews-date').text() + '</div>');
            result.push('</div>');
            result.push('</a></li>');
        });
        result.push('</ul>');
        result.push('</div>');
    } catch (e) {
        return panel_data;
    }
    return result.join('');
}

function add_new_mark(gd_share_path, panel_data) {
    var li_by_mark = $(panel_data);
    var start = moment().subtract(7, 'days').format('YYYY-MM-DD');
    var end = moment().add(1, 'days').format('YYYY-MM-DD');
    $.each(li_by_mark, function (idx, item) {
        $.each($(item).find('li'), function (idx2, item2) {
            var date = $(item2).find('span').text();
            //console.log(start, end, date);
            var a_tag = $(item2).find('a');
            if (a_tag.text().length > 30) {
                a_tag.text(a_tag.text().str_cut(50));
            }
            if (moment(date).isBetween(start, end)) {
                a_tag.append(' <img src="' + gd_share_path + 'img/icon_new.png" alert="NEW" class="img-fix">');
            }
        });
    });
    return li_by_mark.html();
}
