<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Userlist;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    // Fetch country code
    public function countryCode()
    {      // Tested and working
        $countryCodes = Country::get();
        $result = array();
        foreach ($countryCodes as $key => $countryCode) {
            $result[$key] = $countryCode->phonecode;
        }
        return response()->json($result);
    }

    // Fetch state list by country code
    public function fetchStateList(Request $request)
    {          // Tested and working
        $countryCode = '';
        if (Session::has('country-code')) {
            $countryCode = session()->get('country-code');
        } else {
            $countryCode = $request->countryCode;
        }
        $state = State::where('country_code', '=', $countryCode)->get();
        if ($state) {
            return response()->json($state);
        } else {
            return response()->json('empty');
        }
    }

    // Fetch city list by state id
    public function fetchCityList(Request $request)
    {          // Tested and working
        $stateID = $request->state_id;
        $city = City::where('state_id', '=', $stateID)->get();
        if ($city) {
            return response()->json($city);
        } else {
            return response()->json('empty');
        }
    }

    // Test page route
    public function default()
    {          // Just for fun
        return redirect('https://youtu.be/87K5Uh3AML0');
    }

    // Logout user if loggedin
    public function logoutUser(Request $request)
    {       // Tested and working
        if ($request->loggedin) {
            session()->pull('userid');
            session()->pull('phonecode');
            session()->pull('mobile');
            session()->pull('name');
            session()->put('loggedin', false);
            $token = [['loggedin' => session()->get('loggedin')], ['msg' => 'success']];
            return response()->json($token);
        }
    }

    // New user registration process
    public function registration(Request $request)
    {         // Tested and working
        $request->validate([
            'user_mob' => 'required|numeric',
        ]);
        $reference = Userlist::where('referral', '=', $request->referral)->first();
        if (!$request->referral == '') {
            if (!$reference) {
                return response()->json('wrongref');
            }
        }

        $user = Userlist::where('user_mob', '=', $request->user_mob)->first();
        $otp = rand(1000, 9999);
        start:
        $ref = 'fab@' . rand(100000000, 999999999);
        $check = Userlist::where('referral', '=', $ref)->first();
        if ($check) {
            goto start;
        } else {
            if ($user) {
                if($user->registration_flag==0){
                    $uid = Userlist::where('phonecode', '=', $request->phonecode)
                        ->where('user_mob', '=', $request->user_mob)
                        ->value('user_id');
                    return response()->json(['exists' => $uid]);
                }
                return response()->json(['registered' => true]);
            } else {
                $user = new Userlist();
                $user->phonecode = $request->phonecode;
                $user->user_mob = $request->user_mob;
                $user->user_password = 'Specbits@' . rand(100000, 999999);
                $user->user_code = $otp;
                $user->referral = $ref;
                if ($reference) {
                    $user->reference = $reference->referral;
                }
                $user->save();

                $uid = Userlist::where('phonecode', '=', $request->phonecode)
                    ->where('user_mob', '=', $request->user_mob)
                    ->where('usercode', '=', $otp)->first();
                $data = [$request->phonecode, $request->user_mob, $otp, $uid];
                return response()->json($data);
            }
        }
    }

    // One Time Password (OTP) verification process
    public function verifyOTP(Request $request)
    {         // Tested and working
        $request->validate([
            'vcode' => 'required|numeric',
        ]);

        $user = Userlist::where('user_mob', '=', $request->user_mob)->first();
        if ($user) {
            if ($user->phonecode == $request->phonecode) {
                if ($user->user_code == $request->vcode) {
                    return response()->json('success');
                } else {
                    return response()->json('Wrong OTP');
                }
            } else {
                return response()->json('wpc');
            }
        } else {
            return response()->json('mnf');
        }
    }

    // User Login Process
    public function userLogin(Request $request)
    {         // Tested and working
        $request->validate([
            'user_mob' => 'required|numeric',
            'user_pwd' => 'required',
        ]);
        $user = Userlist::where('user_mob', '=', $request->user_mob)->first();
        if ($user) {
            if ($user->phonecode == $request->phonecode) {
                if ($request->user_pwd == $user->user_password) {
                    session()->put('loggedin', true);
                    session()->put('userid', $user->user_id);
                    session()->put('phonecode', $user->phonecode);
                    session()->put('mobile', $user->user_mob);
                    session()->put('name', $user->user_name);
                    $countryCode = Country::where('phonecode', '=', $user->phonecode)->first();

                    session()->put('country-code', $countryCode->country_code);
                    $token = [
                        ['loggedin' => session()->get('loggedin')],
                        ['msg' => 'success'],
                        ['username' => session()->get('name')],
                        ['userid' => session()->get('userid')],
                        ['phonecode' => session()->get('phonecode')],
                        ['mobile' => session()->get('mobile')]
                    ];
                    return response()->json($token);
                } else {
                    return response()->json('wrongpwd');
                }
            } else {
                return response()->json('wpc');
            }
        } else {
            return response()->json('mnf');
        }
    }

    // Forgot Password reset process
    public function resetPasswordValidation(Request $request)
    {         // Tested and working
        $request->validate([
            'user_mob' => 'required|numeric',
        ]);
        $otp = rand(1000, 9999);
        $user = Userlist::where('user_mob', '=', $request->user_mob)->first();
        if ($user) {
            if ($user->phonecode == $request->phonecode) {
                $user_id = Userlist::where('user_mob', '=', $request->user_mob)->value('user_id');
                $user = Userlist::find($user_id);
                $user->user_code = $otp;
                $user->update();
                $data = [$request->phonecode, $request->user_mob, $otp];
                return response()->json($data);
            } else {
                return response()->json('wpc');
            }
        } else {
            return response()->json('mnf');
        }
    }

    // Password update process
    public function updatePassword(Request $request)
    {         // Tested and working
        $request->validate([
            'user_pwd1' => 'required|between:8,16',
            'user_pwd2' => 'required|same:user_pwd1|between:8,16',
        ]);

        $user = Userlist::where('user_mob', '=', $request->user_mob)->first();
        if ($user) {
            if ($user->phonecode == $request->phonecode) {
                $user_id = Userlist::where('user_mob', '=', $request->user_mob)->value('user_id');
                $user = Userlist::find($user_id);
                $user->user_password = $request->user_pwd1;
                // $user->user_password = Hash::make($request->user_pwd1);
                $user->update();
                return response()->json('success');
            } else {
                return response()->json('wpc');
            }
        } else {
            return response()->json('fail');
        }
    }

    public function profileComplition(Request $request)
    {          // Tested and working
        $request->validate([
            'name' => 'required',
            'pwd' => 'required',
            'confirmpwd' => 'required|same:pwd',
        ], [
            'name.required' => 'Name field is required!',
            'pwd.required' => 'Password is required!',
            'confirmpwd.required' => 'Confirm Password is required!',
            'confirmpwd.same' => 'Password and Confirm password should be same!',
        ]);
        $name = $request->name;
        $password = $request->pwd;
        $uid = $request->uid;
        $user = Userlist::find($uid);
        if($user->registration_flag == 1){
            return response()->json(['complete'=>'Profile already completed!']);
        }
        if ($user) {
            $user->user_name = $name;
            $user->user_password = $password;
            $user->registration_flag = 1;
            $user->update();

            $data = Userlist::find($uid);
            return response()->json([
                'username' => $data->user_name,
                'userid' => $data->user_id,
                'phonecode' => $data->phonecode,
                'usermob' => $data->user_mob
            ]);
        } else {
            return response()->json(['sorry' => "Sorry we can't complete your profile right now!"]);
        }
    }
}
