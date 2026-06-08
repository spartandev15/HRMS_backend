<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;
use App\Models\NoticeBoard;    
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class NoticeBoardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notice_board =  NoticeBoard::all();
        return response()->json([
         'result' => true,
         'message' => 'Notice board Lists.',
         'data'=>$notice_board,
        ]);
    }
   
     /** 
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        $notice_board = $this->store($request->all());
        if ($notice_board) {
            return response()->json([
                'result' => true,
                'message' => 'Notice board Created successful.',
                
            ]);
        } else {
            return $this->registrationFailed("Notice board Created failed");
        }
    }  

    /**
     * Store a newly created resource in storage.
     */
    public function store($data)
    {
        $user = auth()->user();
        $notice_board = NoticeBoard::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'user_id' => $user->id,
        ]);
         return $notice_board;
    }
    protected function registrationFailed($message)
    {
        return response()->json([
            'result' => false,
            'message' => $message,
           
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request,$id)
    {
        $user = auth()->user();
        $projects =  NoticeBoard::where('id',$id)->where('user_id',$user->id)->get();
        return response()->json([
         'result' => true,
         'message' => 'Notice Board detail data',
         'data'=>$projects,
     ]);
    }
    public function update_data($data)
    {
        $notice_board = NoticeBoard::where('id',$data['id'])->update([
           'title' => $data['title'],
            'description' => $data['description'],
        ]);
         return $notice_board;
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'descriptionn' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        $notice_board = $this->update_data($request->all());
        if ($notice_board) {
            return response()->json([
                'result' => true,
                'message' => 'NOtice Board Updated Successful.',
                
            ]);
        } else {
            return $this->registrationFailed("Updated Failed");
        }
    }

}
