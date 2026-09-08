import { Head, router, useForm } from '@inertiajs/react';
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/react';
import { useState } from 'react';
import {
    ArrowDownTrayIcon,
    EyeIcon,
    XMarkIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    PaperAirplaneIcon,
} from '@heroicons/react/24/outline';

const statusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'processing', label: 'Processing' },
    { value: 'completed', label: 'Completed' },
    { value: 'rejected', label: 'Rejected' },
];

function StatusBadge({ status }) {
    const classes = {
        pending: 'bg-yellow-400/10 text-yellow-300 border-yellow-400/20',
        processing: 'bg-blue-400/10 text-blue-300 border-blue-400/20',
        completed: 'bg-emerald-400/10 text-emerald-300 border-emerald-400/20',
        rejected: 'bg-red-400/10 text-red-300 border-red-400/20',
    };

    return (
        <span className={`inline-flex rounded-full border px-3 py-1 text-xs font-semibold ${classes[status] || classes.pending}`}>
            {status}
        </span>
    );
}

function InfoBox({ label, value }) {
    return (
        <div className="rounded-2xl bg-black/30 p-4">
            <p className="text-xs text-slate-500">{label}</p>
            <p className="mt-1 whitespace-pre-wrap break-words text-sm font-semibold text-white">
                {value || '-'}
            </p>
        </div>
    );
}

