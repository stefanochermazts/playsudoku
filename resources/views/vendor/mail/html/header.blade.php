@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel' || trim($slot) === config('app.name'))
<div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
    <div style="font-size: 28px;">🧩</div>
    <span style="font-size: 24px; font-weight: bold; color: #1f2937;">PlaySudoku</span>
</div>
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
