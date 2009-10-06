<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if ($_POST['submit'])
{
	$a = $_POST['a'];
	$b = $_POST['b'];
	echo 'a + b = ' . ($a + $b);
}
else
{
	echo 'please enter a and b';
}

?>