export default function Index({ requests, filters, pageStatus }) {
    const [selectedRequest, setSelectedRequest] = useState(null);
    const [modalOpen, setModalOpen] = useState(false);

    const { data, setData, patch, processing, errors, reset, clearErrors } = useForm({
        status: 'pending',
        admin_note: '',
    });

    const openRequest = (item) => {
        setSelectedRequest(item);
        clearErrors();

        setData({
            status: item.status || 'pending',
            admin_note: item.admin_note || '',
        });

        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setSelectedRequest(null);
        reset();
        clearErrors();
    };

    const updateRequest = (e) => {
        e.preventDefault();

        if (!selectedRequest) {
            return;
        }

        patch(route('admin.takedowns.update', selectedRequest.id), {
            preserveScroll: true,
            onSuccess: closeModal,
        });
    };

    const filterByStatus = (status) => {
        router.get(
            route('admin.takedowns.index'),
            status ? { status } : {},
            {
                preserveScroll: true,
                preserveState: true,
            }
        );
    };

    return (
        <AdminDashboardLayout
            title="Take Down Requests"
            subtitle="Review and process user take down requests."
        >
            <Head title="Admin Take Downs" />

            <div className="rounded-3xl border border-white/10 bg-gradient-to-br from-red-500/20 via-slate-900 to-black p-6 shadow-2xl sm:p-8">
                <div className="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div className="inline-flex items-center gap-2 rounded-full border border-red-400/20 bg-red-400/10 px-3 py-1 text-xs font-semibold text-red-300">
                            <ArrowDownTrayIcon className="h-4 w-4" />
                            DMCA / Take Down Control
                        </div>

                        <h1 className="mt-4 text-2xl font-bold text-white sm:text-4xl">
                            Take Down Requests
                        </h1>

                        <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                            Manage submitted links, process requests and notify users when completed or rejected.
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        <button
                            type="button"
                            onClick={() => filterByStatus(null)}
                            className={`rounded-2xl px-4 py-2.5 text-sm font-semibold ${
                                !filters?.status
                                    ? 'bg-red-500 text-white'
                                    : 'bg-white/10 text-slate-300 hover:bg-white/20'
                            }`}
                        >
                            All
                        </button>

                        {statusOptions.map((item) => (
                            <button
                                key={item.value}
                                type="button"
                                onClick={() => filterByStatus(item.value)}
                                className={`rounded-2xl px-4 py-2.5 text-sm font-semibold ${
                                    filters?.status === item.value
                                        ? 'bg-red-500 text-white'
                                        : 'bg-white/10 text-slate-300 hover:bg-white/20'
                                }`}
                            >
                                {item.label}
                            </button>
                        ))}
                    </div>
                </div>
            </div>

            {pageStatus && (
                <div className="mt-6 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                    {pageStatus}
                </div>
            )}

            <div className="mt-6 space-y-4">
                {requests.data.length > 0 ? (
                    requests.data.map((item) => (
                        <div
                            key={item.id}
                            className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl"
                        >
                            <div className="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                <div>
                                    <div className="flex flex-wrap items-center gap-3">
                                        <h3 className="text-xl font-bold text-white">
                                            {item.song_name}
                                        </h3>
                                        <StatusBadge status={item.status} />
                                    </div>

                                    <p className="mt-2 text-sm text-slate-400">
                                        User: {item.user.name} — {item.user.email}
                                    </p>

                                    <p className="mt-1 text-xs text-slate-500">
                                        Submitted at {item.created_at}
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    onClick={() => openRequest(item)}
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500 px-5 py-3 text-sm font-semibold text-white hover:bg-red-400"
                                >
                                    <EyeIcon className="h-4 w-4" />
                                    Review
                                </button>
                            </div>

                            <div className="mt-5 grid gap-4 lg:grid-cols-3">
                                <InfoBox label="Album Name" value={item.album_name} />
                                <InfoBox label="Status" value={item.status} />
                                <InfoBox label="Updated" value={item.updated_at} />
                            </div>

                            <div className="mt-4 rounded-2xl bg-black/30 p-4">
                                <p className="text-xs text-slate-500">Links</p>
                                <p className="mt-2 line-clamp-3 whitespace-pre-wrap break-words text-sm text-slate-300">
                                    {item.links}
                                </p>
                            </div>

                            {item.admin_note && (
                                <div className="mt-4 rounded-2xl bg-yellow-400/10 p-4 text-sm text-yellow-300">
                                    Admin Note: {item.admin_note}
                                </div>
                            )}
                        </div>
                    ))
                ) : (
                    <div className="rounded-3xl border border-dashed border-white/10 bg-white/[0.04] p-10 text-center">
                        <ArrowDownTrayIcon className="mx-auto h-10 w-10 text-slate-600" />

                        <h3 className="mt-4 font-semibold text-white">
                            No take down requests found
                        </h3>

                        <p className="mt-2 text-sm text-slate-500">
                            User submitted take down requests will appear here.
                        </p>
                    </div>
                )}
            </div>

            {requests.links?.length > 3 && (
                <div className="mt-6 flex flex-wrap gap-2">
                    {requests.links.map((link, index) => (
                        <button
                            key={index}
                            type="button"
                            disabled={!link.url}
                            onClick={() => link.url && router.visit(link.url)}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                            className={[
                                'rounded-xl px-4 py-2 text-sm font-semibold',
                                link.active
                                    ? 'bg-red-500 text-white'
                                    : 'bg-white/10 text-slate-300 hover:bg-white/20',
                                !link.url ? 'cursor-not-allowed opacity-50' : '',
                            ].join(' ')}
                        />
                    ))}
                </div>
            )}

            <Dialog open={modalOpen} onClose={closeModal} className="relative z-50">
                <div className="fixed inset-0 bg-black/80" />

                <div className="fixed inset-0 flex items-center justify-center p-3 sm:p-6">
                    <DialogPanel className="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-3xl border border-white/10 bg-slate-950 shadow-2xl">
                        <div className="flex items-center justify-between border-b border-white/10 px-5 py-4">
                            <div>
                                <DialogTitle className="text-lg font-bold text-white">
                                    Review Take Down Request
                                </DialogTitle>

                                <p className="mt-1 text-xs text-slate-400">
                                    Update status and add admin note.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={closeModal}
                                className="rounded-xl bg-white/10 p-2 text-white hover:bg-white/20"
                            >
                                <XMarkIcon className="h-5 w-5" />
                            </button>
                        </div>

                        {selectedRequest && (
                            <div className="overflow-y-auto p-5">
                                <div className="grid gap-4 lg:grid-cols-2">
                                    <InfoBox label="User" value={`${selectedRequest.user.name} — ${selectedRequest.user.email}`} />
                                    <InfoBox label="Song Name" value={selectedRequest.song_name} />
                                    <InfoBox label="Album Name" value={selectedRequest.album_name} />
                                    <InfoBox label="Submitted At" value={selectedRequest.created_at} />
                                </div>

                                <div className="mt-4 rounded-2xl bg-black/30 p-4">
                                    <p className="text-xs text-slate-500">Links</p>
                                    <p className="mt-2 whitespace-pre-wrap break-words text-sm text-slate-300">
                                        {selectedRequest.links}
                                    </p>
                                </div>

                                {selectedRequest.reason && (
                                    <div className="mt-4 rounded-2xl bg-black/30 p-4">
                                        <p className="text-xs text-slate-500">Reason</p>
                                        <p className="mt-2 whitespace-pre-wrap break-words text-sm text-slate-300">
                                            {selectedRequest.reason}
                                        </p>
                                    </div>
                                )}

                                <form onSubmit={updateRequest} className="mt-6 space-y-5">
                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-slate-300">
                                            Status
                                        </label>

                                        <select
                                            value={data.status}
                                            onChange={(e) => setData('status', e.target.value)}
                                            className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-red-500 focus:ring-red-500"
                                        >
                                            {statusOptions.map((item) => (
                                                <option key={item.value} value={item.value}>
                                                    {item.label}
                                                </option>
                                            ))}
                                        </select>

                                        {errors.status && (
                                            <p className="mt-2 text-sm text-red-400">
                                                {errors.status}
                                            </p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-slate-300">
                                            Admin Note
                                        </label>

                                        <textarea
                                            rows="5"
                                            value={data.admin_note || ''}
                                            onChange={(e) => setData('admin_note', e.target.value)}
                                            placeholder="Write note for user..."
                                            className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none placeholder:text-slate-600 focus:border-red-500 focus:ring-red-500"
                                        />

                                        {errors.admin_note && (
                                            <p className="mt-2 text-sm text-red-400">
                                                {errors.admin_note}
                                            </p>
                                        )}
                                    </div>

                                    <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                                        <div className="flex items-start gap-3">
                                            {data.status === 'completed' ? (
                                                <CheckCircleIcon className="mt-0.5 h-5 w-5 text-emerald-300" />
                                            ) : data.status === 'rejected' ? (
                                                <ExclamationTriangleIcon className="mt-0.5 h-5 w-5 text-red-300" />
                                            ) : data.status === 'processing' ? (
                                                <PaperAirplaneIcon className="mt-0.5 h-5 w-5 text-blue-300" />
                                            ) : (
                                                <ClockIcon className="mt-0.5 h-5 w-5 text-yellow-300" />
                                            )}

                                            <p className="text-sm leading-6 text-slate-400">
                                                Completed သို့ Rejected ပြောင်းလျှင် user ဆီ email notification ပို့ပါမယ်။
                                                Pending / Processing ပြောင်းရင် email မပို့ပါဘူး။
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex flex-col-reverse gap-3 border-t border-white/10 pt-5 sm:flex-row sm:justify-end">
                                        <button
                                            type="button"
                                            onClick={closeModal}
                                            className="rounded-2xl bg-white/10 px-5 py-3 text-sm font-semibold text-white hover:bg-white/20"
                                        >
                                            Cancel
                                        </button>

                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="rounded-2xl bg-red-500 px-5 py-3 text-sm font-semibold text-white hover:bg-red-400 disabled:opacity-60"
                                        >
                                            {processing ? 'Updating...' : 'Update Request'}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        )}
                    </DialogPanel>
                </div>
            </Dialog>
        </AdminDashboardLayout>
    );
}