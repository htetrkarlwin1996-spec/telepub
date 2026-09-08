import { Head, Link, router, useForm } from '@inertiajs/react';
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/react';
import { useState } from 'react';
import {
    ArrowLeftIcon,
    BanknotesIcon,
    DocumentTextIcon,
    CheckCircleIcon,
    XCircleIcon,
    UserCircleIcon,
    CreditCardIcon,
    MusicalNoteIcon,
    ArrowDownTrayIcon,
    XMarkIcon,
} from '@heroicons/react/24/outline';

function InfoBox({ label, value }) {
    return (
        <div className="rounded-2xl bg-black/30 p-4">
            <p className="text-xs text-slate-500">{label}</p>
            <p className="mt-1 break-words text-sm font-semibold text-white">
                {value || '-'}
            </p>
        </div>
    );
}

function StatusBadge({ active }) {
    return active ? (
        <span className="inline-flex items-center gap-1 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-300">
            <CheckCircleIcon className="h-4 w-4" />
            Active
        </span>
    ) : (
        <span className="inline-flex items-center gap-1 rounded-full border border-red-400/20 bg-red-400/10 px-3 py-1 text-xs font-semibold text-red-300">
            <XCircleIcon className="h-4 w-4" />
            Inactive
        </span>
    );
}

export default function Show({
    selectedUser,
    profile,
    wallet,
    agreement,
    paymentMethods = [],
    albums = [],
    payoutRequests = [],
    takedownRequests = [],
    status,
}) {
    const [previewOpen, setPreviewOpen] = useState(false);

    const walletForm = useForm({
        action: 'add',
        amount: '',
    });

    const submitWallet = (e) => {
        e.preventDefault();

        walletForm.patch(route('admin.users.wallet.update', selectedUser.id), {
            preserveScroll: true,
            onSuccess: () => walletForm.reset('amount'),
        });
    };

    const toggleStatus = () => {
        router.patch(
            route('admin.users.status.update', selectedUser.id),
            { is_active: !selectedUser.is_active },
            { preserveScroll: true }
        );
    };

    return (
        <AdminDashboardLayout
            title={selectedUser.name}
            subtitle="User profile, wallet, agreement and activity overview."
        >
            <Head title={`User - ${selectedUser.name}`} />

            <Link
                href={route('admin.users.index')}
                className="mb-6 inline-flex items-center gap-2 rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20"
            >
                <ArrowLeftIcon className="h-4 w-4" />
                Back to Users
            </Link>

            {status && (
                <div className="mb-6 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                    {status}
                </div>
            )}

            <div className="rounded-3xl border border-white/10 bg-gradient-to-br from-red-500/20 via-slate-900 to-black p-6 shadow-2xl sm:p-8">
                <div className="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div className="flex items-center gap-4">
                        <div className="flex h-16 w-16 items-center justify-center rounded-3xl bg-red-500 text-white">
                            <UserCircleIcon className="h-9 w-9" />
                        </div>

                        <div>
                            <h1 className="text-2xl font-bold text-white sm:text-4xl">
                                {selectedUser.name}
                            </h1>
                            <p className="mt-1 text-sm text-slate-400">
                                {selectedUser.email}
                            </p>
                            <div className="mt-3">
                                <StatusBadge active={selectedUser.is_active} />
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        onClick={toggleStatus}
                        className={[
                            'rounded-2xl px-5 py-3 text-sm font-semibold',
                            selectedUser.is_active
                                ? 'bg-red-500/10 text-red-300 hover:bg-red-500/20'
                                : 'bg-emerald-500/10 text-emerald-300 hover:bg-emerald-500/20',
                        ].join(' ')}
                    >
                        {selectedUser.is_active ? 'Disable User' : 'Enable User'}
                    </button>
                </div>
            </div>

            <div className="mt-6 grid gap-6 lg:grid-cols-3">
                <div className="lg:col-span-2 space-y-6">
                    <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                        <h3 className="text-lg font-bold text-white">Profile Information</h3>

                        <div className="mt-6 grid gap-4 sm:grid-cols-2">
                            <InfoBox label="Display Name" value={profile?.display_name} />
                            <InfoBox label="Artist Name" value={profile?.artist_name} />
                            <InfoBox label="Publisher Name" value={profile?.publisher_name} />
                            <InfoBox label="IPI Name" value={profile?.ipi_name} />
                            <InfoBox label="IPI Number" value={profile?.ipi_number} />
                            <InfoBox label="Country" value={profile?.country} />
                            <InfoBox label="City" value={profile?.city} />
                            <InfoBox label="Address" value={profile?.address} />
                        </div>
                    </div>

                    <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                        <h3 className="text-lg font-bold text-white">Albums</h3>

                        <div className="mt-6 space-y-3">
                            {albums.length > 0 ? (
                                albums.map((album) => (
                                    <div key={album.id} className="rounded-2xl bg-black/30 p-4">
                                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <p className="font-semibold text-white">{album.album_name}</p>
                                                <p className="mt-1 text-xs text-slate-500">
                                                    {album.artist_name || '-'} • {album.songs_count} songs
                                                </p>
                                            </div>

                                            <span className="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-slate-300">
                                                {album.status}
                                            </span>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="rounded-2xl border border-dashed border-white/10 p-8 text-center">
                                    <MusicalNoteIcon className="mx-auto h-10 w-10 text-slate-600" />
                                    <p className="mt-3 text-sm text-slate-500">No albums yet.</p>
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                        <h3 className="text-lg font-bold text-white">Recent Payout Requests</h3>

                        <div className="mt-6 space-y-3">
                            {payoutRequests.length > 0 ? (
                                payoutRequests.slice(0, 5).map((payout) => (
                                    <div key={payout.id} className="rounded-2xl bg-black/30 p-4">
                                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <p className="font-semibold text-white">
                                                    {payout.amount} {payout.currency}
                                                </p>
                                                <p className="mt-1 text-xs text-slate-500">
                                                    {payout.payment_method || '-'} • {payout.created_at}
                                                </p>
                                            </div>

                                            <span className="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-slate-300">
                                                {payout.status}
                                            </span>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <p className="text-sm text-slate-500">No payout requests.</p>
                            )}
                        </div>
                    </div>

                    <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                        <h3 className="text-lg font-bold text-white">Take Down Requests</h3>

                        <div className="mt-6 space-y-3">
                            {takedownRequests.length > 0 ? (
                                takedownRequests.slice(0, 5).map((item) => (
                                    <div key={item.id} className="rounded-2xl bg-black/30 p-4">
                                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <p className="font-semibold text-white">{item.song_name}</p>
                                                <p className="mt-1 text-xs text-slate-500">
                                                    {item.album_name || '-'} • {item.created_at}
                                                </p>
                                            </div>

                                            <span className="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-slate-300">
                                                {item.status}
                                            </span>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <p className="text-sm text-slate-500">No takedown requests.</p>
                            )}
                        </div>
                    </div>
                </div>

                <div className="space-y-6">
                    <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                        <div className="flex items-center gap-3">
                            <div className="rounded-2xl bg-white/10 p-3">
                                <BanknotesIcon className="h-6 w-6 text-red-300" />
                            </div>

                            <div>
                                <h3 className="text-lg font-bold text-white">Wallet</h3>
                                <p className="text-xs text-slate-500">Manual balance control</p>
                            </div>
                        </div>

                        <div className="mt-6 rounded-2xl bg-black/30 p-4">
                            <p className="text-xs text-slate-500">Current Balance</p>
                            <p className="mt-1 text-2xl font-bold text-white">
                                {wallet.balance} {wallet.currency}
                            </p>
                        </div>

                        <form onSubmit={submitWallet} className="mt-5 space-y-4">
                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-300">
                                    Action
                                </label>
                                <select
                                    value={walletForm.data.action}
                                    onChange={(e) => walletForm.setData('action', e.target.value)}
                                    className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-red-500 focus:ring-red-500"
                                >
                                    <option value="add">Add Balance</option>
                                    <option value="subtract">Subtract Balance</option>
                                    <option value="set">Set Balance</option>
                                </select>
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-300">
                                    Amount
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    value={walletForm.data.amount}
                                    onChange={(e) => walletForm.setData('amount', e.target.value)}
                                    className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-red-500 focus:ring-red-500"
                                />
                                {walletForm.errors.amount && (
                                    <p className="mt-2 text-sm text-red-400">
                                        {walletForm.errors.amount}
                                    </p>
                                )}
                            </div>

                            <button
                                type="submit"
                                disabled={walletForm.processing}
                                className="w-full rounded-2xl bg-red-500 px-5 py-3 text-sm font-semibold text-white hover:bg-red-400 disabled:opacity-60"
                            >
                                {walletForm.processing ? 'Updating...' : 'Update Wallet'}
                            </button>
                        </form>
                    </div>

                    <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                        <div className="flex items-center gap-3">
                            <div className="rounded-2xl bg-white/10 p-3">
                                <DocumentTextIcon className="h-6 w-6 text-red-300" />
                            </div>

                            <div>
                                <h3 className="text-lg font-bold text-white">Agreement</h3>
                                <p className="text-xs text-slate-500">Signed PDF</p>
                            </div>
                        </div>

                        {agreement ? (
                            <>
                                <div className="mt-5 rounded-2xl bg-black/30 p-4">
                                    <p className="font-semibold text-white">{agreement.agreement_name}</p>
                                    <p className="mt-1 text-sm text-slate-500">
                                        Signed by {agreement.signed_name}
                                    </p>
                                    <p className="mt-1 text-sm text-slate-500">
                                        Date: {agreement.signed_date}
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    onClick={() => setPreviewOpen(true)}
                                    className="mt-5 w-full rounded-2xl bg-red-500 px-5 py-3 text-sm font-semibold text-white hover:bg-red-400"
                                >
                                    View Agreement PDF
                                </button>
                            </>
                        ) : (
                            <p className="mt-5 text-sm text-slate-500">No agreement found.</p>
                        )}
                    </div>

                    <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                        <div className="flex items-center gap-3">
                            <div className="rounded-2xl bg-white/10 p-3">
                                <CreditCardIcon className="h-6 w-6 text-red-300" />
                            </div>

                            <div>
                                <h3 className="text-lg font-bold text-white">Payment Methods</h3>
                                <p className="text-xs text-slate-500">User payout accounts</p>
                            </div>
                        </div>

                        <div className="mt-5 space-y-3">
                            {paymentMethods.length > 0 ? (
                                paymentMethods.map((method) => (
                                    <div key={method.id} className="rounded-2xl bg-black/30 p-4">
                                        <div className="flex items-center justify-between gap-3">
                                            <p className="font-semibold text-white">{method.type_label}</p>
                                            {method.is_default && (
                                                <span className="rounded-full bg-emerald-400/10 px-2 py-1 text-xs text-emerald-300">
                                                    Default
                                                </span>
                                            )}
                                        </div>

                                        <p className="mt-1 text-sm text-slate-500">
                                            {method.account_name}
                                        </p>

                                        <p className="mt-1 text-xs text-slate-600">
                                            {method.account_number || method.phone_number || '-'}
                                        </p>
                                    </div>
                                ))
                            ) : (
                                <p className="text-sm text-slate-500">No payment methods.</p>
                            )}
                        </div>
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
                                    Agreement PDF Preview
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
                                    title="Agreement PDF"
                                    className="h-full w-full rounded-2xl border border-white/10 bg-white"
                                />
                            ) : (
                                <div className="flex h-full items-center justify-center rounded-2xl border border-white/10 bg-black/30 text-sm text-slate-400">
                                    PDF not available.
                                </div>
                            )}
                        </div>
                    </DialogPanel>
                </div>
            </Dialog>
        </AdminDashboardLayout>
    );
}