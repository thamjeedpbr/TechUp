<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Traits\ResponseAPI;
use Exception;

class UserController extends Controller
{
    //list
    use ResponseAPI;

    public function list()
    {
        try {
            $employies = Employee::with('company')->get();
            if ($employies) {
                return $this->success($employies, 200);
            } else {
                return $this->error('No data');
            }

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
