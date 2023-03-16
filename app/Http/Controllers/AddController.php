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
use Illuminate\Support\Facades\DB;
use Nette\Utils\Json;

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

    public function showForm(Request $request){     // Under development phase
        // $aid = $request->aid;
        $aid = 1;
        $formInfo = formData::where('add_id','=',$aid)->get();
        return response()->json($formInfo);
    }

    public function showAdds(Request $request){     // Under development phase
        $user_id = 1;
        $formInfo = array();
        $adds = Adds::where('user_id','=',$user_id)->get();
        foreach ($adds as $key => $add) {
            $formInfo[$key] = formData::where('add_id','=',$add->add_id)->get();
        }
        $response = array_merge($adds->toArray(),$formInfo);
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
    public function addImageUpload(Request $request){       // Under testing phase
        $aid = $request->aid;
        $uid = $request->uid;
        $image = $request->file('simage');

        // $image = $request->simage;
        // return response()->json($image);

        $aid = 1;
        $uid = 1;

        $msg = 'success';
        foreach ($image as $img)
        {
            $addImage = new AddImages();
            $extension = $img->getClientOriginalExtension();
            $filename = "image".hexdec(uniqid()).'.'.$extension;
            $img->move('public/addImages/', $filename);
            $addImage->user_id= $uid;
            $addImage->add_id = $aid;
            $addImage->image_name = $filename;
            $result = $addImage->save();
            if(!$result){
                $msg = "Failed";
                return response()->json($msg);
            }
        }
        return response()->json($msg);
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

    public function displayAds(){
        $addsList = array();
        $adds = Adds::select('add_id','user_id')->get();
        foreach ($adds as $key=>$ad) {
            $addData = formData::where('add_id', '=', $ad->add_id)->get();
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
}

/*
----+-------------------+
ID  |   Label           |
----+-------------------+
1   |   Year Of make    |
2   |   Status of car   |
3   |   Color           |
4   |   Kilometer       |
5   |   Price           |
6   |   Hidden          |
7   |   Negotiable      |
----+-------------------+


*/