<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Add New Scooter') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إدخال بيانات السكوتر وربطها بالنظام') }}
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
                <form method="POST" action="{{ route('admin.scooters.store') }}" class="space-y-6">
                    @csrf

                    @include('admin.scooters._form', ['scooter' => null])

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('admin.scooters.index') }}"
                           class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                            {{ trans('messages.Cancel') }}
                        </a>
                        <button type="submit"
                                class="px-5 py-2 rounded-lg bg-primary text-secondary text-sm font-semibold shadow-sm hover:bg-yellow-400 transition">
                            {{ trans('messages.Save Scooter') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>



