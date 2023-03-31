<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminChat;

class ChatController extends Controller
{
    public function loadAllChatsFromAdmin(Request $request)
    {    // Tested and working
        $uid = $request->uid;
        $msgs = AdminChat::get();
        $chats = array();
        foreach ($msgs as $msg) {
            if ($msg->msg_from == 0 && $msg->msg_to == $uid) {
                if ($msg->seen_flag == 0) {
                    array_push($chats, ['sender' => 'admin', 'message' => $msg->message, 'status' => 'unseen']);
                } else {
                    array_push($chats, ['sender' => 'admin', 'message' => $msg->message, 'status' => 'seen']);
                }
            }
            if ($msg->msg_from == $uid) {
                if ($msg->seen_flag == 0) {
                    array_push($chats, ['sender' => 'user', 'message' => $msg->message, 'status' => 'sent']);
                } else {
                    array_push($chats, ['sender' => 'user', 'message' => $msg->message, 'status' => 'seen']);
                }
            }
        }
        return response()->json($chats);
    }

    public function messageSendToAdmin(Request $request)
    {       // Tested and working
        $uid = $request->uid;
        $msg = $request->msg;
        $result = AdminChat::create([
            'message' => $msg,
            'msg_to' => 0,
            'msg_from' => $uid
        ]);
        if ($result) {
            return response()->json('sent');
        } else {
            return response()->json('fail');
        }
    }

    public function adminChatSeenFlagChange(Request $request)
    {       // Tested and working
        $unseenMessages = AdminChat::where('msg_from', '=', 0)
            ->where('msg_to', '=', $request->uid)
            ->where('seen_flag', '=', 0)
            ->get();
        foreach ($unseenMessages as $msg) {
            $msg->seen_flag = 1;
            $msg->update();
        }
    }
}
