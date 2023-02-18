<?php

namespace App\Http\Controllers;

use App\Events\NotificationPusher as EventsNotificationPusher;
use App\Mail\EmailVerification;
use App\Models\Admin;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use NotificationPusher;

class UserController extends Controller
{


    private $access_token;
    private $email_verification;

    public function __construct()
    {
        $this->access_token = uniqid(base64_encode(Str::random(40)));
        $this->email_verification = Str::random(80);;
    }

    public function sendVerificationEmail(Request $request)
    {

        $data = [
            'email'  => $request->input('email'),
            'token' => $request->input('token'),
        ];

        $user = User::where('email', $data['email'])->first();





        if ($user) {

            $user->update([
                'remember_token' => $this->email_verification
            ]);

            Mail::to($user->email)->send(new EmailVerification($user, $this->email_verification));

            return response()->json(["message" => "Email sent successfully."], 200);
        } else {
            return response()->json(["message" => "Email didn't sent , please try again later."], 401);
        }
    }


    public function verifyEmail($email, $token)
    {

        $user = User::where('email', $email)->first();

        if ($user) {
            if ($user->remember_token === $token) {
                $token_ = $user->createToken($user->email . 'auth_token')->plainTextToken;

                if (!$user->hasVerifiedEmail()) {
                    $timeNoew = Carbon::now();

                    $user->update([
                        'email_verified_at' => $timeNoew
                    ]);

                    return response()->json(["message" => "Email verified successfully.", 'user' => $user, 'access_token' => $token_], 200);
                } else {
                    return response()->json(["message" => "Email already verified.", 'user' => $user, 'access_token' => $token_], 200);
                }
            } else {
                return response()->json(["message" => "Token is incorrect."], 401);
            }
        } else {
            return response()->json(["message" => "User not found."], 404);
        }
    }


    public function checkVerification(Request $request)
    {


        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if ($user->hasVerifiedEmail()) {
                $token_ = $user->createToken($user->email . 'auth_token')->plainTextToken;

                return response()->json(["message" => "Email is verified.", 'user' => $user, 'access_token' => $token_], 200);
            } else {
                return response()->json(["message" => "Token is incorrect."], 401);
            }
        } else {
            return response()->json(["message" => "User not found."], 404);
        }
    }

    public function profile(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user !== null) {
            return response()->json([
                'status' => 'success',
                'user' => $user,
            ], 200);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Something went wrong',
            ], 401);
        }
    }

    public function login(Request $request)
    {


        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $user = User::where('email', $request->email)->first();
        $admin = Admin::where('email', $request->email)->first();

        if ($user) {
            if ($user->email_verified_at) {

                $credentials = $request->only('email', 'password');

                if (Auth::attempt($credentials)) {

                    $token = $user->createToken($user->email . 'auth_token')->plainTextToken;

                    return response()->json([
                        'status' => 'success',
                        'message' => $user->name . ' Signed In successfully',
                        'access_token' => $token,
                        'user' => $user,
                    ], 200);
                } else if (!Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Password does not match',
                    ], 401);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Email is not verified',
                ], 400);
            }
        } else if ($admin) {
            if (Hash::check($request->password, $admin->password)) {
                return response()->json([
                    'status' => 'success',
                    'message' => $admin->name . ' Signed In successfully',
                    'access_token' => $admin->remember_token,
                    'admin' => $admin,
                ], 200);
            } else if (!Hash::check($request->password, $admin->password)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Password does not match',
                ], 401);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Something went wrong',
                ], 401);
            }
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'User does not exist',
            ], 401);
        }
    }




    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
        ]);



        $emailFound = User::where('email', $request->email)->first();

        if (!$emailFound) {

            $postArray = $request->all();
            $postArray['full_name'] = $request->first_name . ' ' . $request->last_name;
            $postArray['remember_token'] = $this->access_token;

            $userFNfound = User::where('full_name', $postArray['full_name'])->first();

            if (!$userFNfound) {
                $user = User::create($postArray);

                return response()->json([
                    'status' => 'success',
                    'message' => $request->name . 'Registred successfully',
                    'user' => $postArray,
                ]);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'First Name Or Last Name already exists',
                ], 401);
            }
        } else if ($emailFound) {
            return response()->json([
                'message' => 'Email already exists'
            ], 400);
        } else {
            return response()->json([
                'message' => 'Something went wrong , please try again'
            ], 403);
        }
    }


    public function update(Request $request)
    {

        $user = User::where('email', 'bohsineyahya@gmail.com')->first();




        if ($request->name || $request->email || $request->password) {
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            if (Hash::check($request->password, $user->password)) {
                $user->password = $request->input(Hash::make('new_password'));
            }
            $user->remember_token = $request->input('remember_token');
            $user->update([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('new_password')),
            ]);

            $token = $user->createToken($user->email . 'auth_token')->plainTextToken;
        }



        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'user' => $user,
            'access_token' => $token
        ], 200);
    }

    public function updateAvatar(Request $request)
    {

        $request->validate([
            'avatar' => 'required|max:2048',
            'user_id' => 'required|numeric|exists:users,id',
        ]);


        $user = User::findOrFail($request->user_id);

        if ($user) {
            if ($request->has('avatar')) {
                $image = $request->file('avatar');
                $filename = time() . '.' . $image->getClientOriginalExtension();
                $image->move('uploads/users/', $filename);

                $user->update([
                    'avatar' => $filename
                ]);

                $token = $user->createToken($user->email . 'auth_token')->plainTextToken;

                return response()->json([
                    'status' => 'success',
                    'message' => 'Image updated successfully',
                    'user' => $user,
                    'access_token' => $token
                ], 200);
            }
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'User not found',
            ], 401);
        }
    }


    public function delete(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password_check' => 'required',
        ]);


        $user = User::where('email', $request->email)->first();


        if ($user && Hash::check($request->password_check, $user->password)) {
            $user->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'User Deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Password does not match our records',
            ], 401);
        }
    }



    public function checkAdmin(Request $request)
    {


        $request->validate([
            'email' => 'required|email',
            'admin_token' => 'required',
        ]);


        $admin = Admin::where('email', $request->email)->first();

        if ($admin->remember_token == $request->admin_token) {
            return response()->json([], 200);
        } else {
            return response()->json([
                'status' => 'failed',
            ], 401);
        }
    }
}
