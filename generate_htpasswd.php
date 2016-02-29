<?php
	error_reporting(E_ALL);

	if (isset($_POST['htp']) && !empty($_POST['htp']))
	{
		if (file_put_contents('.htpasswd', $_POST['htp']) > 0)
			echo "Ok, done.";
		else
			echo "failed?";
	}
	else
	{
		echo '<html><body><form action="generate_htpasswd.php" method="POST">'
			.'Enter .htpasswd content:<br /><textarea name="htp" cols="40" rows="6"></textarea>'
			.'<br /><input type="submit">'
			.'</form></body></html>';
	}
?>
	