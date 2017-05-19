<html>
	<head>
		<title>Ashlyn - Training Field</title>
		<style>
			body{
				margin: 0px;
				padding: 0px;
			}
			.table{
				width: 80%;
				margin-left: 10%;
				margin-right: 10%;
				text-align: left;
			}
		</style>
	</head>
	<body>
		<table class="table">
	    <thead>
	      <tr>
	        <th>#</th>
	        <th>Phrase</th>
	        <th>Answer</th>
	        <th>Active</th>
	      </tr>
	    </thead>
	    <tbody>
	      <tr class="odd">
	        <td>1</td>
	        <td id="first_name:1" contenteditable="true">Lagi apa?</td>
	        <td id="last_name:1" contenteditable="true">Lagi gagaro nih, garateul</td>
	        <td id="city:1" contenteditable="true">active</td>
	      </tr>
	      <tr>
	        <td>2</td>
	        <td id="first_name:2" contenteditable="true">hai</td>
	        <td id="last_name:2" contenteditable="true">hai juga ganteng</td>
	        <td id="city:2" contenteditable="true">active</td>
	      </tr>
	      <tr class="odd">
	        <td>3</td>
	        <td id="first_name:3" contenteditable="true">apa kabar?</td>
	        <td id="last_name:3" contenteditable="true">baik-baik saja</td>
	        <td id="city:3" contenteditable="true">active</td>
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

<!--
<?php
/*
if(!empty($_POST))
{
	//database settings
	include "db_config.php";
	foreach($_POST as $field_name => $val)
	{
		//clean post values
		$field_userid = strip_tags(trim($field_name));
		$val = strip_tags(trim(mysql_real_escape_string($val)));

		//from the fieldname:user_id we need to get user_id
		$split_data = explode(':', $field_userid);
		$user_id = $split_data[1];
		$field_name = $split_data[0];
		if(!empty($user_id) && !empty($field_name) && !empty($val))
		{
			//update the values
			mysql_query("UPDATE user_details SET $field_name = '$val' WHERE user_id = $user_id") or mysql_error();
			echo "Updated";
		} else {
			echo "Invalid Requests";
		}
	}
} else {
	echo "Invalid Requests";
}
*/
-->