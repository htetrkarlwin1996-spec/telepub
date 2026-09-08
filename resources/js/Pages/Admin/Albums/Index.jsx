import { Head, Link, useForm, router } from '@inertiajs/react';
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/react';
import { useState } from 'react';
import {
    MusicalNoteIcon,
    PlusIcon,
    EyeIcon,
    XMarkIcon,
    BanknotesIcon,
} from '@heroicons/react/24/outline';

const registrationTypes = [
    { value: 'single', label: 'Single Album', fee: 5000 },
    { value: 'ep', label: 'EP - up to 5 songs', fee: 25000 },
    { value: 'album_12', label: 'Album - up to 12 songs', fee: 50000 },
    { value: 'album_over_12', label: 'Album - over 12 songs', fee: 100000 },
];

function formatMoney(amount) {
    return new Intl.NumberFormat('en-US').format(Number(amount || 0));
}

function StatusBadge({ status }) {
    const classes = {
        pending: 'bg-yellow-400/10 text-yellow-300 border-yellow-400/20',
        approved: 'bg-blue-400/10 text-blue-300 border-blue-400/20',
        completed: 'bg-emerald-400/10 text-emerald-300 border-emerald-400/20',
        delivery: 'bg-purple-400/10 text-purple-300 border-purple-400/20',
    };

    return (
        <span
            className={`inline-flex rounded-full border px-3 py-1 text-xs font-semibold ${
                classes[status] || classes.pending
            }`}
        >
            {status}
        </span>
    );
}

