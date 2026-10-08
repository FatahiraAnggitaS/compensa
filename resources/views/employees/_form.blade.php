@if ($errors->any())
    <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-900" role="alert">
        <p class="font-semibold">Periksa kembali data employee.</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-6">
    <div>
        <label class="form-label" for="employee-code">Kode employee</label>
        <input
            id="employee-code"
            name="employee_code"
            type="text"
            value="{{ old('employee_code', $employee?->employee_code) }}"
            class="form-input uppercase @error('employee_code') form-input-error @enderror"
            minlength="3"
            maxlength="32"
            pattern="[A-Za-z0-9][A-Za-z0-9_-]{2,31}"
            autocomplete="off"
            autocapitalize="characters"
            aria-describedby="employee-code-help @error('employee_code') employee-code-error @enderror"
            @error('employee_code') aria-invalid="true" @enderror
            required
            autofocus
        >
        <p id="employee-code-help" class="form-help">3–32 karakter: huruf, angka, tanda minus, atau underscore. Sistem menyimpan kode dalam uppercase.</p>
        @error('employee_code')
            <p id="employee-code-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label" for="full-name">Nama lengkap</label>
        <input
            id="full-name"
            name="full_name"
            type="text"
            value="{{ old('full_name', $employee?->full_name) }}"
            class="form-input @error('full_name') form-input-error @enderror"
            maxlength="150"
            autocomplete="name"
            aria-describedby="full-name-help @error('full_name') full-name-error @enderror"
            @error('full_name') aria-invalid="true" @enderror
            required
        >
        <p id="full-name-help" class="form-help">Gunakan nama display yang diperlukan untuk mengenali employee.</p>
        @error('full_name')
            <p id="full-name-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
    <a href="{{ $cancelUrl }}" class="button-secondary">Batal</a>
    <button type="submit" class="button-primary">{{ $submitLabel }}</button>
</div>
