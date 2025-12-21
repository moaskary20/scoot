<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-secondary leading-tight">
                    {{ trans('messages.Inactive Users') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ trans('messages.Review and activate newly registered users') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-red-50 border border-red-200 rounded-lg">
                    <span class="text-sm font-semibold text-red-600">
                        {{ trans('messages.Total') }}: {{ $inactiveCount }} {{ trans('messages.Inactive Users') }}
                    </span>
                </div>
                <a href="{{ route('admin.users.inactive', array_merge(request()->query(), ['export' => 'csv'])) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-blue-600 transition">
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ trans('messages.Export to CSV') }}
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg shadow-sm hover:bg-gray-200 transition">
                    {{ trans('messages.All Users') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Info Banner -->
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-yellow-800 mb-1">{{ trans('messages.Review Required') }}</h3>
                        <p class="text-xs text-yellow-700">{{ trans('messages.These users are waiting for review. Please verify their information and activate them to allow access to the system.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Search and Date Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
                <form method="GET" action="{{ route('admin.users.inactive') }}" id="filter-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-xs text-gray-500 mb-1">
                                {{ trans('messages.Search') }} ({{ trans('messages.Name') }} / {{ trans('messages.Phone') }} / {{ trans('messages.University ID') }})
                            </label>
                            <input type="text" 
                                   name="search" 
                                   id="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="{{ trans('messages.Enter name, phone, or university ID') }}"
                                   class="w-full text-sm rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label for="date_from" class="block text-xs text-gray-500 mb-1">
                                {{ trans('messages.Date From') }}
                            </label>
                            <input type="date" 
                                   name="date_from" 
                                   id="date_from" 
                                   value="{{ request('date_from') }}"
                                   class="w-full text-sm rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label for="date_to" class="block text-xs text-gray-500 mb-1">
                                {{ trans('messages.Date To') }}
                            </label>
                            <input type="date" 
                                   name="date_to" 
                                   id="date_to" 
                                   value="{{ request('date_to') }}"
                                   class="w-full text-sm rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-2 bg-primary text-secondary text-sm font-medium rounded-lg hover:bg-yellow-400 transition">
                                {{ trans('messages.Filter') }}
                            </button>
                        </div>
                    </div>
                    @if(request('search') || request('date_from') || request('date_to'))
                        <div class="flex justify-end">
                            <a href="{{ route('admin.users.inactive') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition">
                                {{ trans('messages.Reset') }}
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            <!-- Bulk Actions -->
            @if($users->count() > 0)
            <div class="mb-4 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <form id="bulk-activate-form" action="{{ route('admin.users.bulk-activate') }}" method="POST">
                    @csrf
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-primary focus:ring-primary">
                            <label for="select-all" class="text-sm text-gray-700 font-medium">
                                {{ trans('messages.Select All') }}
                            </label>
                        </div>
                        <button type="submit" 
                                class="px-4 py-2 bg-emerald-500 text-white text-sm font-semibold rounded-lg hover:bg-emerald-600 transition">
                            {{ trans('messages.Bulk Activate Selected') }}
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Users Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" dir="rtl">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" class="bulk-checkbox rounded border-gray-300 text-primary focus:ring-primary">
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ trans('messages.Name') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ trans('messages.Email') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ trans('messages.Phone') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ trans('messages.University ID') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ trans('messages.Verification Status') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ trans('messages.Registered At') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ trans('messages.Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="bulk-checkbox rounded border-gray-300 text-primary focus:ring-primary">
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                @if($user->age)
                                                    <div class="text-xs text-gray-500">{{ trans('messages.Age') }}: {{ $user->age }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                            @if($user->email_verified_at)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800" title="{{ trans('messages.Email Verified') }}">
                                                    <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800" title="{{ trans('messages.Not Verified') }}">
                                                    <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm text-gray-900">{{ $user->phone ?? '-' }}</div>
                                            @if($user->phone)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800" title="{{ trans('messages.Phone Verified') }}">
                                                    <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800" title="{{ trans('messages.Not Verified') }}">
                                                    <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm text-gray-900">{{ $user->university_id ?? '-' }}</div>
                                            @if($user->university_id)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800" title="{{ trans('messages.University ID Verified') }}">
                                                    <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800" title="{{ trans('messages.Not Verified') }}">
                                                    <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex flex-col gap-1">
                                            <div class="text-xs">
                                                <span class="font-medium">{{ trans('messages.Email') }}:</span>
                                                <span class="{{ $user->email_verified_at ? 'text-emerald-600' : 'text-red-600' }}">
                                                    {{ $user->email_verified_at ? trans('messages.Verified') : trans('messages.Not Verified') }}
                                                </span>
                                            </div>
                                            <div class="text-xs">
                                                <span class="font-medium">{{ trans('messages.Phone') }}:</span>
                                                <span class="{{ $user->phone ? 'text-emerald-600' : 'text-red-600' }}">
                                                    {{ $user->phone ? trans('messages.Verified') : trans('messages.Not Verified') }}
                                                </span>
                                            </div>
                                            <div class="text-xs">
                                                <span class="font-medium">{{ trans('messages.University ID') }}:</span>
                                                <span class="{{ $user->university_id ? 'text-emerald-600' : 'text-red-600' }}">
                                                    {{ $user->university_id ? trans('messages.Verified') : trans('messages.Not Verified') }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $user->created_at->format('Y-m-d H:i') }}</div>
                                        <div class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-left">
                                        <div class="flex items-center gap-2">
                                            <button onclick="openQuickPreview({{ $user->id }})" 
                                                    class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                                {{ trans('messages.Quick Preview') }}
                                            </button>
                                            <a href="{{ route('admin.users.show', $user) }}"
                                               class="text-xs text-gray-600 hover:text-secondary">
                                                {{ trans('messages.View') }}
                                            </a>
                                            <form action="{{ route('admin.users.toggle-active', $user) }}"
                                                  method="POST"
                                                  class="inline-block">
                                                @csrf
                                                <button type="submit"
                                                        class="text-xs text-emerald-500 hover:text-emerald-600 font-semibold">
                                                    {{ trans('messages.Activate') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500" dir="rtl">
                                        {{ trans('messages.No inactive users found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Preview Modal -->
    <div id="quick-preview-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" dir="rtl">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-xl bg-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-secondary">{{ trans('messages.User Details Preview') }}</h3>
                <button onclick="closeQuickPreview()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="preview-content" class="space-y-4">
                <!-- Content will be loaded here -->
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeQuickPreview()" class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    {{ trans('messages.Close') }}
                </button>
                <a id="full-details-link" href="#" class="px-4 py-2 bg-primary text-secondary text-sm font-medium rounded-lg hover:bg-yellow-400 transition">
                    {{ trans('messages.View Full Details') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Review Notes Modal -->
    <div id="review-notes-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" dir="rtl">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-xl bg-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-secondary">{{ trans('messages.Add Review Notes') }}</h3>
                <button onclick="closeReviewNotes()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="review-notes-form" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label for="review_notes" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ trans('messages.Review Notes') }}
                    </label>
                    <textarea name="review_notes" id="review_notes" rows="5" 
                              class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary"
                              placeholder="{{ trans('messages.Enter review notes or comments...') }}"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="closeReviewNotes()" class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                        {{ trans('messages.Cancel') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-secondary text-sm font-medium rounded-lg hover:bg-yellow-400 transition">
                        {{ trans('messages.Save Notes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        // Bulk selection
        document.getElementById('select-all')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.bulk-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        // Update select-all when individual checkboxes change
        document.querySelectorAll('.bulk-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(document.querySelectorAll('.bulk-checkbox:not(#select-all)')).every(cb => cb.checked);
                document.getElementById('select-all').checked = allChecked;
            });
        });

        // Form submission
        document.getElementById('bulk-activate-form')?.addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('.bulk-checkbox:not(#select-all):checked');
            if (checked.length === 0) {
                e.preventDefault();
                alert('{{ trans('messages.Please select at least one user') }}');
                return false;
            }
            return confirm('{{ trans('messages.Are you sure you want to activate the selected users?') }}');
        });

        // Quick Preview
        function openQuickPreview(userId) {
            fetch(`/admin/users/${userId}/quick-preview`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('preview-content');
                    const fullDetailsLink = document.getElementById('full-details-link');
                    
                    content.innerHTML = `
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-gray-500">{{ trans('messages.Name') }}</label>
                                <p class="text-sm font-medium text-gray-900">${data.name}</p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">{{ trans('messages.Email') }}</label>
                                <p class="text-sm text-gray-900">${data.email} ${data.email_verified ? '<span class="text-emerald-600">✓</span>' : '<span class="text-red-600">✗</span>'}</p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">{{ trans('messages.Phone') }}</label>
                                <p class="text-sm text-gray-900">${data.phone || '-'} ${data.phone ? '<span class="text-emerald-600">✓</span>' : '<span class="text-red-600">✗</span>'}</p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">{{ trans('messages.University ID') }}</label>
                                <p class="text-sm text-gray-900">${data.university_id || '-'} ${data.university_id ? '<span class="text-emerald-600">✓</span>' : '<span class="text-red-600">✗</span>'}</p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">{{ trans('messages.Age') }}</label>
                                <p class="text-sm text-gray-900">${data.age || '-'}</p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">{{ trans('messages.Registered At') }}</label>
                                <p class="text-sm text-gray-900">${data.registered_at}</p>
                            </div>
                            ${data.review_notes ? `
                            <div class="col-span-2">
                                <label class="text-xs text-gray-500">{{ trans('messages.Review Notes') }}</label>
                                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">${data.review_notes}</p>
                            </div>
                            ` : ''}
                        </div>
                    `;
                    
                    fullDetailsLink.href = `/admin/users/${userId}`;
                    document.getElementById('quick-preview-modal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ trans('messages.Failed to load user details') }}');
                });
        }

        function closeQuickPreview() {
            document.getElementById('quick-preview-modal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('quick-preview-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeQuickPreview();
            }
        });

        // Review Notes
        function openReviewNotes(userId) {
            const form = document.getElementById('review-notes-form');
            form.action = `/admin/users/${userId}/review-notes`;
            
            fetch(`/admin/users/${userId}/review-notes`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('review_notes').value = data.review_notes || '';
                    document.getElementById('review-notes-modal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('review-notes-modal').classList.remove('hidden');
                });
        }

        function closeReviewNotes() {
            document.getElementById('review-notes-modal').classList.add('hidden');
        }

        document.getElementById('review-notes-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeReviewNotes();
            }
        });
    </script>
</x-app-layout>
