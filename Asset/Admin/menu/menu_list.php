<div>
    <div class="page-header js-affix">
        <h3><?php echo $data['cateTitle']; ?> 관리 </h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red js-save-menu"/>
        </div>
    </div>

    <div class="col-xs-4">
        <div class="tree-wrap">
            <ul class="tree" id="treeRoot1"></ul>
        </div>
    </div>
    <div class="col-xs-8" style="border-right: 1px solid #CFCFCF">
        <div class="menuInfo" id="menuInfo"></div>
    </div>
</div>

<script language="JavaScript">
    var data = <?= $jsonData; ?>;

    var tree1 = new tui.component.Tree(data, {
        rootElement: document.getElementById('treeRoot1'),
        nodeDefaultState: 'closed',
        template: {
            internalNode:
            '<button type="button" class="{{toggleBtnClass}}">{{stateLabel}}</button>' +
            '<span class="{{textClass}}" data-number="{{number}}">({{number}}) {{text}}</span>' +
            '<ul class="{{subtreeClass}}">{{children}}</ul>',
            leafNode:
                '<span class="{{textClass}}" data-number="{{number}}">({{number}}) {{text}}</span>'
        }
    }).enableFeature('Checkbox', {
        checkboxClassName: 'tui-tree-checkbox'
    }).enableFeature('Selectable');

    $(function(){
        tree1.on('select', function(id){
            loadConfig($('#' + id).find('> .tui-tree-text').data('number'));
        });
        $('.js-save-menu').click(function () {
            $('#frmMenuConfig').submit();
        });

        loadConfig();
    });

    function loadConfig(menuNo) {
        $('#menuInfo').load("menu_config.php", { menuNo: menuNo }, function(response, status, xhr) {
            if (status == 'error') {
                console.log(msg + xhr.status+" "+xhr.statusText);
            }
        });
    }
</script>
