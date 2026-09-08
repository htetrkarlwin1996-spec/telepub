import { Head, Link } from '@inertiajs/react';
import UserDashboardLayout from '@/Layouts/UserDashboardLayout';
import {
    MusicalNoteIcon,
    EyeIcon,
    ClockIcon,
    CheckCircleIcon,
    ShieldCheckIcon,
} from '@heroicons/react/24/outline';

function StatusBadge({ status }) {
    const classes = {
        pending: 'bg-yellow-400/10 text-yellow-300 border-yellow-400/20',
        approved: 'bg-blue-400/10 text-blue-300 border-blue-400/20',
        completed: 'bg-emerald-400/10 text-emerald-300 border-emerald-400/20',
    };

    return (
        <span className={`inline-flex rounded-full border px-3 py-1 text-xs font-semibold ${classes[status] || classes.pending}`}>
            {status}
        </span>
    );
}

function StatusIcon({ status }) {
    if (status === 'completed') {
        return <CheckCircleIcon className="h-6 w-6 text-emerald-300" />;
    }

    if (status === 'approved') {
        return <ShieldCheckIcon className="h-6 w-6 text-blue-300" />;
    }

    return <ClockIcon className="h-6 w-6 text-yellow-300" />;
}

export default function Index({ albums = [] }) {
    return (
        <UserDashboardLayout
            title="Albums"
            subtitle="View albums and song registration information added by admin."
        >
            <Head title="Albums" />

            <div className="rounded-3xl border border-white/10 bg-gradient-to-br from-indigo-500/20 via-slate-900 to-black p-6 shadow-2xl sm:p-8">
                <div className="max-w-3xl">
                    <div className="inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-400/10 px-3 py-1 text-xs font-semibold text-indigo-300">
                        <MusicalNoteIcon className="h-4 w-4" />
                        Music Catalog
                    </div>

                    <h1 className="mt-4 text-2xl font-bold text-white sm:text-4xl">
                        Your Albums
                    </h1>

                    <p className="mt-3 text-sm leading-6 text-slate-300">
                        Albums are added by admin. Song details will be visible only after album status becomes completed.
                    </p>
                </div>
            </div>

            <div className="mt-6 grid gap-4 lg:grid-cols-2">
                {albums.length > 0 ? (
                    albums.map((album) => (
                        <div
                            key={album.id}
                            className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl"
                        >
                            <div className="flex flex-col gap-5 sm:flex-row">
                                <div className="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white/10">
                                    {album.cover_image ? (
                                        <img
                                            src={album.cover_image}
                                            alt={album.album_name}
                                            className="h-full w-full object-cover"
                                        />
                                    ) : (
                                        <MusicalNoteIcon className="h-10 w-10 text-indigo-300" />
                                    )}
                                </div>

                                <div className="flex-1">
                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <h3 className="text-xl font-bold text-white">
                                                {album.album_name}
                                            </h3>

                                            <p className="mt-1 text-sm text-slate-400">
                                                {album.artist_name || 'Unknown Artist'}
                                            </p>
                                        </div>

                                        <StatusBadge status={album.status} />
                                    </div>

                                    <div className="mt-5 grid gap-3 text-sm sm:grid-cols-3">
                                        <div className="rounded-2xl bg-black/30 p-3">
                                            <p className="text-xs text-slate-500">Songs</p>
                                            <p className="mt-1 font-semibold text-white">
                                                {album.songs_count}
                                            </p>
                                        </div>

                                        <div className="rounded-2xl bg-black/30 p-3">
                                            <p className="text-xs text-slate-500">Registered</p>
                                            <p className="mt-1 font-semibold text-white">
                                                {album.registered_date || '-'}
                                            </p>
                                        </div>

                                        <div className="rounded-2xl bg-black/30 p-3">
                                            <p className="text-xs text-slate-500">Status</p>
                                            <div className="mt-1 flex items-center gap-2 font-semibold text-white">
                                                <StatusIcon status={album.status} />
                                                <span>{album.status}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {album.admin_note && (
                                        <div className="mt-4 rounded-2xl bg-yellow-400/10 p-3 text-sm text-yellow-300">
                                            Admin Note: {album.admin_note}
                                        </div>
                                    )}

                                    {album.music_stores?.length > 0 && (
                                        <p className="mt-4 text-xs text-sky-300">
                                            {album.music_stores.length} music stores selected
                                        </p>
                                    )}

                                    <Link
                                        href={route('user.albums.show', album.id)}
                                        className="mt-5 inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-500 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-400"
                                    >
                                        <EyeIcon className="h-4 w-4" />
                                        View Album
                                    </Link>
                                </div>
                            </div>
                        </div>
                    ))
                ) : (
                    <div className="rounded-3xl border border-dashed border-white/10 bg-white/[0.04] p-10 text-center lg:col-span-2">
                        <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10">
                            <MusicalNoteIcon className="h-7 w-7 text-indigo-300" />
                        </div>

                        <h3 className="mt-5 text-lg font-bold text-white">
                            No albums yet
                        </h3>

                        <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-400">
                            Your albums will appear here after admin adds them to your account.
                        </p>
                    </div>
                )}
            </div>
        </UserDashboardLayout>
    );
}
