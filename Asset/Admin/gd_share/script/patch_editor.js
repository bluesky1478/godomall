var patchEditor = {
    value: '', orig1: '', orig2: '', dv: '', panes: 2, highlight: true, connect: null,
    leftSource: '',  //왼쪽 에디터소스
    rightSource: '',      //오른쪽 소스
    codeTextarea: '',
    target: '',
    dv: null,
    options: {
        mergeMode: true,
        collapse: false,
        allowEditingOriginals: false,
        readOnly: false,
        revertButtons : true,
        foldGutter: true,
        gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
    },
    toggleCollapse: function (obj) {
        this.create(
            {
                leftSource : this.getLeftSourceValue(),
                rightSource : this.getRightSourceValue(),
                options: {
                    collapse: !this.options.collapse
                }
            }
        )
        if (this.options.collapse) {
            $(obj).text('전체 보기');
        }
        else {
            $(obj).text('차이점만 보기');
        }
    },
    create: function (param) {
        if(typeof param == 'undefined') {
            param = [];
        }
        this.target = param.target ? param.target : this.target;
        this.leftSource = param.leftSource ? param.leftSource : this.leftSource;
        this.rightSource = param.rightSource ? param.rightSource  : this.rightSource;
        this.options = $.extend(true, this.options, param.options);
        this.exec();
    },
    setLeftSource: function (source) {
        this.leftSource = source;
        this.exec();
    },
    setRightSource: function (source) {
        this.rightSource = source;
        this.exec();
    },
    getLeftSourceValue : function(){
        if(this.options.mergeMode == true) {
            result =  this.dv.editor().getValue();
            //result =   result.replace(/&/g, "&amp;")
        }
        else {
            result =  this.dv.getValue();
        }
        return result;
    },
    getRightSourceValue : function(){
        return this.dv.rightOriginal().getValue()  ;
    },
    getLeftEditor : function(){
        return this.dv.editor();
    },
    getRightEditor : function(){
        return this.dv.rightOriginal();
    },
    exec: function () {

        $(this.target).empty();
        if (this.options.mergeMode == true) {
            this.dv = CodeMirror.MergeView(this.target, {
                value: this.leftSource,
                origLeft: this.panes == 3 ? this.orig1 : null,
                orig: this.rightSource,
                lineNumbers: true,
                mode: "text/html",
                highlightDifferences: this.highlight,
                connect: this.connect,
                collapseIdentical: this.options.collapse,
                indentUnit: 4,
                tabSize: 4,
                indentWithTabs: true,
                readOnly: this.options.readOnly,  //비교만
                allowEditingOriginals: this.options.allowEditingOriginals,   //오른쪽 타겟 활성화
                matchTags: true,
                revertButtons : this.options.revertButtons,
//            extraKeys: {"Alt-F": "findPersistent"}
            });
            $('.CodeMirror-merge').css('white-space', 'inherit');
            $('.CodeMirror-merge-pane').css('position', 'relative').css('width', '45%').css('white-space', 'inherit');
            $('.CodeMirror-merge-pane.CodeMirror-merge-pane-rightmost').after($('#diffHandler').html());
            $('.CodeMirror-merge-gap').css('height', '550px');
            $('.CodeMirror-merge-2pane').css('height', '550px');
        }
        else {
            if (document.getElementById("codeTextarea") == null) {
                codeTextarea = '<textarea id="codeTextarea" >' + this.leftSource.replace(/&/g, "&amp;") + '</textarea>';
                $(this.target).append(codeTextarea);
                //result.replace(/\r\n/g, "\n");
            }
            this.dv = CodeMirror.fromTextArea(document.getElementById("codeTextarea"), {
                lineNumbers: true,
                mode: "text/html",
                styleActiveLine: true,
                matchTags: true,
                matchBrackets: true,
            });
        }
        if (this.options.readOnly == true) {
            $('.CodeMirror-merge-pane:not(.CodeMirror-merge-pane-rightmost) .CodeMirror-code').css('background', '#eeeeee');  //left
        }

        if (this.options.allowEditingOriginals == false) {
            $('.CodeMirror-merge-pane.CodeMirror-merge-pane-rightmost .CodeMirror-code').css('background', '#eeeeee');  //right
        }

        $('.CodeMirror').css('height', '550px');
        $(this.target).css('height', '550px');
    },
    diffGo: function (mode) {
        if (mode == 'top') {
            this.dv.rightOriginal().setCursor(0, 0);
            return;
        }
        else if (mode == 'bottom') {
            this.dv.rightOriginal().setCursor(this.dv.rightOriginal().lastLine(), 0);
            return;
        }
        else if (mode == 'up') {
            var command = 'goPrevDiff';
        }
        else {
            var command = 'goNextDiff';
        }

        this.dv.rightOriginal().execCommand(command);
    }
}

function toggleDifferences() {
    dv.setShowDifferences(highlight = !highlight);
}


function mergeViewHeight(mergeView) {
    function editorHeight(editor) {
        if (!editor) return 0;
        return editor.getScrollInfo().height;
    }

    return Math.max(editorHeight(mergeView.leftOriginal()),
        editorHeight(mergeView.editor()),
        editorHeight(mergeView.rightOriginal()));
}
function resize(mergeView) {
    var height = mergeViewHeight(mergeView);
    for (; ;) {
        if (mergeView.leftOriginal())
            mergeView.leftOriginal().setSize(null, height);
        mergeView.editor().setSize(null, height);
        if (mergeView.rightOriginal())
            mergeView.rightOriginal().setSize(null, height);
        var newHeight = mergeViewHeight(mergeView);
        if (newHeight >= height) break;
        else height = newHeight;
    }
    mergeView.wrap.style.height = height + "px";
}
