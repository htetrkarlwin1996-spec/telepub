import { Head, useForm, usePage } from '@inertiajs/react';
import UserDashboardLayout from '@/Layouts/UserDashboardLayout';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/react';
import { useState } from 'react';
import {
    DocumentTextIcon,
    CheckBadgeIcon,
    ArrowTopRightOnSquareIcon,
    XMarkIcon,
} from '@heroicons/react/24/outline';

function TextInput({ label, value, onChange, error, placeholder = '' }) {
    return (
        <div>
            <label className="mb-2 block text-sm font-medium text-slate-300">
                {label}
            </label>

            <input
                type="text"
                value={value || ''}
                onChange={onChange}
                placeholder={placeholder}
                className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none placeholder:text-slate-600 focus:border-indigo-500 focus:ring-indigo-500"
            />

            {error && <p className="mt-2 text-sm text-red-400">{error}</p>}
        </div>
    );
}

function ReadOnlyBox({ label, value, note }) {
    return (
        <div>
            <label className="mb-2 block text-sm font-medium text-slate-300">
                {label}
            </label>

            <div className="rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white">
                {value || 'Loading...'}
            </div>

            {note && (
                <p className="mt-2 text-xs leading-5 text-slate-500">
                    {note}
                </p>
            )}
        </div>
    );
}

