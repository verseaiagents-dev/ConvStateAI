<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WidgetCustomization;

class WidgetCustomizationController extends Controller
{
    /**
     * Get widget customization data for current user
     */
    public function getCustomization()
    {
        $user = Auth::user();
        $customization = WidgetCustomization::where('user_id', $user->id)->first();
        
        if (!$customization) {
            // Return default values if no customization exists
            return response()->json([
                'success' => true,
                'data' => [
                    'ai_name' => 'Kadir AI',
                    'welcome_message' => 'Merhaba ben Kadir, senin dijital asistanınım. Sana nasıl yardımcı olabilirim?',
                    'customization_data' => null
                ]
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => $customization
        ]);
    }

 
    /**
     * Get public widget customization data (for React app)
     */
    public function getPublicCustomization(Request $request)
    {
        // Personal token ve user ID kontrolü
        $token = $request->header('X-Personal-Token');
        $userId = $request->header('X-User-ID');
        
        if (!$token || !$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Personal token ve user ID gerekli'
            ], 401);
        }
        
        // Token ile user'ı bul
        $user = \App\Models\User::findByPersonalToken($token);
        
        if (!$user || $user->id != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz token veya user ID'
            ], 401);
        }
        
        // User'ın widget customization'ını getir
        $customization = WidgetCustomization::where('user_id', $user->id)->first();
        
        if (!$customization) {
            // Return default values if no customization exists
            return response()->json([
                'success' => true,
                'data' => [
                    'ai_name' => 'Kadir AI',
                    'welcome_message' => 'Merhaba ben Kadir, senin dijital asistanınım. Sana nasıl yardımcı olabilirim?',
                    'customization_data' => null
                ]
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => $customization
        ]);
    }
}
