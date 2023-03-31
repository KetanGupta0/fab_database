<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Adds;
use App\Models\Fields;
use App\Models\formData;
use App\Models\Category;
use App\Models\FormSelect;
use App\Models\AddImages;
use App\Models\AdsPersonalInfo;
use App\Models\Comment;
use App\Models\Userlist;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AddController extends Controller
{
    // Function for main category
    public function indexCategories(){      // Tested and working
        $cat = Category::where('parent', '=', 0)->get();
        return response()->json($cat);
    }

    // Function for sub categories
    public function subCategories(Request $request){        // Tested and working
        $check = Category::where('parent', '=', $request->cid)->first();
        if($check){
            $sub = Category::where('parent', '=', $request->cid)->get();
            return response()->json($sub);
        }
        else{
            return response()->json('empty');   
        }
    }

    // Dynamic Forms Generation
    public function formFields(Request $request){       // Tested and working
        $fields = Fields::where('category_field_id','=',$request->cid)->get();
        $options = array();
        foreach($fields as $key=>$field){
            if($field->type == 'select'){
                $options[$key] = FormSelect::where('form_select_data.form_fields_id','=',$field->form_field_id)->join('form_fields','form_select_data.form_fields_id','=','form_fields.form_field_id','left')->get();
            }
        }
        $response = array_merge($fields->toArray(), $options);
        return response()->json($response);
    }

    // Saving Ads - Stage - 1 of 4
    public function saveAddInfo(Request $request){      // Tested and working
        $cid = $request->cid;       //Last Category ID
        $uid = $request->uid;       //User ID
        $userData = $request->formData;
        $parent = $cid;
        repeat:
        $category = Category::find($parent);
        if($category->parent != 0){
            $parent = $category->parent;
            goto repeat;
        }
        else{
            $add = new Adds();
            $add->user_id = $uid;
            $add->cat_id = $cid;
            $add->main_cat_id = $category->cid;
            $add->add_status = 'incomplete';
            $add->save();
            $add = Adds::where('user_id','=',$uid)->where('add_status','=','incomplete')->first();
            $aid = $add->add_id;
            $fields = Fields::where('category_field_id','=',$cid)->get();
            foreach ($fields as $key => $field) {
                $data = new formData();
                $data->form_field_id = $field->form_field_id;
                $data->add_id = $aid;
                $data->main_data = $userData[$key];
                $data->save();
            }
            /* * * * This code will work only after defining protected $fillable = ['coulmn_name']; in Model Section * * * */
            Adds::find($aid)->update(['add_status' => 'pending']);
            return response()->json([['add_id' => $aid],['user_id' => $uid]]);
        }
    }

    // Saving Ads - Stage - 2 of 4
    public function addImageUpload(Request $request){       // Tested and working
        $target_path = "public/addImages/";
        $target_path = $target_path .rand(100000,999999). basename($_FILES['file']['name']);
        $addImage = new AddImages();
        if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path)){
            header('Content-type: application/json');
            $addImage->image_name = $target_path;
            $addImage->flag = 'new';
            $result = $addImage->save();
            if($result){
                $nextStep = AddImages::where('flag','=','new')->first();
                $finalStep = AddImages::find($nextStep->image_id);
                $finalStep->flag = 'pending';
                $final_result = $finalStep->update();
                if(!$final_result){
                    return response()->json("failed");
                }
            }
        }
        return response()->json('success');
    }

    public function imageAidUid(Request $request){
        $aid = $request->aid;
        $uid = $request->uid;
        $images = AddImages::where('flag','=','pending')->get();
        foreach($images as $image){
            $image->user_id= $uid;
            $image->add_id = $aid;
            $image->flag = 'done';
            $result = $image->update();
            if(!$result){
                return response()->json('failed');
            }
        }
        return response()->json('success');
    }
    
    // Saving Ads - Stage - 3 of 4
    public function saveAddTitle(Request $request){     // Tested and working
        $uid = $request->uid;
        $aid = $request->aid;
        $userData = $request->formData;
        if($userData[0] == '' || $userData[1] == ''){
            return response()->json('blankField');
        }
        else{
            $add = Adds::where('add_id','=',$aid)->where('user_id','=',$uid)->first();
            $add->add_title = $userData[0];
            $add->add_detail = $userData[1];
            $add->update();
            return response()->json([['add_id' => $aid],['user_id' => $uid]]);
        }
    }

    // Saving Ads - Stage - 4 of 4
    public function saveAdsFinal(Request $request){         // Under Development Phase
        $aid = $request->aid;
        $uid = $request->uid;
        $phonecode = $request->phonecode;
        $mobile = $request->mobile;
        $check = Adds::where('add_id','=',$aid)
                    ->where('user_id','=',$uid)
                    ->first();
        if($check){
            $adsPersonalInfo = new AdsPersonalInfo();
            $adsPersonalInfo->add_id = $aid;
            $adsPersonalInfo->user_id = $uid;
            $adsPersonalInfo->phonecode = $phonecode;
            $adsPersonalInfo->mobile = $mobile;
            $adsPersonalInfo->save();
            return response()->json(['add_id' => $aid,'user_id' => $uid,'msg' => 'success']);
        }
        else{
            return response()->json(['add_id' => $aid,'user_id' => $uid,'msg' => 'fail']);
        }
    }

    public function storeBlobData(Request $request){
        // $aid = $request->aid;
        // $uid = $request->uid;
        $data = $request->img;

        return response()->json($request->image('name'));

        AddImages::insert([
            // 'add_id' => $aid,
            // 'user_id' => $uid,
            'image_name' => $data,
        ]);

        return response()->json(['message' => 'Blob data stored successfully.']);
    }

    public function uploadFile(Request $request){
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('uploads');
            
            // Save the file path to the database or do other processing.
            // You can access the original file name with $file->getClientOriginalName().

            return response()->json(['message' => 'File uploaded successfully.']);
        } else {
            return response()->json(['error' => 'No file was uploaded.'], 400);
        }
    }

    public function displayAds(){
        $addsList = array();
        $adds = Adds::select('add_id','user_id')->get();
        foreach ($adds as $key=>$ad) {
            $addData = formData::where('add_id', '=', $ad->add_id)->join('form_fields','form_data.form_field_id','=','form_fields.form_field_id')->get();
            $addImage = AddImages::where('add_id', '=', $ad->add_id)->where('user_id','=',$ad->user_id)->get();
            $addHeadings = Adds::where('add_id','=',$ad->add_id)->where('user_id','=',$ad->user_id)->first();
            $addPersonalInfo = AdsPersonalInfo::where('add_id', '=', $ad->add_id)->where('user_id','=',$ad->user_id)->first();
            $addsList[$key] = [
                'addHeadings' => $addHeadings,
                'addData' => $addData,
                'addPersonalInfo' => $addPersonalInfo,
                'addImage' => $addImage,
            ];
        }
        return response()->json($addsList);
    }

    // Comments Processing
    public function adsComments(Request $request){
        $commenter = $request->uid;
        // Check user
        $check =  Userlist::find($commenter);
        if(!$check){
            return response()->json('nouser');
        }
        // Check add
        $aid = $request->aid;
        $check = Adds::find($aid);
        if(!$check){
            return response()->json('noadd');
        }
        $comment = $request->comment;
        // Find owner id
        $addowner = Adds::find($aid)->value('user_id');
        // Create new comment
        $result = Comment::create([
            'add_id' => $aid,
            'comment_from' => $commenter,
            'comment_to' => $addowner,
            'comment_msg' => $comment,
        ]);
        // Confirm creation and send relatable reply
        if($result){
            return response()->json('success');
        }
        else{
            return response()->json('fail');
        }
    }

    public function fetchAdsComments(Request $request){
        $aid = $request->aid;
        // Check Add
        $check = Adds::find($aid);
        if(!$check){
            return response()->json('noadd');
        }
        $activeUser = $request->uid;
        // Check user
        $check = Userlist::find($activeUser);
        if(!$check){
            return response()->json('nouser');
        }
        // Find Add owner
        $addowner = Adds::find($aid)->value('user_id');
        // Checking ownership
        if($addowner == $activeUser){
            // Get comments if add owner
            $comments = Comment::where('add_id',$aid)->get();
            $usersarray = [];
            foreach($comments as $comment){
                array_push($usersarray,$comment->comment_from);
            }
            $users = array_unique($usersarray);
            // return response()->json($users);
            $dataArray = [];
            foreach($users as $user){
                $data = [];
                $uname = Userlist::find($user);
                array_push($dataArray,[
                    'user_id' => $user,
                    'username' => $uname->user_name,
                ]);
            }
            return response()->json($dataArray);
        }
        else{
            $dataArray = [];
            // Get comments if not add owner
            $comments = Comment::where('add_id',$aid)
                                ->where('comment_from',$activeUser)
                                ->orWhere('comment_to',$activeUser)
                                ->get();
            foreach($comments as $comment){
                $data=[];
                $uname = Userlist::find($comment->comment_from);
                if($addowner == $comment->comment_from){
                    $data=[
                        'commenter' => 'owner',
                        'username' => $uname->user_name,
                        'comment' => $comment->comment_msg,
                        'status' => $comment->seen_flag,
                    ];
                }
                else{
                    $data=[
                        'commenter' => 'user',
                        'username' => $uname->user_name,
                        'comment' => $comment->comment_msg,
                        'status' => $comment->seen_flag,
                    ];
                }
                array_push($dataArray,$data);
            }
            return response()->json($dataArray);
        }
    }

    public function fetchOwnerComment(Request $request){
        $aid = $request->aid;
        // Check Add
        $check = Adds::find($aid);
        if(!$check){
            return response()->json('noadd');
        }
        $activeUser = $request->uid;
        // Check user
        $check = Userlist::find($activeUser);
        if(!$check){
            return response()->json('nouser');
        }
        // Get comments
        $comments = Comment::where('add_id',$aid)
                            ->where('comment_from','=',$activeUser)
                            ->where('comment_to','=',$activeUser)
                            ->get();
        $dataArray = [];
        
        return response()->json($dataArray);
    }

    public function commentSeenChange(Request $request){
        $unseenMessages = Comment::where('msg_from', '=', 0)
            ->where('comment_to', '=', $request->uid)
            ->where('seen_flag', '=', 0)
            ->get();
        foreach ($unseenMessages as $msg) {
            $msg->seen_flag = 1;
            $msg->update();
        }
    }

    public function fetchAds(Request $request) {
        $clickedCategoryId = $request->cid;
        $clickedCategoryId = 1;
        $map = array();
        $addsList = array();
        $i = 0;
        $check1 = true;
        while($check1){
            $check1 = Category::where('cid','=',$clickedCategoryId)->first();
            if($check1){
                $map[$i] = Category::where('parent','=',$clickedCategoryId)->value('cid');
                $clickedCategoryId = $map[$i];
                $i++;
            }
            else{
                echo $clickedCategoryId;
                break;
            }
        }
        if(sizeof($map)<1){
            return response()->json($addsList);
        }
        $adds = Adds::where('cat_id','=',$map[sizeof($map)-2])->get();
        foreach ($adds as $key=>$ad) {
            $addData = formData::where('add_id', '=', $ad->add_id)->join('form_fields','form_data.form_field_id','=','form_fields.form_field_id')->get();
            $addImage = AddImages::where('add_id', '=', $ad->add_id)->where('user_id','=',$ad->user_id)->get();
            $addHeadings = Adds::where('add_id','=',$ad->add_id)->where('user_id','=',$ad->user_id)->first();
            $addPersonalInfo = AdsPersonalInfo::where('add_id', '=', $ad->add_id)->where('user_id','=',$ad->user_id)->first();
            $addsList[$key] = [
                'addHeadings' => $addHeadings,
                'addData' => $addData,
                'addPersonalInfo' => $addPersonalInfo,
                'addImage' => $addImage,
            ];
        }
        return response()->json($addsList);

    }

    public function replyAdsComments(Request $request){
        $request->validate(['comment' => 'required'],['comment.required' => 'Comment field must have some message!!']);
        $aid = $request->aid;
        $uid = $request->uid;
        $oid = $request->id;
        $msg = $request->comment;

        $add = Adds::find($aid);

        if($add->user_id != $oid){
            return response()->json('You are not allowed!');
        }

        $new = new Comment();

        $new->add_id = $aid;
        $new->owner_id = $oid;
        $new->comment_msg = $msg;
        $new->comment_to = $uid;
        $new->comment_from = $oid;
        $result = $new->save();
        if($result){
            return response()->json('success');
        }
        else{
            return response()->json('fail');
        }
    }

    public function fetchLoggedAds(Request $request){
        $uid = $request->uid;
        $allAdds = Adds::get();
        $adds = [];
        foreach($allAdds as $add){
            $price = 0;
            $formData = formData::where('add_id','=',$add->add_id)->get();
            foreach($formData as $field){
                $title = Fields::find($field->form_field_id);
                if($title->label == 'Price'){
                    $price = $field->main_data;
                }
            }
            $img = AddImages::where('add_id','=',$add->add_id)->get();
            $checkfavourite = Wishlist::where('owner_wish_id','=',$uid)->where('add_wish_id','=',$add->add_id)->first();
            if($checkfavourite){
                array_push($adds,[
                    'id' => $add->add_id,
                    'title' => $add->add_title,
                    'price' => $price,
                    'image' => $img,
                    'timestamp' => $add->timestamp,
                    'favourite' => 1,
                ]);
            }else{
                array_push($adds,[
                    'id' => $add->add_id,
                    'title' => $add->add_title,
                    'price' => $price,
                    'image' => $img,
                    'timestamp' => $add->timestamp,
                    'favourite' => 0,
                ]);
            }
        }
        return response()->json($adds);
    }
}

