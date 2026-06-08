<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail; // Add this to send emails
use Illuminate\Support\Facades\Hash; // <-- Add this line
use App\Models\PasswordReset; // Import the PasswordReset model
use App\Models\User; // Import the PasswordReset model



use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email', // Ensure the email exists in the users table
        ]);
    
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid email or email does not exist.', 'result' => false], 200);
        }
    
        // Send the password reset link using the Laravel built-in function
        $response = Password::sendResetLink(
            $request->only('email'),
            function ($user, $token) use ($request) {
                // Create the custom reset URL
                $customResetUrl = "https://hrms.spartanstaging.site/change-password";
                $resetUrl = $customResetUrl . '/'.$token;
                 
                // Store token in the password_resets table using Eloquent
                try {
                    // Insert the token and email into the password_resets table
                    PasswordReset::create([
                        'email' => $user->email,
                        'token' => $token,
                        'created_at' => now(), // Timestamp when the token is created
                    ]);
    
                    // Send the custom email with the custom reset URL using HTML content
                    Mail::html(
                        "<html>
                            <body>
                                <h1>Reset Your Password</h1>
                                <p>You are receiving this email because we received a password reset request for your account.</p>
                                <p><a href='{$resetUrl}'>Click here to reset your password</a></p>
                                <p>If you did not request a password reset, no further action is required.</p>
                            </body>
                        </html>",
                        function ($message) use ($user) {
                            $message->to($user->email)
                                    ->subject('Reset Your Password');
                        }
                    );
    
                    // Log success
                    \Log::info('Password reset email sent to ' . $user->email);
                } catch (\Exception $e) {
                    // Log any errors
                    \Log::error('Failed to send reset email: ' . $e->getMessage());
                }
            }
        );
    
        // Return response based on the result
        return $response == Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Password reset link sent.', 'result' => true], 200)
            : response()->json(['message' => 'Failed to send reset link.', 'result' => false], 500);
    }


    public function resetPassword(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:8',
           // 'email' => 'required|email',
        ]);
    
        if ($validator->fails()) {
            // Return validation error with status 200 instead of 422
            return response()->json(['message' => $validator->errors()->first()], 200);
        }
         
    
        // Retrieve the password reset record using Eloquent (password_resets table)
        $passwordReset = PasswordReset::where('token', $request->token)
                                      ->first();
    
        if (!$passwordReset) {
            return response()->json(['message' => 'Invalid token.', 'result' => false], 400);
        }
    
        // Optionally, check if the token has expired (default expiration is 60 minutes)
        $tokenCreationTime = $passwordReset->created_at;
        $tokenExpirationTime = now()->subMinutes(60); // 60 minutes expiration time (can be adjusted)
        
        if ($tokenCreationTime < $tokenExpirationTime) {
            // Delete the expired token to prevent reuse
            PasswordReset::where('token', $request->token)       
                        ->delete();  // Explicit deletion based on token and email
            return response()->json(['message' => 'The password reset token has expired.', 'result' => false], 400);
        }
        $user =    User::where('email',$passwordReset->email)->update([
            'password'=>  Hash::make($request->password), // Hash the password
        ]);
        return response()->json([
            'result' => true,
            'message' => 'Password reset successfully',
                
        ]);
        // Proceed to reset the password if the token is valid
        // $response = Password::reset(
        //     $request->only('password', 'token'),
        //     function ($user) use ($request) {
        //         // Reset the password and save the new hashed password
        //         $user->forceFill([
        //             'password' => Hash::make($request->password), // Hash the password
        //         ])->save();
        //     }
        // );
    
        // Log the response for debugging
        // \Log::debug('Password reset response:', ['response' => $response]);
    
        // // After the password reset, delete the token from the database to prevent reuse
        // PasswordReset::where('token', $request->token)
        //             ->where('email', $request->email)
        //             ->delete();  // Explicit deletion based on token and email
    
        // // Return the response based on the result
        // return $response == Password::PASSWORD_RESET
        //     ? response()->json(['message' => 'Password has been reset successfully.', 'result' => true], 200)
        //     : response()->json(['message' => 'Failed to reset password.', 'result' => false, 'error' => $response], 500);
    }
    
}


