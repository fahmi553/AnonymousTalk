@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://imgur.com/a/0DLSdzN" class="logo" alt="Anonymous Talk Logo" width="100">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
