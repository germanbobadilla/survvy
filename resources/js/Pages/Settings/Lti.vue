<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    platform: Object,
    toolUrls: Object,
});

const page = usePage();
const tenant = computed(() => page.props.auth.tenant);

const form = useForm({
    name: props.platform?.name ?? (tenant.value?.name ?? ''),
    issuer: props.platform?.issuer ?? '',
});

const copied = ref({});

function copyToClipboard(key, value) {
    navigator.clipboard.writeText(value).then(() => {
        copied.value[key] = true;
        setTimeout(() => { copied.value[key] = false; }, 2000);
    });
}

const flashSuccess = computed(() => page.props.flash?.success);

const credentials = computed(() => {
    if (!props.platform) return {};
    return {
        client_id: { label: 'Client ID', value: props.platform.client_id },
        launch:    { label: 'Tool Launch URL (Redirect URI)', value: props.toolUrls.launch },
        login:     { label: 'Initiate Login URL', value: props.toolUrls.login },
        jwks:      { label: 'Public Keyset URL (JWKS)', value: props.toolUrls.jwks },
    };
});
</script>

<template>
    <Head title="LTI Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                LTI 1.3 Integration
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl space-y-8 sm:px-6 lg:px-8">

                <!-- Success flash -->
                <div v-if="flashSuccess" class="rounded-md bg-green-50 p-4 border border-green-200">
                    <p class="text-sm text-green-800">{{ flashSuccess }}</p>
                </div>

                <!-- Generate / Regenerate form -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-1">
                            {{ platform ? 'Regenerate Credentials' : 'Generate LTI Credentials' }}
                        </h3>
                        <p class="text-sm text-gray-500 mb-6">
                            {{ platform
                                ? 'Regenerating will create a new keypair and client ID. Update Moodle after regenerating.'
                                : 'Generate an RSA keypair and client ID to register Survvy as an LTI 1.3 tool in Moodle.' }}
                        </p>

                        <form @submit.prevent="form.post(route('settings.lti.generate'))" class="space-y-4">
                            <div>
                                <InputLabel for="name" value="Platform Label" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="e.g. My Institution Moodle"
                                    required
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="issuer" value="Moodle Site URL (Issuer)" />
                                <TextInput
                                    id="issuer"
                                    v-model="form.issuer"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="https://moodle.myschool.edu"
                                    required
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    The base URL of your Moodle instance (without trailing slash).
                                </p>
                                <InputError :message="form.errors.issuer" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4 pt-2">
                                <PrimaryButton :disabled="form.processing">
                                    {{ platform ? 'Regenerate' : 'Generate Credentials' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Credentials card (only when platform exists) -->
                <div v-if="platform" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-1">
                            Moodle Configuration
                        </h3>
                        <p class="text-sm text-gray-500 mb-6">
                            Copy these values into Moodle &rsaquo; Site administration &rsaquo; Plugins &rsaquo;
                            Activity modules &rsaquo; External tool &rsaquo; Manage tools &rsaquo; Configure a tool manually.
                        </p>

                        <dl class="space-y-4">
                            <div v-for="(item, key) in credentials" :key="key">
                                <dt class="text-sm font-medium text-gray-500 mb-1">{{ item.label }}</dt>
                                <dd class="flex items-center gap-2">
                                    <code class="flex-1 rounded bg-gray-50 border border-gray-200 px-3 py-2 text-sm text-gray-800 font-mono break-all">
                                        {{ item.value }}
                                    </code>
                                    <button
                                        type="button"
                                        @click="copyToClipboard(key, item.value)"
                                        class="shrink-0 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 transition"
                                    >
                                        {{ copied[key] ? 'Copied!' : 'Copy' }}
                                    </button>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Public key card -->
                <div v-if="platform?.public_key" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Public Key (RSA)</h3>
                        <p class="text-sm text-gray-500 mb-4">
                            Paste this into Moodle's "Public key" field for your External Tool.
                        </p>
                        <div class="relative">
                            <pre class="rounded bg-gray-50 border border-gray-200 p-4 text-xs text-gray-700 font-mono whitespace-pre-wrap break-all overflow-auto max-h-64">{{ platform.public_key }}</pre>
                            <button
                                type="button"
                                @click="copyToClipboard('pubkey', platform.public_key)"
                                class="absolute top-2 right-2 rounded border border-gray-300 bg-white px-2 py-1 text-xs text-gray-600 hover:bg-gray-50"
                            >
                                {{ copied['pubkey'] ? 'Copied!' : 'Copy' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- How-to guide -->
                <div class="overflow-hidden bg-blue-50 border border-blue-200 sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-base font-medium text-blue-900 mb-3">How to register in Moodle</h3>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800">
                            <li>In Moodle, go to <strong>Site administration → Plugins → External tool → Manage tools</strong>.</li>
                            <li>Click <strong>Configure a tool manually</strong>.</li>
                            <li>Set <strong>Tool URL</strong> to the Launch URL above.</li>
                            <li>Set <strong>LTI version</strong> to <strong>LTI 1.3</strong>.</li>
                            <li>Set <strong>Initiate login URL</strong> to the Login URL above.</li>
                            <li>Set <strong>Redirection URI(s)</strong> to the Launch URL above.</li>
                            <li>Copy your <strong>Client ID</strong> from the field above.</li>
                            <li>Paste your <strong>Public key</strong> from the section above.</li>
                            <li>Save — Moodle will then show you a <strong>Platform ID</strong>, <strong>Authentication URL</strong>, and <strong>JWKS URL</strong> to complete the setup later.</li>
                        </ol>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
