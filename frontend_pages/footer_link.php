<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/colresizable/1.6.0/colResizable-1.6.min.js"></script>

<script src="../js/theme.js?v=<?php echo time(); ?>"></script>

<script>
    // Now '$' is defined because jQuery loaded first
    $(document).ready(function() {
        $("#rankingTable").colResizable({
            liveDrag: true,
            gripInnerHtml: "<div class='grip'></div>",
            draggingClass: "dragging"
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>