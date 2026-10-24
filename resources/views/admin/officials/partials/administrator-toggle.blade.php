{{-- Penunjukan Administrator: hanya terlihat oleh Superadmin --}}
@if(auth()->user()->isSuperadmin())
    <label class="mt-3 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3 cursor-pointer dark:border-amber-900/60 dark:bg-amber-950/30">
        <input type="checkbox" name="is_administrator" value="1" {{ old('is_administrator', $checked ?? false) ? 'checked' : '' }} class="mt-0.5 rounded text-amber-600 focus:ring-amber-500">
        <div>
            <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Jadikan Administrator</span>
            <p class="text-[11px] text-zinc-500">Hanya untuk akun Panitia; hak kelola penuh data kegiatan</p>
        </div>
    </label>
    @error('is_administrator')
        <span class="mt-1 block text-[11px] text-rose-500">{{ $message }}</span>
    @enderror
@endif
