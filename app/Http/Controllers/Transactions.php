<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Userlist;
use App\Models\PointTransaction;

class Transactions extends Controller
{
    public function redeemPoints(Request $request){
        $request->validate([
            'amount' => 'required|numeric',
        ],[
            'amount.required' => 'You must specify some amount!',
            'amount.numeric' => 'Use numeric values only!'
        ]);

        $amount = $request->amount;
        $uid = $request->uid;
        $availablePoints = 0;

        // Checking user
        $user = Userlist::find($uid);
        if($user){      // If exists
            $availablePoints = $user->points;   // Update $availablePoints from userlist table (col: points)
        }
        else{           // If not exists
            return response()->json('nouser');
        }

        if($availablePoints < $amount){     // If user have not sufficient points
            return redirect()->json('insufficient');
        }
        else{                               // If user have sufficient points
            $availablePoints = $availablePoints - $amount;      // Deducting specified amount

            // Updating user's total points
            $result = Userlist::find($uid)->update([
                'points' => $availablePoints,
            ]);

            // Checking point updation result if failed
            if(!$result){
                return redirect()->json('fail');
            }

            // Creating a new transaction record
            $result = PointTransaction::create([
                'user_id' => $uid,
                'available_points' => $availablePoints,
                'transaction_amount' => $amount,
            ]);

            // Confirm and send response for new transaction record
            if($result){
                return redirect()->json('success');
            }
            else{
                return redirect()->json('fail');
            }
        }
    }
}
