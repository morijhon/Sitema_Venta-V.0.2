<?php

include("conexio2.php");


if(isset($_POST['send2'])){
	
	if(
	  strlen($_POST['name']) >= 1 &&
	  strlen($_POST['email']) >= 1 &&
	  strlen($_POST['phone']) >= 1 &&
	  strlen($_POST['messages']) >= 1 
	){
		$name = trim($_POST['name']);
		$password = trim($_POST['email']);
		$email = trim($_POST['phone']);
		$phone = trim($_POST['messages']);
	    $fecha = date("y/m/d");
		$consulta = "INSERT INTO datos(nombre,email,telefono,mensajes,fecha)
		             Values('$name','$email','$phone','$messages','$fecha')";
	    $resultado = mysqli_query($conex, $consulta);
		if($resultado){
			?>
			  <h3 class="success">Tu registro se a completado</h3)
			  
			<?php
		}else{
			
			?>
			<h3 class="error">Ocurrio un error</h3>
			<?php
		}
	}  else{
		?>
		<h3 class="error">LLena todos los campos</h3>
		<?php
	} 
}

?>