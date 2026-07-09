<script src="<?php echo SITE_URL; ?>js//jquery.min.js"></script> 
<script src="<?php echo SITE_URL; ?>js/bootstrap.min.js"></script>

<script>
function subscribe()
	{
		var subscribe_email = document.getElementById("subscribe_email").value;

		
		
		 $.post("ajax.php",
		  {
			 action				:	"subscribemail",
			 subscribe_email	:	subscribe_email,   	 
		  },
		  function(data)
		  { 
			  document.getElementById('showsubscribeerror').innerHTML=data;
		  });
		
		
	}

</script>