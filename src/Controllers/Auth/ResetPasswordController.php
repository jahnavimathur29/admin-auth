<?php

namespace Modules\Admin\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Carbon\Carbon;
use Modules\Admin\Models\Admin;

class ResetPasswordController extends Controller
{
    public function resetPassword($token)
    {
        $checkTokenExpired = DB::table('admin_password_resets')
                                    ->where('token','=', $token)
                                    ->where('created_at','>',Carbon::now()->subHours(2))
                                    ->first();

        if(empty($checkTokenExpired)) { 
            Session::flash('linked-expired', 'Reset password link is expired.'); 
        } 

        return view('admin::admin.auth.reset-password', ['token' => $token]);

    }

    public function postResetPassword(Request $request)
    {

        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            //'password' => 'required|min:6|confirmed',
            'password' => [ 'required', 'min:6', 'confirmed'],
        ]);

        //$userID = base64_decode($request->token);
        
        $checkTokenExpired = DB::table('admin_password_resets')
                ->where('token','=',$request->token)
                ->where('created_at','>',Carbon::now()->subHours(2))
                ->first();

        if(isset($checkTokenExpired)) {

            
            $userExists = Admin::where([ 'email'=>$request->email ])->first();
            if ($userExists) {            
                $admin = Admin::where('id', $userExists->id)->update(['password' => Hash::make($request->password)]);
                DB::table('admin_password_resets')->where('token','=',$request->token)->delete();
                return redirect('/admin/login')->with(['success' => "Password udpated successfully. Please login here"]);
            } else {
                return  back()->withErrors(['email' => 'Something went wrong. Please try later']);
            }

        } else {
            
            return  back()->withErrors(['email' => 'Reset password link is expired.']);
        }
    }
}