<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Adds;
use App\Models\AdsPersonalInfo;
use App\Models\Userlist;
use App\Models\AddImages;
use App\Models\formData;
use App\Models\Fields;

class WishlistController extends Controller
{
    // Add to wishlist process
    public function addToWishlist(Request $request)
    {
        $uid = $request->uid;
        $aid = $request->aid;
        // Check user
        $check = Userlist::find($uid);
        if ($check) {
            // Check add
            $check = Adds::find($aid);
            if ($check) {
                $result = Wishlist::create([
                    'owner_wish_id' => $uid,
                    'add_wish_id' => $aid,
                ]);
                if ($result) {
                    return response()->json('success');
                } else {
                    return response()->json('fail');
                }
            } else {
                return response()->json('noadd');
            }
        } else {
            return response()->json('nouser');
        }
    }

    // Remove from wishlist
    public function removeFromWishlist(Request $request){
        $uid = $request->uid;
        $aid = $request->aid;
        $result = Wishlist::where('owner_wish_id','=',$uid)->where('add_wish_id','=',$aid)->delete();
        if ($result) {
            return response()->json('success');
        } else {
            return response()->json('fail');
        }
    }

    // Favorites Page load process
    public function fetchUserWishList(Request $request){
        $uid = $request->uid;
        $wishAdds = Wishlist::where('owner_wish_id','=',$uid)->get();
        $addsinfo = [];
        foreach($wishAdds as $add){
            $aid = $add->add_wish_id;
            $ad = Adds::find($aid);
            $img = AddImages::where('add_id','=',$aid)->get();
            $price = 0;
            $formData = formData::where('add_id','=',$aid)->get();
            foreach($formData as $field){
                $title = Fields::find($field->form_field_id);
                if($title->label == 'Price'){
                    $price = $field->main_data;
                }
            }
            array_push($addsinfo,[
                'id' => $ad->add_id,
                'title' => $ad->add_title,
                'price' => $price,
                'images' => $img,
                'timestamp' => $ad->timestamp,
            ]);
        }
        return response()->json($addsinfo);
    }
}
