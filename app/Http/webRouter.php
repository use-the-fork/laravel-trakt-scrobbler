<?php

declare(strict_types=1);

foreach (glob(app_path('Domains/*/Http/Controllers/router.php')) as $file) {
    require $file;
}
