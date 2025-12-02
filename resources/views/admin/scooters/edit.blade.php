<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Edit Scooter') }} #{{ $scooter->code }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تحديث بيانات السكوتر وحالته') }}
                </p>
            </div>
            <a href="{{ route('admin.scooters.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.Back to Scooters') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <form method="POST" action="{{ route('admin.scooters.update', $scooter) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @include('admin.scooters._form', ['scooter' => $scooter])

                    <div class="flex items-center justify-between gap-3">
                        <div class="text-xs text-gray-500">
                            {{ trans('messages.Last Updated') }}:
                            <span class="font-semibold text-secondary">
                                {{ $scooter->updated_at?->format('Y-m-d H:i') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.scooters.show', $scooter) }}"
                               class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                                {{ trans('messages.View Details') }}
                            </a>
                            <button type="submit"
                                    class="px-5 py-2 rounded-lg bg-primary text-secondary text-sm font-semibold shadow-sm hover:bg-yellow-400 transition">
                                {{ trans('messages.Save Changes') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>



