# TIC - Text Image Converter

**Usage Example**
```php
<?php

require_once 'lib/tic.php';


TIC::factory('fonts/Generell TW-Regular.otf')
    ->setText('Hello World !')
    ->setPadding(10)
    ->setBgColor('ff0000')
    ->setFontColor(0xff, 0xff, 0x00)
    ->setFontSize(24)->create(true);
```
