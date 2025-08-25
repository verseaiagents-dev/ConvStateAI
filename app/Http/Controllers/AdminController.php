<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\AdminUser;
use App\Models\Campaign;
use App\Models\FAQ;
use App\Models\KnowledgeBase;
use App\Models\EnhancedChatSession;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function index()
    {
        $admin = Auth::user();
        
        // Admin istatistikleri
        $stats = [
            'total_users' => User::count(),
            'total_campaigns' => Campaign::count(),
            'total_faqs' => FAQ::count(),
            'total_knowledge_bases' => KnowledgeBase::count(),
            'total_chat_sessions' => EnhancedChatSession::count(),
            'recent_users' => User::latest()->take(5)->get(),
            'recent_campaigns' => Campaign::latest()->take(5)->get(),
        ];
        
        return view('admin.dashboard', compact('admin', 'stats'));
    }

    /**
     * Show admin profile
     */
    public function profile()
    {
        $admin = Auth::user();
        return view('admin.profile', compact('admin'));
    }

    /**
     * Show admin settings
     */
    public function settings()
    {
        $admin = Auth::user();
        return view('admin.settings', compact('admin'));
    }

    /**
     * Show user management
     */
    public function users()
    {
        $users = User::with('campaigns')->latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    /**
     * Show system analytics
     */
    public function analytics()
    {
        $admin = Auth::user();
        
        // Sistem istatistikleri
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('last_login_at', '>', now()->subDays(30))->count(),
            'total_campaigns' => Campaign::count(),
            'active_campaigns' => Campaign::where('status', 'active')->count(),
            'total_faqs' => FAQ::count(),
            'total_knowledge_bases' => KnowledgeBase::count(),
            'total_chat_sessions' => EnhancedChatSession::count(),
            'recent_chat_sessions' => EnhancedChatSession::with('user')->latest()->take(10)->get(),
        ];
        
        return view('admin.analytics', compact('admin', 'stats'));
    }
}
