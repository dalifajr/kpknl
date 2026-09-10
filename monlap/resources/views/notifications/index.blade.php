@extends('layouts.app')

@section('title', 'Notification Hub')

@section('content')
<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="d-flex justify-between align-center mb-3">
        <h2 class="card-title" style="margin: 0;">Notification Hub</h2>
        
        @if($notifications->contains('is_read', false))
            <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-flat" style="color: var(--primary);">
                    <i class="material-icons" style="font-size: 18px; margin-right: 4px; vertical-align: middle;">done_all</i> Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>



    <div style="display: flex; flex-direction: column; gap: 8px;">
        @forelse($notifications as $notification)
            <div style="padding: 16px; border-radius: 4px; border: 1px solid var(--divider); display: flex; gap: 16px; background: {{ $notification->is_read ? '#FFFFFF' : '#E0F2F1' }}; transition: var(--transition);">
                <div style="color: {{ $notification->is_read ? 'var(--text-secondary)' : 'var(--primary)' }};">
                    <i class="material-icons" style="font-size: 32px;">notifications</i>
                </div>
                <div style="flex: 1;">
                    <div class="d-flex justify-between" style="margin-bottom: 4px;">
                        <h4 style="margin: 0; font-weight: {{ $notification->is_read ? '500' : '700' }}; color: var(--text-primary);">{{ $notification->title }}</h4>
                        <span style="font-size: 12px; color: var(--text-secondary);">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                    <p style="margin: 0; font-size: 14px; color: {{ $notification->is_read ? 'var(--text-secondary)' : 'var(--text-primary)' }};">{{ $notification->message }}</p>
                    
                    @if($notification->link)
                        <div style="margin-top: 12px;">
                            <a href="{{ route('notifications.read', $notification->id) }}" class="btn btn-primary" style="height: 28px; font-size: 12px; padding: 0 12px; min-width: unset;">Lihat Detail</a>
                        </div>
                    @else
                        @if(!$notification->is_read)
                        <div style="margin-top: 12px;">
                            <a href="{{ route('notifications.read', $notification->id) }}" class="btn btn-flat" style="height: 28px; font-size: 12px; padding: 0 12px; min-width: unset; border: 1px solid var(--divider);">Tandai Dibaca</a>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div style="padding: 32px; text-align: center; color: var(--text-secondary); background: #F9F9F9; border-radius: 4px;">
                <i class="material-icons" style="font-size: 48px; color: #E0E0E0; margin-bottom: 16px;">notifications_none</i><br>
                Belum ada notifikasi.
            </div>
        @endforelse
    </div>

    <div style="margin-top: 24px;">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
