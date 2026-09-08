import { Head, Link, useForm, router } from '@inertiajs/react';
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/react';
import { useState } from 'react';
import {
    ArrowLeftIcon,
    MusicalNoteIcon,
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    XMarkIcon,
    CheckCircleIcon,
    ClockIcon,
    BanknotesIcon,
} from '@heroicons/react/24/outline';

const statusOptions = [
    { value: 'pending', label: 'Pending - waiting BMI / IPI' },
    { value: 'completed', label: 'Completed - BMI Work ID ready' },
    { value: 'delivery', label: 'Delivery - IPI Name ready' },
];

const registrationTypes = [
    { value: 'single', label: 'Single Album', fee: 5000 },
    { value: 'ep', label: 'EP - up to 5 songs', fee: 25000 },
    { value: 'album_12', label: 'Album - up to 12 songs', fee: 50000 },
    { value: 'album_over_12', label: 'Album - over 12 songs', fee: 100000 },
];

const musicStores = [
    'Spotify', 'Apple Music', 'YouTube Music', 'Amazon Music', 'Deezer',
    'TIDAL', 'TikTok', 'Facebook & Instagram', 'SoundCloud', 'Pandora',
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

function Field({ label, error, children }) {
    return (
        <div>
            <label className="mb-2 block text-sm font-medium text-slate-300">
                {label}
            </label>

            {children}

            {error && <p className="mt-2 text-sm text-red-400">{error}</p>}
        </div>
    );
}

function TextInput({ value, onChange, placeholder = '', type = 'text' }) {
    return (
        <input
            type={type}
            value={value || ''}
            onChange={onChange}
            placeholder={placeholder}
            className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none placeholder:text-slate-600 focus:border-red-500 focus:ring-red-500"
        />
    );
}

export default function Show({ album, status }) {
    const [songModalOpen, setSongModalOpen] = useState(false);
    const [editingSong, setEditingSong] = useState(null);

    const albumForm = useForm({
        album_name: album.album_name || '',
        artist_name: album.artist_name || '',
        registration_type: album.registration_type || 'single',
        registration_fee: album.raw_registration_fee || 5000,
        registered_date: album.registered_date || '',
        status: album.status || 'pending',
        admin_note: album.admin_note || '',
        music_stores: album.music_stores || [],
    });

    const songForm = useForm({
        song_name: '',
        artist_name: '',
        registered_date: '',
        bmi_work_id: '',
        bmi_ipi_name: '',
    });

    const handleRegistrationTypeChange = (value) => {
        const selected = registrationTypes.find((item) => item.value === value);

        albumForm.setData({
            ...albumForm.data,
            registration_type: selected?.value || 'single',
            registration_fee: selected?.fee || 5000,
        });
    };

    const updateAlbum = (e) => {
        e.preventDefault();

        albumForm.patch(route('admin.albums.update', album.id), {
            preserveScroll: true,
        });
    };

    const toggleMusicStore = (store) => {
        albumForm.setData('music_stores', albumForm.data.music_stores.includes(store)
            ? albumForm.data.music_stores.filter((item) => item !== store)
            : [...albumForm.data.music_stores, store]);
    };

    const openAddSong = () => {
        setEditingSong(null);
        songForm.clearErrors();

        songForm.setData({
            song_name: '',
            artist_name: album.artist_name || '',
            registered_date: '',
            bmi_work_id: '',
            bmi_ipi_name: '',
        });

        setSongModalOpen(true);
    };

    const openEditSong = (song) => {
        setEditingSong(song);
        songForm.clearErrors();

        songForm.setData({
            song_name: song.song_name || '',
            artist_name: song.artist_name || '',
            registered_date: song.registered_date || '',
            bmi_work_id: song.bmi_work_id || '',
            bmi_ipi_name: song.bmi_ipi_name || '',
        });

        setSongModalOpen(true);
    };

    const closeSongModal = () => {
        setSongModalOpen(false);
        setEditingSong(null);
        songForm.clearErrors();
    };

    const submitSong = (e) => {
        e.preventDefault();

        if (editingSong) {
            songForm.patch(route('admin.songs.update', editingSong.id), {
                preserveScroll: true,
                onSuccess: closeSongModal,
            });
        } else {
            songForm.post(route('admin.albums.songs.store', album.id), {
                preserveScroll: true,
                onSuccess: closeSongModal,
            });
        }
    };

    const deleteSong = (song) => {
        if (!confirm(`Delete song "${song.song_name}"?`)) {
            return;
        }

        router.delete(route('admin.songs.destroy', song.id), {
            preserveScroll: true,
        });
    };

    return (
        <AdminDashboardLayout
            title={album.album_name}
            subtitle="Manage album status, registration fee, BMI Work ID and IPI Name."
        >
            <Head title={`Admin Album - ${album.album_name}`} />

            <Link
                href={route('admin.albums.index')}
                className="mb-6 inline-flex items-center gap-2 rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20"
            >
                <ArrowLeftIcon className="h-4 w-4" />
                Back to Albums
            </Link>

            {status && (
                <div className="mb-6 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                    {status}
                </div>
            )}

            <div className="rounded-3xl border border-white/10 bg-gradient-to-br from-red-500/20 via-slate-900 to-black p-6 shadow-2xl sm:p-8">
                <div className="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                    <div>
                        <div className="inline-flex items-center gap-2 rounded-full border border-red-400/20 bg-red-400/10 px-3 py-1 text-xs font-semibold text-red-300">
                            <MusicalNoteIcon className="h-4 w-4" />
                            Album Management
                        </div>

                        <h1 className="mt-4 text-2xl font-bold text-white sm:text-4xl">
                            {album.album_name}
                        </h1>

                        <p className="mt-2 text-sm text-slate-400">
                            User: {album.user.name} — {album.user.email}
                        </p>
                    </div>

                    <div className="flex flex-col items-start gap-3 md:items-end">
                        <StatusBadge status={album.status} />

                        <div className="inline-flex items-center gap-2 rounded-2xl bg-emerald-400/10 px-4 py-2 text-sm font-semibold text-emerald-300">
                            <BanknotesIcon className="h-4 w-4" />
                            {album.registration_fee || '0.00'} MMK
                        </div>

                        <p className="text-xs text-slate-500">
                            {album.registration_type_label || '-'}
                        </p>
                    </div>
                </div>

                <div className="mt-6 grid gap-3 sm:grid-cols-4">
                    <div className="rounded-2xl bg-black/30 p-4">
                        <p className="text-xs text-slate-500">Pending</p>
                        <p className="mt-1 text-sm font-semibold text-yellow-300">
                            BMI / IPI မရသေး
                        </p>
                    </div>

                    <div className="rounded-2xl bg-black/30 p-4">
                        <p className="text-xs text-slate-500">Completed</p>
                        <p className="mt-1 text-sm font-semibold text-emerald-300">
                            BMI Work ID ရပြီး
                        </p>
                    </div>

                    <div className="rounded-2xl bg-black/30 p-4">
                        <p className="text-xs text-slate-500">Delivery</p>
                        <p className="mt-1 text-sm font-semibold text-purple-300">
                            IPI Name ပါရပြီး
                        </p>
                    </div>

                    <div className="rounded-2xl bg-black/30 p-4">
                        <p className="text-xs text-slate-500">Register Fee</p>
                        <p className="mt-1 text-sm font-semibold text-emerald-300">
                            {album.registration_fee || '0.00'} MMK
                        </p>
                    </div>
                </div>
            </div>

            <div className="mt-6 grid gap-6 lg:grid-cols-3">
                <div className="lg:col-span-1">
                    <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                        <h3 className="text-lg font-bold text-white">Album Info</h3>

                        <p className="mt-1 text-sm text-slate-400">
                            Update album information, package fee and visibility status.
                        </p>

                        <form onSubmit={updateAlbum} className="mt-6 space-y-5">
                            <Field label="Album Name" error={albumForm.errors.album_name}>
                                <TextInput
                                    value={albumForm.data.album_name}
                                    onChange={(e) => albumForm.setData('album_name', e.target.value)}
                                />
                            </Field>

                            <Field label="Artist Name" error={albumForm.errors.artist_name}>
                                <TextInput
                                    value={albumForm.data.artist_name}
                                    onChange={(e) => albumForm.setData('artist_name', e.target.value)}
                                />
                            </Field>

                            <Field label="Registration Type" error={albumForm.errors.registration_type}>
                                <select
                                    value={albumForm.data.registration_type}
                                    onChange={(e) => handleRegistrationTypeChange(e.target.value)}
                                    className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-red-500 focus:ring-red-500"
                                >
                                    {registrationTypes.map((type) => (
                                        <option key={type.value} value={type.value}>
                                            {type.label} - {formatMoney(type.fee)} MMK
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            <Field label="Registration Fee" error={albumForm.errors.registration_fee}>
                                <TextInput
                                    type="number"
                                    value={albumForm.data.registration_fee}
                                    onChange={(e) =>
                                        albumForm.setData('registration_fee', e.target.value)
                                    }
                                />
                                <p className="mt-2 text-xs text-slate-500">
                                    Current fee: {formatMoney(albumForm.data.registration_fee)} MMK
                                </p>
                            </Field>

                            <Field label="Registered Date" error={albumForm.errors.registered_date}>
                                <TextInput
                                    type="date"
                                    value={albumForm.data.registered_date}
                                    onChange={(e) =>
                                        albumForm.setData('registered_date', e.target.value)
                                    }
                                />
                            </Field>

                            <Field label="Album Status" error={albumForm.errors.status}>
                                <select
                                    value={albumForm.data.status}
                                    onChange={(e) => albumForm.setData('status', e.target.value)}
                                    className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-red-500 focus:ring-red-500"
                                >
                                    {statusOptions.map((item) => (
                                        <option key={item.value} value={item.value}>
                                            {item.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            <Field label="Admin Note" error={albumForm.errors.admin_note}>
                                <textarea
                                    rows="4"
                                    value={albumForm.data.admin_note || ''}
                                    onChange={(e) => albumForm.setData('admin_note', e.target.value)}
                                    className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none placeholder:text-slate-600 focus:border-red-500 focus:ring-red-500"
                                />
                            </Field>

                            <Field label="Music Stores" error={albumForm.errors.music_stores}>
                                <div className="mb-3 flex justify-end">
                                    <button
                                        type="button"
                                        onClick={() => albumForm.setData('music_stores', albumForm.data.music_stores.length === musicStores.length ? [] : [...musicStores])}
                                        className="text-xs font-semibold text-red-300 hover:text-red-200"
                                    >
                                        {albumForm.data.music_stores.length === musicStores.length ? 'Clear all' : 'Select all'}
                                    </button>
                                </div>
                                <div className="grid gap-2 sm:grid-cols-2">
                                    {musicStores.map((store) => (
                                        <label key={store} className="flex cursor-pointer items-center gap-3 rounded-xl border border-white/10 bg-black/20 px-3 py-2.5 text-sm text-slate-300 hover:bg-white/5">
                                            <input
                                                type="checkbox"
                                                checked={albumForm.data.music_stores.includes(store)}
                                                onChange={() => toggleMusicStore(store)}
                                                className="rounded border-white/20 bg-black/30 text-red-500 focus:ring-red-500"
                                            />
                                            {store}
                                        </label>
                                    ))}
                                </div>
                            </Field>

                            <button
                                type="submit"
                                disabled={albumForm.processing}
                                className="w-full rounded-2xl bg-red-500 px-5 py-3 text-sm font-semibold text-white hover:bg-red-400 disabled:opacity-60"
                            >
                                {albumForm.processing ? 'Updating...' : 'Update Album'}
                            </button>
                        </form>
                    </div>
                </div>

                <div className="lg:col-span-2">
                    <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 className="text-lg font-bold text-white">Songs</h3>
                                <p className="mt-1 text-sm text-slate-400">
                                    Add songs and fill BMI Work ID / IPI Name when available.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={openAddSong}
                                className="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500 px-5 py-3 text-sm font-semibold text-white hover:bg-red-400"
                            >
                                <PlusIcon className="h-5 w-5" />
                                Add Song
                            </button>
                        </div>

                        <div className="mt-6 space-y-4">
                            {album.songs.length > 0 ? (
                                album.songs.map((song) => (
                                    <div
                                        key={song.id}
                                        className="rounded-2xl border border-white/10 bg-black/30 p-4"
                                    >
                                        <div className="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                            <div>
                                                <h4 className="text-lg font-bold text-white">
                                                    {song.song_name}
                                                </h4>

                                                <p className="mt-1 text-sm text-slate-400">
                                                    {song.artist_name}
                                                </p>
                                            </div>

                                            <div className="flex flex-col gap-2 sm:flex-row">
                                                <button
                                                    type="button"
                                                    onClick={() => openEditSong(song)}
                                                    className="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/20"
                                                >
                                                    <PencilSquareIcon className="h-4 w-4" />
                                                    Edit
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() => deleteSong(song)}
                                                    className="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500/10 px-4 py-2.5 text-sm font-semibold text-red-300 hover:bg-red-500/20"
                                                >
                                                    <TrashIcon className="h-4 w-4" />
                                                    Delete
                                                </button>
                                            </div>
                                        </div>

                                        <div className="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                            <div className="rounded-xl bg-white/[0.04] p-3">
                                                <p className="text-xs text-slate-500">Registered Date</p>

                                                <p className="mt-1 text-sm font-semibold text-white">
                                                    {song.registered_date || '-'}
                                                </p>
                                            </div>

                                            <div className="rounded-xl bg-white/[0.04] p-3">
                                                <p className="text-xs text-slate-500">BMI Work ID</p>

                                                <p className="mt-1 text-sm font-semibold text-emerald-300">
                                                    {song.bmi_work_id || 'Pending'}
                                                </p>
                                            </div>

                                            <div className="rounded-xl bg-white/[0.04] p-3">
                                                <p className="text-xs text-slate-500">IPI Name</p>

                                                <p className="mt-1 text-sm font-semibold text-purple-300">
                                                    {song.bmi_ipi_name || 'Waiting'}
                                                </p>
                                            </div>

                                            <div className="rounded-xl bg-white/[0.04] p-3">
                                                <p className="text-xs text-slate-500">Visibility</p>

                                                <div className="mt-1 flex items-center gap-2">
                                                    {song.bmi_work_id ? (
                                                        <CheckCircleIcon className="h-4 w-4 text-emerald-300" />
                                                    ) : (
                                                        <ClockIcon className="h-4 w-4 text-yellow-300" />
                                                    )}

                                                    <span className="text-sm font-semibold text-white">
                                                        {song.bmi_ipi_name
                                                            ? 'Delivery'
                                                            : song.bmi_work_id
                                                                ? 'Completed'
                                                                : 'Pending'}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="rounded-2xl border border-dashed border-white/10 p-10 text-center">
                                    <MusicalNoteIcon className="mx-auto h-10 w-10 text-slate-600" />

                                    <h4 className="mt-4 font-semibold text-white">
                                        No songs yet
                                    </h4>

                                    <p className="mt-2 text-sm text-slate-500">
                                        Add songs for this album.
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <Dialog open={songModalOpen} onClose={closeSongModal} className="relative z-50">
                <div className="fixed inset-0 bg-black/80" />

                <div className="fixed inset-0 flex items-center justify-center p-3 sm:p-6">
                    <DialogPanel className="w-full max-w-2xl overflow-hidden rounded-3xl border border-white/10 bg-slate-950 shadow-2xl">
                        <div className="flex items-center justify-between border-b border-white/10 px-5 py-4">
                            <div>
                                <DialogTitle className="text-lg font-bold text-white">
                                    {editingSong ? 'Edit Song' : 'Add Song'}
                                </DialogTitle>

                                <p className="mt-1 text-xs text-slate-400">
                                    BMI Work ID can be added later. IPI Name can be added when delivered.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={closeSongModal}
                                className="rounded-xl bg-white/10 p-2 text-white hover:bg-white/20"
                            >
                                <XMarkIcon className="h-5 w-5" />
                            </button>
                        </div>

                        <form onSubmit={submitSong} className="space-y-5 p-5">
                            <div className="grid gap-5 sm:grid-cols-2">
                                <Field label="Song Name" error={songForm.errors.song_name}>
                                    <TextInput
                                        value={songForm.data.song_name}
                                        onChange={(e) =>
                                            songForm.setData('song_name', e.target.value)
                                        }
                                        placeholder="Song name"
                                    />
                                </Field>

                                <Field label="Artist Name" error={songForm.errors.artist_name}>
                                    <TextInput
                                        value={songForm.data.artist_name}
                                        onChange={(e) =>
                                            songForm.setData('artist_name', e.target.value)
                                        }
                                        placeholder="Artist name"
                                    />
                                </Field>

                                <Field label="Registered Date" error={songForm.errors.registered_date}>
                                    <TextInput
                                        type="date"
                                        value={songForm.data.registered_date}
                                        onChange={(e) =>
                                            songForm.setData('registered_date', e.target.value)
                                        }
                                    />
                                </Field>

                                <Field label="BMI Work ID" error={songForm.errors.bmi_work_id}>
                                    <TextInput
                                        value={songForm.data.bmi_work_id}
                                        onChange={(e) =>
                                            songForm.setData('bmi_work_id', e.target.value)
                                        }
                                        placeholder="BMI Work ID"
                                    />
                                </Field>

                                <Field label="IPI Name" error={songForm.errors.bmi_ipi_name}>
                                    <TextInput
                                        value={songForm.data.bmi_ipi_name}
                                        onChange={(e) =>
                                            songForm.setData('bmi_ipi_name', e.target.value)
                                        }
                                        placeholder="IPI Name"
                                    />
                                </Field>
                            </div>

                            <div className="rounded-2xl bg-white/[0.04] p-4 text-sm text-slate-400">
                                <p>
                                    If BMI Work ID is empty, user will see Pending.
                                    If BMI Work ID is filled, user can see Completed data.
                                    If IPI Name is filled, user will see Delivery.
                                </p>
                            </div>

                            <div className="flex flex-col-reverse gap-3 border-t border-white/10 pt-5 sm:flex-row sm:justify-end">
                                <button
                                    type="button"
                                    onClick={closeSongModal}
                                    className="rounded-2xl bg-white/10 px-5 py-3 text-sm font-semibold text-white hover:bg-white/20"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    disabled={songForm.processing}
                                    className="rounded-2xl bg-red-500 px-5 py-3 text-sm font-semibold text-white hover:bg-red-400 disabled:opacity-60"
                                >
                                    {songForm.processing
                                        ? 'Saving...'
                                        : editingSong
                                            ? 'Update Song'
                                            : 'Save Song'}
                                </button>
                            </div>
                        </form>
                    </DialogPanel>
                </div>
            </Dialog>
        </AdminDashboardLayout>
    );
}
