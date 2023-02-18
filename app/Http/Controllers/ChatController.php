<?php

namespace App\Http\Controllers;

use App\Events\MessageEvent;
use App\Models\Admin;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{


    public function getAdminToChat(Request  $request)
    {

        $request->validate([
            'user_id' => 'required',
            'sender_id' => 'required',
        ]);

        $user = User::where('id', $request->user_id)->first();

        if ($user && $user->remember_token === $request->sender_id) {

            $messages = Message::where('sender_id', $user->id);


            if ($messages) {
                return response()->json(['messages' => $messages], 200);
            } else {

                $admin = Admin::where('available', 'true')->random()->first();


                if ($admin) {
                    return response()->json([
                        'status' => 'success',
                        'reciever_token' => $admin->remember_token
                    ], 200);
                } else {
                    $admin_already = Admin::random();

                    return response()->json([
                        'status' => 'success',
                        'reciever_token' => $admin_already->remember_token
                    ], 200);
                }
            }
        }
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required',
            'user_id' => 'required',
            'reciever_id' => 'required',
        ]);


        $user = User::where('id', $request->user_id)->first();

        $admin = Admin::where('id', $request->reciever_id)->first();

        if ($user) {

            $message = $request->input('message');

            Message::create([
                'message' => $message,
                'sender_id' => $request->user_id,
                'reciever_id' => $request->reciever_id,
            ]);

            $messages = Message::all();

            event(new MessageEvent($messages));



            return response()->json(['message' => $message], 200);
        } else {
            return response()->json(['message' => 'Unauthorized User'], 401);
        }
    }



    public function messages(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'reciever_id' => 'required',
        ]);

        $user = User::where('id', $request->user_id)->first();

        if ($user) {

            $messages = Message::where('reciever_id', $request->reciever_id)->orWhere('sender_id', $request->user_id)->get();
        } else {
            return response()->json(['message' => 'Unauthorized User'], 401);
        }
    }
}
