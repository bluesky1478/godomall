<style type="text/css">
    .jFiler-input-dragDrop { width:100%; margin-bottom:25px; }

    .fa-code { padding-left:13px; color:#595959; }
</style>

<div class="page-header js-affix">
    <h3>WebFTP
        <small>이미지와 HTML/CSS/JS, 웹폰트(eot, woff, ttf) 파일을 업로드하실 수 있습니다.</small>
    </h3>
    <button class="close" onclick="self.close();">×</button>
</div>

<?php if ($webftp->getSystemMessages()): ?>
    <?php foreach ($webftp->getSystemMessages() as $message): ?>
        <div class="alert alert-<?php echo $message['type']; ?>">
            <?php echo $message['text']; ?>
            <a class="close" data-dismiss="alert" href="#">&times;</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php $breadcrumbs = $webftp->listBreadcrumbs(); ?>
<ol class="breadcrumb mgb30">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php if ($breadcrumb != end($breadcrumbs)) { ?>
            <li><a href="<?php echo $breadcrumb['link']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } else { ?>
            <li><?php echo $breadcrumb['text']; ?></li>
        <?php } ?>
    <?php } ?>
</ol>

<div class="mgb30">
    <input id="filer" type="file" class="no-filestyle" name="file_data" multiple="multiple"/>
</div>

<div id="upload_progress"></div>

<div class="table-action mgb0">
    <div class="pull-left">
        <form action="popup_webftp_ps.php?" method="post" id="mkdir" class="form-inline">
            <label class="control-label" for="dirname">새폴더 만들기&nbsp;</label>
            <div class="input-group">
                <input id="dirname" type="text" name="name" class="form-control" value="" data-dirpath="<?= $currentDirectory ?>"/>
                <div class="input-group-btn">
                    <input type="submit" value="폴더생성" class="btn btn-gray btn-sm" style="background-color: #BCBCBC;"/>
                </div>
            </div>
        </form>
    </div>
    <div class="pull-right">
        <div class="input-group form-inline">
            <input type="text" class="form-control" id="fileSearch" name="fileSearch"/>
            <button type="button" class="btn btn-gray btn-sm" id="btnSearch" data-target-id="fileSearch">검색</button>
        </div>
    </div>
</div>

