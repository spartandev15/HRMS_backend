<?php

namespace App\Http\Controllers\Api;

use App\Models\UserDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserDocumentController extends Controller
{


    public function getDocumentById(Request $request)
    {
        $documents = UserDocument::where('user_id', auth()->user()->id)->first();
        // Check if documents were found, if not, return a 404 response
        if (!$documents) {
            return response()->json(['message' => 'Documents not found.'], 404);
        }
        $documents->aadhaar_card = $documents->aadhaar_card ? json_decode($documents->aadhaar_card, true) : null;
        $documents->pan_card = $documents->pan_card ? json_decode($documents->pan_card, true) : null;
        $documents->tenth_dmc = $documents->tenth_dmc ? json_decode($documents->tenth_dmc, true) : null;
        $documents->twelfth_dmc = $documents->twelfth_dmc ? json_decode($documents->twelfth_dmc, true) : null;
        $documents->college_degree = $documents->college_degree ? json_decode($documents->college_degree, true) : null;
        $documents->user_photo = $documents->user_photo ? json_decode($documents->user_photo, true) : null;
        $documents->bank_details =$documents->bank_details ? json_decode($documents->bank_details, true) : null;
        $documents->previous_experience = $documents->previous_experience ? json_decode($documents->previous_experience, true) : null;
        $documents->previous_salary_slip = $documents->previous_salary_slip ? json_decode($documents->previous_salary_slip, true) : null;
    

        return response()->json([
            'message' => 'Documents retrieved successfully.',
            'data'    => $documents
        ], 200);
    }
    
    public function getAllDocuments(Request $request)
    {
        // Define how many records per page you want to retrieve, default to 10 if not specified in the request
        $perPage = $request->get('per_page', 10); 
    
        // Fetch paginated documents with their associated user information
        $documents = UserDocument::with('user')->paginate($perPage);
    
        // Iterate through each document and decode the JSON fields
        foreach($documents as $data){
            $data->aadhaar_card = json_decode($data->aadhaar_card, true);
            $data->pan_card = json_decode($data->pan_card, true);
            $data->tenth_dmc = json_decode($data->tenth_dmc, true);
            $data->twelfth_dmc = json_decode($data->twelfth_dmc, true);
            $data->college_degree = json_decode($data->college_degree, true);
            $data->user_photo = json_decode($data->user_photo, true);
            $data->bank_details = json_decode($data->bank_details, true);
            $data->previous_experience = json_decode($data->previous_experience, true);
            $data->previous_salary_slip = json_decode($data->previous_salary_slip, true);
        }
    
        // Check if no documents are found
        if ($documents->isEmpty()) {
            return response()->json(['message' => 'No documents found.'], 404);
        }
    
        // Return the paginated response with meta information
        return response()->json([
            'message' => 'All documents with user details retrieved successfully.',
            'data'    => $documents,
            'pagination' => [
                'total'        => $documents->total(),
                'current_page' => $documents->currentPage(),
                'per_page'     => $documents->perPage(),
                'last_page'    => $documents->lastPage(),
                'next_page_url'=> $documents->nextPageUrl(),
                'prev_page_url'=> $documents->previousPageUrl(),
            ]
        ], 200);
    }
    

    public function getDocumentDetailsById($id)
    {
        $document = UserDocument::with('user')->find($id);
        $document->aadhaar_card = json_decode($document->aadhaar_card, true);
        $document->pan_card = json_decode($document->pan_card, true);
        $document->tenth_dmc = json_decode($document->tenth_dmc, true);
        $document->twelfth_dmc = json_decode($document->twelfth_dmc, true);
        $document->college_degree = json_decode($document->college_degree, true);
        $document->user_photo = json_decode($document->user_photo, true);
        $document->bank_details = json_decode($document->bank_details, true);
        $document->previous_experience = json_decode($document->previous_experience, true);
        $document->previous_salary_slip = json_decode($document->previous_salary_slip, true);
        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        return response()->json([
            'message' => 'Document and user details retrieved successfully.',
            'data'    => [
                'document' => $document,
                'user' => $document->user // Assuming the 'user' relation is set in the UserDocument model
            ]
        ], 200);
    }

  
    public function uploadDocuments(Request $request)
    {
        $overallStatus = 'uploaded'; // Default to uploaded unless a pending document is found
        $documentPaths = [];
    
        $fields = [
            'aadhaar_card', 'pan_card', 'twelfth_dmc', 'tenth_dmc',
            'college_degree', 'previous_salary_slip', 'previous_experience',
            'user_photo', 'bank_details'
        ];
    
        $userDocument = UserDocument::where('user_id', auth()->user()->id)->first();
        if (!$userDocument) {
            $userDocument = new UserDocument();
            $userDocument->user_id = auth()->user()->id;
        }
    
        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                $files = $request->file($field);
    
                if (is_array($files)) {
                    // For multiple files
                    $existingDocs = $userDocument->$field ? json_decode($userDocument->$field, true) : [];
                    $existingDocs = is_array($existingDocs) ? $existingDocs : [];
    
                    foreach ($files as $file) {
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $file->move(public_path('user-documents/' . auth()->user()->id), $filename);
    
                        $existingDocs[] = [
                            'name' => $field,
                            'url' => url('public/user-documents/' . auth()->user()->id . '/' . $filename),
                            'status' => 'uploaded'
                        ];
                    }
                    $documentPaths[$field] = $existingDocs; // Ensure it remains an array
                } else {
                    // For a single file
                    $filename = time() . '_' . $files->getClientOriginalName();
                    $files->move(public_path('user-documents/' . auth()->user()->id), $filename);
                         $documentPaths[$field] = (object)[
                            'name' => $field,
                            'url' => url('public/user-documents/' . auth()->user()->id . '/' . $filename),
                            'status' => 'uploaded'
                        ];
                    }
            }
        }
    
        foreach ($documentPaths as $doc) {
            if (is_array($doc)) {
                foreach ($doc as $item) {
                    if (isset($item['status']) && $item['status'] === 'pending') {
                        $overallStatus = 'pending';
                        break 2;
                    }
                }
            } elseif (is_object($doc)) {
                // Handle case where doc is an object (e.g., bank_details)
                if (isset($doc->status) && $doc->status === 'pending') {
                    $overallStatus = 'pending';
                    break;
                }
            }else {
                if (isset($doc['status']) && $doc['status'] === 'pending') {
                    $overallStatus = 'pending';
                    break;
                }
            }
        }
    
        $userDocument->status = $overallStatus;
    
        // Ensure you store the document paths as JSON strings for multi-file or single-file
        foreach ($documentPaths as $field => $data) {
            if (in_array($field, ['previous_salary_slip', 'previous_experience'])) {
                // For fields like salary slip or experience that can have multiple documents, encode as an array
                $userDocument->$field = json_encode(array_values($data));
            } else {
                // For other fields, encode as a single array of objects
                $userDocument->$field = json_encode($data);
            }
        }
    
        $userDocument->save();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Documents uploaded successfully.',
            'user_documents' => $documentPaths
        ], 200);
    }
    

