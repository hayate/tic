<?php
require_once 'lib/tic.php';
TIC::factory('fonts/Generell TW-Regular.otf')
->setText('Hello World !')
->setPadding(10)
->setBackground(0x00, 0xff, 0xff)
->setColor(0x00, 0x00, 0x00)
->setSize(24)->create(true);
