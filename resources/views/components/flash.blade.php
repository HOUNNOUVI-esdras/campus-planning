@if (session('status'))
    <x-ui.callout variant="success" :heading="session('status')" />
@endif

@if (session('warning'))
    <x-ui.callout variant="warning" :heading="session('warning')" />
@endif
