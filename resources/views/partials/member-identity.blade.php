@if($member)
    <a class="member-identity" href="{{ route('community.profile', $member) }}">
        @if($member->equippedReward?->emoji)<span aria-hidden="true">{{ $member->equippedReward->emoji }}</span>@endif
        <strong>{{ $member->display_name }}</strong>
        <small>{{ app(\App\Services\CommunityProgressService::class)->rankFor($member) }}</small>
    </a>
@else
    <span class="member-identity"><strong>Compte supprimé</strong></span>
@endif
