<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    use ApiResponse;

    public function getCategories(Request $request)
    {
        try {
            App::setLocale(Auth::user('customer')->lang);

            $categories = CategoryResource::collection(Category::all());
            return $this->respondValue(
                $categories,
                __('messages.categories_retrieved_successfully'),
            );
        } catch (Exception $e) {
            logger()->error('Get Categories error:', ['error' => $e->getMessage()]);
            return $this->respondError();
        }
    }
}
