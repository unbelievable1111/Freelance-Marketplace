<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\DispatchesJobs;
use Illuminate\Foundation\Auth\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs as BusDispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests as ValidationValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, BusDispatchesJobs, ValidationValidatesRequests;
}
