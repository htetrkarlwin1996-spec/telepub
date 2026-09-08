import { Head, Link } from '@inertiajs/react';
import UserDashboardLayout from '@/Layouts/UserDashboardLayout';
import {
    MusicalNoteIcon,
    ArrowLeftIcon,
    LockClosedIcon,
    CheckCircleIcon,
    PaperAirplaneIcon,
    ClockIcon,
} from '@heroicons/react/24/outline';

function StatusBadge({ status }) {
    const classes = {
        pending: 'bg-yellow-400/10 text-yellow-300 border-yellow-400/20',
        approved: 'bg-blue-400/10 text-blue-300 border-blue-400/20',
        completed: 'bg-emerald-400/10 text-emerald-300 border-emerald-400/20',
        delivery: 'bg-purple-400/10 text-purple-300 border-purple-400/20',
    };

    const labels = {
        pending: 'Pending',
        approved: 'Approved',
        completed: 'Completed',
        delivery: 'Delivery',
    };

    return (
        <span
            className={`inline-flex rounded-full border px-3 py-1 text-xs font-semibold ${
                classes[status] || classes.pending
            }`}
        >
            {labels[status] || status}
        </span>
    );
}

function VisibilityBadge({ status }) {
    if (status === 'delivery') {
        return (
            <div className="inline-flex items-center gap-2 rounded-2xl bg-purple-400/10 px-4 py-2 text-sm font-semibold text-purple-300">
                <PaperAirplaneIcon className="h-4 w-4" />
                Delivery
            </div>
        );
    }

    if (status === 'completed') {
        return (
            <div className="inline-flex items-center gap-2 rounded-2xl bg-emerald-400/10 px-4 py-2 text-sm font-semibold text-emerald-300">
                <CheckCircleIcon className="h-4 w-4" />
                Completed
            </div>
        );
    }

    return (
        <div className="inline-flex items-center gap-2 rounded-2xl bg-yellow-400/10 px-4 py-2 text-sm font-semibold text-yellow-300">
            <LockClosedIcon className="h-4 w-4" />
            Locked until completed
        </div>
    );
}

function StatusInfoCard({ status }) {
    if (status === 'delivery') {
        return (
            <div className="mt-5 rounded-2xl border border-purple-400/20 bg-purple-400/10 p-4">
                <div className="flex items-center gap-2 text-purple-300">
                    <PaperAirplaneIcon className="h-5 w-5" />
                    <p className="text-sm font-semibold">Delivery Status</p>
                </div>
                <p className="mt-2 text-sm leading-6 text-slate-300">
                    BMI Work ID နဲ့ IPI Name ရပြီးပါပြီ။ ဒီ album/song registration data ကို user ဘက်မှာ အပြည့်အစုံမြင်နိုင်ပါတယ်။
                </p>
            </div>
        );
    }

    if (status === 'completed') {
        return (
            <div className="mt-5 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-4">
                <div className="flex items-center gap-2 text-emerald-300">
                    <CheckCircleIcon className="h-5 w-5" />
                    <p className="text-sm font-semibold">Completed Status</p>
                </div>
                <p className="mt-2 text-sm leading-6 text-slate-300">
                    BMI Work ID ရပြီးပါပြီ။ IPI Name မရသေးရင် Optional / Waiting အနေနဲ့ပြထားပါမယ်။
                </p>
            </div>
        );
    }

    return (
        <div className="mt-5 rounded-2xl border border-yellow-400/20 bg-yellow-400/10 p-4">
            <div className="flex items-center gap-2 text-yellow-300">
                <ClockIcon className="h-5 w-5" />
                <p className="text-sm font-semibold">Pending Status</p>
            </div>
            <p className="mt-2 text-sm leading-6 text-slate-300">
                BMI Work ID / IPI Name မရသေးတာကြောင့် song details ကို lock လုပ်ထားပါတယ်။
                Admin က Completed သို့ Delivery ပြောင်းပြီးမှ data များမြင်ရပါမယ်။
            </p>
        </div>
    );
}

