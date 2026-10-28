@if($song->audio_file || $song->file_path)
    <a href="{{ route('admin.songs.download-audio', $song) }}"
       class="{{ !empty($compact) ? 'mt-2' : 'mt-3 ml-9' }} inline-flex items-center gap-1.5 px-3 py-2 bg-blue-100 border-2 border-black text-[10px] font-extrabold uppercase hover:bg-blue-200 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/>
        </svg>
        Download Audio
    </a>
@else
    <span class="{{ !empty($compact) ? 'mt-2' : 'mt-3 ml-9' }} inline-block text-[10px] font-bold text-black/40">No audio file</span>
@endif
