<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Services\TaskSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgendaController extends Controller
{
    protected $taskSyncService;

    public function __construct(TaskSyncService $taskSyncService)
    {
        $this->taskSyncService = $taskSyncService;
    }

    /**
     * Store a newly created agenda.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_holiday' => 'nullable|boolean',
            'color' => 'nullable|string|max:30',
        ]);

        $isHoliday = $request->boolean('is_holiday');
        $color = $validated['color'] ?? ($isHoliday ? '#e53935' : '#1e88e5');

        $agenda = Agenda::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'is_holiday' => $isHoliday,
            'color' => $color,
            'created_by' => Auth::id(),
        ]);

        // If marked as holiday, recalculate deadlines
        if ($isHoliday) {
            $this->taskSyncService->recalculateAllDeadlines();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Agenda berhasil ditambahkan.' . ($isHoliday ? ' Deadline tugas yang terdampak otomatis disesuaikan.' : ''),
                'data' => $agenda,
            ]);
        }

        return redirect()->back()->with('success', 'Agenda berhasil ditambahkan.');
    }

    /**
     * Update the specified agenda.
     */
    public function update(Request $request, Agenda $agenda)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_holiday' => 'nullable|boolean',
            'color' => 'nullable|string|max:30',
        ]);

        $wasHoliday = $agenda->is_holiday;
        $isHoliday = $request->boolean('is_holiday');
        $color = $validated['color'] ?? ($isHoliday ? '#e53935' : '#1e88e5');

        $agenda->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'is_holiday' => $isHoliday,
            'color' => $color,
        ]);

        // If it was holiday or is now holiday, recalculate deadlines
        if ($wasHoliday || $isHoliday) {
            $this->taskSyncService->recalculateAllDeadlines();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Agenda berhasil diperbarui.' . ($isHoliday || $wasHoliday ? ' Deadline tugas otomatis disesuaikan.' : ''),
                'data' => $agenda,
            ]);
        }

        return redirect()->back()->with('success', 'Agenda berhasil diperbarui.');
    }

    /**
     * Remove the specified agenda.
     */
    public function destroy(Request $request, Agenda $agenda)
    {
        $wasHoliday = $agenda->is_holiday;
        $agenda->delete();

        if ($wasHoliday) {
            $this->taskSyncService->recalculateAllDeadlines();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Agenda berhasil dihapus.' . ($wasHoliday ? ' Deadline tugas otomatis disesuaikan kembali.' : ''),
            ]);
        }

        return redirect()->back()->with('success', 'Agenda berhasil dihapus.');
    }
}
