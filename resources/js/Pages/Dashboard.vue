<script setup>
import { computed } from 'vue';
import { usePage, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const tenant = computed(() => page.props.auth.tenant);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <!-- Institution banner -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-500 mb-1">
                            Institution Admin
                        </p>
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ tenant?.name ?? 'Your Institution' }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Welcome back, {{ user?.name }}.
                        </p>
                    </div>
                </div>

                <!-- Quick actions -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

                    <Link
                        :href="route('settings.lti')"
                        class="group overflow-hidden rounded-lg bg-white shadow-sm hover:shadow-md transition-shadow border border-gray-100"
                    >
                        <div class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 group-hover:bg-indigo-100 transition-colors">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">LTI Integration</h3>
                                    <p class="text-sm text-gray-500">Generate credentials for Moodle</p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <Link
                        :href="route('profile.edit')"
                        class="group overflow-hidden rounded-lg bg-white shadow-sm hover:shadow-md transition-shadow border border-gray-100"
                    >
                        <div class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 group-hover:bg-gray-100 transition-colors">
                                    <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Profile</h3>
                                    <p class="text-sm text-gray-500">Update your name and password</p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <!-- Placeholder: Surveys (Phase 2) -->
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm border border-gray-100 opacity-50 cursor-not-allowed">
                        <div class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-50">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Surveys</h3>
                                    <p class="text-sm text-gray-500">Coming soon</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
