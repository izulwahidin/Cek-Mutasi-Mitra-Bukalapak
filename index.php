<?php
require('./bl.php');

$bl = new MITRABUKALAPAK(
    'e882b6f7ebefxxxxxxxx', // your identity from bl
    'U70WtCmPO_xxxxxxxx', // your refresh token from bl
);


print_r($bl->mutasi());
