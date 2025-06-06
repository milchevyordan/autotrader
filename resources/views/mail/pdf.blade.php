@component('mail::message')
    <h1>{{ __('Hello') }},</h1>
    @if($customText)
        <div style="margin-bottom: 1rem">
            {{ $customText }}<br>
        </div>
    @endif
    <div style="margin-bottom: 1rem">
        {{ $textTop }}<br>
    </div>
    <div style="margin-bottom: 1rem">
        {{ __('You can find') }} {{ $subject }} {{ __('file attached.') }}<br>
    </div>
    @if($customButton)
        @component('mail::button', ['url' => $customButton['route']])
            {{ $customButton['name'] }}
        @endcomponent
    @endif
    <div style="margin-bottom: 1rem">
        {{ $textBottom }}<br>
    </div>
    {{ __('Regards') }},<br>
    {{ config('app.name') }}<br>
    @if($customButton)
        <div style="margin-top: 1rem; border-top: 1px solid #e8e5ef; padding-top: 1rem;">
            {{ __("If you're having trouble clicking the") }} "{{ $customButton['name'] }}" {{ __("button, copy and paste the URL below into your web browser:") }}<br>
            <a href="{{ $customButton['route'] }}">{{ $customButton['route'] }}</a>
        </div>
    @endif
@endcomponent
