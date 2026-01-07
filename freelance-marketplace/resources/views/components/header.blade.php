<header>
    <ul class="button-list" aria-label="Пример кнопок">
        <div class="header-btn-group">
            <li><a href="{{ route('api-keys.index') }}"><button class="btn btn-primary">API Keys</button></a></li>
            <li><a href="{{ route('yt-profiles.index') }}"><button class="btn btn-primary">YT Profiles</button></a></li>
            <li><a href="{{ route('video-themes.index') }}"><button class="btn btn-primary">Video themes</button></a></li>
            <li><a href="{{ route('yt-channels.index') }}"><button class="btn btn-primary">YT Channels</button></a></li>
            <li><a href="{{ route('video-categories.index') }}"><button class="btn btn-primary">Video Categories</button></a></li>
        </div>

        <div class="header-btn-group">
            <li><a href="{{ route('video-subjects.index') }}"><button class="btn btn-primary">Video Subjects</button></a></li>
            <li><a href="{{ route('chapters.index') }}"><button class="btn btn-primary">Chapters</button></a></li>
            <li><a href="{{ route('video-texts.index') }}"><button class="btn btn-primary">Video Texts</button></a></li>
        </div>

        <div class="header-btn-group">
            <li><a href="{{ route('chapter-generation-tasks.index') }}"><button class="btn btn-primary">Generating chapters</button></a></li>
            <li><a href="{{ route('video-text-tasks.index') }}"><button class="btn btn-primary">Generating texts</button></a></li>
        </div>
        {{-- <li><button class="btn btn-secondary">Secondary</button></li>
    <li><button class="btn btn-outline">Outline</button></li>
    <li><button class="btn btn-ghost">Ghost</button></li>
    <li><button class="btn btn-pill">Pill</button></li>
    <li><button class="btn btn-sm">Small</button></li>
    <li><button class="btn btn-lg">Large</button></li>
    <li>
      <button class="btn btn-icon" aria-label="Search">
        <svg width="16" height="16" viewBox="0 0 24 24">
          <path fill="currentColor" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
        </svg>
      </button>
    </li>
    <li><button class="btn btn-toggle" aria-pressed="false">Toggle</button></li>
    <li><button class="btn" disabled>Disabled</button></li>
    <li style="width:100%"><button class="btn btn-primary btn-full">Full width</button></li> --}}
    </ul>
</header>
