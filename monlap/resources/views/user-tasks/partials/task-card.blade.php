            @php
                $borderColor = match($assignment->status) {
                    'pending' => '#FF9800',
                    'draft' => '#9E9E9E',
                    'submitted' => '#2196F3',
                    'acc' => '#4CAF50',
                    'revisi' => '#F44336',
                    default => '#E0E0E0'
                };
            @endphp
            <div style="border: 1px solid var(--divider); border-top: 4px solid {{ $borderColor }}; border-radius: 4px; padding: 16px; background: var(--surface-color); box-shadow: var(--elevation-1);">
                <div class="d-flex justify-between" style="margin-bottom: 8px;">
                    <span style="background: var(--hover-bg); color: var(--text-secondary); padding: 2px 8px; border-radius: 12px; font-size: 12px;">{{ $assignment->period }}</span>
                    <span style="color: {{ $borderColor }}; font-weight: 500; font-size: 12px; text-transform: uppercase;">
                        {{ $assignment->status }}
                        @if($assignment->submission && $assignment->submission->submitted_by && $assignment->submission->submitted_by !== $assignment->user_id)
                            <br><small style="color: var(--text-secondary); text-transform: none;">(Diambil alih oleh: {{ optional($assignment->submission->submitter)->name }})</small>
                        @endif
                    </span>
                </div>
                <h3 style="font-size: 18px; margin-bottom: 4px;">{{ $assignment->task->title }}</h3>
                @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                    <div style="font-size: 12px; color: var(--primary); margin-bottom: 8px; font-weight: 500;">
                        <i class="material-icons" style="font-size: 14px; vertical-align: middle;">person</i> PIC: {{ optional($assignment->user)->name }}
                    </div>
                @endif
                <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 16px; line-height: 1.4;">
                    {{ Str::limit($assignment->task->description, 80) }}
                </p>
                <div class="d-flex justify-between align-center" style="border-top: 1px solid var(--divider); padding-top: 12px;">
                    @php
                        $notDone = !in_array($assignment->status, ['acc', 'submitted']);
                        $isPast = $assignment->deadline_date && $assignment->deadline_date->isPast();
                        $isNear = $assignment->deadline_date && $assignment->deadline_date->isFuture() && \Carbon\Carbon::now()->diffInDays($assignment->deadline_date) <= 3;
                        $deadlineColor = 'var(--text-secondary)';
                        if ($notDone) {
                            if ($isPast) $deadlineColor = '#F44336';
                            elseif ($isNear) $deadlineColor = '#FF9800';
                        }
                    @endphp
                    <span style="font-size: 12px; color: {{ $deadlineColor }}; font-weight: {{ ($notDone && ($isPast || $isNear)) ? 'bold' : 'normal' }};">
                        <i class="material-icons" style="font-size: 14px; vertical-align: middle;">event</i> 
                        Deadline: {{ $assignment->deadline_date ? $assignment->deadline_date->format('d M Y') : '-' }}
                        @if($notDone && $isPast)
                            <span style="background: #F44336; color: white; padding: 2px 6px; border-radius: 4px; margin-left: 4px; font-weight: normal;">Terlewat</span>
                        @elseif($notDone && $isNear)
                            <span style="background: #FF9800; color: white; padding: 2px 6px; border-radius: 4px; margin-left: 4px; font-weight: normal;">Segera Berakhir</span>
                        @endif
                    </span>
                    @php
                        $isLocked = $assignment->open_date && \Carbon\Carbon::parse($assignment->open_date)->isFuture();
                    @endphp
                    @if($isLocked)
                        <button type="button" class="btn btn-flat" style="height: 28px; font-size: 12px; min-width: unset; padding: 0 12px; color: var(--text-secondary); cursor: not-allowed;" title="Terbuka pada {{ \Carbon\Carbon::parse($assignment->open_date)->format('d M Y') }}">Terkunci</button>
                    @else
                        <a href="{{ route('user-tasks.show', $assignment->id) }}" class="btn btn-primary ripple-surface" style="height: 28px; font-size: 12px; min-width: unset; padding: 0 12px;">Buka</a>
                    @endif
                </div>
            </div>
