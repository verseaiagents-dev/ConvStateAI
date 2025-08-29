<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\WidgetCustomization;
use App\Models\WidgetActions;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class WidgetDesignController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->query('project_id');
        
        // If project_id is provided, validate it exists
        if ($projectId) {
            $project = Project::find($projectId);
            if (!$project) {
                abort(404, 'Project not found');
            }
        }
        
        return view('dashboard.widget-design', compact('projectId'));
    }

    public function loadContent(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $projectId = $request->query('project_id');
            
            // Get widget customization data
            $widgetCustomization = WidgetCustomization::where('user_id', $user->id)->first();
            $widgetActions = null;
            
            if ($widgetCustomization) {
                $widgetActions = $widgetCustomization->widgetActions;
            }
            
            // Get project data if project_id is provided
            $project = null;
            if ($projectId) {
                $project = Project::find($projectId);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'widgetCustomization' => $widgetCustomization,
                    'widgetActions' => $widgetActions,
                    'project' => $project
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'İçerik yüklenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // WidgetCustomization'ı bul veya oluştur
            $widgetCustomization = WidgetCustomization::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'ai_name' => 'AI Asistan',
                    'welcome_message' => 'Merhaba! Size nasıl yardımcı olabilirim?',
                    'is_active' => true
                ]
            );
            
            // WidgetActions'ı bul veya oluştur
            $widgetActions = WidgetActions::firstOrCreate(
                ['widget_customization_id' => $widgetCustomization->id],
                [
                    'siparis_durumu_endpoint' => $request->siparis_durumu_endpoint,
                    'kargo_durumu_endpoint' => $request->kargo_durumu_endpoint,
                    'http_action' => 'GET'
                ]
            );
            
            // Mevcut verileri güncelle
            $widgetActions->update([
                'siparis_durumu_endpoint' => $request->siparis_durumu_endpoint,
                'kargo_durumu_endpoint' => $request->kargo_durumu_endpoint,
                'http_action' => 'GET'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Widget ayarları başarıyla kaydedildi',
                'data' => $widgetActions
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Widget ayarları kaydedilirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testEndpoint(Request $request): JsonResponse
    {
        try {
            $endpoint = $request->endpoint;
            $type = $request->type; // 'siparis' veya 'kargo'
            
            if (!$endpoint) {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint belirtilmedi'
                ], 400);
            }
            
            // Test request gönder (basit HTTP test)
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return response()->json([
                    'success' => true,
                    'message' => 'Endpoint başarıyla test edildi',
                    'data' => [
                        'status' => $httpCode,
                        'endpoint' => $endpoint,
                        'type' => $type
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint test edilemedi: HTTP ' . $httpCode,
                    'data' => [
                        'status' => $httpCode,
                        'endpoint' => $endpoint,
                        'type' => $type
                    ]
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint test edilirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
}
