<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" href="style.css" type="text/css" media="screen, projection"/>
		<link rel="shortcut icon" type="image/ico" href="favicon_jdoe.png" />
		<script src="jquery-2.1.1.min.js" type="text/javascript"></script>
		<script type="text/javascript" language="javascript" src="jquery.dropdownPlain.js"></script>
		<title>CasperJS Automated Testing Unit</title>
	</head>
	<center>
	<body>
	<div id="mainContent">
<p>Welcome to the CasperJS Automated Testing Unit</p>
<a href="index.php"><img width="200" height="200" src="weill-med-cornell-clg.png" id="WCMC" title="ORDER"/></a>
<br>
<br>
  <button id="button_AJAX">Run CasperJS</button>
  <button id="button_STOP" onclick="myStopFunction()" style="display: none">Stop CasperJS</button>
	</div>
	<br>
	<div id="loading"></div>
<script type="text/javascript">
	$('#button_AJAX').click(function executecasperJS() {
	   $('#loading').html('<img src="rays.gif"><br><i>Web harvesting in progress; please wait for test results.</i>');	// Loading image
	  		$.ajax({	// Run ajax request
	        type: "GET",
	        dataType: "text",
	        url: "phporder.php",
			success: function (data) {        
	                $('#loading').html(data);
	        }	
	    });	
timeout = setTimeout(executecasperJS,1800000);	
});
    $("#button_AJAX").click(function() {$("#button_AJAX").text("CasperJS Executed");});
	$("#button_STOP").click(function() {$("#button_AJAX").text("Run CasperJS");});
	function myStopFunction() {
	    clearTimeout(timeout);
	} // Dropdown menu: http://css-tricks.com/simple-jquery-dropdowns/
	
    $("#button_AJAX").click(function(){
       $("#button_STOP").show();
     });
	 
     $("#button_STOP").click(function(){
        $("#button_STOP").hide();
      });
	
</script>
</div>
	<div id="page-wrap">
	        <ul class="dropdown"> 
        	<li><a href="#">CasperJS Logs</a>
        		<ul class="sub_menu">
        			 <li><a href="casperjs_log.txt" target="_blank">Testing Log</a></li>
        			 <li><a href="casperjs_error.txt" target="_blank">Error Log</a></li>
        </ul>
	</div>
<br>
<div style="position: fixed; bottom: 0; width:100%; text-align: center">
	<button id="serverinfo">Server Info</button>
	<p id="content" style="display: none">
		    <script type="text/javascript">
		      $("document").ready(function() {
		        getData();
		      });
		      function getData() {
		        $.ajax({
		          url: "serverinfo.php",							// the URL for the request
		          type: "GET",										// whether this is a POST or GET request
		          dataType : "text",								// the type of data we expect back
				  success: successFn,								// function to call for success
		          error: errorFn,									// function to call on an error
				  complete: function( xhr, status ) {			    // code to run regardless of success or failure
		            //console.log("The request is complete!");
		          }
		        });
		      }
		      function successFn(result) {
		        //console.log("Setting result");
		      	$("#content").append(result);
		      }
		      function errorFn(xhr, status, strErr) {
		        //console.log("There was an error!");
		      	$("#content").append(result);
		      }
		  </script>
	</p>

	<script>
	$( "#serverinfo" ).click(function() {
	  $( "p" ).slideToggle( "slow" );
	});
	</script>	
</div>
<div id="logout">
	<script language="JavaScript">
	//Logout check works for FF + Chrome, not Safari (try: WebKit Page Cache II – The unload Event)
	$(window).on('beforeunload', function() {
	    var x =logout();
	    return x;
	});
	function logout(){
	        jQuery.ajax({
		        type: "GET",
		        dataType: "text",
		        url: "logout.php",
				success: function (loggingout) {        
		        }	
	        });
	        
	}
	</script>
</div>
</center>
	</body>
</html>	
	