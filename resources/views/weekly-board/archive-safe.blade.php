<x-site-layout>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <h1>DEBUG: Weekly Board Archive</h1>
        
        {{-- TESTING NAVIGATION SECTION --}}
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('localized.daily-board.index', ['locale' => app()->getLocale()]) }}" 
               class="inline-flex items-center text-primary-600 hover:text-primary-700">
                ← Back to today
            </a>
            
            <div class="flex space-x-2">
                <a href="{{ route('localized.weekly-board.archive', ['locale' => app()->getLocale(), 'week' => $currentWeek->copy()->subWeeks(12)->format('Y-m-d')]) }}" 
                   class="px-3 py-1 text-sm bg-white border rounded">
                    Previous month
                </a>
            </div>
        </div>
        
        @if($challenges->count() > 0)
            <p>Found {{ $challenges->count() }} challenges</p>
            
            @foreach($challenges as $challenge)
                <div style="border: 1px solid red; margin: 10px; padding: 10px;">
                    <p>Challenge ID: {{ $challenge->id ?? 'NO_ID' }}</p>
                    <p>Challenge Type: {{ $challenge->type ?? 'NO_TYPE' }}</p>
                    
                    {{-- TESTING DATE FORMATTING --}}
                    <p>Start Date: {{ $challenge->starts_at->format('l, F j, Y') ?? 'NO_START_DATE' }}</p>
                    
                    @if($challenge->puzzle)
                        <p>Puzzle exists: YES</p>
                        <p>Difficulty: {{ $challenge->puzzle->difficulty ?? 'NO_DIFFICULTY' }}</p>
                        
                        {{-- TESTING SWITCH CASE --}}
                        <p>Difficulty with classes: 
                            <span class="px-2 py-1 rounded text-xs font-medium
                                @switch($challenge->puzzle->difficulty)
                                    @case('easy') bg-green-100 text-green-800 @break
                                    @case('normal') bg-blue-100 text-blue-800 @break
                                    @case('hard') bg-orange-100 text-orange-800 @break
                                    @case('expert') bg-red-100 text-red-800 @break
                                    @case('crazy') bg-purple-100 text-purple-800 @break
                                @endswitch
                            ">
                                {{ ucfirst($challenge->puzzle->difficulty) }}
                            </span>
                        </p>
                        
                        <p>Seed: {{ $challenge->puzzle->seed ?? 'NO_SEED' }}</p>
                    @else
                        <p>Puzzle exists: NO</p>
                    @endif
                    
                    <p>Attempts count: {{ $challenge->attempts->count() ?? 'NO_ATTEMPTS' }}</p>
                    
                    {{-- TESTING PARTICIPANTS CALCULATION --}}
                    <p>Participants (ORIGINAL): {{ $challenge->attempts->where('valid', true)->unique('user_id')->count() }}</p>
                    <p>Participants (WITH CAST): {{ (int) $challenge->attempts->where('valid', true)->unique('user_id')->count() }}</p>
                    
                    {{-- TESTING ATTEMPTS LOOP --}}
                    @if($challenge->attempts->count() > 0)
                        <div>
                            <p>Top attempts:</p>
                            @foreach($challenge->attempts->take(3) as $index => $attempt)
                                <div style="border: 1px solid blue; margin: 5px; padding: 5px;">
                                    <p>Attempt {{ $index + 1 }}</p>
                                    <p>User: {{ $attempt->user?->name ?? '—' }}</p>
                                    <p>Duration MS: {{ $attempt->duration_ms ?? 0 }}</p>
                                    
                                    {{-- TESTING TIME FORMATTING --}}
                                    @php($ms = (int) ($attempt->duration_ms ?? 0))
                                    @php($s = intdiv($ms, 1000))
                                    @php($cs = intdiv($ms % 1000, 10))
                                    <p>Formatted Time: {{ sprintf('%02d:%02d', intdiv($s,60), $s%60) }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    {{-- TESTING ACTION BUTTONS WITH ROUTE --}}
                    <div style="border: 1px solid green; margin: 5px; padding: 5px;">
                        <p>Action buttons test:</p>
                        <p>Challenge ID type: {{ gettype($challenge->id) }}</p>
                        <p>Start date format test: {{ $challenge->starts_at->format('Y-m-d') }}</p>
                        
                        <a href="{{ route('localized.daily-board.show', ['locale' => app()->getLocale(), 'date' => $challenge->starts_at->format('Y-m-d')]) }}">
                            View Details
                        </a>
                        
                        <a href="{{ route('localized.leaderboard.show', ['locale' => app()->getLocale(), 'challenge' => $challenge->id]) }}">
                            Leaderboard
                        </a>
                    </div>
                </div>
            @endforeach
        @else
            <p>No challenges found</p>
        @endif
    </div>
</x-site-layout>
