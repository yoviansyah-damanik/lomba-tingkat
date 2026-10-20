<?php

namespace App\Http\Controllers;

use App\Models\AssessmentCriterion;
use App\Models\Competition;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCompetitionController extends Controller
{
    /**
     * Tampilkan katalog seluruh cabang mata lomba
     */
    public function index(Request $request): View
    {
        $query = Competition::withCount(['criteria', 'schedules'])->orderBy('code');

        // Filter berdasarkan kategori cabang lomba
        if ($request->filled('category') && $request->category !== 'all_categories') {
            $query->where('category', $request->category);
        }

        // Filter berdasarkan tipe kepesertaan
        if ($request->filled('participant_type')) {
            $query->where('participant_type', $request->participant_type);
        }

        // Pencarian nama atau kode lomba
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $competitions = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => Competition::count(),
            'patrol' => Competition::whereIn('participant_type', ['patrol', 'full_patrol'])->count(),
            'group' => Competition::where('participant_type', 'group')->count(),
            'individual' => Competition::where('participant_type', 'individual')->count(),
            'published' => Competition::where('is_published', true)->count(),
        ];

        return view('admin.competitions.index', compact('competitions', 'stats'));
    }

    /**
     * Tampilkan formulir tambah cabang lomba baru
     */
    public function create(): View
    {
        $events = Event::all();
        return view('admin.competitions.create', compact('events'));
    }

    /**
     * Simpan cabang lomba baru ke database
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:competitions,code',
            'category' => 'required|in:boys,girls,all',
            'participant_type' => 'required|in:patrol,group,individual',
            'min_participants' => 'required|integer|min:1',
            'max_participants' => 'required|integer|min:1|gte:min_participants',
            'scoring_formula' => 'required|in:average,sum',
            'description' => 'nullable|string',
            'guidelines_text' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

        $validated['is_published'] = $request->boolean('is_published', true);
        $validated['event_id'] = Event::where('is_active', true)->value('id') ?? Event::first()?->id;

        $competition = Competition::create($validated);

        return redirect()->route('admin.competitions.edit', $competition)
            ->with('status', "Mata lomba {$competition->title} berhasil dibuat. Silakan tambahkan kriteria rubrik penilaian.");
    }

    /**
     * Tampilkan formulir edit cabang lomba dan manajemen rubrik kriteria
     */
    public function edit(Competition $competition): View
    {
        $competition->load(['criteria', 'schedules.judges']);
        $events = Event::all();

        return view('admin.competitions.edit', compact('competition', 'events'));
    }

    /**
     * Perbarui data cabang lomba
     */
    public function update(Request $request, Competition $competition): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:competitions,code,' . $competition->id,
            'category' => 'required|in:boys,girls,all',
            'participant_type' => 'required|in:patrol,group,individual',
            'min_participants' => 'required|integer|min:1',
            'max_participants' => 'required|integer|min:1|gte:min_participants',
            'scoring_formula' => 'required|in:average,sum',
            'description' => 'nullable|string',
            'guidelines_text' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

        $validated['is_published'] = $request->boolean('is_published', true);

        $competition->update($validated);

        return back()->with('status', "Data mata lomba {$competition->title} berhasil diperbarui.");
    }

    /**
     * Hapus cabang lomba beserta kriteria dan jadwalnya
     */
    public function destroy(Competition $competition): RedirectResponse
    {
        $title = $competition->title;
        $competition->delete();

        return redirect()->route('admin.competitions.index')
            ->with('status', "Mata lomba {$title} berhasil dihapus.");
    }

    /**
     * Tambah kriteria rubrik penilaian baru untuk cabang lomba
     */
    public function storeCriterion(Request $request, Competition $competition): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'min_score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|gt:min_score',
            'weight_percentage' => 'required|numeric|min:1|max:100',
            'order_index' => 'nullable|integer',
        ]);

        $validated['competition_id'] = $competition->id;
        $validated['order_index'] = $request->filled('order_index') 
            ? (int) $request->order_index 
            : ($competition->criteria()->count() + 1);

        $competition->criteria()->create($validated);

        return back()->with('status', "Kriteria rubrik '{$validated['name']}' berhasil ditambahkan ke lomba {$competition->title}.");
    }

    /**
     * Hapus kriteria rubrik penilaian
     */
    public function destroyCriterion(AssessmentCriterion $criterion): RedirectResponse
    {
        $name = $criterion->name;
        $criterion->delete();

        return back()->with('status', "Kriteria rubrik '{$name}' berhasil dihapus.");
    }
}
