@if (session('success'))
    <div class="alert alert-success">
        <span>✓</span>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-error">
        <span>✕</span>
        <div>{{ session('error') }}</div>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning">
        <span>⚠</span>
        <div>{{ session('warning') }}</div>
    </div>
@endif

@if (session('info'))
    <div class="alert alert-info">
        <span>ℹ</span>
        <div>{{ session('info') }}</div>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-error">
        <span>✕</span>
        <div>
            <ul style="margin-left: 16px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
