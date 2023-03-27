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

    public function adsComments(Request $request){
        $request->validate(['comment' => 'required'],['comment.required' => 'Comment field must have some message!!']);
        $aid = $request->aid;
        $uid = $request->uid;
        $oid = $request->oid;
        $msg = $request->comment;
        
        if($uid == $oid){
            return response()->json('You are not allowed');
        }

        $comment = new Comment();

        $comment->add_id = $aid;
        $comment->owner_id = $oid;
        $comment->comment_msg = $msg;
        $comment->comment_from = $uid;
        $comment->comment_to = $oid;
        $result = $comment->save();

        if($result){
            return response()->json('success');
        }
        else{
            return response()->json('fail');
        }

    }

    public function displayAdsComments(Request $request){
        $aid = $request->aid;
        $uid = $request->uid;

        $comment = [];
        $comments = Comment::where('add_id','=',$aid)->get();

        $add = Adds::find($aid);
        $naam = Userlist::find($uid);

        if($add->user_id == $uid){      // If user is owner
            foreach($comments as $cmt){
                $name = Userlist::find($cmt->comment_from);
                if($cmt->add_id == $aid){
                    if($cmt->comment_from == $cmt->owner_id){
                        array_push($comment,[
                            'username' => $name->user_name,
                            'comment_id' => $cmt->id,
                            'add_id' => $cmt->add_id,
                            'owner_id' => $cmt->owner_id,
                            'comment_msg' => $cmt->comment_msg,
                            'commenter' => 'owner',
                            'user_id' => $cmt->comment_from,
                            'created_at' => $cmt->created_at,
                            'updated_at' => $cmt->updated_at,
                        ]);
                    }else{
                        array_push($comment,[
                            'username' => $name->user_name,
                            'comment_id' => $cmt->id,
                            'add_id' => $cmt->add_id,
                            'owner_id' => $cmt->owner_id,
                            'comment_msg' => $cmt->comment_msg,
                            'commenter' => 'user',
                            'user_id' => $cmt->comment_from,
                            'created_at' => $cmt->created_at,
                            'updated_at' => $cmt->updated_at,
                        ]);
                    }
                }
            }
            return response()->json($comment);
        }
        foreach($comments as $cmt){
            $name = Userlist::find($cmt->comment_from);
            if($cmt->owner_id == $cmt->comment_from){
                if($uid == $cmt->comment_to){
                    array_push($comment,[
                        'username' => $name->user_name,
                        'comment_id' => $cmt->id,
                        'add_id' => $cmt->add_id,
                        'owner_id' => $cmt->owner_id,
                        'comment_msg' => $cmt->comment_msg,
                        'commenter' => 'owner',
                        'user_id' => $cmt->comment_from,
                        'created_at' => $cmt->created_at,
                        'updated_at' => $cmt->updated_at,
                    ]);
                }
            }
            else{
                if($uid == $cmt->comment_from){
                    array_push($comment,[
                        'username' => $name->user_name,
                        'comment_id' => $cmt->id,
                        'add_id' => $cmt->add_id,
                        'owner_id' => $cmt->owner_id,
                        'comment_msg' => $cmt->comment_msg,
                        'commenter' => 'user',
                        'user_id' => $cmt->comment_from,
                        'created_at' => $cmt->created_at,
                        'updated_at' => $cmt->updated_at,
                    ]);
                }
            }
        }
        return response()->json($comment);
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

    public function fetchAdsComments(Request $request){
        $aid = $request->aid;
        $uid = $request->uid;
        $oid = $request->oid;
        
        $comment =  Comment::get();
        
        $data=[];

        foreach($comment as $cmt){
            $username = Userlist::find($uid);
            if($cmt->comment_from == $cmt->owner_id){
                array_push($data,[
                    'commenter' => 'owner',
                    'comment' => $cmt->comment_msg,
                    'username' => $username->user_name,
                    'created_at' => $cmt->created_at,
                    'updated_at' => $cmt->updated_at,
                ]);
            }
            else{
                array_push($data,[
                    'commenter' => 'user',
                    'comment' => $cmt->comment_msg,
                    'username' => $username->user_name,
                    'created_at' => $cmt->created_at,
                    'updated_at' => $cmt->updated_at,
                ]);
            }
        }

        return response()->json($data);
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
}

