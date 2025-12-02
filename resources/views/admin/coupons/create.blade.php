<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Add New Coupon') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.إضافة كوبون جديد') }}
                </p>
            </div>
            <a href="{{ route('admin.coupons.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.رجوع لقائمة الكوبونات') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form action="{{ route('admin.coupons.store') }}" method="POST">
                    @csrf

                    @include('admin.coupons._form', ['coupon' => null])

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.coupons.index') }}"
                           class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                            {{ trans('messages.إلغاء') }}
                        </a>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                            {{ trans('messages.حفظ') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

