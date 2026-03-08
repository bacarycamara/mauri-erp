@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <div>
        <label class="label">Nom *</label>
        <input type="text" name="name"
               value="{{ old('name',$supplier->name ?? '') }}"
               required class="input-style">
    </div>

    <div>
        <label class="label">Contact</label>
        <input type="text" name="contact_person"
               value="{{ old('contact_person',$supplier->contact_person ?? '') }}"
               class="input-style">
    </div>

    <div>
        <label class="label">Téléphone</label>
        <input type="text" name="phone"
               value="{{ old('phone',$supplier->phone ?? '') }}"
               class="input-style">
    </div>

    <div>
        <label class="label">Email</label>
        <input type="email" name="email"
               value="{{ old('email',$supplier->email ?? '') }}"
               class="input-style">
    </div>

    <div>
        <label class="label">NIF</label>
        <input type="text" name="nif"
               value="{{ old('nif',$supplier->nif ?? '') }}"
               class="input-style">
    </div>

    <div>
        <label class="label">RC</label>
        <input type="text" name="rc"
               value="{{ old('rc',$supplier->rc ?? '') }}"
               class="input-style">
    </div>

    <div class="md:col-span-2">
        <label class="label">Adresse</label>
        <input type="text" name="address"
               value="{{ old('address',$supplier->address ?? '') }}"
               class="input-style">
    </div>

    <div>
        <label class="label">Ville</label>
        <input type="text" name="city"
               value="{{ old('city',$supplier->city ?? '') }}"
               class="input-style">
    </div>

    <div>
        <label class="label">Solde initial</label>
        <input type="number" step="0.01" name="opening_balance"
               value="{{ old('opening_balance',$supplier->opening_balance ?? 0) }}"
               class="input-style">
    </div>

    <div class="md:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active"
                   {{ old('is_active',$supplier->is_active ?? true) ? 'checked' : '' }}>
            Actif
        </label>
    </div>

</div>

<style>
.label { @apply block text-sm font-medium text-gray-600 mb-2; }
.input-style {
    @apply w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500;
}
</style>