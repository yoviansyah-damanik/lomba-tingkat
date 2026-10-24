{{-- Progress verifikasi kontingen; variabel: $percent (0-100), $message --}}
<div class="space-y-2" role="status" aria-live="polite">
    <progress value="{{ $percent }}" max="100"
        class="h-2.5 w-full overflow-hidden rounded-full [&::-moz-progress-bar]:bg-emerald-600 [&::-webkit-progress-bar]:bg-zinc-200 [&::-webkit-progress-value]:bg-emerald-600 dark:[&::-webkit-progress-bar]:bg-zinc-700"></progress>
    <div class="flex items-center justify-between text-xs">
        <span class="text-zinc-600 dark:text-zinc-300">{{ $message }}</span>
        <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400">{{ $percent }}%</span>
    </div>
</div>
