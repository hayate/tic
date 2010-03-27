<?php
require_once 'lib/tic.php';
TIC::factory('fonts/Generell TW-Regular.otf')
->setText('Hello World !')
->setPadding(10)
->setBgColor(0x00, 0xff, 0xff)
->setFontColor(0x00, 0x00, 0x00)
->setFontSize(24)->create(true);
