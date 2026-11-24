@props(['status'])
@php($colors = match($status) { 'active', 'approved', 'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10', 'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/10', 'expired' => 'bg-rose-50 text-rose-700 ring-rose-600/10', default => 'bg-slate-100 text-slate-600 ring-slate-500/10' })
<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-medium ring-1 ring-inset {{ $colors }}"><span class="size-1.5 rounded-full bg-current"></span>{{ ucfirst($status) }}</span>
