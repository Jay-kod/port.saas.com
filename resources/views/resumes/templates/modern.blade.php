<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #1a1a1a; font-size: 12px; }
        h1 { font-size: 22px; margin-bottom: 0; }
        h2 { font-size: 14px; color: #444; margin-top: 0; }
        .section { margin-top: 16px; }
        .section-title { font-size: 13px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
    </style>
</head>
<body>
    <h1>{{ $profile->full_name }}</h1>
    <h2>{{ $profile->headline }}</h2>
    <p>{{ $profile->email }} @if($profile->phone) &middot; {{ $profile->phone }} @endif @if($profile->location) &middot; {{ $profile->location }} @endif</p>

    @if($generation && $generation->tailored_content)
        <div class="section">
            <div class="section-title">Summary</div>
            <p>{{ $generation->tailored_content['summary'] ?? '' }}</p>
        </div>
    @else
        <div class="section">
            <div class="section-title">Summary</div>
            <p>{{ $profile->bio }}</p>
        </div>
    @endif

    <div class="section">
        <div class="section-title">Experience</div>
        @foreach($experiences as $experience)
            <p><strong>{{ $experience->title }}</strong> — {{ $experience->company }}<br>
            {{ $experience->description }}</p>
        @endforeach
    </div>

    <div class="section">
        <div class="section-title">Skills</div>
        <p>{{ $skills->pluck('name')->implode(', ') }}</p>
    </div>
</body>
</html>
