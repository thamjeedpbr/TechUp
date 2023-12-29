<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ResponseAPI;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class ApiAuthController extends Controller
{
    use ResponseAPI;

    public function login(Request $request)
    {
        try {
            $v = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);
            if ($v->fails()) {
                return $this->error(['message' => $v->errors()]);
            } else {
                $user = User::where('email', $request->email)->first();

                if ($user) {

                    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                        $user = Auth::user();
                        if ($user) {
                            $token = $user->createToken("token")->plainTextToken;
                            $succes = [
                                'success' => 'user logged in',
                                'token' => $token,
                                'logged_in' => $user,
                            ];

                            return $this->success($succes, 200);
                        } else {
                            return $this->error('Unauthorized Request');
                        }
                    } else {
                        return $this->error('Unauthorized Login');
                    }
                } else {
                    return $this->error('This email not registered');
                }
            }

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function register(Request $request)
    {
        try {
            $rules = [
                'name' => 'required',
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->error($validator->errors());
            }

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            if ($user->save()) {
                return $this->success("Registered Succesfully");
            }
        } catch (Exception $e) {
            return $this->error('Try again');
        }

    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            if ($request->allDevice) {
                $user->tokens->each(function ($token) {
                    $token->delete();
                });
                return $this->success('Logged Out from all devices !!');
            }
            $user->currentAccessToken()->delete();
            return $this->success('Logged Out Successfully !!');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
