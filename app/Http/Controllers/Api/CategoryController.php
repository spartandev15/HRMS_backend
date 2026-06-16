<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\Employee;
use App\Models\LeaveManagement;
use App\Models\Category;
use App\Models\JobDetail;
use App\Models\User;
use App\Models\User_Detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:categories,name',
        ]);
        
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        
        $category = $this->store($request->all());
        if ($category) {
            return response()->json([
                'result' => true,
                'message' => 'category Created successful.',
                
            ]);
        } else {
            return $this->registrationFailed("created failed");
        }
    }

    public function store($data)

    {
        
        $category_data = Category::create([
            'name' => $data['name'],
            'orpect_user_id' => auth()->user()->orpect_user_id
        ]);
         return $category_data;
    }
    protected function registrationFailed($message)
    {
        return response()->json([
            'result' => false,
            'message' => $message,
           
        ]);
    }


    public function edit(Request $request,$id)   // to show existing data
    {
       
        
        $category =  Category::where('id',$id)->where('user_id',$user->id)->get();
        return response()->json([
         'result' => true,
         'message' => 'Categoty detail data',
         'data'=>$category,
     ]);
    }

    public function update_data($data)
    {
        // echo "<pre>";print_r($data);die;
        $category = Category::where('id',$data['id'])->update([
            'name' => $data['name']
        ]);
         return $category;
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        $category = $this->update_data($request->all());
        if ($category) {
            return response()->json([
                'result' => true,
                'message' => 'Category updated successfully.',
                
            ]);
        } else {
            return $this->registrationFailed("updated failed");
        }
    }
    public function get_allcategory(Request $request)
{
    // Get the page number from the query string, default to 1 if not provided
    $page = $request->get('page', 1);  // Default to page 1 if no 'page' parameter is passed
    
    // Set the fixed number of records per page
    $perPage = 5; // You can change this to any number of records per page you want

    // Paginate the categories, 5 categories per page
    $orpectUserId = auth()->user()->orpect_user_id;

    // $category_data = Category::paginate($perPage, ['*'], 'page', $page);
    $category_data = Category::where('orpect_user_id', $orpectUserId)
                    ->paginate($perPage, ['*'], 'page', $page);
    
    // Check if there are any categories
    if ($category_data->isEmpty()) {
        return response()->json([
            'result' => false,
            'message' => 'No categories available',
            'categories' => [],
            'pagination' => [
                'current_page' => $category_data->currentPage(),
                'per_page' => $category_data->perPage(),
                'total' => $category_data->total(),
                'last_page' => $category_data->lastPage(),
            ]
        ]);
    }

    // Return categories data with pagination
    return response()->json([
        'result' => true,
        'message' => 'Getting all categories data',
        'categories' => $category_data->items(),  // Get only the items for the current page
        'pagination' => [
            'current_page' => $category_data->currentPage(),
            'per_page' => $category_data->perPage(),
            'total' => $category_data->total(),
            'last_page' => $category_data->lastPage(),
        ]
    ]);
}



    public function delete_category(Request $request){
        $id = $request->id;
        $category = Category::where('id',$id)->first();
        if(!is_null($category)){
            // User::where('id',$employee->user_id)->delete();
            // User_Detail::where('user_id',$employee->user_id)->delete();
            // JobDetail::where('user_id',$employee->user_id)->delete();
            // LeaveManagement::where('user_id',$employee->user_id)->delete();
            Category::where('id',$id)->delete();
            return response()->json([   
                'result' => true,
                'message' => 'Category all data is deleted',
            ]);
        }else{
            return response()->json([   
                'result' => false,
                'message' => 'Category id  is not valid',
            ]);
        }
    }
}