<script>
var resizefunc = [];
</script>
<script src="<?php echo SITE_URL; ?>admin/assets/js/final.js"></script>
 
<script type="text/javascript" src="<?php echo SITE_URL; ?>admin/assets/js/bootstrap-datepicker.min.js"></script>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>admin/assets/css/bootstrap-datepicker3.css"/>
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();   
});
</script>
<script>
$(document).ready(function() {
   	
	 $('#datatable').DataTable({});
	 $('#datatable1').DataTable({});


jQuery('.datepicker').datepicker({
			autoclose: true,
			format: "yyyy-mm-dd",
			todayHighlight: true
    });
});
</script>