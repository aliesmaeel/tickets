<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    public function getCategories(Request $request)
    {
        try {
            $categories = CategoryResource::collection(Category::all());
            return $this->respondValue('Categories fetched successfully', $categories);
        } catch (Exception $e) {
            logger()->error('Get Categories error:', ['error' => $e->getMessage()]);
            return $this->respondError();
        }
    }
}
