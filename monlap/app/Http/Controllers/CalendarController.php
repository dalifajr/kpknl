<?php

namespace App\Http\Controllers;

use App\Models\TaskAssignment;
use App\Models\Holiday;
use App\Models\Agenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar.index');
    }

    public function getEvents(Request $request)
    {
        $startQuery = $request->query('start');
        $endQuery = $request->query('end');

        $query = TaskAssignment::with(['task', 'user']);

        if (Auth::user()->role === 'user' || Auth::user()->role === 'pic') {
            $query->where('user_id', Auth::id());
        }

        if ($startQuery) {
            $query->where(function($q) use ($startQuery, $endQuery) {
                $q->whereBetween('deadline_date', [$startQuery, $endQuery])
                  ->orWhereBetween('created_at', [$startQuery, $endQuery])
                  ->orWhere('open_date', '>=', $startQuery);
            });
        }

        $assignments = $query->get();
        $events = [];

        foreach ($assignments as $assignment) {
            $color = '#2196f3'; // blue
            if ($assignment->status === 'submitted') {
                $color = '#ff9800'; // orange
            } elseif ($assignment->status === 'approved') {
                $color = '#4caf50'; // green
            } elseif ($assignment->status === 'revision') {
                $color = '#f44336'; // red
            }

            $startDateStr = $assignment->open_date ? $assignment->open_date : $assignment->created_at->format('Y-m-d');

            // Tanda Ujung (Deadline Event)
            $events[] = [
                'id' => 'task_' . $assignment->id,
                'title' => $assignment->task->title,
                'start' => $assignment->deadline_date,
                'allDay' => true,
                'color' => $color,
                'extendedProps' => [
                    'is_task' => true,
                    'pic' => $assignment->user ? $assignment->user->name : '-',
                    'period' => $assignment->period,
                    'status' => $assignment->status,
                    'assignment_id' => $assignment->id,
                    'task_type' => $assignment->task->period_type,
                    'start_date' => $startDateStr,
                    'deadline' => $assignment->deadline_date,
                ]
            ];
        }

        // Add National Holidays
        $holidaysQuery = Holiday::where('is_national_holiday', true);
        if ($startQuery) {
            $holidaysQuery->whereBetween('date', [$startQuery, $endQuery]);
        }
        $holidays = $holidaysQuery->get();

        foreach ($holidays as $holiday) {
            $events[] = [
                'id' => 'holiday_text_' . $holiday->id,
                'title' => 'Libur: ' . $holiday->name,
                'start' => $holiday->date,
                'allDay' => true,
                'color' => '#d32f2f', // red for holiday
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'is_holiday' => true,
                    'name' => $holiday->name,
                    'date' => $holiday->date,
                ]
            ];
        }

        // Add Agendas
        $agendasQuery = Agenda::query();
        if ($startQuery) {
            $agendasQuery->where(function($q) use ($startQuery, $endQuery) {
                $q->whereBetween('start_date', [$startQuery, $endQuery])
                  ->orWhere(function($sub) use ($startQuery, $endQuery) {
                      $sub->whereNotNull('end_date')
                          ->where('start_date', '<=', $endQuery)
                          ->where('end_date', '>=', $startQuery);
                  });
            });
        }
        $agendas = $agendasQuery->get();

        $canManageAgenda = in_array(Auth::user()->role, ['admin', 'superadmin']);

        foreach ($agendas as $agenda) {
            $isHoliday = (bool) $agenda->is_holiday;
            $color = $agenda->color ?: ($isHoliday ? '#e53935' : '#1e88e5');
            
            $endDate = null;
            if ($agenda->end_date && $agenda->end_date > $agenda->start_date) {
                $endDate = Carbon::parse($agenda->end_date)->addDay()->format('Y-m-d');
            }

            $events[] = [
                'id' => 'agenda_' . $agenda->id,
                'title' => ($isHoliday ? '📌 [Libur] ' : '📅 ') . $agenda->title,
                'start' => $agenda->start_date ? $agenda->start_date->format('Y-m-d') : null,
                'end' => $endDate,
                'allDay' => true,
                'color' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'is_agenda' => true,
                    'agenda_id' => $agenda->id,
                    'title' => $agenda->title,
                    'description' => $agenda->description,
                    'start_date' => $agenda->start_date ? $agenda->start_date->format('Y-m-d') : null,
                    'end_date' => $agenda->end_date ? $agenda->end_date->format('Y-m-d') : null,
                    'is_holiday' => $isHoliday,
                    'color' => $color,
                    'can_manage' => $canManageAgenda,
                ]
            ];
        }

        return response()->json($events);
    }
}
