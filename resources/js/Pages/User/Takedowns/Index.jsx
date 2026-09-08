import { Head, useForm } from '@inertiajs/react';
import UserDashboardLayout from '@/Layouts/UserDashboardLayout';
import {
    ArrowDownTrayIcon,
    PaperAirplaneIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/react/24/outline';

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

export default function Index({ requests = [], status }) {
    const { data, setData, post, processing, errors, reset, recentlySuccessful } = useForm({
        song_name: '',
        album_name: '',
        links: '',
        reason: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('user.takedowns.store'), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    return (
        <UserDashboardLayout
            title="Take Down Requests"
            subtitle="Submit DMCA or unauthorized use takedown requests."
        >
            <Head title="Take Down Requests" />

            <div className="rounded-3xl border border-white/10 bg-gradient-to-br from-red-500/20 via-slate-900 to-black p-6 shadow-2xl sm:p-8">
                <div className="max-w-3xl">
                    <div className="inline-flex items-center gap-2 rounded-full border border-red-400/20 bg-red-400/10 px-3 py-1 text-xs font-semibold text-red-300">
                        <ArrowDownTrayIcon className="h-4 w-4" />
                        DMCA / Take Down
                    </div>

                    <h1 className="mt-4 text-2xl font-bold text-white sm:text-4xl">
                        Take Down Requests
                    </h1>

                    <p className="mt-3 text-sm leading-6 text-slate-300">
                        Submit song name, album name and links where unauthorized content is published. Admin will review and process your request.
                    </p>
                </div>
            </div>

            {status && (
                <div className="mt-6 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                    {status}
                </div>
            )}

            {recentlySuccessful && (
                <div className="mt-6 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                    Take down request submitted successfully.
                </div>
            )}

            <div className="mt-6 grid gap-6 lg:grid-cols-3">
                <div className="lg:col-span-1">
                    <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                        <h3 className="text-lg font-bold text-white">
                            Submit Request
                        </h3>

                        <p className="mt-1 text-sm text-slate-400">
                            Add song, album and infringing links.
                        </p>

                        <form onSubmit={submit} className="mt-6 space-y-5">
                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-300">
                                    Song Name
                                </label>
                                <input
                                    type="text"
                                    value={data.song_name}
                                    onChange={(e) => setData('song_name', e.target.value)}
                                    placeholder="Song name"
                                    className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none placeholder:text-slate-600 focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.song_name && (
                                    <p className="mt-2 text-sm text-red-400">{errors.song_name}</p>
                                )}
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-300">
                                    Album Name Optional
                                </label>
                                <input
                                    type="text"
                                    value={data.album_name}
                                    onChange={(e) => setData('album_name', e.target.value)}
                                    placeholder="Album name"
                                    className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none placeholder:text-slate-600 focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.album_name && (
                                    <p className="mt-2 text-sm text-red-400">{errors.album_name}</p>
                                )}
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-300">
                                    Links
                                </label>
                                <textarea
                                    rows="5"
                                    value={data.links}
                                    onChange={(e) => setData('links', e.target.value)}
                                    placeholder="Paste one or more links here"
                                    className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none placeholder:text-slate-600 focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.links && (
                                    <p className="mt-2 text-sm text-red-400">{errors.links}</p>
                                )}
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-300">
                                    Reason Optional
                                </label>
                                <textarea
                                    rows="4"
                                    value={data.reason}
                                    onChange={(e) => setData('reason', e.target.value)}
                                    placeholder="Explain the issue"
                                    className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none placeholder:text-slate-600 focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {errors.reason && (
                                    <p className="mt-2 text-sm text-red-400">{errors.reason}</p>
                                )}
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-indigo-500 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-400 disabled:opacity-60"
                            >
                                <PaperAirplaneIcon className="h-4 w-4" />
                                {processing ? 'Submitting...' : 'Submit Request'}
                            </button>
                        </form>
                    </div>
                </div>

                <div className="lg:col-span-2">
                    <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                        <h3 className="text-lg font-bold text-white">
                            Request History
                        </h3>

                        <p className="mt-1 text-sm text-slate-400">
                            Track your submitted take down requests.
                        </p>

                        <div className="mt-6 space-y-4">
                            {requests.length > 0 ? (
                                requests.map((item) => (
                                    <div
                                        key={item.id}
                                        className="rounded-2xl border border-white/10 bg-black/30 p-4"
                                    >
                                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <h4 className="text-lg font-bold text-white">
                                                    {item.song_name}
                                                </h4>

                                                <p className="mt-1 text-sm text-slate-500">
                                                    {item.album_name || 'No album name'} • {item.created_at}
                                                </p>
                                            </div>

                                            <StatusBadge status={item.status} />
                                        </div>

                                        <div className="mt-4 rounded-xl bg-white/[0.04] p-3">
                                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Links
                                            </p>
                                            <p className="mt-2 whitespace-pre-wrap break-words text-sm text-slate-300">
                                                {item.links}
                                            </p>
                                        </div>

                                        {item.reason && (
                                            <div className="mt-3 rounded-xl bg-white/[0.04] p-3">
                                                <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                    Reason
                                                </p>
                                                <p className="mt-2 whitespace-pre-wrap text-sm text-slate-300">
                                                    {item.reason}
                                                </p>
                                            </div>
                                        )}

                                        {item.admin_note && (
                                            <div className="mt-3 rounded-xl bg-yellow-400/10 p-3 text-sm text-yellow-300">
                                                Admin Note: {item.admin_note}
                                            </div>
                                        )}
                                    </div>
                                ))
                            ) : (
                                <div className="rounded-2xl border border-dashed border-white/10 p-8 text-center">
                                    <ClockIcon className="mx-auto h-10 w-10 text-slate-600" />

                                    <h4 className="mt-4 font-semibold text-white">
                                        No requests yet
                                    </h4>

                                    <p className="mt-2 text-sm text-slate-500">
                                        Your take down request history will appear here.
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </UserDashboardLayout>
    );
}