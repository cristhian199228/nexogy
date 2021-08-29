<?php

namespace App\Http\Controllers;


use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    public function login(Request $request) {

        $response = Http::withToken($request->bearerToken())
            ->get('https://graph.microsoft.com/v1.0/me?$select=id,givenName,mail,surname');

        if ($response->successful()) {
            $res = $response->json();
            //dd($res);
            $user = User::updateOrCreate(['email' => $res['mail']], [
                'nombres' => $res['givenName'],
                'apellidos' => $res['surname'],
                'azure_id' => $res['id'],
                'password' => Hash::make($res['id'])
            ]);
            Auth::login($user);

            return response([
                'message' => 'User successfully authenticated',
                'data' => Auth::user()
            ]);
        }

        return response([
            'message' => 'Invalid User'
        ], 401);
    }

    public function currentUser() {
        return Auth::user();
    }

    public function logout() {
        Auth::logout();

        return response([
            'message' => 'Usuario deslogueado correctamente'
        ]);
    }
}
