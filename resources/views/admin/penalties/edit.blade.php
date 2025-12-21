<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Edit Penalty') }} #{{ $penalty->id }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.تعديل بيانات الغرامة') }}
                </p>
            </div>
            <a href="{{ route('admin.penalties.index') }}"
               class="text-sm text-gray-600 hover:text-secondary">
                {{ trans('messages.رجوع') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form action="{{ route('admin.penalties.update', $penalty) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @include('admin.penalties._form', ['penalty' => $penalty])

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.penalties.index') }}"
                           class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                            {{ trans('messages.Cancel') }}
                        </a>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-secondary text-sm font-semibold rounded-lg shadow-sm hover:bg-yellow-400 transition">
                            {{ trans('messages.Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                // Initialize Select2 for user search
                $('#user_id').select2({
                    placeholder: '{{ trans('messages.Select User') }}',
                    allowClear: true,
                    language: {
                        noResults: function() {
                            return '{{ trans('messages.No users found') }}';
                        },
                        searching: function() {
                            return '{{ trans('messages.Searching') }}...';
                        }
                    },
                    templateResult: function(user) {
                        if (!user.id) {
                            return user.text;
                        }
                        var $user = $('<span>' + user.text + '</span>');
                        return $user;
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>

