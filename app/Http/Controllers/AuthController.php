<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Services\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller {
    public function loginForm(){return view('auth.login');}
    public function registerForm(){return view('auth.register');}
    public function login(Request $request,Cart $cart){
        $data=$request->validate(['email'=>'required|email','password'=>'required|string']);
        if (!Auth::attempt($data,$request->boolean('remember'))) throw ValidationException::withMessages(['email'=>'These credentials do not match our records.']);
        $request->session()->regenerate();$cart->merge($request,Auth::id());return redirect()->intended(route('account'));
    }
    public function register(Request $request,Cart $cart){
        $data=$request->validate(['name'=>'required|string|max:100','email'=>'required|email|unique:users,email','password'=>['required','confirmed',PasswordRule::defaults()]]);
        $user=User::create($data); Auth::login($user); $request->session()->regenerate();$cart->merge($request,$user->id);return redirect()->route('account');
    }
    public function logout(Request $request){Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('home');}
    public function forgotForm(){return view('auth.forgot');}
    public function forgot(Request $request){$request->validate(['email'=>'required|email']);Password::sendResetLink($request->only('email'));return back()->with('message','If the address exists, a reset link has been sent.');}
    public function resetForm(Request $request,string $token){return view('auth.reset',['token'=>$token,'email'=>$request->query('email')]);}
    public function reset(Request $request){
        $data=$request->validate(['token'=>'required','email'=>'required|email','password'=>['required','confirmed',PasswordRule::defaults()]]);
        $status=Password::reset($data,function(User $user,string $password){$user->forceFill(['password'=>Hash::make($password),'remember_token'=>Str::random(60)])->save();event(new PasswordReset($user));});
        return $status===Password::PASSWORD_RESET ? redirect()->route('login')->with('message','Password updated.') : back()->withErrors(['email'=>__($status)]);
    }
}
