<table class="table table-rows">
    <colgroup>
        <col class="width-xs"/>
        <col/>
    </colgroup>
    <thead>
    <tr>
        <th>No</th>
        <th>회원등급명</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach (array_values($data) as $k => $v) {?>
        <tr>
            <td class="center"><?php echo count($data) - $k; ?></td>
            <td><?php echo $v; ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<p class="center">
    <button class="btn btn-black btn-lg js-layer-close">닫기</button>
</p>
