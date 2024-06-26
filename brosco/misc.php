<?php

require(dirname(__FILE__) . '/../config/config.inc.php');
require_once(dirname(__FILE__) . '/../init.php');

$a = ToolsCore::passwdGen(30);

?>


<?= $a ?>
