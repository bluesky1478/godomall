<style>
    .code-title {clear:both; border:1px solid #000; padding:5px 10px;}
    .code-info {float:left; width:100%; padding:10px 0 10px 10px;}
    .code-info span.info {float:left; display:inline-block;}
    .code-info span.copy {float:right; display:inline-block;}
</style>
<div style="height:300px; overflow-y:auto;">
    <div class="code-title">치환코드</div>
    <div class="code-info">
        <span class="info"><?php echo nl2br(htmlspecialchars($data[0]->designCode)); ?></span>
        <span class="copy"><button type="button" title="치환코드 복사" class="btn btn-gray btn-sm js-clipboard-copy" data-clipboard-text="<?php echo nl2br(htmlspecialchars($data[0]->designCode)); ?>">코드복사</button></span>
    </div>
    <div class="code-title">설명</div>
    <div class="code-info"><?php echo htmlspecialchars($data[0]->designCodeInfo); ?></div>
    <div class="code-title">예제</div>
    <div class="code-info"><?php echo nl2br($data[0]->designCodeExample); ?></div>
</div>
<p class="center"><button class="btn btn-black btn-lg js-layer-close">확인</button></p>

<script type="text/javascript">
    if ($('.js-clipboard-copy').length) {
        // https://clipboardjs.com
        var clipboard = new Clipboard('.js-clipboard-copy');
        clipboard.on('success', function (e){
            var title = $(e.trigger).attr('title') == undefined ? '' : $(e.trigger).attr('title');
            alert('[' + title + '] 정보를 클립보드에 복사했습니다.\n<code>Ctrl+V</code>를 이용해서 사용하세요.');
            e.clearSelection();
        });
        clipboard.on('error', function (e) {
            console.error('Action:', e.action);
            console.error('Trigger:', e.trigger);
        });
    }
</script>
