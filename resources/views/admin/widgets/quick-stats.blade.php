<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach($quickStats as $stat)
    <div class="bg-white rounded-lg p-4 border-l-4 border-{{ $stat['color'] }}-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase">{{ $stat['label'] }}</p>
                <p class="text-xl font-bold">{{ $stat['value'] }}</p>
                @if(isset($stat['change']))
                <p class="text-xs mt-1">
                    <span class="{{ $stat['change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $stat['change'] >= 0 ? '+' : '' }}{{ $stat['change'] }}%
                    </span>
                </p>
                @endif
            </div>
            <i class="fas {{ $stat['icon'] }} text-{{ $stat['color'] }}-500 text-2xl"></i>
        </div>
    </div>
    @endforeach
</div>