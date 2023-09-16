<?php 
	session_start();
	if($_SESSION['secure'] == strtolower($_POST['user_input'])){
        $_SESSION['validated'] = true;
        header("Location: /home");
	} 
	else{
      $attempts = $_SESSION['attempts'];
      $attempts = $attempts + 1;
      $_SESSION['attempts'] = $attempts;
	  header("Location: /");
	}
?>