public function updateDocumentStatus(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        // 'document_field' => 'required|string|in:aadhaar_card,pan_card,tenth_dmc,twelfth_dmc,college_degree,user_photo,bank_details,previous_experience,previous_salary_slip',
        'document_field' => 'required',
        'status' => 'required',
        'document_url' => 'nullable|string'
    ]);

    $userDocument = UserDocument::where('user_id', $request->user_id)->first();
    // dd($userDocument);
    if (!$userDocument) {
        return response()->json(['message' => 'Document record not found for this user.'], 404);
    }

    $documentField = $request->document_field;
    $documentData = json_decode($userDocument->$documentField, true) ?? [];

    if (in_array($documentField, ['previous_experience', 'previous_salary_slip'])) {
        if (!$request->document_url) {
            return response()->json(['message' => 'Document URL is required for multi-document fields.'], 400);
        }

        $updated = false;
        foreach ($documentData as &$doc) {
            if (isset($doc['url']) && $doc['url'] === $request->document_url) {
                $doc['status'] = $request->status;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            return response()->json(['message' => 'Document with the provided URL not found.'], 404);
        }
    } else {
        $documentData['status'] = $request->status;
    }

    $userDocument->$documentField = json_encode($documentData);
    $userDocument->save();

    return response()->json([
        'message' => 'Document status updated successfully.',
        'document_field' => $documentField,
        'status' => $request->status
    ], 200);
}

    
    
   
}
