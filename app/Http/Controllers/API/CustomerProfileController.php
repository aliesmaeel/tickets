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
        return $this->success([
            'customer' => $request->user(),
        ], 'Profile fetched successfully');
    }

    public function update(Request $request)
    {
        $data = $request->only('name', 'lang', 'password');

        $validator = Validator::make($data, [
            'name' => 'nullable|string|max:255',
            'lang' => 'nullable|string|max:10',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
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

        if($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $path = $file->store('profile_images', 'customers');
            $customer->profile_image = $path;
        }

        $customer->save();

        return $this->success([
            'customer' => $customer,
        ], 'Profile updated successfully');
    }
}
