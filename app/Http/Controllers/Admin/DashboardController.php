<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\Material;
use App\Models\Question;
use App\Models\GameSession;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // SECURITY CHECK - Lapis Kedua
        // Memastikan siswa tidak bisa mengakses dashboard meskipun lolos middleware
        if (Auth::check() && !Auth::user()->canAccessQGame()) {
            return redirect()->route('game')->with('error', 'Akses Ditolak: Akun Siswa tidak diizinkan masuk Dashboard Guru.');
        }

        $totalTopics = Topic::count();
        $totalMaterials = Material::count();
        $totalQuestions = Question::count();
        $totalSessions = GameSession::count();
        
        $recentSessions = GameSession::with('topic')
                            ->orderBy('created_at', 'desc')
                            ->take(5)
                            ->get();

        // Get recent tournaments
        $recentTournaments = Tournament::orderBy('created_at', 'desc')
                                ->take(5)
                                ->get();

        // Format names for display
        foreach ($recentSessions as $session) {
            $session->display_title = $this->getSessionName($session);
        }

        return view('admin.dashboard', compact(
            'totalTopics', 
            'totalMaterials', 
            'totalQuestions', 
            'totalSessions', 
            'recentSessions',
            'recentTournaments'
        ));
    }

    private function getSessionName($session)
    {
        // Logic penamaan session
        if ($session->material_id) {
            $matName = $session->material ? $session->material->name : 'Materi Dihapus';
            $matId = $session->material_id;
            
            // Count previous sessions with same material to append number
            $count = GameSession::where('material_id', $matId)
                        ->where('id', '<=', $session->id)
                        ->count();
            
            // Or count total? Usually sequential ID based for uniqueness in view
             // Let's just use the logic: "Materi A (Game 1)", "Materi A (Game 2)" 
             // or check if there are multiple
             
             $totalWithSame = GameSession::where('material_id', $matId)->count();
             if($totalWithSame > 1) {
                  // Find rank
                  $rank = GameSession::where('material_id', $matId)
                        ->where('id', '<=', $session->id)
                        ->count();
                  return "$matName #$rank";
             }
             return $matName;
        }

        return $session->title ?? $session->topic->name ?? 'Game Session';
    }
}
