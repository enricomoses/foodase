<?php
declare(strict_types=1);

require __DIR__ . '/../../../app/bootstrap.php';

use App\Controllers\OrderController;

(new OrderController())->detail();
