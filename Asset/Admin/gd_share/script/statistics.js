/**
 * Created by atomyang on 2017-02-22
 */

function statistics_excel_download(excelName,targetId,data) {

    var $form = $('<form></form>');
    $form.attr('action', './excel_ps.php');
    $form.attr('method', 'post');
    $form.attr('target', 'ifrmProcess');
    $form.appendTo('body');

    var mode = $('<input type="hidden" name="mode" value="excel_download">');
    var target, targetTagNm;
    if (typeof excelName == 'undefined') excelName ="";
    if ($(".nav-tabs .active").length > 0 ) excelName += $(".nav-tabs .active").text().trim();
    var excel_name = $('<input type="hidden" name="excel_name" value="'+excelName+'">');

    if (typeof targetId == 'undefined') targetId = ".js-excel-data";
    if(data) {
        var html = "<style>td{mso-number-format:'\@';}</style><table border='1'>"+data+"</table>";
    } else {
        var headHtml = "";
        var rsideRowspan = $(targetId).find(".tui-grid-rside-area .tui-grid-head-area table tbody tr").length;

        $(targetId).find(".tui-grid-rside-area .tui-grid-head-area table tbody tr").each(function() {
            var lsideHeadHtml = $(targetId).find(".tui-grid-lside-area .tui-grid-head-area table tbody tr").eq($(this).index()).html();
            if(lsideHeadHtml) {
                if(rsideRowspan > 1 && $(this).index() =='0') lsideHeadHtml = lsideHeadHtml.replace(/rowspan="1"/gi, "rowspan='"+rsideRowspan+"'");
                headHtml +="<tr>"+lsideHeadHtml+$(this).html()+"</tr>";
            }
            else headHtml +="<tr>"+$(this).html()+"</tr>";
        });

        var bodyHtml = "";
        $(targetId).find(".tui-grid-lside-area .tui-grid-body-area table tbody tr").each(function() {
            var rsideBodyHtml = $(targetId).find(".tui-grid-rside-area .tui-grid-body-area table tbody tr").eq($(this).index()).html();
            bodyHtml +="<tr>"+$(this).html()+rsideBodyHtml+"</tr>";

            // 특수문자 처리
            var location = $('body').attr('class');
            location = location.split(' ');
            if (location[0] === 'statistics' && !_.isEmpty(location[1])) {
                switch (location[1]) {
                    case 'goods-cart':
                    case 'goods-sale-rank':
                    case 'goods-wish':
                        targetTagNm = 'goodsNm';
                        break;
                    case 'goods-search-word-rank':
                        targetTagNm = 'keyword';
                        break;
                    case 'goods-category':
                        targetTagNm = 'cateNm';
                        break;
                }
            }

            if (!_.isEmpty(targetTagNm)) {
                var targetText = $(targetId).find(".tui-grid-rside-area .tui-grid-body-area table tbody tr").eq($(this).index()).find('td[data-column-name="' + targetTagNm + '"] div').text();
                if (_.isEmpty(targetText)) {
                    targetText = $(this).find('td[data-column-name="' + targetTagNm + '"] div').text();
                }
                if (!_.isEmpty(targetText)) {
                    if (!_.isEmpty(target)) {
                        target += '^|^';
                    }
                    target += targetText;
                }
            }
        });

        var html = "<style>td{mso-number-format:'\@';}</style><table border='1'><tr>"+headHtml+"</tr>"+bodyHtml+"</table>";
    }

    var data = $('<input type="hidden" name="data" value="' + encodeURI(html) + '">');

    $form.append(mode).append(excel_name).append(data);
    if (!_.isEmpty(target)) {
        var replaceData = $('<input type="hidden" name="replaceData" value="' + encodeURI(target) + '">');
        $form.append(replaceData);
    }
    $form.submit();

}
