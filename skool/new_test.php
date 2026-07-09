 <?php
//allow remote access to this script, replace the * to your domain e.g http://www.example.com if you wish to recieve requests only from your server
header("Access-Control-Allow-Origin: *");
//rebuild form data
$postdata = http_build_query(
    array(
        'username' => isset($_POST["username"])? $_POST["username"]: $_GET["username"],
        'password' => isset($_POST["password"])?$_POST["password"]: $_GET["password"],
  'message' => isset($_POST["message"])?$_POST["message"]: $_GET["message"],
  'mobiles' => isset($_POST["mobiles"])?$_POST["mobiles"]: $_GET["mobiles"],
  'sender' => isset($_POST["sender"])?$_POST["sender"]: $_GET["sender"],
    )
);
//prepare a http post request
$opts = array('http' =>
    array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => $postdata
    )
);
//craete a stream to communicate with betasms api
$context  = stream_context_create($opts);
//get result from communication
$result = file_get_contents('http://login.betasms.com/api/', false, $context);
//return result to client, this will return the appropriate respond code
echo $result;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>BetaSMS API Form Method</title>
<style type="text/css">
body
{
 margin:0;
 padding:0;
 font:14px Arial, Helvetica, sans-serif;
}
form
{
 background-color:#F5F5F5;
 padding:30px;
 display:table;
 margin:10px auto;
 border-radius:5px;
}
form p
{
 padding:0;
 color:#464646;
}
form input,form textarea
{
 padding:8px 5px;
 width:100%;
 border:1px solid #ccc;
 border-radius:5px;
}
form input[type="submit"]
{
 width:auto;
 float:right;
 margin:5px 0 5px 5px;
 padding:10px 15px;
}
form input[type="submit"]:active
{
 font-weight:bold;
}
</style>
</head>
<body>
         <form method="post" action="">
               <p>Email/Username : </p>
                <input type="email" class="login-username" placeholder="email" name="username"/>
                <p>Password : </p>
                <input type="password" placeholder="password"  name="password"/>
            <p>Recipient(s) : ( example: 234803..., 234802...)</p>
                <input type="text"  placeholder="Recipients" name="mobiles"/>
             <p>Sender : (example : GIT-SMS) max length : <b>11</b></p>
                <input type="text"  placeholder="Sender" name="sender" maxlength="11"/>
             <p>Message : max length (<b id="chars-left-message">160</b>) </p>  
             <textarea  placeholder="Message" name="message" rows="7" maxlength="160"></textarea> 
             <input type="submit" value="Send"/>
          </form>
</body>
</html>