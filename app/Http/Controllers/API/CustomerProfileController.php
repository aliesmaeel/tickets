<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerProfileController extends Controller
{
    use ApiResponse;

    public function getProfile(Request $request)
    {
        try {
            $customer = $request->user();
            if (!$customer) {
                return $this->respondError('Customer not found', null, 404);
            }
            return $this->respondValue($customer, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Profile retrieval error:', ['error' => $e->getMessage()]);
            return $this->respondError('Failed to retrieve profile', null, 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $data = $request->only('name', 'lang', 'password','password_confirmation','image');

        $validator = Validator::make($data, [
            'name' => 'nullable|string|max:255',
            'lang' => 'nullable|string|max:10',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->respondValidationErrors($validator->errors()?->toArray());
        }

        $customer = $request->user();

        if (isset($data['name'])) {
            $customer->name = $data['name'];
        }

        if (isset($data['lang'])) {
            $customer->lang = $data['lang'];
        }

        if (isset($data['password'])) {
            $customer->password = Hash::make($data['password']);
        }

        if($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('images', 'customers');
            $customer->image = '/storage/customers/'.$path;
        }

        $customer->save();

        return $this->respondSuccess('Profile updated successfully', [
            'customer' => $customer,
        ]);
    }
}