export default function Edit({ status, user = null, profile = null, agreement = null }) {
    const { auth } = usePage().props;

    /**
     * user prop က ProfileController ကနေ ပို့ထားတာပါ။
     * အကယ်၍ မရောက်လာသေးရင် Inertia shared auth.user ကနေ fallback ယူမယ်။
     */
    const pageUser = user || auth?.user || {};

    const [previewOpen, setPreviewOpen] = useState(false);

    const { data, setData, patch, processing, errors, recentlySuccessful } = useForm({
        display_name: profile?.display_name || pageUser?.name || '',
        artist_name: profile?.artist_name || '',
        publisher_name: profile?.publisher_name || '',
        ipi_name: profile?.ipi_name || '',
        ipi_number: profile?.ipi_number || '',
        country: profile?.country || '',
        city: profile?.city || '',
        address: profile?.address || '',
    });

    const submit = (e) => {
        e.preventDefault();

        patch(route('profile.update'), {
            preserveScroll: true,
        });
    };

    return (
        <UserDashboardLayout
            title="Profile Settings"
            subtitle="Update your publishing profile and view your signed agreement."
        >
            <Head title="Profile Settings" />

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="lg:col-span-2">
                    <form onSubmit={submit} className="space-y-6">
                        <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                            <h3 className="text-lg font-bold text-white">
                                Account Information
                            </h3>

                            <p className="mt-1 text-sm text-slate-400">
                                Your registered legal name and email are locked for agreement security.
                            </p>

                            {status && (
                                <div className="mt-5 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                                    {status}
                                </div>
                            )}

                            {recentlySuccessful && (
                                <div className="mt-5 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                                    Publishing profile updated successfully.
                                </div>
                            )}

                            <div className="mt-6 grid gap-5 sm:grid-cols-2">
                                <ReadOnlyBox
                                    label="Legal Name"
                                    value={pageUser?.name}
                                    note="This name was used to sign your agreement PDF."
                                />

                                <ReadOnlyBox
                                    label="Email"
                                    value={pageUser?.email}
                                    note="Email change is not available from user dashboard."
                                />
                            </div>
                        </div>

                        <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                            <h3 className="text-lg font-bold text-white">
                                Publishing Profile
                            </h3>

                            <p className="mt-1 text-sm text-slate-400">
                                Artist, publisher and royalty identification information.
                            </p>

                            <div className="mt-6 grid gap-5 sm:grid-cols-2">
                                <TextInput
                                    label="Display Name"
                                    value={data.display_name}
                                    onChange={(e) => setData('display_name', e.target.value)}
                                    error={errors.display_name}
                                    placeholder="Display name"
                                />

                                <TextInput
                                    label="Artist Name"
                                    value={data.artist_name}
                                    onChange={(e) => setData('artist_name', e.target.value)}
                                    error={errors.artist_name}
                                    placeholder="Artist name"
                                />

                                <TextInput
                                    label="Publisher Name"
                                    value={data.publisher_name}
                                    onChange={(e) => setData('publisher_name', e.target.value)}
                                    error={errors.publisher_name}
                                    placeholder="Publisher name"
                                />

                                <TextInput
                                    label="IPI Name"
                                    value={data.ipi_name}
                                    onChange={(e) => setData('ipi_name', e.target.value)}
                                    error={errors.ipi_name}
                                    placeholder="IPI name optional"
                                />

                                <TextInput
                                    label="IPI Number"
                                    value={data.ipi_number}
                                    onChange={(e) => setData('ipi_number', e.target.value)}
                                    error={errors.ipi_number}
                                    placeholder="IPI number optional"
                                />

                                <TextInput
                                    label="Country"
                                    value={data.country}
                                    onChange={(e) => setData('country', e.target.value)}
                                    error={errors.country}
                                    placeholder="Country"
                                />

                                <TextInput
                                    label="City"
                                    value={data.city}
                                    onChange={(e) => setData('city', e.target.value)}
                                    error={errors.city}
                                    placeholder="City"
                                />
                            </div>

                            <div className="mt-5">
                                <label className="mb-2 block text-sm font-medium text-slate-300">
                                    Address
                                </label>

                                <textarea
                                    rows="4"
                                    value={data.address || ''}
                                    onChange={(e) => setData('address', e.target.value)}
                                    placeholder="Full address"
                                    className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none placeholder:text-slate-600 focus:border-indigo-500 focus:ring-indigo-500"
                                />

                                {errors.address && (
                                    <p className="mt-2 text-sm text-red-400">
                                        {errors.address}
                                    </p>
                                )}
                            </div>

                            <div className="mt-6 flex justify-end">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-2xl bg-indigo-500 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-400 disabled:opacity-60"
                                >
                                    {processing ? 'Saving...' : 'Save Publishing Profile'}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div>
                    <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                        <div className="flex items-center gap-3">
                            <div className="rounded-2xl bg-indigo-500/20 p-3">
                                <DocumentTextIcon className="h-6 w-6 text-indigo-300" />
                            </div>

                            <div>
                                <h3 className="text-lg font-bold text-white">
                                    Signed Agreement
                                </h3>

                                <p className="text-xs text-slate-500">
                                    Auto-generated after registration
                                </p>
                            </div>
                        </div>

                        {agreement ? (
                            <>
                                <div className="mt-5 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-4">
                                    <div className="flex items-center gap-2 text-emerald-300">
                                        <CheckBadgeIcon className="h-5 w-5" />
                                        <span className="text-sm font-semibold">
                                            Agreement Signed
                                        </span>
                                    </div>

                                    <p className="mt-4 text-sm font-semibold text-white">
                                        {agreement.agreement_name}
                                    </p>

                                    <div className="mt-4 space-y-2 text-sm">
                                        <div className="flex justify-between gap-4">
                                            <span className="text-slate-500">
                                                Signed Name
                                            </span>

                                            <span className="text-right text-white">
                                                {agreement.signed_name}
                                            </span>
                                        </div>

                                        <div className="flex justify-between gap-4">
                                            <span className="text-slate-500">
                                                Signed Date
                                            </span>

                                            <span className="text-right text-white">
                                                {agreement.signed_date}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    onClick={() => setPreviewOpen(true)}
                                    className="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-indigo-500 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-400"
                                >
                                    View Signed PDF
                                    <ArrowTopRightOnSquareIcon className="h-4 w-4" />
                                </button>

                                <a
                                    href={agreement.url}
                                    download
                                    className="mt-3 inline-flex w-full items-center justify-center rounded-2xl bg-white/10 px-5 py-3 text-sm font-semibold text-white hover:bg-white/20"
                                >
                                    Download PDF
                                </a>
                            </>
                        ) : (
                            <div className="mt-5 rounded-2xl bg-yellow-400/10 p-4 text-sm text-yellow-300">
                                Agreement PDF is not generated yet.
                            </div>
                        )}
                    </div>

                    <div className="mt-6 rounded-3xl border border-white/10 bg-white/[0.04] p-6">
                        <h3 className="text-lg font-bold text-white">
                            Important Note
                        </h3>

                        <p className="mt-2 text-sm leading-6 text-slate-400">
                            Your signed agreement uses the legal name and date from your registration.
                            If you need to change legal information, please contact admin support.
                        </p>
                    </div>
                </div>
            </div>

            <Dialog open={previewOpen} onClose={setPreviewOpen} className="relative z-50">
                <div className="fixed inset-0 bg-black/80" />

                <div className="fixed inset-0 flex items-center justify-center p-3 sm:p-6">
                    <DialogPanel className="flex h-[90vh] w-full max-w-6xl flex-col overflow-hidden rounded-3xl border border-white/10 bg-slate-950 shadow-2xl">
                        <div className="flex items-center justify-between border-b border-white/10 px-4 py-4 sm:px-6">
                            <div>
                                <DialogTitle className="text-base font-bold text-white sm:text-lg">
                                    Signed Agreement PDF Preview
                                </DialogTitle>

                                <p className="mt-1 text-xs text-slate-400">
                                    {agreement?.agreement_name}
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={() => setPreviewOpen(false)}
                                className="rounded-xl bg-white/10 p-2 text-white hover:bg-white/20"
                            >
                                <XMarkIcon className="h-5 w-5" />
                            </button>
                        </div>

                        <div className="flex-1 bg-slate-900 p-3 sm:p-5">
                            {agreement?.url ? (
                                <iframe
                                    src={agreement.url}
                                    title="Signed Agreement PDF"
                                    className="h-full w-full rounded-2xl border border-white/10 bg-white"
                                />
                            ) : (
                                <div className="flex h-full items-center justify-center rounded-2xl border border-white/10 bg-black/30 text-sm text-slate-400">
                                    PDF not available.
                                </div>
                            )}
                        </div>

                        <div className="flex flex-col gap-3 border-t border-white/10 px-4 py-4 sm:flex-row sm:justify-end sm:px-6">
                            <a
                                href={agreement?.url || '#'}
                                target="_blank"
                                rel="noreferrer"
                                className="inline-flex justify-center rounded-2xl bg-white/10 px-5 py-3 text-sm font-semibold text-white hover:bg-white/20"
                            >
                                Open in New Tab
                            </a>

                            <a
                                href={agreement?.url || '#'}
                                download
                                className="inline-flex justify-center rounded-2xl bg-indigo-500 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-400"
                            >
                                Download PDF
                            </a>
                        </div>
                    </DialogPanel>
                </div>
            </Dialog>
        </UserDashboardLayout>
    );
}