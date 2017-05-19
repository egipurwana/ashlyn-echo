<html>
	<head>
		<title>Ashlyn - Training Field</title>
	</head>
	<body>
		<table class="table">
	    <thead>
	      <tr>
	        <th>#</th>
	        <th>First Name</th>
	        <th>Last Name</th>
	        <th>City</th>
	      </tr>
	    </thead>
	    <tbody>
	      <tr class="odd">
	        <td>1</td>
	        <td id="first_name:1" contenteditable="true">Karthikeyan</td>
	        <td id="last_name:1" contenteditable="true">K</td>
	        <td id="city:1" contenteditable="true">Chennai</td>
	      </tr>
	      <tr>
	        <td>2</td>
	        <td id="first_name:2" contenteditable="true">Facebook</td>
	        <td id="last_name:2" contenteditable="true">Inc</td>
	        <td id="city:2" contenteditable="true">California</td>
	      </tr>
	      <tr class="odd">
	        <td>3</td>
	        <td id="first_name:3" contenteditable="true">W3lessons</td>
	        <td id="last_name:3" contenteditable="true">Blog</td>
	        <td id="city:3" contenteditable="true">Chennai, India</td>
	      </tr>
	    </tbody>
	 </table>
	 <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
	 <script>
		 $(function(){
		    var message_status = $("#status");
		    $("td[contenteditable=true]").blur(function(){
		        var field_userid = $(this).attr("id") ;
		        var value = $(this).text() ;
		        $.post('ajax.php' , field_userid + "=" + value, function(data){
		            if(data != '')
					{
						message_status.show();
						message_status.text(data);
						setTimeout(function(){message_status.hide()},3000);
					}
		        });
		    });
		});
	 </script>
	 
	</body>
</html>