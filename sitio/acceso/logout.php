<?php
require '../../aplicacion/compartido/config.php';
session_destroy();
header('Location: /proyecto/index.php');
