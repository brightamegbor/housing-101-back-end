<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Payload;
use Tymon\JWTAuth\Manager as JWT;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->json()->all() , [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:355|unique:users',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|min:1'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        };

        $user = User::create([
            'name' => $request->json()->get('name'),
            'email' => $request->json()->get('email'),
            'password' => Hash::make($request->json()->get('password')),
            'phone_number' => $request->json()->get('phone_number'),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->json()->all();

        try {
            if(! $token = JWTAuth::attempt($credentials)){
                return response()->json(['error' => 'invalid_credential'], 400);
            }
        }catch(JWTException $e){
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        return response()->json(compact('token'));
    }

    public function getAuthenticatedUser()
    {

        try {
            if(! $user = JWTAuth::parseToken()->authenticate()){
                return response()->json(['user_not_found'], 404);
            }
        }catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e){
            return response()->json(['token_expired'], $e->getStatusCode());
        }catch(Tymon\JWTAuth\Exceptions\TokenInvalidException $e){
            return response()->json(['token_invalid'], $e->getStatusCode());
        }catch(Tymon\JWTAuth\Exceptions\JWTException $e){
            return response()->json(['token_absent'], $e->getStatusCode());
        }

        return response()->json(compact('user'));
    }

    public function updateProfile(User $user, Request $request)
    {
        $user_id = $request->json()->get('id');
        $user = User::find($user_id);

        $company = $request->json()->get('company');
        $address = $request->json()->get('address');
        $zip_code = $request->json()->get('zip_code');
        $city = $request->json()->get('city');
        $state_region = $request->json()->get('state_region');
        $country = $request->json()->get('country');


        if($address != ''){
            $user->address = $address;
        }

        if($company != ''){
            $user->company = $company;
        }


        if($zip_code != ''){
            $user->zip_code = $zip_code;
        }

        if($city != ''){
            $user->city = $city;
        }

        if($state_region != ''){
            $user->state_region = $state_region;
        }

        if($country != ''){
            $user->country = $country;
        }

        $user->save();

        return response()->json(['user_updated' => true], 201);
    }


}