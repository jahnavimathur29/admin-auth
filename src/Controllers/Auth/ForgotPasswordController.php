<?php

namespace Modules\Admin\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\Models\Admin;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Modules\Admin\Mail\ForgotPasswordMail;

class ForgotPasswordController extends Controller
{
    public function forgotPassword()
    {
        return view('admin::admin.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([            
            'email' => 'required|email:rfc,dns',            
        ]);

        $this->validateEmail($request);
        $admin = Admin::where('email', $request->email)->first();

        if(!$admin){
            return redirect()->back()
            ->withError('We can not find user with this email')
            ->withInput();
        }
        else {
            //$token = base64_encode($admin->id);
            $token = Str::random(7);

            $admin->token = $token;
            DB::table('admin_password_resets')->where('email','=', $request->email)->delete();
            DB::table('admin_password_resets')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);

            $mail_status = Mail::to($admin->email)->send(new ForgotPasswordMail($admin));
            return redirect()->route('admin.forgotPassword')->with(['success' => "We have emailed you password reset link!"]);
        }
    }

    protected function validateEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
    }

}