<?php
/* @var $this \C\View\ConcreteContext */
/* @var $url string */
/* @var $target string */
/* @var $id string */
?>
<script type="text/javascript">
    $.get('<?php echo $url; ?>?target=<?php echo $target; ?>', function(data){
        var receiver = $('#<?php echo $id; ?>');
        data = $("<div>"+data+"</div>").first();
        if (data.length) {
            if (data.first().is("c_block_node")) {
                data = data.children().unwrap();
            }
        }
        receiver.replaceWith(data);
        $(document).trigger('c_block_loaded', '#<?php echo $target; ?>')
    });
</script>
