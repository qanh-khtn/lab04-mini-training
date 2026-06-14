<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Support\Response;

class HomeController
{
    public function index(): void
    {
        Response::view('home', [
            'title' => 'Mini Training Center Lead Portal',
        ]);
    }
}