<table id="fileList" class="table table-rows mgl0">
    <colgroup>
        <col/>
        <col class="width-sm"/>
        <col class="width-md"/>
        <col class="width-md"/>
        <col class="width-xl"/>
    </colgroup>
    <thead>
    <tr>
        <th>파일명</th>
        <th>크기</th>
        <th>권한</th>
        <th>최종수정일자</th>
        <th>처리</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($dirArray as $name => $fileInfo) { ?>
        <tr class="text-center" data-name="<?= $name; ?>" data-href="<?= $fileInfo['url_path']; ?>">
            <td data-sort="<?= $name; ?>" class="text-left">
                <?php if ($fileInfo['is_image']) { ?>
                    <a href="<?php echo $fileInfo['url_path']; ?>" class="btn btn-link <?= $fileInfo['icon_class']; ?>" data-title="<?= basename($fileInfo['file_path']) ?>" data-gallery="godomall5Image"
                       data-toggle="lightbox"><?= $name; ?></a>
                <?php } elseif ($fileInfo['is_html']) { ?>
                    <span class="<?= $fileInfo['icon_class']; ?>"><?= $name; ?></span>
                <?php } else { ?>
                    <a href="<?php echo $fileInfo['url_path']; ?>" class="btn btn-link <?= $fileInfo['icon_class']; ?>"><?= $name; ?></a>
                <?php } ?>
            </td>
            <td data-sort="<?= $fileInfo['file_size'] ?>" class="font-num"><?= $fileInfo['file_size']; ?></td>
            <td class="font-eng"><?= implode('+', $fileInfo['file_permission']); ?></td>
            <td data-sort="<?= $fileInfo['mod_time'] ?>" class="font-date"><?= $fileInfo['mod_time'] ?></td>
            <td>
                <?php if ($name != '..') { ?>
                    <button type="button" class="btn btn-sm btn-gray js-clipboard" title="주소복사하기" data-clipboard-text="/<?= $fileInfo['file_path'] ?>">주소복사</button>
                    <?php if (is_file($fileInfo['file_path'])) { ?>
                        <a href="popup_webftp_ps.php?mode=download&file=<?= $fileInfo['file_path'] ?>" class="btn btn-sm btn-white js-file-download">다운로드</a>
                    <?php } ?>
                    <button type="button" data-dirpath="<?= $currentDirectory ?>" data-file="<?= $fileInfo['file_path'] ?>" class="btn btn-sm btn-white js-file-rename">이름변경</button>
                    <button type="button" data-file="<?= $fileInfo['file_path'] ?>" class="btn btn-sm btn-white js-file-delete">삭제</button>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
    <tbody class="display-none">
    <tr>
        <td colspan="5" class="no-data">검색된 정보가 없습니다.</td>
    </tr>
    </tbody>
</table>

<script type="text/javascript">
    // 테이블 정렬 플러그인
    (function ($) {
        $.fn.tablesorter = function () {
            var $table = this;
            this.find('th').click(function () {
                var idx = $(this).index();
                var direction = $(this).hasClass('sort_asc');
                $table.tablesortby(idx, direction);
            });
            return this;
        };
        $.fn.tablesortby = function (idx, direction) {
            var $rows = this.find('tbody:eq(0) tr');

            function elementToVal(a) {
                var $a_elem = $(a).find('td:nth-child(' + (idx + 1) + ')');
                var a_val = $a_elem.attr('data-sort') || $a_elem.text();
                return (a_val == parseInt(a_val) ? parseInt(a_val) : a_val);
            }

            $rows.sort(function (a, b) {
                var a_val = elementToVal(a), b_val = elementToVal(b);
                return (a_val > b_val ? 1 : (a_val == b_val ? 0 : -1)) * (direction ? 1 : -1);
            });
            this.find('th').removeClass('sort_asc sort_desc');
            $(this).find('thead th:nth-child(' + (idx + 1) + ')').addClass(direction ? 'sort_desc' : 'sort_asc');
            for (var i = 0; i < $rows.length; i++)
                this.append($rows[i]);
            this.settablesortmarkers();
            return this;
        };
        $.fn.retablesort = function () {
            var $e = this.find('thead th.sort_asc, thead th.sort_desc');
            if ($e.length)
                this.tablesortby($e.index(), $e.hasClass('sort_desc'));

            return this;
        };
        $.fn.settablesortmarkers = function () {
            this.find('thead th span.indicator').remove();
            this.find('thead th.sort_asc').append('<span class="indicator">&darr;<span>');
            this.find('thead th.sort_desc').append('<span class="indicator">&uarr;<span>');
            return this;
        }
    })(jQuery);

    $(function () {
        // 테이블 정렬
        $('#fileList').tablesorter();

        // 라이트박스
        $(document).on('click', '*[data-toggle="lightbox"]', function (event) {
            event.preventDefault();
            $(this).ekkoLightbox();
        });

        var $btn_search = $('#btnSearch');
        var $search_text = $('#' + $btn_search.data('target-id'));

        /**
         * 검색함수
         */
        function search_file() {
            var $fileList = $('#fileList');
            $fileList.find('tbody:eq(0)').removeClass('display-none');
            $fileList.find('tbody:eq(1)').addClass('display-none');
            var $file_rows = $fileList.find('tbody:eq(0) tr:not(:first)');
            var search_text = $search_text.val();
            try {
                $.each($file_rows, function (idx, item) {
                    var $item = $(item);
                    $item.removeClass('display-none');
                    var name = $item.data('name') + '';     // 문자열이 아닌 경우 아래 함수를 사용할 수 없기 때문에 문자열 변환함.
                    if (name.toUpperCase().indexOf(search_text.toUpperCase()) == -1) {
                        $item.addClass('display-none');
                    }
                    var $child = $(item).find('td:first a.fa-folder,a.fa-picture-o,span.fa-code');
                    $child.html(name);
                    var child_html = $child.text();
                    $child.html(exec_search(child_html, search_text));
                });
                if ($fileList.find('tbody:eq(0) tr.text-center:not(.display-none)').length == 1) {
                    $fileList.find('tbody:eq(0)').addClass('display-none');
                    $fileList.find('tbody:eq(1)').removeClass('display-none');
                }
            } catch (e) {
                console.log(e);
            }
        }

        /**
         * 치환대상에서 검색어를 찾아서 치환 및 하이라이트 처리
         * @param html 치환대상
         * @param search 검색어
         * @returns {string} 치환된 결과
         */
        function exec_search(html, search) {
            var regexp = new RegExp(search, 'i');
            var result = '';
            var breakCnt = 0;
            if (search == '') {
                return html;
            }
            while (html != '' && breakCnt < 10) {
                var exec = regexp.exec(html);
                if (exec == null) {
                    result += html.substring(0, html.length);
                    break;
                }
                if (exec.index > 0) {
                    result += html.substring(0, exec.index);
                    result += append_highlight(exec[0]);
                    html = html.substring(exec.index + exec[0].length, html.length);
                } else {
                    result += append_highlight(exec[0]);
                    html = html.substring(exec[0].length, html.length);
                }
                breakCnt++;
            }
            return result;
        }

        /**
         * 검색된 검색어에 하이라이트 표시 추가
         * @param text 검색어
         * @returns {string} 하이라이트처리된 html
         */
        function append_highlight(text) {
            return '<span style="background-color:#ffff00; color:#000000;">' + text + '</span>';
        }

        // 검색 버튼 이벤트
        $('body').on('click', $btn_search, search_file);
        $('body').on('keydown', $search_text, function (e) {
            if (e.keyCode === 13) {
                search_file();
            }
        });

        // 폴더 만들기
        $('#mkdir').submit(function (e) {
            var $dir = $(this).find('[name=name]');
            if ($dir.val() == '') {
                alert('폴더명을 입력해주세요.');
                return false;
            }
            e.preventDefault();
            var params = {
                mode: 'mkdir',
                name: $dir.val(),
                file: $dir.data('dirpath')
            };
            $dir.val().length && $.post('popup_webftp_ps.php?', params, function (data) {
                if (data.error == 0) {
                    window.location.reload(true);
                }
            }, 'json');
            $dir.val('');
            return false;
        });

        // 파일 삭제하기
        $('.js-file-delete').on('click', function (e) {
            var filename = $(this).data('file');
            BootstrapDialog.confirm({
                title: '파일 삭제하기',
                message: '"' + filename + '" 을(를) 정말로 삭제하시겠습니까?<br>삭제 후 복구하실 수 없습니다.',
                callback: function (result) {
                    if (result) {
                        var params = {
                            mode: 'delete',
                            file: filename
                        };
                        $.post("popup_webftp_ps.php", params, function (data) {
                            if (data.error == 0) {
                                window.location.reload(true);
                            } else {
                                alert(data.message);
                            }
                        }, 'json');
                    }
                }
            });
        });

        // 파일 이름변경
        $('.js-file-rename').on('click', function (e) {
            var $self = $(this);
            var filename = $(this).data('file');
            var compiled = _.template($('#fileRenameForm').html());
            var datas = {
                filename: filename
            };

            BootstrapDialog.show({
                nl2br: false,
                dialog: false,
                title: '파일 이름변경',
                message: compiled(datas),
                onshown: function (dialog) {
                    $('#frmRename').validate({
                        submitHandler: function (form) {
                            dialog.close();

                            var params = {
                                mode: 'rename',
                                file: $self.data('file'),
                                target: $self.data('dirpath') + '/' + $('input[name="target"]').val()
                            };
                            $.post("popup_webftp_ps.php", params, function (data) {
                                if (data.error == 0) {
                                    window.location.reload(true);
                                }
                            }, 'json');
                        },
                        rules: {
                            target: 'required'
                        },
                        messages: {
                            target: '변경하실 이름을 입력해주세요.'
                        }
                    });
                }
            });
        });

        // 파일 드래그앤 드롭 업로드
        // @see http://filer.grandesign.md/#documentation
        $("#filer").filer({
            limit: null,
            maxSize: <?=$maxUploadSize;?>, // 최대 업로드 사이즈 (M단위)
            extensions: <?=json_encode(USERDATA_UPLOADABLE_FILE)?>,
            changeInput: '<div class="jFiler-input-dragDrop"><div class="jFiler-input-inner"><div class="jFiler-input-icon"><i class="icon-jfi-cloud-up-o"></i></div><div class="jFiler-input-text"><h4>여기에 파일을 끌어다 놓거나 직접 선택하면 파일업로드가 완료됩니다.<br>파일명은 영문으로 작성하여 주시기 바랍니다.<br><small>(한글 파일명으로 업로드 시 이미지가 정상적으로 노출되지 않는 등 문제가 발생할 수 있습니다.)</small></h4> <span style="display:inline-block; margin: 10px 0"></span></div><a class="btn btn-lg btn-black">파일직접 선택</a></div></div>',
            showThumbs: true,
            theme: "dragdropbox",
            templates: {
                box: '<ul class="jFiler-items-list jFiler-items-grid"></ul>',
                item: '<li class="jFiler-item">\
                        <div class="jFiler-item-container">\
                            <div class="jFiler-item-inner">\
                                <div class="jFiler-item-thumb">\
                                    <div class="jFiler-item-status"></div>\
                                    <div class="jFiler-item-info">\
                                        <span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 25}}</b></span>\
                                        <span class="jFiler-item-others">{{fi-size2}}</span>\
                                    </div>\
                                    {{fi-image}}\
                                </div>\
                                <div class="jFiler-item-assets jFiler-row">\
                                    <ul class="list-inline pull-left">\
                                        <li>{{fi-progressBar}}</li>\
                                    </ul>\
                                    <ul class="list-inline pull-right">\
                                        <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                    </ul>\
                                </div>\
                            </div>\
                        </div>\
                    </li>',
                itemAppend: '<li class="jFiler-item">\
                            <div class="jFiler-item-container">\
                                <div class="jFiler-item-inner">\
                                    <div class="jFiler-item-thumb">\
                                        <div class="jFiler-item-status"></div>\
                                        <div class="jFiler-item-info">\
                                            <span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 25}}</b></span>\
                                            <span class="jFiler-item-others">{{fi-size2}}</span>\
                                        </div>\
                                        {{fi-image}}\
                                    </div>\
                                    <div class="jFiler-item-assets jFiler-row">\
                                        <ul class="list-inline pull-left">\
                                            <li><span class="jFiler-item-others">{{fi-icon}}</span></li>\
                                        </ul>\
                                        <ul class="list-inline pull-right">\
                                            <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                        </ul>\
                                    </div>\
                                </div>\
                            </div>\
                        </li>',
                progressBar: '<div class="bar"></div>',
                itemAppendToEnd: false,
                removeConfirmation: true,
                _selectors: {
                    list: '.jFiler-items-list',
                    item: '.jFiler-item',
                    progressBar: '.bar',
                    remove: '.jFiler-item-trash-action'
                }
            },
            dragDrop: {
                dragEnter: null,
                dragLeave: null,
                drop: null,
            },
            uploadFile: {
                url: "./popup_webftp_ps.php",
                data: {
                    mode: 'upload',
                    file: '<?=$currentDirectory?>'
                },
                type: 'POST',
                enctype: 'multipart/form-data',
                beforeSend: function () {
                },
                success: function (data, el) {
                    var parent = el.find(".jFiler-jProgressBar").parent();
                    el.find(".jFiler-jProgressBar").fadeOut("slow", function () {
                        $("<div class=\"jFiler-item-others text-success\"><i class=\"icon-jfi-check-circle\"></i> Success</div>").hide().appendTo(parent).fadeIn("slow");
                    });
                },
                error: function (el, l, p, o, s, cid, jqXHR) {
                    var parent = el.find(".jFiler-jProgressBar").parent();
                    el.find(".jFiler-jProgressBar").fadeOut("slow", function () {
                        $("<div class=\"jFiler-item-others text-error\"><i class=\"icon-jfi-minus-circle\"></i> Error : "+ jqXHR.responseText +"</div>").hide().appendTo(parent).fadeIn("slow");
                    });
                },
                statusCode: null,
                onProgress: null,
                onComplete: null
            },
            files: null,
            addMore: false,
            clipBoardPaste: true,
            excludeName: null,
            beforeRender: null,
            afterRender: null,
            beforeShow: null,
            beforeSelect: null,
            onSelect: null,
            afterShow: null,
            onRemove: function (itemEl, file, id, listEl, boxEl, newInputEl, inputEl) {
                var file = file.name;
                $.post('./php/remove_file.php', {file: file});
            },
            onEmpty: null,
            options: null,
            captions: {
                button: "파일선택",
                feedback: "업로드를 위해 파일을 선택해주세요.",
                feedback2: "파일을 선택해주세요.",
                drop: "파일을 이곳에 드래그하세요.",
                removeConfirmation: "Are you sure you want to remove this file?",
                errors: {
                    filesLimit: "{{fi-limit}}개까지 업로드 하실 수 있습니다.",
                    filesType: "이미지 혹은 스타일시트, 스크립트, HTML 파일만 업로드하실 수 있습니다.",
                    filesSize: "{{fi-name}} is too large! Please upload file up to {{fi-maxSize}} MB.",
                    filesSizeAll: "Files you've choosed are too large! Please upload files up to {{fi-maxSize}} MB."
                }
            }
        });
    });

    function formatFileSize(bytes) {
        var s = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB'];
        for (var pos = 0; bytes >= 1000; pos++, bytes /= 1024);
        var d = Math.round(bytes * 10);
        return pos ? [parseInt(d / 10), ".", d % 10, " ", s[pos]].join('') : bytes + ' bytes';
    }
</script>
<script type="text/template" id="fileRenameForm">
    <form id="frmRename">
        <table class="table table-cols">
            <tbody>
            <tr>
                <th>변경파일</th>
                <td><%=filename%></td>
            </tr>
            <tr>
                <th>변경후 파일</th>
                <td>
                    <input type="text" name="target"/>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="table-btn">
            <button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
            <button type="submit" class="btn btn-lg btn-black">변경</button>
        </div>
    </form>
</script>
