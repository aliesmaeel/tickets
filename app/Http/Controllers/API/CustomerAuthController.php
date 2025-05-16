<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\OtpService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
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
        $data = $request->only('phone', 'password', 'name', 'lang');

        $validator = Validator::make($data, [
            'phone' => 'required|unique:customers,phone',
            'password' => 'required|min:6',
            'name' => 'required|string',
            'lang' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        try {
            $this->otpService->send($data['phone'], $data['lang'], [
                'password' => $data['password'],
                'name' => $data['name'],
                'lang' => $data['lang'],
                'recipient' => $data['phone'],
            ]);


            $cached = cache("otp:{$data['phone']}");
            logger()->info('OTP cache data:', ['otp' => $cached]);

        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }

        return $this->success([], 'OTP sent to your phone');
    }

    public function registerVerify(Request $request)
    {
        $data = $request->only('recipient', 'code');

        $validator = Validator::make($data, [
            'recipient' => 'required',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        if (!$this->otpService->verify($data['recipient'], $data['code'])) {
            return $this->error([], 'Invalid OTP', 401);
        }

        $cached = cache("register:{$data['recipient']}");
        if (!$cached || !isset($cached['password'])) {
            return $this->error([], 'No registration data found', 400);
        }


        $customer = Customer::create([
            'phone' => $cached['recipient'],
            'password' => Hash::make($cached['password']),
            'name' => $cached['name'] ?? null,
            'is_active' => true,
            'lang' => $cached['lang'] ?? null,
        ]);

        cache()->forget("otp:{$data['recipient']}");
        cache()->forget("register:{$data['recipient']}");


        $token = $customer->createToken('auth_token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'customer' => $customer,
        ], 'Customer registered successfully');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('phone', 'password');

        $validator = Validator::make($credentials, [
            'phone' => 'required|exists:customers,phone',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        $customer = Customer::where('phone', $credentials['phone'])->first();

        if (!$customer || !Hash::check($credentials['password'], $customer->password)) {
            return $this->error([], 'Invalid phone or password', 401);
        }

        if (!$customer->is_active) {
            return $this->error([], 'Account is not active', 403);
        }

        $token = $customer->createToken('auth_token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'customer' => $customer,
        ], 'Login successful');
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->only('phone','lang'), [
            'phone' => 'required|exists:customers,phone'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        $this->otpService->send($request->phone,$request->lang);
        return $this->success([], 'OTP sent to your phone');
    }

    public function resetPassword(Request $request)
    {
        $data = $request->only('phone', 'otp', 'password', 'password_confirmation');

        $validator = Validator::make($data, [
            'phone' => 'required|exists:customers,phone',
            'otp' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        if (!$this->otpService->verify($data['phone'], $data['otp'])) {
            return $this->error([], 'Invalid OTP', 401);
        }

        $customer = Customer::where('phone', $data['phone'])->first();
        $customer->update(['password' => Hash::make($data['password'])]);

        cache()->forget("otp:{$data['phone']}");

        return $this->success([], 'Password reset successfully');
    }
}