export default function Index({ albums, users = [], status }) {
    const [open, setOpen] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        user_id: '',
        album_name: '',
        artist_name: '',
        registration_type: 'single',
        registration_fee: 5000,
        registered_date: '',
        admin_note: '',
    });

    const openCreate = () => {
        reset();

        setData({
            user_id: '',
            album_name: '',
            artist_name: '',
            registration_type: 'single',
            registration_fee: 5000,
            registered_date: '',
            admin_note: '',
        });

        setOpen(true);
    };

    const handleRegistrationTypeChange = (value) => {
        const selected = registrationTypes.find((item) => item.value === value);

        setData({
            ...data,
            registration_type: selected?.value || 'single',
            registration_fee: selected?.fee || 5000,
        });
    };

    const submit = (e) => {
        e.preventDefault();

        post(route('admin.albums.store'), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setOpen(false);
            },
        });
    };

    return (
        <AdminDashboardLayout
            title="Albums Management"
            subtitle="Add albums for users and manage publishing status."
        >
            <Head title="Admin Albums" />

            <div className="rounded-3xl border border-white/10 bg-gradient-to-br from-red-500/20 via-slate-900 to-black p-6 shadow-2xl sm:p-8">
                <div className="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="inline-flex items-center gap-2 rounded-full border border-red-400/20 bg-red-400/10 px-3 py-1 text-xs font-semibold text-red-300">
                            <MusicalNoteIcon className="h-4 w-4" />
                            Album Control
                        </div>

                        <h1 className="mt-4 text-2xl font-bold text-white sm:text-4xl">
                            Albums Management
                        </h1>

                        <p className="mt-3 text-sm leading-6 text-slate-300">
                            Add albums with registration fee. Pending = no BMI/IPI yet,
                            Completed = BMI Work ID available, Delivery = IPI Name delivered.
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={openCreate}
                        className="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500 px-5 py-3 text-sm font-semibold text-white hover:bg-red-400"
                    >
                        <PlusIcon className="h-5 w-5" />
                        Add Album
                    </button>
                </div>
            </div>

            {status && (
                <div className="mt-6 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                    {status}
                </div>
            )}

            <div className="mt-6 overflow-hidden rounded-3xl border border-white/10 bg-white/[0.04] shadow-xl">
                <div className="hidden grid-cols-8 gap-4 border-b border-white/10 bg-white/[0.04] px-5 py-4 text-xs font-semibold uppercase tracking-wide text-slate-500 lg:grid">
                    <div className="col-span-2">Album</div>
                    <div>User</div>
                    <div>Songs</div>
                    <div>Fee</div>
                    <div>Status</div>
                    <div>Date</div>
                    <div className="text-right">Action</div>
                </div>

                <div className="divide-y divide-white/10">
                    {albums?.data?.length > 0 ? (
                        albums.data.map((album) => (
                            <div
                                key={album.id}
                                className="grid gap-4 px-5 py-5 text-sm text-slate-300 lg:grid-cols-8 lg:items-center"
                            >
                                <div className="lg:col-span-2">
                                    <p className="font-semibold text-white">{album.album_name}</p>
                                    <p className="mt-1 text-xs text-slate-500">
                                        {album.artist_name || '-'}
                                    </p>
                                    <p className="mt-1 text-xs text-slate-600">
                                        {album.registration_type_label || '-'}
                                    </p>
                                </div>

                                <div>
                                    <p className="font-semibold text-white">{album.user.name}</p>
                                    <p className="mt-1 text-xs text-slate-500">{album.user.email}</p>
                                </div>

                                <div>
                                    <p className="lg:hidden text-xs text-slate-500">Songs</p>
                                    {album.songs_count}
                                </div>

                                <div>
                                    <p className="lg:hidden text-xs text-slate-500">Fee</p>
                                    <span className="inline-flex items-center gap-1 font-semibold text-emerald-300">
                                        <BanknotesIcon className="h-4 w-4" />
                                        {album.registration_fee} MMK
                                    </span>
                                </div>

                                <div>
                                    <StatusBadge status={album.status} />
                                </div>

                                <div>
                                    <p className="lg:hidden text-xs text-slate-500">Date</p>
                                    {album.registered_date || '-'}
                                </div>

                                <div className="lg:text-right">
                                    <Link
                                        href={route('admin.albums.show', album.id)}
                                        className="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-400"
                                    >
                                        <EyeIcon className="h-4 w-4" />
                                        Manage
                                    </Link>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="p-10 text-center">
                            <MusicalNoteIcon className="mx-auto h-10 w-10 text-slate-600" />
                            <h3 className="mt-4 font-semibold text-white">No albums found</h3>
                            <p className="mt-2 text-sm text-slate-500">
                                Albums created by admin will appear here.
                            </p>
                        </div>
                    )}
                </div>
            </div>

            {albums?.links?.length > 3 && (
                <div className="mt-6 flex flex-wrap gap-2">
                    {albums.links.map((link, index) => (
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

            <Dialog open={open} onClose={setOpen} className="relative z-50">
                <div className="fixed inset-0 bg-black/80" />

                <div className="fixed inset-0 flex items-center justify-center p-3 sm:p-6">
                    <DialogPanel className="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl border border-white/10 bg-slate-950 p-6 shadow-2xl">
                        <div className="mb-6 flex items-center justify-between">
                            <div>
                                <DialogTitle className="text-lg font-bold text-white">
                                    Add Album
                                </DialogTitle>
                                <p className="mt-1 text-xs text-slate-400">
                                    Select package type and registration fee.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={() => setOpen(false)}
                                className="rounded-xl bg-white/10 p-2 text-white hover:bg-white/20"
                            >
                                <XMarkIcon className="h-5 w-5" />
                            </button>
                        </div>

                        <form onSubmit={submit} className="space-y-5">
                            <div>
                                <label className="mb-2 block text-sm text-slate-300">User</label>

                                <select
                                    value={data.user_id}
                                    onChange={(e) => setData('user_id', e.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white"
                                >
                                    <option value="">Select user</option>

                                    {users.map((user) => (
                                        <option key={user.id} value={user.id}>
                                            {user.name} - {user.email}
                                        </option>
                                    ))}
                                </select>

                                {errors.user_id && (
                                    <p className="mt-2 text-sm text-red-400">{errors.user_id}</p>
                                )}
                            </div>

                            <div>
                                <label className="mb-2 block text-sm text-slate-300">
                                    Album Name
                                </label>

                                <input
                                    value={data.album_name}
                                    onChange={(e) => setData('album_name', e.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white"
                                />

                                {errors.album_name && (
                                    <p className="mt-2 text-sm text-red-400">{errors.album_name}</p>
                                )}
                            </div>

                            <div>
                                <label className="mb-2 block text-sm text-slate-300">
                                    Artist Name
                                </label>

                                <input
                                    value={data.artist_name}
                                    onChange={(e) => setData('artist_name', e.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white"
                                />

                                {errors.artist_name && (
                                    <p className="mt-2 text-sm text-red-400">{errors.artist_name}</p>
                                )}
                            </div>

                            <div className="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label className="mb-2 block text-sm text-slate-300">
                                        Registration Type
                                    </label>

                                    <select
                                        value={data.registration_type}
                                        onChange={(e) => handleRegistrationTypeChange(e.target.value)}
                                        className="w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white"
                                    >
                                        {registrationTypes.map((type) => (
                                            <option key={type.value} value={type.value}>
                                                {type.label} - {formatMoney(type.fee)} MMK
                                            </option>
                                        ))}
                                    </select>

                                    {errors.registration_type && (
                                        <p className="mt-2 text-sm text-red-400">
                                            {errors.registration_type}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm text-slate-300">
                                        Registration Fee
                                    </label>

                                    <input
                                        type="number"
                                        value={data.registration_fee}
                                        onChange={(e) => setData('registration_fee', e.target.value)}
                                        className="w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white"
                                    />

                                    <p className="mt-2 text-xs text-slate-500">
                                        Current fee: {formatMoney(data.registration_fee)} MMK
                                    </p>

                                    {errors.registration_fee && (
                                        <p className="mt-2 text-sm text-red-400">
                                            {errors.registration_fee}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="mb-2 block text-sm text-slate-300">
                                    Registered Date
                                </label>

                                <input
                                    type="date"
                                    value={data.registered_date}
                                    onChange={(e) => setData('registered_date', e.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white"
                                />

                                {errors.registered_date && (
                                    <p className="mt-2 text-sm text-red-400">
                                        {errors.registered_date}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label className="mb-2 block text-sm text-slate-300">
                                    Admin Note
                                </label>

                                <textarea
                                    rows="3"
                                    value={data.admin_note}
                                    onChange={(e) => setData('admin_note', e.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white"
                                />

                                {errors.admin_note && (
                                    <p className="mt-2 text-sm text-red-400">
                                        {errors.admin_note}
                                    </p>
                                )}
                            </div>

                            <button
                                disabled={processing}
                                className="w-full rounded-2xl bg-red-500 px-5 py-3 text-sm font-semibold text-white hover:bg-red-400 disabled:opacity-60"
                            >
                                {processing ? 'Saving...' : 'Save Album'}
                            </button>
                        </form>
                    </DialogPanel>
                </div>
            </Dialog>
        </AdminDashboardLayout>
    );
}