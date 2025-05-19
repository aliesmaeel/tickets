<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CustomerApiResource;
use App\Models\Customer;
use App\Services\OtpService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerAuthController extends Controller
{
    use ApiResponse;

    protected $otpService;

    public function __construct()
    {
        $this->otpService = new OtpService();
    }

    public function registerRequest(Request $request)
    {
        App::setLocale($request->lang ?? 'en');

        try {

            $data = $request->only('phone', 'password', 'name', 'lang');

            $validator = Validator::make($data, [
                'phone' => 'required|unique:customers,phone',
                'password' => 'required|min:6',
                'name' => 'required|string',
                'lang' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->respondValidationErrors($validator->errors()?->toArray());
            }

           if($this->otpService->send($data['phone'], $data['lang'], $data) ){

               $cached = cache("otp:{$data['phone']}");
               logger()->info('OTP cache data:', ['otp' => $cached]);

               return $this->respondValue(
                   null,
                   __('messages.otp_sent_successfully_to_phone')
               );
           }

           return $this->respondError(__('messages.failed_to_send_otp'), null, 401);

        }catch (\Exception $e){
            logger()->error('Register error:', ['error' => $e->getMessage()]);
            return $this->respondError();
        }
    }

    public function registerVerify(Request $request)
    {
            App::setLocale($request->lang ?? 'en');

            $data = $request->only('recipient', 'code');

            $validator = Validator::make($data, [
                'recipient' => 'required',
                'code' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->respondValidationErrors($validator->errors()->toArray());
            }

            if (!$this->otpService->verify($data['recipient'], $data['code'])) {
                return $this->respondError(__('messages.opt_is_invalid_please_try_again'), null, 401);
            }

            $cached=cache("register:{$data['recipient']}");

            if (!$cached || !isset($cached['password'])) {
                return $this->respondError(__('messages.no_registration_data_found'), null, 401);
            }

            $customer = Customer::create([
                'phone' => $cached['phone'],
                'password' => Hash::make($cached['password']),
                'name' => $cached['name'] ?? null,
                'is_active' => true,
                'lang' => $cached['lang'] ?? null,
            ]);

            cache()->forget("otp:{$data['recipient']}");
            cache()->forget("register:{$data['recipient']}");


            $token = $customer->createToken('auth_token')->plainTextToken;

            $customer = CustomerApiResource::make($customer);

            return $this->respondValue([
                'token' => $token,
                'customer' => $customer,
            ], __('messages.customer_registered_successfully'));


    }

    public function login(Request $request)
    {
        App::setLocale($request->lang ?? 'en');

        try{

            $credentials = $request->only('phone', 'password');

            $validator = Validator::make($credentials, [
                'phone' => 'required|exists:customers,phone',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->respondValidationErrors($validator->errors()?->toArray());
            }

            $customer = Customer::where('phone', $credentials['phone'])
                ->first();

            if (!$customer || !Hash::check($credentials['password'], $customer->password)) {
                return $this->respondError(__('messages.invalid_phone_or_password'), null, 401);
            }


            if (!$customer->is_active) {
                return $this->respondError(__('messages.account_not_active'), null, 401);
            }

            $token = $customer->createToken('auth_token')->plainTextToken;

            $customer = CustomerApiResource::make($customer);

            return $this->respondValue([
                'token' => $token,
                'customer' => $customer,
            ], __('messages.login_successful'));
        }
        catch(\Exception $e){
            logger()->error('Login error:', ['error' => $e->getMessage()]);
            return $this->respondError();
        }
    }

    public function forgotPassword(Request $request)
    {
        App::setLocale($request->lang ?? 'en');

        $validator = Validator::make($request->only('phone','lang'), [
            'phone' => 'required|exists:customers,phone'
        ]);

        if ($validator->fails()) {
            return $this->respondValidationErrors($validator->errors()->toArray());
        }

        $this->otpService->send($request->phone,$request->lang);
        return $this->respondValue(
            null,
            __('messages.otp_sent_successfully_to_phone')
        );
    }

    public function resetPassword(Request $request)
    {
        App::setLocale($request->lang ?? 'en');

        $data = $request->only('phone', 'otp', 'password', 'password_confirmation');

        $validator = Validator::make($data, [
            'phone' => 'required|exists:customers,phone',
            'otp' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->respondValidationErrors($validator->errors()->toArray());
        }

        if (!$this->otpService->verify($data['phone'], $data['otp'])) {
            return $this->respondError( __('messages.opt_is_invalid_please_try_again'), null, 401);
        }

        $customer = Customer::where('phone', $data['phone'])->first();
        $customer->update(['password' => Hash::make($data['password'])]);

        cache()->forget("otp:{$data['phone']}");

        return $this->respondValue(
            null,
            __('messages.password_reset_successfully')
        );
    }
}