export default function Show({ album }) {
    const canViewSongs = album.status === 'completed' || album.status === 'delivery';
    const isDelivery = album.status === 'delivery';

    return (
        <UserDashboardLayout
            title={album.album_name}
            subtitle="Album details and song registration information."
        >
            <Head title={album.album_name} />

            <Link
                href={route('user.albums.index')}
                className="mb-6 inline-flex items-center gap-2 rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20"
            >
                <ArrowLeftIcon className="h-4 w-4" />
                Back to Albums
            </Link>

            <div className="rounded-3xl border border-white/10 bg-gradient-to-br from-indigo-500/20 via-slate-900 to-black p-6 shadow-2xl sm:p-8">
                <div className="flex flex-col gap-6 md:flex-row">
                    <div className="flex h-36 w-36 shrink-0 items-center justify-center overflow-hidden rounded-3xl bg-white/10">
                        {album.cover_image ? (
                            <img
                                src={album.cover_image}
                                alt={album.album_name}
                                className="h-full w-full object-cover"
                            />
                        ) : (
                            <MusicalNoteIcon className="h-14 w-14 text-indigo-300" />
                        )}
                    </div>

                    <div className="flex-1">
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h1 className="text-3xl font-bold text-white sm:text-4xl">
                                    {album.album_name}
                                </h1>

                                <p className="mt-2 text-slate-400">
                                    {album.artist_name || 'Unknown Artist'}
                                </p>
                            </div>

                            <StatusBadge status={album.status} />
                        </div>

                        <div className="mt-6 grid gap-3 sm:grid-cols-3">
                            <div className="rounded-2xl bg-black/30 p-4">
                                <p className="text-xs text-slate-500">Registered Date</p>
                                <p className="mt-1 font-semibold text-white">
                                    {album.registered_date || '-'}
                                </p>
                            </div>

                            <div className="rounded-2xl bg-black/30 p-4">
                                <p className="text-xs text-slate-500">Status</p>
                                <p className="mt-1 font-semibold text-white">
                                    {album.status}
                                </p>
                            </div>

                            <div className="rounded-2xl bg-black/30 p-4">
                                <p className="text-xs text-slate-500">Songs</p>
                                <p className="mt-1 font-semibold text-white">
                                    {album.songs?.length || 0}
                                </p>
                            </div>
                        </div>

                        <StatusInfoCard status={album.status} />

                        {album.admin_note && (
                            <div className="mt-5 rounded-2xl bg-yellow-400/10 p-4 text-sm text-yellow-300">
                                Admin Note: {album.admin_note}
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <div className="mt-6 rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 className="text-lg font-bold text-white">
                            Song List
                        </h3>

                        <p className="mt-1 text-sm text-slate-400">
                            Completed status shows BMI Work ID. Delivery status shows BMI Work ID and IPI Name.
                        </p>
                    </div>

                    <VisibilityBadge status={album.status} />
                </div>

                {!canViewSongs ? (
                    <div className="mt-6 rounded-2xl border border-dashed border-white/10 p-8 text-center">
                        <LockClosedIcon className="mx-auto h-10 w-10 text-slate-600" />
                        <h4 className="mt-4 font-semibold text-white">
                            Song details are not available yet
                        </h4>
                        <p className="mt-2 text-sm text-slate-500">
                            Admin must mark this album as Completed or Delivery before full song information is visible.
                        </p>
                    </div>
                ) : album.songs?.length > 0 ? (
                    <div className="mt-6 overflow-hidden rounded-2xl border border-white/10">
                        <div className="hidden grid-cols-5 gap-4 border-b border-white/10 bg-white/[0.04] px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 md:grid">
                            <div>Song Name</div>
                            <div>Artist Name</div>
                            <div>Registered Date</div>
                            <div>BMI Work ID</div>
                            <div>IPI Name</div>
                        </div>

                        <div className="divide-y divide-white/10">
                            {album.songs.map((song) => (
                                <div
                                    key={song.id}
                                    className="grid gap-4 px-4 py-4 text-sm text-slate-300 md:grid-cols-5"
                                >
                                    <div>
                                        <p className="text-xs text-slate-500 md:hidden">Song Name</p>
                                        <p className="font-semibold text-white">{song.song_name}</p>
                                    </div>

                                    <div>
                                        <p className="text-xs text-slate-500 md:hidden">Artist Name</p>
                                        {song.artist_name || '-'}
                                    </div>

                                    <div>
                                        <p className="text-xs text-slate-500 md:hidden">Registered Date</p>
                                        {song.registered_date || '-'}
                                    </div>

                                    <div>
                                        <p className="text-xs text-slate-500 md:hidden">BMI Work ID</p>
                                        {song.bmi_work_id ? (
                                            <span className="font-semibold text-emerald-300">
                                                {song.bmi_work_id}
                                            </span>
                                        ) : (
                                            <span className="text-yellow-300">Pending</span>
                                        )}
                                    </div>

                                    <div>
                                        <p className="text-xs text-slate-500 md:hidden">IPI Name</p>

                                        {isDelivery ? (
                                            song.bmi_ipi_name ? (
                                                <span className="font-semibold text-purple-300">
                                                    {song.bmi_ipi_name}
                                                </span>
                                            ) : (
                                                <span className="text-yellow-300">Waiting</span>
                                            )
                                        ) : (
                                            <span className="text-slate-500">
                                                Available after Delivery
                                            </span>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                ) : (
                    <div className="mt-6 rounded-2xl border border-dashed border-white/10 p-8 text-center">
                        <MusicalNoteIcon className="mx-auto h-10 w-10 text-slate-600" />
                        <h4 className="mt-4 font-semibold text-white">
                            No songs added
                        </h4>
                        <p className="mt-2 text-sm text-slate-500">
                            Admin has not added songs for this album yet.
                        </p>
                    </div>
                )}
            </div>
        </UserDashboardLayout>
    );
}