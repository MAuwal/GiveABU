<div>
    @if($show)
        @if($mode === 'login')
            <livewire:home.login-modal :show="true" />
        @elseif($mode === 'register')
            <livewire:home.registration-modal :show="true" />
        @endif
    @endif
</div>
