var boardUrl = {
    write: '../board/article_write.php?' + getUrlVars() + '&bdId={0}',
    view: '../board/article_view.php?' + getUrlVars() + '&bdId={0}&sno={1}',
    modify: '../board/article_write.php?' + getUrlVars() + '&bdId={0}&mode=modify&sno={1}',
    list: '../board/article_list.php?' + getUrlVars() + '&bdId={0}',
    reply: '../board/article_write.php?' + getUrlVars() + '&mode=reply&bdId={0}&sno={1}',
}
String.prototype.format = function () {
    var formatted = this;
    for (var arg in arguments) {
        formatted = formatted.replace("{" + arg + "}", arguments[arg]);
    }
    return formatted;
};

function getUrlVars(paramKey) {
    if (typeof paramKey == 'undefined') {
        paramKey = '';
    }
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    if (window.location.href.indexOf('?') < 0) {
        return '';
    }
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        key = hash[0];
        val = hash[1];
        if (paramKey != '') {
            if (key == paramKey) {
                return val;
            }
        }

        if (key == 'sno' || key == 'mode' || key == 'bdId') {
            continue;
        }

        vars.push(hashes[i]);
    }

    if (paramKey != '') {
        return '';
    }

    return vars.join('&');
}

function btnWrite(bdId) {
    location.href = boardUrl.write.format(bdId);
}

function btnDelete(bdId, sno, popupMode) {
    dialog_confirm('정말 삭제하시겠습니까?', function (result) {
            if (result) {
                $.ajax({
                    method: "POST",
                    url: "../board/article_ps.php",
                    data: {mode: 'delete', sno: sno, bdId: bdId , popupMode: popupMode},
                    dataType: 'text'
                }).success(function (data) {
                    if(popupMode == 'yes') {
                        dialog_alert(data, '알림');
                    } else {
                        $('body').append(data);
                        location.href = '../board/article_list.php?bdId=' + bdId;
                    }
                }).error(function (e) {
                    alert(e.responseText);
                });
            }
        }
    );


    return;
}

function btnView(bdId, sno) {
    location.href = boardUrl.view.format(bdId, sno, getUrlVars());
}

function btnReplyWrite(bdId, sno) {
    location.href = boardUrl.reply.format(bdId, sno, getUrlVars());
}

function btnList(bdId) {
    location.href = boardUrl.list.format(bdId,getUrlVars());
}

function btnModifyWrite(bdId, sno) {
    location.href = boardUrl.modify.format(bdId, sno);
}
