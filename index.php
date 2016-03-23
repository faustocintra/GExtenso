<?php

  error_reporting(E_ALL);

  require('GExtenso.php');
  
  try {

    for($i = 0; $i < 200; ++$i) {

      $valor = mt_rand(1, GExtenso::VALOR_MAXIMO);
      echo $valor, ': ', GExtenso::numero($valor), '<br /><br />';

      $valor = mt_rand(1, GExtenso::VALOR_MAXIMO);
      echo 'R$ ', $valor / 100, ': ', GExtenso::moeda($valor), '<br /><br />';
      
    }

  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
  

?>
