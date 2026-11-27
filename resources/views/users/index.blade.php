@extends('layout')
@section('title', 'Gérer les membres — WatchList')
@section('content')
<a href="{{ route('admin.index') }}" class="back-link">← Retour au backoffice</a>
<section class="page-header"><p class="eyebrow"><span></span> Administration</p><h1>Les membres</h1><p>{{ $users->count() }} comptes enregistrés sur WatchList.</p></section>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Membre</th><th>Pseudo</th><th>Contact</th><th>Sécurité</th><th>Rôle</th><th>Actions</th></tr></thead><tbody>
@forelse($users as $user)
<tr>
    <td><strong>{{ $user->firstname }} {{ $user->name }}</strong><small class="table-subline">Inscrit {{ $user->created_at?->diffForHumans() }}</small></td>
    <td>{{ $user->pseudo }}</td>
    <td>{{ $user->email }}@if($user->phone)<small class="table-subline">{{ $user->phone }}</small>@endif</td>
    <td><span class="status-dot {{ $user->two_factor_enabled ? 'is-on' : '' }}"></span>{{ $user->two_factor_enabled ? 'A2F '.strtoupper($user->two_factor_method) : 'Standard' }}</td>
    <td><span class="chip">{{ $user->role_label }}</span></td>
    <td><div class="table-actions"><a href="{{ route('users.edit',$user) }}" class="button button-secondary">Modifier</a>@if(auth()->id() !== $user->id)<form action="{{ route('users.destroy',$user) }}" method="POST" onsubmit="return confirm('Supprimer définitivement ce compte ?')">@csrf @method('DELETE')<button type="submit" class="button button-danger">Supprimer</button></form>@endif</div></td>
</tr>
@empty<tr><td colspan="6">Aucun membre enregistré.</td></tr>@endforelse
</tbody></table></div>
@endsection
