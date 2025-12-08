<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\EventRegistration;
use App\Models\Event;

class RacerHistoryController extends Controller
{
    /**
     * Menampilkan halaman history semua pembalap
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'pembalap')
            ->whereHas('profile')
            ->whereHas('kisLicense');

        // Search by nama pembalap
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by klub
        if ($request->filled('club_id')) {
            $query->whereHas('profile', function($q) use ($request) {
                $q->where('club_id', $request->club_id);
            });
        }

        // Filter by kategori KIS
        if ($request->filled('category_id')) {
            $query->whereHas('kisLicense', function($q) use ($request) {
                $q->where('kis_category_id', $request->category_id);
            });
        }

        // Load relationships
        $query->with([
            'profile.club',
            'kisLicense.kisCategory',
            'eventRegistrations' => function($q) {
                $q->whereNotNull('result_position')
                    ->with('event')
                    ->orderBy('created_at', 'desc')
                    ->limit(5);
            }
        ])
        ->withCount([
            'eventRegistrations as total_races' => function($q) {
                $q->whereNotNull('result_position');
            },
            'eventRegistrations as total_wins' => function($q) {
                $q->where('result_position', 1);
            },
            'eventRegistrations as total_podiums' => function($q) {
                $q->whereIn('result_position', [1, 2, 3]);
            }
        ])
        ->withSum('eventRegistrations as total_points', 'points_earned');

        // Sort by
        $sortBy = $request->get('sort_by', 'points'); // default: points
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'wins':
                $query->orderBy('total_wins', 'desc');
                break;
            case 'races':
                $query->orderBy('total_races', 'desc');
                break;
            case 'podiums':
                $query->orderBy('total_podiums', 'desc');
                break;
            default: // points
                $query->orderBy('total_points', 'desc');
                break;
        }

        $racers = $query->paginate(10)->withQueryString();

        // Get data untuk filter dropdown
        $clubs = \App\Models\Club::orderBy('nama_klub')->get();
        $categories = \App\Models\KisCategory::orderBy('tipe')->orderBy('nama_kategori')->get();

        return view('racer-history.index', compact('racers', 'clubs', 'categories'));
    }

    /**
     * Menampilkan detail history satu pembalap
     */
    public function show($userId)
    {
        $racer = User::where('role', 'pembalap')
            ->where('id', $userId)
            ->whereHas('profile')
            ->with([
                'profile.club',
                'kisLicense.kisCategory',
                'eventRegistrations' => function($query) {
                    $query->whereNotNull('result_position')
                        ->with('event')
                        ->orderBy('created_at', 'desc');
                }
            ])
            ->withCount([
                'eventRegistrations as total_races' => function($query) {
                    $query->whereNotNull('result_position');
                },
                'eventRegistrations as total_wins' => function($query) {
                    $query->where('result_position', 1);
                },
                'eventRegistrations as total_podiums' => function($query) {
                    $query->whereIn('result_position', [1, 2, 3]);
                }
            ])
            ->withSum('eventRegistrations as total_points', 'points_earned')
            ->firstOrFail();

        // Group races by year
        $racesByYear = $racer->eventRegistrations
            ->filter(function($reg) {
                return $reg->result_position !== null;
            })
            ->groupBy(function($reg) {
                return \Carbon\Carbon::parse($reg->created_at)->format('Y');
            })
            ->sortKeysDesc();

        // Get championship wins (1st place)
        $championships = $racer->eventRegistrations
            ->where('result_position', 1);

        return view('racer-history.show', compact('racer', 'racesByYear', 'championships'));
    }
}
