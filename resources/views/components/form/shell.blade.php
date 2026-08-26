@props([
    'action',
    'method' => 'POST',
    'fromBuilder' => false,
    'section' => null,
])

<form action="{{ $action }}" method="POST">
    @csrf
    @if (in_array(strtoupper($method), ['PATCH', 'PUT', 'DELETE']))
        @method($method)
    @endif
    @if ($fromBuilder)
        <input type="hidden" name="from" value="builder">
        <input type="hidden" name="section" value="{{ $section }}">
    @endif

    {{ $slot }}
</form>
