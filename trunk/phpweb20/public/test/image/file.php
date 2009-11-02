<?php

require_once 'image.php';

if (isset($_POST['submit']))
{
	$imagepath = 'D:/www/phpweb20/public/test/image/' . $_FILES['image']['name'];
	move_uploaded_file($_FILES['image']['tmp_name'], $imagepath);
	$image = new Image($imagepath);
	$image->thumb(400, 400);
	$image->watermark();
}

?>

<form action="" method="post" enctype="multipart/form-data">
	<input type="file" name="image" /><br />
	<input type="submit" name="submit" value="Submit!" />
</form>